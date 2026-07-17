<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/accounts.php';
require_post();
require_csrf();

if (current_user_id() > 0) {
    record_account_event($conn, current_user_id(), 'logout');
}

session_unset();

if (ini_get('session.use_cookies')) {
    $parameters = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $parameters['path'], '', (bool) $parameters['secure'], (bool) $parameters['httponly']);
}

session_destroy();
redirect('index.php');
