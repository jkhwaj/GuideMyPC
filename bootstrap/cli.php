<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

$root = dirname(__DIR__);

require_once __DIR__ . '/autoload.php';

require_once $root . '/includes/bootstrap.php';
require_once $root . '/includes/functions.php';
require_once $root . '/includes/security.php';
require_once $root . '/includes/errors.php';
require_once $root . '/includes/db.php';

configure_error_handling();
configure_application_error_handling();
