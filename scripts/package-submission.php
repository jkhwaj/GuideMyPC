<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

/** @return array{code:int,stdout:string,stderr:string} */
function run_process(array $command, ?string $cwd = null): array
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

function run_git(string $root, array $arguments): string
{
    $result = run_process(array_merge(['git', '-C', $root], $arguments), $root);

    if ($result['code'] !== 0) {
        $message = trim($result['stderr']) !== '' ? trim($result['stderr']) : trim($result['stdout']);
        throw new RuntimeException($message !== '' ? $message : 'Git command failed.');
    }

    return $result['stdout'];
}

function normalize_path(string $path): string
{
    $path = str_replace('\\', '/', trim($path));
    $path = preg_replace('~/+~', '/', $path) ?? $path;

    if ($path === '' || str_starts_with($path, '/') || preg_match('~(^|/)\.\.?(/|$)~', $path) === 1) {
        throw new RuntimeException('Unsafe repository path: ' . $path);
    }

    return $path;
}

function absolute_path(string $root, string $path): string
{
    if (preg_match('~^(?:[A-Za-z]:[\\/]|/|\\\\)~', $path) === 1) {
        return $path;
    }

    return $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
}

function ensure_directory(string $path): void
{
    if (is_dir($path)) {
        return;
    }

    if (!mkdir($path, 0775, true) && !is_dir($path)) {
        throw new RuntimeException('Could not create directory: ' . $path);
    }
}

function write_file(string $path, string $contents): void
{
    ensure_directory(dirname($path));

    if (file_put_contents($path, $contents) === false) {
        throw new RuntimeException('Could not write file: ' . $path);
    }
}

function copy_file_checked(string $source, string $destination): void
{
    ensure_directory(dirname($destination));

    if (!copy($source, $destination)) {
        throw new RuntimeException('Could not copy file: ' . $source);
    }
}

function copy_directory_files(string $source, string $destination): void
{
    if (!is_dir($source)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $relative = substr($item->getPathname(), strlen($source) + 1);
        $target = $destination . DIRECTORY_SEPARATOR . $relative;

        if ($item->isDir()) {
            ensure_directory($target);
            continue;
        }

        if ($item->isFile()) {
            copy_file_checked($item->getPathname(), $target);
        }
    }
}

function remove_directory(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    if (is_file($path) || is_link($path)) {
        @unlink($path);
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
        if ($item->isDir()) {
            @rmdir($item->getPathname());
        } else {
            @unlink($item->getPathname());
        }
    }

    @rmdir($path);
}

function add_directory_to_zip(PharData $archive, string $sourceRoot, string $archiveRoot): void
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceRoot, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $item) {
        if (!$item->isFile()) {
            continue;
        }

        $relative = str_replace('\\', '/', substr($item->getPathname(), strlen($sourceRoot) + 1));
        $archive->addFile($item->getPathname(), rtrim($archiveRoot, '/') . '/' . $relative);
    }
}

/** @return string[] */
function image_files(string $directory): array
{
    if (!is_dir($directory)) {
        return [];
    }

    $files = [];
    $iterator = new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS);

    foreach ($iterator as $item) {
        if (!$item->isFile()) {
            continue;
        }

        if (in_array(strtolower($item->getExtension()), ['png', 'jpg', 'jpeg', 'webp'], true)) {
            $files[] = $item->getPathname();
        }
    }

    sort($files, SORT_STRING);
    return $files;
}

function has_diagram_export(string $directory, string $baseName): bool
{
    foreach (['png', 'pdf'] as $extension) {
        if (is_file($directory . DIRECTORY_SEPARATOR . $baseName . '.' . $extension)) {
            return true;
        }
    }

    return false;
}

function print_help(): void
{
    echo <<<'TEXT'
Create the GuideMyPC academic submission ZIP.

Usage:
  php scripts/package-submission.php [options]

Options:
  --commit=REF    Package tracked source from this commit. Default: HEAD
  --output=PATH   ZIP output path. Default: build/GuideMyPC_Submission.zip
  --strict        Require final Word documents, UML source/exports, and 8-10 screenshots
  --force         Replace an existing output ZIP
  --help          Show this help

Final local materials are read from ignored project folders:
  docs/submission/documents/
  docs/submission/screenshots/
  uml/source/
  uml/exports/
TEXT;
    echo PHP_EOL;
}

$options = getopt('', ['commit:', 'output:', 'strict', 'force', 'help']);

if (isset($options['help'])) {
    print_help();
    exit(0);
}

$scriptRoot = dirname(__DIR__);
$rootResult = run_process(['git', '-C', $scriptRoot, 'rev-parse', '--show-toplevel'], $scriptRoot);

