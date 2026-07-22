[CmdletBinding()]
param(
    [string]$Commit = 'HEAD',
    [Parameter(Mandatory)]
    [string]$OutputPath,
    [string]$ComposerCommand = 'composer'
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

$temporaryDirectory = Join-Path ([System.IO.Path]::GetTempPath()) ("guidemypc-deploy-" + [Guid]::NewGuid().ToString('N'))
$temporaryArchive = Join-Path $temporaryDirectory 'source.zip'
$contentDirectory = Join-Path $temporaryDirectory 'content'

try {
    New-Item -ItemType Directory -Path $contentDirectory | Out-Null
    & git archive --format=zip --output=$temporaryArchive $commitId

    if ($LASTEXITCODE -ne 0) {
        throw 'Git could not create the deployment archive.'
    }

    Expand-Archive -LiteralPath $temporaryArchive -DestinationPath $contentDirectory
    & $ComposerCommand install --no-dev --classmap-authoritative --no-interaction --no-progress --working-dir=$contentDirectory

    if ($LASTEXITCODE -ne 0) {
        throw 'Composer could not install locked production dependencies.'
    }

    if (-not (Test-Path -LiteralPath (Join-Path $contentDirectory 'vendor\autoload.php'))) {
        throw 'The deployment artifact is missing vendor\autoload.php.'
    }

    Compress-Archive -Path (Join-Path $contentDirectory '*') -DestinationPath $output
} finally {
    if (Test-Path -LiteralPath $temporaryDirectory) {
        Remove-Item -LiteralPath $temporaryDirectory -Recurse -Force
    }
}

Write-Host "Created deployment artifact $output from commit $commitId."
