<?php
require_once __DIR__ . '/config.php';
require_post();
require_csrf();

session_unset();

if (ini_get('session.use_cookies')) {
    $parameters = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $parameters['path'], '', (bool) $parameters['secure'], (bool) $parameters['httponly']);
}

session_destroy();
redirect('index.php');
