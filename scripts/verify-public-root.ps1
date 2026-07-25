[CmdletBinding()]
param(
    [int]$Port = 8765,
    [string]$ApacheRoot = 'C:\xampp\apache',
    [string]$PhpRoot = 'C:\xampp\php',
    [string]$ChromePath = 'C:\Program Files\Google\Chrome\Application\chrome.exe',
    [int]$BrowserPort = 0
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
$publicRootConfig = $publicRoot.Replace('\', '/')
$errorLogConfig = $errorLog.Replace('\', '/')
$pidFileConfig = $pidFile.Replace('\', '/')
$process = $null
$browserProcess = $null
$browserProfile = Join-Path $temporaryDirectory 'chrome-profile'

function Stop-ProcessTree {
    param([System.Diagnostics.Process]$RootProcess)

    if ($RootProcess -ne $null -and -not $RootProcess.HasExited) {
        & taskkill.exe /PID $RootProcess.Id /T /F | Out-Null
        $RootProcess.WaitForExit()
    }
}

function Invoke-HttpProbe {
    param(
        [Parameter(Mandatory)][string]$Path,
        [Parameter(Mandatory)][int]$ExpectedStatus,
        [string]$Method = 'GET',
        [string]$Contains = '',
        [string]$Excludes = '',
        [string]$ContentType = ''
    )

    $probeId = [Guid]::NewGuid().ToString('N')
    $bodyPath = Join-Path $temporaryDirectory ($probeId + '.body')
    $headerPath = Join-Path $temporaryDirectory ($probeId + '.headers')
    $url = "http://127.0.0.1:$Port$Path"
    $status = & curl.exe --silent --show-error --max-redirs 0 --request $Method --output $bodyPath --dump-header $headerPath --write-out '%{http_code}' $url

    if ($LASTEXITCODE -ne 0 -or [int]$status -ne $ExpectedStatus) {
        $details = if (Test-Path -LiteralPath $bodyPath) { [System.IO.File]::ReadAllText($bodyPath) } else { 'No response body was created.' }
        throw "HTTP probe failed for $Method $Path. Expected $ExpectedStatus, received $status. $details"
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
    param([Parameter(Mandatory)][string]$Url)

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
                $previousPort = $env:GUIDEMYPC_CHROME_DEBUG_PORT
                $previousStyleRequirement = $env:GUIDEMYPC_REQUIRE_PACKAGE_STYLES
                $env:GUIDEMYPC_CHROME_DEBUG_PORT = $BrowserPort
                $env:GUIDEMYPC_REQUIRE_PACKAGE_STYLES = '1'
                try {
                    $browserOutput = & node (Join-Path $repositoryRoot 'scripts\check-browser-accessibility.js') $Url 2>&1 | Out-String
                    if ($LASTEXITCODE -ne 0) {
                        throw "Rendered package browser check failed.`n$browserOutput"
                    }
                    Write-Host $browserOutput.TrimEnd()
                } finally {
                    $env:GUIDEMYPC_CHROME_DEBUG_PORT = $previousPort
                    $env:GUIDEMYPC_REQUIRE_PACKAGE_STYLES = $previousStyleRequirement
                }
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
LoadModule rewrite_module modules/mod_rewrite.so
LoadFile "$phpRootConfig/php8ts.dll"
LoadFile "$phpRootConfig/libpq.dll"
LoadFile "$phpRootConfig/libsqlite3.dll"
LoadModule php_module "$phpRootConfig/php8apache2_4.dll"
PHPIniDir "$phpRootConfig"

<FilesMatch "\.php$">
    SetHandler application/x-httpd-php
</FilesMatch>

DocumentRoot "$publicRootConfig"
DirectoryIndex index.php

<Directory />
    AllowOverride None
    Require all denied
</Directory>

<Directory "$publicRootConfig">
    Options FollowSymLinks
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

    $homeBody = Invoke-HttpProbe -Path '/' -ExpectedStatus 200 -Contains '<title>GuideMyPC'
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
    $browserProcess = Invoke-BrowserStyleProbe -Url "http://127.0.0.1:$Port/"
    Invoke-HttpProbe -Path '/robots.txt' -ExpectedStatus 200 -Excludes 'ai.php' | Out-Null
    Invoke-HttpProbe -Path '/robots.txt' -ExpectedStatus 200 -Contains 'Sitemap: http://guidemypc.test/sitemap.php' | Out-Null
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
        Invoke-HttpProbe -Path $privatePath -ExpectedStatus 404 -Contains '<title>Page not found | GuideMyPC</title>' -Excludes $repositoryRoot | Out-Null
    }

    Write-Host 'PASS: isolated Apache exposes only public assets and approved legacy routes; private and retired paths return bounded responses.'
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
    if ($process -ne $null -and -not $process.HasExited) {
        Stop-Process -Id $process.Id -Force
        $process.WaitForExit()
    }
    if ($browserProcess -ne $null -and -not $browserProcess.HasExited) {
        Stop-ProcessTree -RootProcess $browserProcess
    }

    if (Test-Path -LiteralPath $temporaryDirectory) {
        Remove-Item -LiteralPath $temporaryDirectory -Recurse -Force
    }
}
