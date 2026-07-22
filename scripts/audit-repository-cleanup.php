<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

$root = dirname(__DIR__);

/** @return array{code:int,stdout:string,stderr:string} */
function run_process(array $command, string $cwd): array
{
    $specification = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $specification, $pipes, $cwd, null, ['bypass_shell' => true]);
    if (!is_resource($process)) {
        throw new RuntimeException('Could not start process: ' . implode(' ', $command));
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $code = proc_close($process);

    return [
        'code' => $code,
        'stdout' => $stdout === false ? '' : $stdout,
        'stderr' => $stderr === false ? '' : $stderr,
    ];
}

$result = run_process(['git', 'ls-files', '-z'], $root);
if ($result['code'] !== 0) {
    fwrite(STDERR, "FAIL: Could not list tracked files.\n" . $result['stderr']);
    exit(1);
}

$files = array_values(array_filter(explode("\0", $result['stdout']), static fn (string $path): bool => $path !== ''));
sort($files, SORT_STRING);

$routeFiles = ['routes/web.php', 'routes/admin.php', 'routes/api.php'];
$requiredRootPhp = ['config.php'];

foreach ($routeFiles as $routeFile) {
    $routePath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $routeFile);
    if (!is_file($routePath)) {
        continue;
    }

    $routes = require $routePath;
    if (is_array($routes)) {
        foreach (array_keys($routes) as $route) {
            if (is_string($route) && !str_contains($route, '/')) {
                $requiredRootPhp[] = $route;
            }
        }
    }
}

$requiredRootPhp = array_values(array_unique($requiredRootPhp));
sort($requiredRootPhp, SORT_STRING);

$rootAllowlist = [
    '.env.example',
    '.gitignore',
    '.htaccess',
    'AGENTS.md',
    'README.md',
    'composer.json',
    'composer.lock',
];

$hashGroups = [];
$basenameGroups = [];
$rootFiles = [];
$unexpectedRootFiles = [];
$generatedArtifacts = [];
$caseGroups = [];

foreach ($files as $file) {
    $absolute = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    if (!is_file($absolute)) {
        continue;
    }

    $size = filesize($absolute);
    if ($size !== false && $size > 0) {
        $hash = hash_file('sha256', $absolute);
        if (is_string($hash)) {
            $hashGroups[$hash][] = $file;
        }
    }

    $basenameGroups[basename($file)][] = $file;
    $caseGroups[strtolower($file)][] = $file;

    if (!str_contains($file, '/')) {
        $rootFiles[] = $file;
        $isExpectedPhp = str_ends_with($file, '.php') && in_array($file, $requiredRootPhp, true);
        if (!$isExpectedPhp && !in_array($file, $rootAllowlist, true)) {
            $unexpectedRootFiles[] = $file;
        }
    }

    if (preg_match('/\.(?:zip|7z|rar|pdf|docx?|vpp|log|bak|backup|sql\.gz)$/i', $file) === 1) {
        $generatedArtifacts[] = $file;
    }
}

$duplicateGroups = array_values(array_filter(
    $hashGroups,
    static fn (array $paths): bool => count($paths) > 1
));
usort($duplicateGroups, static fn (array $left, array $right): int => strcmp($left[0], $right[0]));

$duplicateBasenames = array_filter(
    $basenameGroups,
    static fn (array $paths): bool => count($paths) > 1
);
ksort($duplicateBasenames, SORT_STRING);

$caseCollisions = array_values(array_filter(
    $caseGroups,
    static fn (array $paths): bool => count(array_unique($paths)) > 1
));

sort($rootFiles, SORT_STRING);
sort($unexpectedRootFiles, SORT_STRING);
sort($generatedArtifacts, SORT_STRING);

echo "Repository cleanup audit\n";
echo "========================\n";
echo 'Tracked files: ' . count($files) . "\n";
echo 'Expected root compatibility PHP files: ' . count($requiredRootPhp) . "\n\n";

echo "ROOT FILES\n";
foreach ($rootFiles as $file) {
    $classification = in_array($file, $requiredRootPhp, true)
        ? 'required compatibility route/bootstrap'
        : (in_array($file, $rootAllowlist, true) ? 'expected project metadata' : 'review candidate');
    echo "- {$file} [{$classification}]\n";
}

echo "\nUNEXPECTED ROOT FILES\n";
if ($unexpectedRootFiles === []) {
    echo "- none\n";
} else {
    foreach ($unexpectedRootFiles as $file) {
        echo "- {$file}\n";
    }
}

echo "\nEXACT CONTENT DUPLICATES\n";
if ($duplicateGroups === []) {
    echo "- none\n";
} else {
    foreach ($duplicateGroups as $paths) {
        $first = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $paths[0]);
        $size = filesize($first);
        echo '- ' . ($size === false ? '?' : (string) $size) . " bytes\n";
        foreach ($paths as $path) {
            echo "  - {$path}\n";
        }
    }
}

echo "\nREPEATED BASENAMES\n";
if ($duplicateBasenames === []) {
    echo "- none\n";
} else {
    foreach ($duplicateBasenames as $basename => $paths) {
        echo "- {$basename}\n";
        foreach ($paths as $path) {
            echo "  - {$path}\n";
        }
    }
}

echo "\nTRACKED GENERATED OR SUBMISSION ARTIFACTS\n";
if ($generatedArtifacts === []) {
    echo "- none\n";
} else {
    foreach ($generatedArtifacts as $file) {
        echo "- {$file}\n";
    }
}

echo "\nCASE-INSENSITIVE PATH COLLISIONS\n";
if ($caseCollisions === []) {
    echo "- none\n";
} else {
    foreach ($caseCollisions as $paths) {
        foreach ($paths as $path) {
            echo "- {$path}\n";
        }
    }
}

$hasHighConfidenceIssue = $unexpectedRootFiles !== []
    || $duplicateGroups !== []
    || $generatedArtifacts !== []
    || $caseCollisions !== [];
echo "\nRESULT: " . ($hasHighConfidenceIssue ? 'REVIEW REQUIRED' : 'NO HIGH-CONFIDENCE CLEANUP ISSUE') . "\n";

exit($hasHighConfidenceIssue ? 1 : 0);
