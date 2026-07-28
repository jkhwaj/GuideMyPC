[CmdletBinding()]
param(
    [int]$Port = 8765,
    [string]$ApacheRoot = 'C:\xampp\apache',
    [string]$PhpRoot = 'C:\xampp\php',
    [string]$ChromePath = 'C:\Program Files\Google\Chrome\Application\chrome.exe',
    [int]$BrowserPort = 0,
    [switch]$RequireCategoryIcons,
    [ValidateSet('PublicRoot', 'PackageRoot')][string]$Mode = 'PublicRoot',
    [string]$PackageRoot = '',
    [string]$MountName = '',
    [switch]$DisableRewrite,
    [switch]$VerifyNestedBackendGuard,
    [string]$DatabaseName = '',
    [string]$AdminEmail = '',
    [string]$AdminPasswordEnvironmentVariable = ''
)

$ErrorActionPreference = 'Stop'
$repositoryRoot = Split-Path -Parent $PSScriptRoot

if ($Port -lt 1024 -or $Port -gt 65535) {
    throw 'Port must be between 1024 and 65535.'
}
if ($BrowserPort -eq 0) {
    $BrowserPort = $Port + 1
}
if ($BrowserPort -lt 1024 -or $BrowserPort -gt 65535 -or $BrowserPort -eq $Port) {
    throw 'BrowserPort must be between 1024 and 65535 and differ from Port.'
}

$httpd = Join-Path $ApacheRoot 'bin\httpd.exe'
$publicRoot = Join-Path $repositoryRoot 'public'

if (-not (Test-Path -LiteralPath $httpd) -or -not (Test-Path -LiteralPath $publicRoot)) {
    throw 'Apache or the public document root is unavailable.'
}

