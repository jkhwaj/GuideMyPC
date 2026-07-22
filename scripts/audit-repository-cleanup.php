<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This audit is CLI-only.\n");
    exit(2);
}

$root = trim((string) shell_exec('git rev-parse --show-toplevel 2>NUL'));
if ($root === '' || !is_dir($root)) {
    fwrite(STDERR, "Run this script from the GuideMyPC Git working tree.\n");
    exit(2);
}

$output = [];
$status = 0;
exec('git -C ' . escapeshellarg($root) . ' ls-files --cached --others --exclude-standard -z', $output, $status);
if ($status !== 0) {
    fwrite(STDERR, "Unable to enumerate repository files.\n");
    exit(2);
}

$paths = array_values(array_filter(
    explode("\0", implode("\n", $output)),
    static fn (string $path): bool => $path !== '' && is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path))
));
$errors = [];
$prohibitedPatterns = [
    '#(^|/)\.env$#i',
    '#(^|/)(vendor|node_modules|logs|uploads|storage|coverage|database/backups)/#i',
    '#(^|/)\.code-review-graph/#i',
    '#(^|/)(\.idea|\.vscode)/#i',
    '#(^|/)opencode\.json$#i',
    '#(^|/)docs/submission/(documents|uml)/#i',
    '#(^|/)docs/submission/screenshots/.*\.(png|jpe?g|webp)$#i',
    '#\.(pem|key|p12|pfx|sql\.gz|bak|zip|docx|vpp)$#i',
];

foreach ($paths as $path) {
    foreach ($prohibitedPatterns as $pattern) {
        if (preg_match($pattern, $path) === 1) {
            $errors[] = "Prohibited source/package path: $path";
            break;
        }
    }
}

$caseMap = [];
$hashMap = [];
foreach ($paths as $path) {
    $caseKey = strtolower($path);
    $caseMap[$caseKey][] = $path;
    $hash = hash_file('sha256', $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path));
    if ($hash !== false) {
        $hashMap[$hash][] = $path;
    }
}

foreach ($caseMap as $matches) {
    if (count($matches) > 1) {
        $errors[] = 'Case-insensitive path collision: ' . implode(', ', $matches);
    }
}

foreach ($hashMap as $matches) {
    if (count($matches) > 1) {
        $errors[] = 'Exact duplicate files require an explicit inventory exception: ' . implode(', ', $matches);
    }
}

foreach (['ai.php', 'donate.php', 'includes/ai.php', 'includes/uploads.php', 'includes/maintenance.php', 'resources/views/pages/ai.php', 'resources/views/pages/donate.php'] as $retiredPath) {
    if (is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $retiredPath))) {
        $errors[] = "Retired release file still exists: $retiredPath";
    }
}

$runtimePaths = array_filter($paths, static function (string $path): bool {
    if (preg_match('#^(database|docs|Tasks|tests|scripts)/#', $path) === 1) {
        return false;
    }

    return preg_match('#\.(php|js|txt)$#i', $path) === 1;
});
$retiredRuntimePatterns = [
    '#\bai\.php\b#i' => 'AI route reference',
    '#\bdonate\.php\b#i' => 'Donate route reference',
    '#\bVIEW_REPORTS\b#' => 'Reports capability',
    '#\b(?:admin|add|edit|delete)_knowledge\.php\b#i' => 'Knowledge administration route',
];

foreach ($runtimePaths as $path) {
    $contents = file_get_contents($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path));
    if ($contents === false) {
        $errors[] = "Unable to scan runtime file: $path";
        continue;
    }

    foreach ($retiredRuntimePatterns as $pattern => $label) {
        if (preg_match($pattern, $contents) === 1) {
            $errors[] = "$label remains in runtime file: $path";
        }
    }
}

$sensitiveContentPatterns = [
    '#-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----#' => 'private key material',
    '#\bAKIA[0-9A-Z]{16}\b#' => 'AWS access key',
    '#\bgh[pousr]_[A-Za-z0-9]{30,}\b#' => 'GitHub token',
    '#[A-Za-z]:[\\\\/]Users[\\\\/][^\\\\/\s]+#i' => 'machine-specific user-home path',
];

foreach ($paths as $path) {
    if (preg_match('#\.(png|jpe?g|gif|webp|ico|zip|docx|vpp)$#i', $path) === 1) {
        continue;
    }

    $contents = file_get_contents($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path));
    if ($contents === false || str_contains($contents, "\0")) {
        continue;
    }

    foreach ($sensitiveContentPatterns as $pattern => $label) {
        if (preg_match($pattern, $contents) === 1) {
            $errors[] = "Possible $label in $path";
        }
    }
}

$migrationDiff = [];
$migrationStatus = 0;
exec('git -C ' . escapeshellarg($root) . ' diff --name-only HEAD -- database/migrations', $migrationDiff, $migrationStatus);
if ($migrationStatus !== 0 || $migrationDiff !== []) {
    $errors[] = 'Historical migrations differ from HEAD: ' . implode(', ', $migrationDiff);
}

$routeCount = 0;
foreach (['routes/web.php', 'routes/admin.php', 'routes/api.php'] as $routeMap) {
    $map = require $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $routeMap);
    if (!is_array($map)) {
        $errors[] = "Route map did not return an array: $routeMap";
        continue;
    }
    $routeCount += count($map);
}
if ($routeCount !== 53) {
    $errors[] = "Expected 53 approved routes, found $routeCount.";
}

if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, "FAIL: $error\n");
    }
    exit(1);
}

printf(
    "PASS: audited %d current source files; no prohibited paths, exact duplicates, case collisions, retired runtime references, obvious secrets, machine-user paths, or migration edits; 53 approved routes.\n",
    count($paths)
);
