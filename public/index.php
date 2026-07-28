<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$routes = array_replace(
    require $root . '/routes/web.php',
    require $root . '/routes/admin.php',
    require $root . '/routes/api.php'
);
$requestPath = rawurldecode((string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'));
$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$basePath = $basePath === '/' ? '' : rtrim($basePath, '/');
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
