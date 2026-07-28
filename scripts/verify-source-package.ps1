[CmdletBinding()]
param(
    [Parameter(Mandatory)][string]$PackagePath,
    [Parameter(Mandatory)][string]$Database,
    [Parameter(Mandatory)][ValidatePattern('^[0-9a-fA-F]{40}$')][string]$ExpectedCommit,
    [string]$ComposerCommand = 'composer',
    [string]$PhpCommand = 'php',
    [string]$MysqlCommand = 'C:\xampp\mysql\bin\mysql.exe',
    [string]$DatabaseUser = 'root',
    [string]$DatabasePassword = '',
    [int]$ApachePort = 8766
)

$ErrorActionPreference = 'Stop'
$package = [System.IO.Path]::GetFullPath($PackagePath)
$expectedCommitId = $ExpectedCommit.ToLowerInvariant()

if (-not (Test-Path -LiteralPath $package -PathType Leaf)) {
    throw "Package does not exist: $package"
}

if ($Database -notmatch '^[A-Za-z0-9_]+_test$') {
    throw 'Database must be a disposable name ending in _test.'
}
$applicationDatabase = $Database + '_app'
if ($applicationDatabase.Length -gt 64) {
    throw 'Database name is too long to derive the disposable application database.'
}

$temporaryDirectory = Join-Path ([System.IO.Path]::GetTempPath()) ('guidemypc-package-check-' + [Guid]::NewGuid().ToString('N'))
$extractionDirectory = Join-Path $temporaryDirectory 'extracted'
$backendDirectory = Join-Path $extractionDirectory 'GuideMyPC\backend'
$privateDirectory = Join-Path $temporaryDirectory 'private'
$databaseCleanupRequired = $false
$verificationPassed = $false
$verificationFailure = $null

function Invoke-CheckedCommand {
    param(
        [Parameter(Mandatory)][string]$Command,
        [Parameter(Mandatory)][string[]]$Arguments,
        [Parameter(Mandatory)][string]$FailureMessage,
        [string]$WorkingDirectory = ''
    )

    if ($WorkingDirectory -ne '') {
        Push-Location $WorkingDirectory
    }

    try {
        & $Command @Arguments
        if ($LASTEXITCODE -ne 0) {
            throw $FailureMessage
        }
    } finally {
        if ($WorkingDirectory -ne '') {
            Pop-Location
        }
    }
}

function Get-MysqlArguments {
    param([string[]]$Additional)

    $arguments = @('-u', $DatabaseUser)
    if ($DatabasePassword -ne '') {
        $arguments += "--password=$DatabasePassword"
    }
    return $arguments + $Additional
}