if ($rootResult['code'] !== 0 || trim($rootResult['stdout']) === '') {
    fwrite(STDERR, "FAIL: Run this command from a Git working tree.\n");
    exit(1);
}

$root = rtrim(trim($rootResult['stdout']), "\r\n/\\");
$commit = is_string($options['commit'] ?? null) ? trim($options['commit']) : 'HEAD';
$outputOption = is_string($options['output'] ?? null)
    ? trim($options['output'])
    : 'build/GuideMyPC_Submission.zip';
$strict = array_key_exists('strict', $options);
$force = array_key_exists('force', $options);
$temporaryDirectory = '';
$output = '';

try {
    $commitId = trim(run_git($root, ['rev-parse', $commit . '^{commit}']));

    if ($commitId === '') {
        throw new RuntimeException('Could not resolve commit: ' . $commit);
    }

    $trackedOutput = run_git($root, ['ls-tree', '-r', '--name-only', $commitId]);
    $trackedFiles = array_values(array_filter(array_map('trim', preg_split('/\R/', $trackedOutput) ?: [])));

    if ($trackedFiles === []) {
        throw new RuntimeException('The selected commit contains no files.');
    }

    $prohibitedPatterns = [
        '~(^|/)\.env(?:$|\.(?!example$))~i',
        '~(^|/)(?:vendor|node_modules|logs|uploads|storage|coverage|database/backups|build|release)(?:/|$)~i',
        '~(^|/)(?:\.idea|\.vscode|\.code-review-graph)(?:/|$)~i',
        '~\.(?:pem|key|p12|pfx|sql\.gz|bak|backup|zip)$~i',
    ];
    $prohibited = [];

    foreach ($trackedFiles as $path) {
        $normalized = normalize_path($path);

        foreach ($prohibitedPatterns as $pattern) {
            if (preg_match($pattern, $normalized) === 1) {
                $prohibited[] = $normalized;
                break;
            }
        }
    }

    if ($prohibited !== []) {
        throw new RuntimeException("The selected commit contains prohibited paths:\n" . implode("\n", $prohibited));
    }

    $output = absolute_path($root, $outputOption);

    if (strtolower(pathinfo($output, PATHINFO_EXTENSION)) !== 'zip') {
        throw new RuntimeException('The output path must end with .zip.');
    }

    ensure_directory(dirname($output));

    if (file_exists($output)) {
        if (!$force) {
            throw new RuntimeException('Output already exists. Pass --force to replace it: ' . $output);
        }

        if (!unlink($output)) {
            throw new RuntimeException('Could not replace output: ' . $output);
        }
    }

    $temporaryDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'guidemypc-submission-' . bin2hex(random_bytes(8));
    $packageRoot = $temporaryDirectory . DIRECTORY_SEPARATOR . 'GuideMyPC';
    $frontendRoot = $packageRoot . DIRECTORY_SEPARATOR . 'frontend';
    $backendRoot = $packageRoot . DIRECTORY_SEPARATOR . 'backend';
    $databaseRoot = $packageRoot . DIRECTORY_SEPARATOR . 'database';
    $docsRoot = $packageRoot . DIRECTORY_SEPARATOR . 'docs';
    $umlRoot = $packageRoot . DIRECTORY_SEPARATOR . 'uml';

    foreach ([$frontendRoot, $backendRoot, $databaseRoot, $docsRoot, $umlRoot] as $directory) {
        ensure_directory($directory);
    }

    $backendExcludedPrefixes = ['docs/', 'Tasks/', 'uml/'];
    $backendExcludedFiles = ['AGENTS.md', 'opencode.json'];

    foreach ($trackedFiles as $rawPath) {
        $path = normalize_path($rawPath);
        $contents = run_git($root, ['show', $commitId . ':' . $path]);

        if ($path === 'README.md') {
            write_file($packageRoot . DIRECTORY_SEPARATOR . 'README.md', $contents);
        }

        $excludeFromBackend = in_array($path, $backendExcludedFiles, true);

        foreach ($backendExcludedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $excludeFromBackend = true;
                break;
            }
        }

        if (!$excludeFromBackend) {
            write_file($backendRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path), $contents);
        }

        if (str_starts_with($path, 'public/assets/')
            || str_starts_with($path, 'resources/views/')
            || preg_match('~^public/(?:robots\.txt|favicon[^/]*|[^/]+\.(?:png|jpe?g|gif|svg|webp|ico|webmanifest))$~i', $path) === 1
        ) {
            write_file($frontendRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path), $contents);
        }

        if (str_starts_with($path, 'database/')) {
            $relative = substr($path, strlen('database/'));
            write_file($databaseRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative), $contents);
        }

        if (str_starts_with($path, 'docs/')) {
            $relative = substr($path, strlen('docs/'));
            write_file($docsRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative), $contents);
        }

        if (str_starts_with($path, 'uml/')) {
            $relative = substr($path, strlen('uml/'));
            write_file($umlRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative), $contents);
        }
    }

    $localMaterials = [
        $root . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'submission' . DIRECTORY_SEPARATOR . 'documents'
            => $docsRoot . DIRECTORY_SEPARATOR . 'submission' . DIRECTORY_SEPARATOR . 'documents',
        $root . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'submission' . DIRECTORY_SEPARATOR . 'screenshots'
            => $docsRoot . DIRECTORY_SEPARATOR . 'submission' . DIRECTORY_SEPARATOR . 'screenshots',
        $root . DIRECTORY_SEPARATOR . 'uml' . DIRECTORY_SEPARATOR . 'source'
            => $umlRoot . DIRECTORY_SEPARATOR . 'source',
        $root . DIRECTORY_SEPARATOR . 'uml' . DIRECTORY_SEPARATOR . 'exports'
            => $umlRoot . DIRECTORY_SEPARATOR . 'exports',
    ];

    foreach ($localMaterials as $source => $destination) {
        copy_directory_files($source, $destination);
    }

    if ($strict) {
        $missing = [];
        $readmeDocument = $docsRoot . DIRECTORY_SEPARATOR . 'submission' . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR . 'Readme.docx';
        $finalReport = $docsRoot . DIRECTORY_SEPARATOR . 'submission' . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR . 'GuideMyPC-Final-Report.docx';
        $umlProject = $umlRoot . DIRECTORY_SEPARATOR . 'source' . DIRECTORY_SEPARATOR . 'GuideMyPC.vpp';
        $umlExports = $umlRoot . DIRECTORY_SEPARATOR . 'exports';
        $screenshots = $docsRoot . DIRECTORY_SEPARATOR . 'submission' . DIRECTORY_SEPARATOR . 'screenshots';

        foreach ([
            $readmeDocument => 'docs/submission/documents/Readme.docx',
            $finalReport => 'docs/submission/documents/GuideMyPC-Final-Report.docx',
            $umlProject => 'uml/source/GuideMyPC.vpp',
        ] as $path => $label) {
            if (!is_file($path) || filesize($path) === 0) {
                $missing[] = $label;
            }
        }

        foreach (['use-case', 'class-diagram', 'activity-diagram', 'state-machine'] as $diagram) {
            if (!has_diagram_export($umlExports, $diagram)) {
                $missing[] = 'uml/exports/' . $diagram . '.png or .pdf';
            }
        }

        $screenshotCount = count(image_files($screenshots));

        if ($screenshotCount < 8 || $screenshotCount > 10) {
            $missing[] = sprintf('8-10 screenshot images in docs/submission/screenshots/ (found %d)', $screenshotCount);
        }

        if ($missing !== []) {
            throw new RuntimeException("Strict package requirements are incomplete:\n- " . implode("\n- ", $missing));
        }
    }

    $manifest = implode(PHP_EOL, [
        'GuideMyPC academic submission package',
        'Source commit: ' . $commitId,
        'Created at (UTC): ' . gmdate('Y-m-d\TH:i:s\Z'),
        '',
        'Folder mapping:',
        'frontend/  Public assets and server-rendered view templates.',
        'backend/   Runnable source tree with original relative paths. It intentionally repeats frontend and database files required at runtime.',
        'database/  Migrations, seeds, and database command files.',
        'uml/       Editable Visual Paradigm source and exported diagrams.',
        'docs/      Technical documentation, final Word documents, screenshots, and release evidence.',
        '',
        'Local ignored submission materials were overlaid from docs/submission/ and uml/ when present.',
        'Edit the repository source, then rebuild this ZIP. Do not edit generated copies.',
        '',
    ]);
    write_file($packageRoot . DIRECTORY_SEPARATOR . 'PACKAGE-MANIFEST.txt', $manifest);

    if (!class_exists(PharData::class)) {
        throw new RuntimeException('The PHP Phar extension is required to create the ZIP package.');
    }

    $archive = new PharData($output);
    add_directory_to_zip($archive, $packageRoot, 'GuideMyPC');
    unset($archive);

    if (!is_file($output) || filesize($output) === 0) {
        throw new RuntimeException('The ZIP package was not created.');
    }

    echo 'Created ' . $output . ' from commit ' . $commitId . ($strict ? ' with strict checks.' : '.') . PHP_EOL;
} catch (Throwable $exception) {
    if ($output !== '' && is_file($output)) {
        @unlink($output);
    }

    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
} finally {
    if ($temporaryDirectory !== '') {
        remove_directory($temporaryDirectory);
    }
}
