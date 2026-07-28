<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

require_post();
require_login();
require_csrf();
$userId = current_user_id();
remembered_device_service($conn)->revokeAll($userId, 'logout_all');
record_account_event($conn, $userId, 'logout');
clear_remembered_device_cookie();
session_unset();

if (ini_get('session.use_cookies')) {
    $parameters = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $parameters['path'],
        'domain' => $parameters['domain'],
        'secure' => (bool) $parameters['secure'],
        'httponly' => (bool) $parameters['httponly'],
        'samesite' => $parameters['samesite'] ?? 'Lax',
    ]);
}

session_destroy();
redirect('index.php');