try {
    New-Item -ItemType Directory -Path $extractionDirectory | Out-Null
    New-Item -ItemType Directory -Path $privateDirectory | Out-Null
    Expand-Archive -LiteralPath $package -DestinationPath $extractionDirectory

    $topLevelEntries = @(Get-ChildItem -LiteralPath $extractionDirectory -Force)
    if ($topLevelEntries.Count -ne 1 -or -not $topLevelEntries[0].PSIsContainer -or $topLevelEntries[0].Name -ne 'GuideMyPC') {
        throw 'Package must contain exactly one top-level GuideMyPC directory.'
    }

    foreach ($requiredPath in @(
        'GuideMyPC\index.php',
        'GuideMyPC\.htaccess',
        'GuideMyPC\frontend\public\assets',
        'GuideMyPC\frontend\resources\views',
        'GuideMyPC\backend\public\index.php',
        'GuideMyPC\backend\public\assets\css\style.css',
        'GuideMyPC\backend\public\assets\css\design-system.css',
        'GuideMyPC\backend\public\assets\js\script.js',
        'GuideMyPC\backend\public\assets\js\guide-editor.js',
        'GuideMyPC\backend\public\assets\js\chart.umd.min.js',
        'GuideMyPC\backend\composer.json',
        'GuideMyPC\backend\composer.lock',
        'GuideMyPC\database\migrate.php',
        'GuideMyPC\docs',
        'GuideMyPC\docs\screenshots',
        'GuideMyPC\uml\source\GuideMyPC.vpp',
        'GuideMyPC\uml\exports\use-case.png',
        'GuideMyPC\uml\exports\class-diagram.png',
        'GuideMyPC\uml\exports\activity-diagram.png',
        'GuideMyPC\uml\exports\state-machine.png',
        'GuideMyPC\README.md',
        'GuideMyPC\PACKAGE-MANIFEST.txt'
    )) {
        if (-not (Test-Path -LiteralPath (Join-Path $extractionDirectory $requiredPath))) {
            throw "Package is missing required path: $requiredPath"
        }
    }

    $packageRoot = Join-Path $extractionDirectory 'GuideMyPC'
    $screenshots = @(Get-ChildItem -LiteralPath (Join-Path $packageRoot 'docs\screenshots') -File -Filter '*.png')
    if ($screenshots.Count -lt 8 -or $screenshots.Count -gt 10) {
        throw "Package must contain 8-10 reviewed PNG screenshots; found $($screenshots.Count)."
    }
    $prohibitedPatterns = @(
        '(^|\\)\.env$',
        '(^|\\)(vendor|node_modules|logs|uploads|storage|coverage|backups|sessions|cache)(\\|$)',
        '(^|\\)(\.git|\.idea|\.vscode|\.code-review-graph)(\\|$)',
        '(^|\\)opencode\.json$',
        '(\.pem|\.key|\.p12|\.pfx|\.sql\.gz|\.bak|\.zip)$'
    )
    $prohibited = Get-ChildItem -LiteralPath $packageRoot -Force -Recurse | Where-Object {
        $relative = $_.FullName.Substring($packageRoot.Length + 1)
        $prohibitedPatterns | Where-Object { $relative -match $_ }
    }
    if ($prohibited) {
        throw "Package contains prohibited paths:`n$($prohibited.FullName -join "`n")"
    }

    $manifestPath = Join-Path $packageRoot 'PACKAGE-MANIFEST.txt'
    $manifestLines = [System.IO.File]::ReadAllLines($manifestPath)
    $releaseCommitLines = @($manifestLines | Where-Object { $_ -match '^Release commit: ' })
    if ($releaseCommitLines.Count -ne 1 -or $releaseCommitLines[0] -ne "Release commit: $expectedCommitId") {
        throw 'Package manifest is not bound to the expected release commit.'
    }

    $manifestEntries = @($manifestLines | Where-Object { $_ -match '^([A-F0-9]{64})  (.+)$' })
    $packagedFiles = @(Get-ChildItem -LiteralPath $packageRoot -File -Recurse | Where-Object { $_.FullName -ne $manifestPath })
    if ($manifestEntries.Count -ne $packagedFiles.Count) {
        throw "Manifest count $($manifestEntries.Count) does not match package file count $($packagedFiles.Count)."
    }

    $manifestPaths = @{}
    $packagePaths = @{}
    foreach ($file in $packagedFiles) {
        $relativePath = $file.FullName.Substring($packageRoot.Length + 1).Replace('\', '/')
        $packagePaths[$relativePath.ToLowerInvariant()] = $true
    }

    foreach ($entry in $manifestEntries) {
        $null = $entry -match '^([A-F0-9]{64})  (.+)$'
        $expectedHash = $Matches[1]
        $relativePath = $Matches[2].Replace('\', '/')
        if ($relativePath -eq '' -or $relativePath.StartsWith('/') -or $relativePath -match '(^|/)\.\.?(/|$)' -or $relativePath.Contains(':')) {
            throw "Manifest contains an unsafe path: $relativePath"
        }
        $pathKey = $relativePath.ToLowerInvariant()
        if ($manifestPaths.ContainsKey($pathKey)) {
            throw "Manifest contains a duplicate path: $relativePath"
        }
        $manifestPaths[$pathKey] = $true
        if (-not $packagePaths.ContainsKey($pathKey)) {
            throw "Manifest path is missing from the package: $relativePath"
        }
        $filePath = [System.IO.Path]::GetFullPath((Join-Path $packageRoot $relativePath.Replace('/', '\')))
        $packagePrefix = $packageRoot.TrimEnd('\') + '\'
        if (-not $filePath.StartsWith($packagePrefix, [System.StringComparison]::OrdinalIgnoreCase) -or -not (Test-Path -LiteralPath $filePath -PathType Leaf)) {
            throw "Manifest path escapes the package root: $relativePath"
        }
        $actualHash = (Get-FileHash -Algorithm SHA256 -LiteralPath $filePath).Hash
        if ($actualHash -ne $expectedHash) {
            throw "Manifest checksum mismatch: $relativePath"
        }
    }

    foreach ($relativePath in $packagePaths.Keys) {
        if (-not $manifestPaths.ContainsKey($relativePath)) {
            throw "Package file is missing from the manifest: $relativePath"
        }
    }

    Invoke-CheckedCommand -Command $ComposerCommand -Arguments @('install', '--no-interaction', '--no-progress', "--working-dir=$backendDirectory") -FailureMessage 'Composer install failed in the clean extraction.'

    $privatePath = $privateDirectory.Replace('\', '/')
    $environment = [System.IO.File]::ReadAllText((Join-Path $backendDirectory '.env.example'))
    $environment = [regex]::Replace($environment, '(?m)^APP_URL=.*$', "APP_URL=http://127.0.0.1:$ApachePort")
    $environment = [regex]::Replace($environment, '(?m)^# APP_PRIVATE_PATH=.*$', "APP_PRIVATE_PATH=$privatePath")
    $environment = [regex]::Replace($environment, '(?m)^DB_NAME=.*$', "DB_NAME=$applicationDatabase")
    $environment = [regex]::Replace($environment, '(?m)^DB_TEST_NAME=.*$', "DB_TEST_NAME=$Database")
    $environment = [regex]::Replace($environment, '(?m)^DB_USER=.*$', "DB_USER=$DatabaseUser")
    $environment = [regex]::Replace($environment, '(?m)^DB_PASSWORD=.*$', "DB_PASSWORD=$DatabasePassword")
    [System.IO.File]::WriteAllText((Join-Path $backendDirectory '.env'), $environment, [System.Text.UTF8Encoding]::new($false))

    $createSql = "DROP DATABASE IF EXISTS $applicationDatabase; DROP DATABASE IF EXISTS $Database; CREATE DATABASE $applicationDatabase CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE DATABASE $Database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    $databaseCleanupRequired = $true
    Invoke-CheckedCommand -Command $MysqlCommand -Arguments (Get-MysqlArguments -Additional @('--execute', $createSql)) -FailureMessage 'Unable to create the disposable package databases.'

    Invoke-CheckedCommand -Command $PhpCommand -Arguments @('database/migrate.php', "--database=$applicationDatabase") -FailureMessage 'Fresh application-database migration failed.' -WorkingDirectory $backendDirectory
    Invoke-CheckedCommand -Command $PhpCommand -Arguments @('database/migrate.php', "--database=$applicationDatabase") -FailureMessage 'Repeat application-database migration failed.' -WorkingDirectory $backendDirectory
    Invoke-CheckedCommand -Command $PhpCommand -Arguments @('database/seed.php', "--database=$applicationDatabase") -FailureMessage 'Application-database seed failed.' -WorkingDirectory $backendDirectory
    Invoke-CheckedCommand -Command $PhpCommand -Arguments @('database/migrate.php', "--database=$Database") -FailureMessage 'Fresh package migration failed.' -WorkingDirectory $backendDirectory
    Invoke-CheckedCommand -Command $PhpCommand -Arguments @('database/migrate.php', "--database=$Database") -FailureMessage 'Repeat package migration failed.' -WorkingDirectory $backendDirectory
    Invoke-CheckedCommand -Command $PhpCommand -Arguments @('database/seed.php', "--database=$Database") -FailureMessage 'Package seed failed.' -WorkingDirectory $backendDirectory
    Invoke-CheckedCommand -Command $PhpCommand -Arguments @('scripts/verify.php', "--database=$Database") -FailureMessage 'Package full verification failed.' -WorkingDirectory $backendDirectory
    $publicRootScript = Join-Path $backendDirectory 'scripts\verify-public-root.ps1'
    Invoke-CheckedCommand -Command 'powershell.exe' -Arguments @('-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', $publicRootScript, '-Port', $ApachePort.ToString(), '-RequireCategoryIcons', '-Mode', 'PublicRoot') -FailureMessage 'Package backend/public verification failed.' -WorkingDirectory $backendDirectory
    $environment = [regex]::Replace($environment, '(?m)^APP_URL=.*$', 'APP_URL=')
    [System.IO.File]::WriteAllText((Join-Path $backendDirectory '.env'), $environment, [System.Text.UTF8Encoding]::new($false))
    $mountName = 'FinalProject-' + [Guid]::NewGuid().ToString('N').Substring(0, 8)
    Invoke-CheckedCommand -Command 'powershell.exe' -Arguments @('-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', $publicRootScript, '-Port', ($ApachePort + 2).ToString(), '-RequireCategoryIcons', '-Mode', 'PackageRoot', '-PackageRoot', $packageRoot, '-MountName', $mountName) -FailureMessage 'Package localhost subdirectory verification failed.' -WorkingDirectory $backendDirectory
    Invoke-CheckedCommand -Command 'powershell.exe' -Arguments @('-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', $publicRootScript, '-Port', ($ApachePort + 4).ToString(), '-Mode', 'PackageRoot', '-PackageRoot', $packageRoot, '-MountName', $mountName, '-DisableRewrite') -FailureMessage 'Package root did not fail closed without mod_rewrite.' -WorkingDirectory $backendDirectory
    Invoke-CheckedCommand -Command 'powershell.exe' -Arguments @('-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', $publicRootScript, '-Port', ($ApachePort + 6).ToString(), '-Mode', 'PackageRoot', '-PackageRoot', $packageRoot, '-MountName', $mountName, '-VerifyNestedBackendGuard') -FailureMessage 'Package backend/public policy did not block direct browser access.' -WorkingDirectory $backendDirectory
    $verificationPassed = $true
} catch {
    $verificationFailure = $_
} finally {
    $cleanupFailures = [System.Collections.Generic.List[string]]::new()
    try {
        if ($databaseCleanupRequired) {
            $cleanupArguments = Get-MysqlArguments -Additional @('--execute', "DROP DATABASE IF EXISTS $applicationDatabase; DROP DATABASE IF EXISTS $Database;")
            & $MysqlCommand @cleanupArguments
            if ($LASTEXITCODE -ne 0) {
                $cleanupFailures.Add('Unable to remove the disposable package databases.')
            }
        }
    } catch {
        $cleanupFailures.Add('Disposable database cleanup failed: ' + $_.Exception.Message)
    }

    try {
        if (Test-Path -LiteralPath $temporaryDirectory) {
            Remove-Item -LiteralPath $temporaryDirectory -Recurse -Force
        }
    } catch {
        $cleanupFailures.Add('Temporary extraction cleanup failed: ' + $_.Exception.Message)
    }

    if ($cleanupFailures.Count -gt 0) {
        $cleanupMessage = $cleanupFailures -join [Environment]::NewLine
        if ($verificationFailure -ne $null) {
            throw ($verificationFailure.Exception.Message + [Environment]::NewLine + $cleanupMessage)
        }
        throw $cleanupMessage
    }
}

if ($verificationFailure -ne $null) {
    throw $verificationFailure
}

if ($verificationPassed) {
    Write-Host 'PASS: strict source package layout, commit binding, complete manifest, dependency install, isolated application/test database setup and cleanup, full suite, canonical backend/public and localhost package-root checks passed from a clean extraction.'
}