$temporaryDirectory = Join-Path ([System.IO.Path]::GetTempPath()) ('guidemypc-apache-' + [Guid]::NewGuid().ToString('N'))
$configPath = Join-Path $temporaryDirectory 'httpd.conf'
$errorLog = Join-Path $temporaryDirectory 'error.log'
$stderrLog = Join-Path $temporaryDirectory 'stderr.log'
$pidFile = Join-Path $temporaryDirectory 'httpd.pid'
$apacheRootConfig = $ApacheRoot.Replace('\', '/')
$phpRootConfig = $PhpRoot.Replace('\', '/')
$errorLogConfig = $errorLog.Replace('\', '/')
$pidFileConfig = $pidFile.Replace('\', '/')
$process = $null
$browserProcess = $null
$browserProfile = Join-Path $temporaryDirectory 'chrome-profile'
$documentRoot = $publicRoot
$servedDirectory = $publicRoot
$urlPrefix = ''
$previousDatabaseName = $env:DB_NAME
$adminPassword = ''

if (($AdminEmail -eq '') -ne ($AdminPasswordEnvironmentVariable -eq '')) {
    throw 'AdminEmail and AdminPasswordEnvironmentVariable must be supplied together.'
}
if ($AdminEmail -ne '' -and $DatabaseName -eq '') {
    throw 'Authenticated browser checks require an isolated DatabaseName.'
}
if ($AdminPasswordEnvironmentVariable -ne '') {
    if ($AdminPasswordEnvironmentVariable -notmatch '^[A-Z][A-Z0-9_]*$') {
        throw 'AdminPasswordEnvironmentVariable must name an uppercase process environment variable.'
    }
    $adminPassword = [Environment]::GetEnvironmentVariable($AdminPasswordEnvironmentVariable, 'Process')
    if ([string]::IsNullOrWhiteSpace($adminPassword)) {
        throw 'The supplied administrator password environment variable is empty.'
    }
}
if ($DatabaseName -ne '') {
    if ($DatabaseName -notmatch '^[A-Za-z0-9_]+_test$') {
        throw 'DatabaseName must be an isolated _test database.'
    }
    if ($DatabaseName -eq $previousDatabaseName) {
        throw 'DatabaseName must differ from the inherited application database.'
    }
    $env:DB_NAME = $DatabaseName
}

function Stop-ProcessTree {
    param([System.Diagnostics.Process]$RootProcess)

    if ($RootProcess -ne $null -and -not $RootProcess.HasExited) {
        $previousErrorActionPreference = $ErrorActionPreference
        $ErrorActionPreference = 'Continue'
        & cmd.exe /c "taskkill /PID $($RootProcess.Id) /T /F >NUL 2>&1"
        $ErrorActionPreference = $previousErrorActionPreference
        if (-not $RootProcess.HasExited) {
            $RootProcess.WaitForExit()
        }
    }

    # Chrome can detach utility children from its root process on Windows.
    Get-CimInstance Win32_Process -Filter "Name = 'chrome.exe'" |
        Where-Object { $_.CommandLine -like ('*' + $browserProfile + '*') } |
        ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }
}

function Invoke-HttpProbe {
    param(
        [Parameter(Mandatory)][string]$Path,
        [Parameter(Mandatory)][int[]]$ExpectedStatus,
        [string]$Method = 'GET',
        [string]$Contains = '',
        [string]$Excludes = '',
        [string]$ContentType = ''
    )

    $probeId = [Guid]::NewGuid().ToString('N')
    $bodyPath = Join-Path $temporaryDirectory ($probeId + '.body')
    $headerPath = Join-Path $temporaryDirectory ($probeId + '.headers')
    $url = "http://127.0.0.1:$Port$urlPrefix$Path"
    $status = & curl.exe --silent --show-error --max-redirs 0 --request $Method --output $bodyPath --dump-header $headerPath --write-out '%{http_code}' $url

    if ($LASTEXITCODE -ne 0 -or [int]$status -notin $ExpectedStatus) {
        $details = if (Test-Path -LiteralPath $bodyPath) { [System.IO.File]::ReadAllText($bodyPath) } else { 'No response body was created.' }
        throw "HTTP probe failed for $Method $Path. Expected $($ExpectedStatus -join ' or '), received $status. $details"
    }

    $body = [System.IO.File]::ReadAllText($bodyPath)
    $headers = [System.IO.File]::ReadAllText($headerPath)

    if ($Contains -ne '' -and -not $body.Contains($Contains)) {
        throw "HTTP probe for $Path did not contain the required bounded content."
    }

    if ($Excludes -ne '' -and $body.Contains($Excludes)) {
        throw "HTTP probe for $Path exposed prohibited content."
    }

    if ($ContentType -ne '' -and $headers -notmatch ('(?im)^Content-Type:\s*' + [regex]::Escape($ContentType))) {
        throw "HTTP probe for $Path did not return Content-Type $ContentType."
    }

    return $body
}

function Invoke-BrowserStyleProbe {
    param(
        [Parameter(Mandatory)][string[]]$Urls,
        [string]$AdminLoginUrl = '',
        [string]$AdminDownloadsUrl = ''
    )

    if (-not (Test-Path -LiteralPath $ChromePath -PathType Leaf)) {
        throw "Chrome is required for the rendered package check: $ChromePath"
    }
    & curl.exe --silent --max-time 1 --output NUL "http://127.0.0.1:$BrowserPort/json/version"
    if ($LASTEXITCODE -eq 0) {
        throw "BrowserPort $BrowserPort is already in use; refuse to attach the package check to an existing browser."
    }

    $browserProcess = Start-Process -FilePath $ChromePath -ArgumentList @(
        "--remote-debugging-port=$BrowserPort",
        "--user-data-dir=$browserProfile",
        '--headless=new',
        '--disable-gpu',
        '--no-first-run',
        '--no-default-browser-check',
        'about:blank'
    ) -PassThru -WindowStyle Hidden

    try {
        for ($attempt = 0; $attempt -lt 40; $attempt++) {
            Start-Sleep -Milliseconds 250
            if ($browserProcess.HasExited) {
                break
            }

            & curl.exe --silent --output NUL "http://127.0.0.1:$BrowserPort/json/version"
            if ($LASTEXITCODE -eq 0) {
                $auditUrls = @($Urls)
                $previousBrowserPort = $env:GUIDEMYPC_CHROME_DEBUG_PORT
                $env:GUIDEMYPC_CHROME_DEBUG_PORT = $BrowserPort
                if ($AdminLoginUrl -ne '') {
                    $previousAdminPassword = $env:GUIDEMYPC_BROWSER_ADMIN_PASSWORD
                    $env:GUIDEMYPC_BROWSER_ADMIN_PASSWORD = $adminPassword
                    try {
                        $loginOutput = & node (Join-Path $repositoryRoot 'scripts\login-browser-session.js') $AdminLoginUrl $AdminDownloadsUrl $AdminEmail 2>&1 | Out-String
                        if ($LASTEXITCODE -ne 0) {
                            throw "Browser admin login failed.`n$loginOutput"
                        }
                    } finally {
                        if ($null -eq $previousAdminPassword) {
                            Remove-Item Env:GUIDEMYPC_BROWSER_ADMIN_PASSWORD -ErrorAction SilentlyContinue
                        } else {
                            $env:GUIDEMYPC_BROWSER_ADMIN_PASSWORD = $previousAdminPassword
                        }
                    }
                    Write-Host $loginOutput.TrimEnd()
                    $auditUrls += $AdminDownloadsUrl
                }
                foreach ($Url in $auditUrls) {
                    $previousPort = $env:GUIDEMYPC_CHROME_DEBUG_PORT
                    $previousStyleRequirement = $env:GUIDEMYPC_REQUIRE_PACKAGE_STYLES
                    $previousIconRequirement = $env:GUIDEMYPC_EXPECT_CATEGORY_ICONS
                    $env:GUIDEMYPC_CHROME_DEBUG_PORT = $BrowserPort
                    $env:GUIDEMYPC_REQUIRE_PACKAGE_STYLES = '1'
                    $env:GUIDEMYPC_EXPECT_CATEGORY_ICONS = if ($RequireCategoryIcons -and $Url.EndsWith('/')) { '1' } else { '0' }
                    try {
                        $browserOutput = & node (Join-Path $repositoryRoot 'scripts\check-browser-accessibility.js') $Url 2>&1 | Out-String
                        if ($LASTEXITCODE -ne 0) {
                            throw "Rendered package browser check failed.`n$browserOutput"
                        }
                        Write-Host $browserOutput.TrimEnd()
                        $mobileOutput = & node (Join-Path $repositoryRoot 'scripts\check-mobile-layout.js') $Url 2>&1 | Out-String
                        if ($LASTEXITCODE -ne 0) {
                            throw "320px mobile layout check failed.`n$mobileOutput"
                        }
                        Write-Host $mobileOutput.TrimEnd()
                    } finally {
                        $env:GUIDEMYPC_CHROME_DEBUG_PORT = $previousPort
                        $env:GUIDEMYPC_REQUIRE_PACKAGE_STYLES = $previousStyleRequirement
                        $env:GUIDEMYPC_EXPECT_CATEGORY_ICONS = $previousIconRequirement
                    }
                }
                $env:GUIDEMYPC_CHROME_DEBUG_PORT = $previousBrowserPort
                return $browserProcess
            }
        }

        throw 'Chrome did not expose a DevTools endpoint for the rendered package check.'
    } catch {
        if (-not $browserProcess.HasExited) {
            Stop-ProcessTree -RootProcess $browserProcess
        }
        throw
    }
}

try {
    New-Item -ItemType Directory -Path $temporaryDirectory | Out-Null
    if ($Mode -eq 'PackageRoot') {
        if ($PackageRoot -eq '' -or -not (Test-Path -LiteralPath $PackageRoot -PathType Container)) {
            throw 'PackageRoot must name an extracted package directory in PackageRoot mode.'
        }
        if ($MountName -eq '') {
            $MountName = 'FinalProject-' + [Guid]::NewGuid().ToString('N').Substring(0, 8)
        }
        if ($MountName -notmatch '^[A-Za-z0-9][A-Za-z0-9._-]*$') {
            throw 'MountName must be a safe arbitrary htdocs folder name.'
        }
        foreach ($requiredPackageEntry in @('index.php', '.htaccess', 'backend\public\index.php')) {
            if (-not (Test-Path -LiteralPath (Join-Path $PackageRoot $requiredPackageEntry) -PathType Leaf)) {
                throw "PackageRoot is missing required local entry point: $requiredPackageEntry"
            }
        }

        $packageParent = Join-Path $temporaryDirectory 'htdocs'
        $mountedPackage = Join-Path $packageParent $MountName
        New-Item -ItemType Directory -Path $packageParent | Out-Null
        Copy-Item -LiteralPath $PackageRoot -Destination $mountedPackage -Recurse
        $documentRoot = $packageParent
        $servedDirectory = $mountedPackage
        $urlPrefix = '/' + $MountName
        if ($VerifyNestedBackendGuard) {
            Remove-Item -LiteralPath (Join-Path $mountedPackage '.htaccess') -Force
            Remove-Item -LiteralPath (Join-Path $mountedPackage 'backend\.htaccess') -Force
        }
    }

    $documentRootConfig = $documentRoot.Replace('\', '/')
    $servedDirectoryConfig = $servedDirectory.Replace('\', '/')
    $rewriteModule = if ($DisableRewrite) { '' } else { 'LoadModule rewrite_module modules/mod_rewrite.so' }
    $config = @"
ServerRoot "$apacheRootConfig"
Listen 127.0.0.1:$Port
ServerName 127.0.0.1
PidFile "$pidFileConfig"
ErrorLog "$errorLogConfig"
LogLevel warn
TypesConfig conf/mime.types

LoadModule authz_core_module modules/mod_authz_core.so
LoadModule dir_module modules/mod_dir.so
LoadModule env_module modules/mod_env.so
LoadModule headers_module modules/mod_headers.so
LoadModule mime_module modules/mod_mime.so
$rewriteModule
LoadFile "$phpRootConfig/php8ts.dll"
LoadFile "$phpRootConfig/libpq.dll"
LoadFile "$phpRootConfig/libsqlite3.dll"
LoadModule php_module "$phpRootConfig/php8apache2_4.dll"
PHPIniDir "$phpRootConfig"

<FilesMatch "\.php$">
    SetHandler application/x-httpd-php
</FilesMatch>

DocumentRoot "$documentRootConfig"
DirectoryIndex index.php

<Directory />
    AllowOverride None
    Require all denied
</Directory>

<Directory "$documentRootConfig">
    Options -Indexes +FollowSymLinks
    AllowOverride None
    Require all granted
</Directory>

<Directory "$servedDirectoryConfig">
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
"@
    [System.IO.File]::WriteAllText($configPath, $config, [System.Text.UTF8Encoding]::new($false))

    & $httpd -t -f $configPath
    if ($LASTEXITCODE -ne 0) {
        throw 'Temporary Apache configuration validation failed.'
    }

    $process = Start-Process -FilePath $httpd -ArgumentList @('-f', $configPath, '-X') -PassThru -WindowStyle Hidden -RedirectStandardError $stderrLog
    $ready = $false

    for ($attempt = 0; $attempt -lt 40; $attempt++) {
        Start-Sleep -Milliseconds 250
        if ($process.HasExited) {
            break
        }

        & curl.exe --silent --output NUL "http://127.0.0.1:$Port/"
        if ($LASTEXITCODE -eq 0) {
            $ready = $true
            break
        }
    }

    if (-not $ready) {
        $details = if ((Test-Path -LiteralPath $stderrLog) -and (Get-Item -LiteralPath $stderrLog).Length -gt 0) { [System.IO.File]::ReadAllText($stderrLog) } elseif (Test-Path -LiteralPath $errorLog) { 'See the temporary Apache error log after shutdown.' } else { 'No Apache error log was created.' }
        throw "Temporary Apache did not start. $details"
    }

    if ($DisableRewrite) {
        if ($Mode -ne 'PackageRoot') {
            throw 'DisableRewrite is only valid for PackageRoot mode.'
        }
        Invoke-HttpProbe -Path '/' -ExpectedStatus 403 -Excludes 'Index of' | Out-Null
        Invoke-HttpProbe -Path '/database/README.md' -ExpectedStatus 403 | Out-Null
        Write-Host 'PASS: package-root Apache fails closed when mod_rewrite is unavailable.'
        return
    }
    if ($VerifyNestedBackendGuard) {
        if ($Mode -ne 'PackageRoot') {
            throw 'VerifyNestedBackendGuard is only valid for PackageRoot mode.'
        }
        Invoke-HttpProbe -Path '/backend/public/index.php' -ExpectedStatus 403 -Excludes $repositoryRoot | Out-Null
        Write-Host 'PASS: generated backend/public policy independently blocks direct browser access.'
        return
    }

    $homeBody = Invoke-HttpProbe -Path '/' -ExpectedStatus 200 -Contains '<title>GuideMyPC' -Excludes 'Index of'
    Invoke-HttpProbe -Path '/guides.php' -ExpectedStatus 200 -Contains '<title>All Guides | GuideMyPC</title>' | Out-Null
    Invoke-HttpProbe -Path '/contact.php' -ExpectedStatus 200 -Contains '<title>Contact | GuideMyPC</title>' | Out-Null
    Invoke-HttpProbe -Path '/assets/css/style.css' -ExpectedStatus 200 -Contains 'box-sizing: border-box' -ContentType 'text/css' | Out-Null
    Invoke-HttpProbe -Path '/assets/css/design-system.css' -ExpectedStatus 200 -Contains ':root' -ContentType 'text/css' | Out-Null
    Invoke-HttpProbe -Path '/css/style.css' -ExpectedStatus 200 -Contains 'box-sizing: border-box' -ContentType 'text/css' | Out-Null
    Invoke-HttpProbe -Path '/assets/js/script.js' -ExpectedStatus 200 -Contains 'function toggleStep' -ContentType 'text/javascript' | Out-Null
    Invoke-HttpProbe -Path '/assets/js/guide-editor.js' -ExpectedStatus 200 -Contains 'const container' -ContentType 'text/javascript' | Out-Null
    Invoke-HttpProbe -Path '/assets/js/chart.umd.min.js' -ExpectedStatus 200 -Contains 'Chart.js' -ContentType 'text/javascript' | Out-Null
    Invoke-HttpProbe -Path '/js/script.js' -ExpectedStatus 200 -Contains 'function toggleStep' -ContentType 'text/javascript' | Out-Null
    if ($homeBody -match 'href="[^"]*https?://[^"]*https?://') {
        throw 'Homepage navigation contains a duplicated application base URL.'
    }
    if ($Mode -eq 'PackageRoot') {
        foreach ($requiredMountedUrl in @(
            ('href="' + $urlPrefix + '/guides.php"'),
            ('href="' + $urlPrefix + '/css/style.css'),
            ('src="' + $urlPrefix + '/js/script.js')
        )) {
            if (-not $homeBody.Contains($requiredMountedUrl)) {
                throw "Package homepage does not preserve the arbitrary localhost subdirectory URL: $requiredMountedUrl"
            }
        }
    }
    $browserArguments = @{
        Urls = @(
        "http://127.0.0.1:$Port$urlPrefix/",
        "http://127.0.0.1:$Port$urlPrefix/downloads.php"
        )
    }
    if ($AdminEmail -ne '') {
        $browserArguments.AdminLoginUrl = "http://127.0.0.1:$Port$urlPrefix/login.php"
        $browserArguments.AdminDownloadsUrl = "http://127.0.0.1:$Port$urlPrefix/admin_downloads.php"
    }
    $browserProcess = Invoke-BrowserStyleProbe @browserArguments
    Invoke-HttpProbe -Path '/robots.txt' -ExpectedStatus 200 -Excludes 'ai.php' | Out-Null
    if ($Mode -eq 'PublicRoot') {
        Invoke-HttpProbe -Path '/robots.txt' -ExpectedStatus 200 -Contains 'Sitemap: http://guidemypc.test/sitemap.php' | Out-Null
    }
    Invoke-HttpProbe -Path '/missing-page.php' -ExpectedStatus 404 -Contains '<title>Page not found | GuideMyPC</title>' -Excludes $repositoryRoot | Out-Null
    Invoke-HttpProbe -Path '/ai.php' -ExpectedStatus 404 -Contains '<title>Page not found | GuideMyPC</title>' | Out-Null
    Invoke-HttpProbe -Path '/donate.php' -ExpectedStatus 404 -Contains '<title>Page not found | GuideMyPC</title>' | Out-Null
    Invoke-HttpProbe -Path '/search_suggestions.php' -Method POST -ExpectedStatus 405 -Contains '"code":"method_not_allowed"' -ContentType 'application/json; charset=utf-8' | Out-Null
    Invoke-HttpProbe -Path '/search_event.php' -Method GET -ExpectedStatus 405 -Contains '"code":"method_not_allowed"' -ContentType 'application/json; charset=utf-8' | Out-Null
    Invoke-HttpProbe -Path '/.env' -ExpectedStatus 403 -Contains '<title>403 Forbidden</title>' -Excludes $repositoryRoot | Out-Null
    Invoke-HttpProbe -Path '/.git/config' -ExpectedStatus 403 -Contains '<title>403 Forbidden</title>' -Excludes $repositoryRoot | Out-Null

    foreach ($privatePath in @(
        '/config.php',
        '/composer.json',
        '/app/Core/Database.php',
        '/bootstrap/web.php',
        '/database/README.md',
        '/docs/project-structure.md',
        '/includes/functions.php',
        '/scripts/verify.php',
        '/Tasks/web-init/README.md',
        '/tests/helpers_test.php'
    )) {
        $packageRootBlocked = $Mode -eq 'PackageRoot' -and $privatePath -match '^/(database|docs|frontend|uml)(?:/|$)'
        if ($packageRootBlocked) {
            Invoke-HttpProbe -Path $privatePath -ExpectedStatus 403 -Excludes $repositoryRoot | Out-Null
        } else {
            Invoke-HttpProbe -Path $privatePath -ExpectedStatus 404 -Contains '<title>Page not found | GuideMyPC</title>' -Excludes $repositoryRoot | Out-Null
        }
    }

    if ($Mode -eq 'PackageRoot') {
        foreach ($packagePrivatePath in @(
            '/backend/config.php',
            '/database/README.md',
            '/docs/project-structure.md',
            '/frontend/public/assets/css/style.css',
            '/uml/source/GuideMyPC.vpp',
            '/PACKAGE-MANIFEST.txt',
            '/README.md',
            '/.env',
            '/backend/.env'
        )) {
            Invoke-HttpProbe -Path $packagePrivatePath -ExpectedStatus 403 -Excludes $repositoryRoot | Out-Null
        }
        Invoke-HttpProbe -Path '/backend/public/index.php' -ExpectedStatus 403 -Excludes $repositoryRoot | Out-Null
    }

    Write-Host "PASS: isolated Apache $Mode exposes only public assets and approved legacy routes; private and retired paths return bounded responses."
} catch {
    $failure = $_

    if ($process -ne $null -and -not $process.HasExited) {
        Stop-Process -Id $process.Id -Force
        $process.WaitForExit()
    }
    if ($browserProcess -ne $null -and -not $browserProcess.HasExited) {
        Stop-ProcessTree -RootProcess $browserProcess
    }

    Start-Sleep -Milliseconds 500
    $details = if ((Test-Path -LiteralPath $stderrLog) -and (Get-Item -LiteralPath $stderrLog).Length -gt 0) { [System.IO.File]::ReadAllText($stderrLog) } elseif (Test-Path -LiteralPath $errorLog) { [System.IO.File]::ReadAllText($errorLog) } else { 'No Apache error log was created.' }
    throw "$failure`nApache error log:`n$details"
} finally {
    if ($null -eq $previousDatabaseName) {
        Remove-Item Env:DB_NAME -ErrorAction SilentlyContinue
    } else {
        $env:DB_NAME = $previousDatabaseName
    }
    if ($process -ne $null -and -not $process.HasExited) {
        Stop-Process -Id $process.Id -Force
        $process.WaitForExit()
    }
    if ($browserProcess -ne $null -and -not $browserProcess.HasExited) {
        Stop-ProcessTree -RootProcess $browserProcess
    }

    if (Test-Path -LiteralPath $temporaryDirectory) {
        for ($attempt = 0; $attempt -lt 10 -and (Test-Path -LiteralPath $temporaryDirectory); $attempt++) {
            Start-Sleep -Milliseconds 250
            try {
                Remove-Item -LiteralPath $temporaryDirectory -Recurse -Force -ErrorAction Stop
            } catch [System.IO.IOException] {
                if ($attempt -eq 9) {
                    throw
                }
            }
        }
    }
}
