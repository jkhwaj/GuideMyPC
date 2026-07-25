<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

$route = $_SERVER['argv'][1] ?? '';

if (!in_array($route, ['ai.php', 'donate.php'], true)) {
    fwrite(STDERR, "Invalid retired route probe.\n");
    exit(2);
}

$_SERVER['REQUEST_URI'] = '/' . $route;
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

register_shutdown_function(static function (): void {
    echo "\n<!-- test-status:" . http_response_code() . " -->\n";
});

require dirname(__DIR__) . '/public/index.php';
