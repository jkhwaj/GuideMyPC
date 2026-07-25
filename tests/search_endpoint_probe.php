<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

$endpoint = $argv[1] ?? '';
$method = $argv[2] ?? '';
$database = $argv[3] ?? '';
$payload = base64_decode($argv[4] ?? '', true);
$privatePath = $argv[5] ?? '';
$allowedEndpoints = ['search_suggestions.php', 'search_event.php'];

if (!in_array($endpoint, $allowedEndpoints, true)
    || !in_array($method, ['GET', 'POST'], true)
    || preg_match('/^[A-Za-z0-9_]+_test$/', $database) !== 1
    || !is_string($payload)
    || $privatePath === '') {
    fwrite(STDERR, "Invalid endpoint probe arguments.\n");
    exit(2);
}

require_once dirname(__DIR__) . '/bootstrap/web.php';

$guideMyPcEnvironment['DB_NAME'] = $database;
$guideMyPcEnvironment['APP_PRIVATE_PATH'] = $privatePath;
$_SERVER['REQUEST_METHOD'] = $method;
$_SERVER['REQUEST_URI'] = '/' . $endpoint;
$_SERVER['SCRIPT_NAME'] = '/' . $endpoint;
$_SERVER['REMOTE_ADDR'] = '127.0.0.77';
parse_str($payload, $input);

if ($method === 'GET') {
    $_GET = $input;
    $_POST = [];
} else {
    $_GET = [];
    $_POST = $input;
}

register_shutdown_function(static function (): void {
    fwrite(STDOUT, "\n__STATUS__" . http_response_code());
});

require dirname(__DIR__) . '/' . $endpoint;
