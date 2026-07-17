<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (is_file($autoload)) {
    require_once $autoload;
}

require_once $root . '/includes/bootstrap.php';
require_once $root . '/includes/functions.php';
require_once $root . '/includes/security.php';
require_once $root . '/includes/errors.php';
require_once $root . '/includes/db.php';

configure_error_handling();
configure_application_error_handling();

if (PHP_SAPI !== 'cli') {
    configure_session();
    send_security_headers();
}
