<?php

declare(strict_types=1);

require_once __DIR__ . '/cli.php';

// Tests may exercise session-backed helpers without creating a browser session.
if (!isset($_SESSION) || !is_array($_SESSION)) {
    $_SESSION = [];
}
