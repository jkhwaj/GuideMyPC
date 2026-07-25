[CmdletBinding()]
param(
    [string]$Commit = 'HEAD',
    [Parameter(Mandatory)][string]$OutputPath,
    [Parameter(Mandatory)][string]$UmlDirectory,
    [Parameter(Mandatory)][string]$ScreenshotsDirectory
)

$ErrorActionPreference = 'Stop'
$repositoryRoot = (& git rev-parse --show-toplevel).Trim()

if ($LASTEXITCODE -ne 0 -or $repositoryRoot -eq '') {
    throw 'Run this script from a Git working tree.'
}

$commitId = (& git rev-parse "$Commit^{commit}").Trim()

if ($LASTEXITCODE -ne 0) {
    throw "The commit '$Commit' cannot be resolved."
}

$output = [System.IO.Path]::GetFullPath($OutputPath)

if ([System.IO.Path]::GetExtension($output) -ne '.zip') {
    throw 'OutputPath must end with .zip.'
}

if (Test-Path -LiteralPath $output) {
    throw "Output already exists: $output"
}

$outputDirectory = Split-Path -Parent $output

if ($outputDirectory -eq '') {
    $outputDirectory = (Get-Location).Path
    $output = Join-Path $outputDirectory $output
}

New-Item -ItemType Directory -Force -Path $outputDirectory | Out-Null

$trackedFiles = & git ls-tree -r --name-only $commitId
$prohibitedTrackedPatterns = @(
    '(^|/)\.env$',
    '(^|/)(vendor|node_modules|logs|uploads|storage|coverage|database/backups)/',
    '(^|/)\.code-review-graph/',
    '(^|/)(\.idea|\.vscode)/',
    '(^|/)opencode\.json$',
    '(^|/)docs/submission/(documents|uml)/',
    '(^|/)docs/submission/screenshots/.*\.(png|jpe?g|webp)$',
    '(\.pem|\.key|\.p12|\.pfx|\.sql\.gz|\.bak|\.zip|\.docx|\.vpp)$'
)
$prohibitedTracked = $trackedFiles | Where-Object {
    $path = $_
    $prohibitedTrackedPatterns | Where-Object { $path -match $_ }
}

