[CmdletBinding()]
param(
    [string]$Commit = 'HEAD',
    [Parameter(Mandatory)]
    [string]$OutputPath
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

$trackedFiles = & git ls-tree -r --name-only $commitId
$prohibitedPathPatterns = @(
    '(^|/)\.env$',
    '(^|/)(vendor|node_modules|logs|uploads|storage|coverage|database/backups)/',
    '(^|/)\.code-review-graph/',
    '(\.pem|\.key|\.p12|\.pfx|\.sql\.gz|\.bak)$'
)
$prohibitedFiles = $trackedFiles | Where-Object {
    $path = $_
    $prohibitedPathPatterns | Where-Object { $path -match $_ }
}

if ($prohibitedFiles) {
    throw "The selected commit contains prohibited paths:`n$($prohibitedFiles -join "`n")"
}

$outputDirectory = Split-Path -Parent $output

if ($outputDirectory -eq '') {
    $outputDirectory = (Get-Location).Path
    $output = Join-Path $outputDirectory $output
}

New-Item -ItemType Directory -Force -Path $outputDirectory | Out-Null

if (Test-Path -LiteralPath $output) {
    throw "Output already exists: $output"
}

$temporaryDirectory = Join-Path ([System.IO.Path]::GetTempPath()) ("guidemypc-source-" + [Guid]::NewGuid().ToString('N'))
$temporaryArchive = Join-Path $temporaryDirectory 'source.zip'
$contentDirectory = Join-Path $temporaryDirectory 'content'

try {
    New-Item -ItemType Directory -Path $contentDirectory | Out-Null
    & git archive --format=zip --output=$temporaryArchive $commitId

    if ($LASTEXITCODE -ne 0) {
        throw 'Git could not create the source archive.'
    }

    Expand-Archive -LiteralPath $temporaryArchive -DestinationPath $contentDirectory
    $omittedPaths = @('AGENTS.md', 'opencode.json', '.idea', '.vscode', 'Readme.docx', 'GuideMyPC-Final-Report.docx', 'GuideMyPC.vpp')

    foreach ($path in $omittedPaths) {
        $omittedPath = Join-Path $contentDirectory $path

        if (Test-Path -LiteralPath $omittedPath) {
            Remove-Item -LiteralPath $omittedPath -Recurse -Force
        }
    }

    Compress-Archive -Path (Join-Path $contentDirectory '*') -DestinationPath $output
} finally {
    if (Test-Path -LiteralPath $temporaryDirectory) {
        Remove-Item -LiteralPath $temporaryDirectory -Recurse -Force
    }
}

Write-Host "Created $output from commit $commitId."
