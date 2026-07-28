<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$routes = array_replace(
    require $root . '/routes/web.php',
    require $root . '/routes/admin.php',
    require $root . '/routes/api.php'
);
$requestPath = rawurldecode((string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'));
$entryBasePath = $_SERVER['GUIDEMYPC_ENTRY_BASE_PATH'] ?? null;
$entryScriptName = $_SERVER['GUIDEMYPC_ENTRY_SCRIPT_NAME'] ?? $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$basePath = is_string($entryBasePath) ? $entryBasePath : str_replace('\\', '/', dirname($entryScriptName));
$basePath = $basePath === '/' ? '' : rtrim($basePath, '/');
if ($basePath !== '' && preg_match('#^/(?:[A-Za-z0-9_-][A-Za-z0-9._~-]*(?:/[A-Za-z0-9_-][A-Za-z0-9._~-]*)*)$#', $basePath) !== 1) {
    $basePath = '';
}
$_SERVER['GUIDEMYPC_BASE_PATH'] = $basePath;

if ($basePath !== '' && str_starts_with($requestPath, $basePath . '/')) {
    $requestPath = substr($requestPath, strlen($basePath));
}

$route = ltrim($requestPath, '/');
$route = $route === '' ? 'index.php' : $route;

if (!isset($routes[$route])) {
    require $root . '/bootstrap/web.php';
    abort_request(404, 'route_not_found', 'The requested page was not found.');
}

$_SERVER['SCRIPT_NAME'] = ($basePath === '' ? '' : $basePath) . '/' . $route;

require $root . DIRECTORY_SEPARATOR . $route;