if ($prohibitedTracked) {
    throw "The selected commit contains prohibited paths:`n$($prohibitedTracked -join "`n")"
}

$temporaryDirectory = Join-Path ([System.IO.Path]::GetTempPath()) ('guidemypc-source-' + [Guid]::NewGuid().ToString('N'))
$sourceArchive = Join-Path $temporaryDirectory 'source.zip'
$sourceDirectory = Join-Path $temporaryDirectory 'source'
$packageParent = Join-Path $temporaryDirectory 'package'
$packageRoot = Join-Path $packageParent 'GuideMyPC'
$backendDirectory = Join-Path $packageRoot 'backend'
$frontendDirectory = Join-Path $packageRoot 'frontend'
$databaseDirectory = Join-Path $packageRoot 'database'
$docsDirectory = Join-Path $packageRoot 'docs'
$packageScreenshotsDirectory = Join-Path $docsDirectory 'screenshots'
$packageUmlDirectory = Join-Path $packageRoot 'uml'

function Copy-RequiredEntry {
    param(
        [Parameter(Mandatory)][string]$RelativePath,
        [Parameter(Mandatory)][string]$DestinationRoot
    )

    $source = Join-Path $sourceDirectory $RelativePath

    if (-not (Test-Path -LiteralPath $source)) {
        throw "Required release source is missing: $RelativePath"
    }

    $destination = Join-Path $DestinationRoot $RelativePath
    $destinationParent = Split-Path -Parent $destination
    New-Item -ItemType Directory -Force -Path $destinationParent | Out-Null
    Copy-Item -LiteralPath $source -Destination $destination -Recurse
}

try {
    New-Item -ItemType Directory -Path $sourceDirectory | Out-Null
    New-Item -ItemType Directory -Path $packageRoot | Out-Null
    New-Item -ItemType Directory -Path $backendDirectory | Out-Null
    New-Item -ItemType Directory -Path $frontendDirectory | Out-Null

    & git archive --format=zip --output=$sourceArchive $commitId
    if ($LASTEXITCODE -ne 0) {
        throw 'Git could not create the release-commit source archive.'
    }

    Expand-Archive -LiteralPath $sourceArchive -DestinationPath $sourceDirectory

    foreach ($entry in @(
        '.env.example',
        '.htaccess',
        'app',
        'bootstrap',
        'composer.json',
        'composer.lock',
        'config',
        'config.php',
        'database',
        'includes',
        'public',
        'resources',
        'routes',
        'scripts',
        'tests'
    )) {
        Copy-RequiredEntry -RelativePath $entry -DestinationRoot $backendDirectory
    }

    Get-ChildItem -LiteralPath $sourceDirectory -File -Filter '*.php' | ForEach-Object {
        Copy-Item -LiteralPath $_.FullName -Destination (Join-Path $backendDirectory $_.Name)
    }

    foreach ($requiredBackendAsset in @(
        'public\assets\css\style.css',
        'public\assets\css\design-system.css',
        'public\assets\js\script.js',
        'public\assets\js\guide-editor.js',
        'public\assets\js\chart.umd.min.js'
    )) {
        if (-not (Test-Path -LiteralPath (Join-Path $backendDirectory $requiredBackendAsset) -PathType Leaf)) {
            throw "Runnable backend is missing required public asset: $requiredBackendAsset"
        }
    }

    Copy-RequiredEntry -RelativePath 'public/assets' -DestinationRoot $frontendDirectory
    Copy-RequiredEntry -RelativePath 'resources/views' -DestinationRoot $frontendDirectory
    Copy-Item -LiteralPath (Join-Path $sourceDirectory 'database') -Destination $databaseDirectory -Recurse
    Copy-Item -LiteralPath (Join-Path $sourceDirectory 'docs') -Destination $docsDirectory -Recurse
    Copy-Item -LiteralPath (Join-Path $sourceDirectory 'README.md') -Destination (Join-Path $packageRoot 'README.md')

    $resolvedUmlDirectory = [System.IO.Path]::GetFullPath($UmlDirectory)

    if (-not (Test-Path -LiteralPath $resolvedUmlDirectory -PathType Container)) {
        throw 'The reviewed UML directory is required.'
    }

    $requiredUmlPaths = @(
        'source\GuideMyPC.vpp',
        'exports\use-case.png',
        'exports\class-diagram.png',
        'exports\activity-diagram.png',
        'exports\state-machine.png'
    )

    foreach ($requiredUmlPath in $requiredUmlPaths) {
        if (-not (Test-Path -LiteralPath (Join-Path $resolvedUmlDirectory $requiredUmlPath) -PathType Leaf)) {
            throw "Required reviewed UML artifact is missing: $requiredUmlPath"
        }
    }

    $reviewedUmlFiles = @(Get-ChildItem -LiteralPath $resolvedUmlDirectory -File -Force -Recurse | ForEach-Object {
        $_.FullName.Substring($resolvedUmlDirectory.Length + 1)
    })
    if ($reviewedUmlFiles.Count -ne $requiredUmlPaths.Count -or @($reviewedUmlFiles | Where-Object { $_ -notin $requiredUmlPaths }).Count -gt 0) {
        throw 'The reviewed UML directory must contain only the native project and four approved PNG exports.'
    }

    Copy-Item -LiteralPath $resolvedUmlDirectory -Destination $packageUmlDirectory -Recurse

    $resolvedScreenshotsDirectory = [System.IO.Path]::GetFullPath($ScreenshotsDirectory)
    if (-not (Test-Path -LiteralPath $resolvedScreenshotsDirectory -PathType Container)) {
        throw 'The reviewed screenshots directory is required.'
    }
    $screenshots = @(Get-ChildItem -LiteralPath $resolvedScreenshotsDirectory -File -Filter '*.png')
    if ($screenshots.Count -lt 8 -or $screenshots.Count -gt 10) {
        throw "The reviewed screenshot set must contain 8-10 PNG files; found $($screenshots.Count)."
    }
    New-Item -ItemType Directory -Path $packageScreenshotsDirectory | Out-Null
    $screenshots | Sort-Object Name | ForEach-Object {
        Copy-Item -LiteralPath $_.FullName -Destination (Join-Path $packageScreenshotsDirectory $_.Name)
    }

    $prohibitedPackagePatterns = @(
        '(^|\\)\.env$',
        '(^|\\)(vendor|node_modules|logs|uploads|storage|coverage|backups|sessions|cache)(\\|$)',
        '(^|\\)(\.git|\.idea|\.vscode|\.code-review-graph)(\\|$)',
        '(^|\\)opencode\.json$',
        '(\.pem|\.key|\.p12|\.pfx|\.sql\.gz|\.bak|\.zip)$'
    )
    $prohibitedPackageFiles = Get-ChildItem -LiteralPath $packageRoot -Force -Recurse | Where-Object {
        $relative = $_.FullName.Substring($packageRoot.Length + 1)
        $prohibitedPackagePatterns | Where-Object { $relative -match $_ }
    }

    if ($prohibitedPackageFiles) {
        throw "Generated package contains prohibited paths:`n$($prohibitedPackageFiles.FullName -join "`n")"
    }

    $manifestPath = Join-Path $packageRoot 'PACKAGE-MANIFEST.txt'
    $manifest = [System.Collections.Generic.List[string]]::new()
    $manifest.Add('GuideMyPC strict source package')
    $manifest.Add("Release commit: $commitId")
    $manifest.Add('Layout: frontend/, backend/, database/, uml/, docs/')
    $manifest.Add('Backend document root: backend/public/')
    $manifest.Add('UML source: uml/source/GuideMyPC.vpp')
    $manifest.Add("Reviewed screenshots: docs/screenshots/ ($($screenshots.Count) PNG files)")
    $manifest.Add('')
    $manifest.Add('SHA-256 file manifest:')

    Get-ChildItem -LiteralPath $packageRoot -File -Recurse |
        Where-Object { $_.FullName -ne $manifestPath } |
        Sort-Object FullName |
        ForEach-Object {
            $relative = $_.FullName.Substring($packageRoot.Length + 1).Replace('\', '/')
            $hash = (Get-FileHash -Algorithm SHA256 -LiteralPath $_.FullName).Hash
            $manifest.Add("$hash  $relative")
        }

    [System.IO.File]::WriteAllLines($manifestPath, $manifest, [System.Text.UTF8Encoding]::new($false))
    Compress-Archive -LiteralPath $packageRoot -DestinationPath $output
} finally {
    if (Test-Path -LiteralPath $temporaryDirectory) {
        Remove-Item -LiteralPath $temporaryDirectory -Recurse -Force
    }
}

Write-Host "Created strict source package $output from release commit $commitId."
