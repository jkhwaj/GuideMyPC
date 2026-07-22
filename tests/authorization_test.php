<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once dirname(__DIR__) . '/bootstrap/test.php';

use GuideMyPC\Security\Authorization;

function authorization_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, 'FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$expectations = [
    'user' => [
        Authorization::VIEW_PERSONAL_DASHBOARD => true,
        Authorization::VIEW_CONTENT_DASHBOARD => false,
        Authorization::MANAGE_CONTENT => false,
        Authorization::VIEW_AUDIT => false,
        Authorization::MANAGE_USERS => false,
    ],
    'editor' => [
        Authorization::VIEW_PERSONAL_DASHBOARD => true,
        Authorization::VIEW_CONTENT_DASHBOARD => true,
        Authorization::MANAGE_CONTENT => true,
        Authorization::PUBLISH_CONTENT => true,
        Authorization::MODERATE_COMMUNITY => true,
        Authorization::DELETE_CONTENT => false,
        Authorization::VIEW_AUDIT => false,
        Authorization::MANAGE_USERS => false,
    ],
    'admin' => [
        Authorization::VIEW_PERSONAL_DASHBOARD => true,
        Authorization::VIEW_CONTENT_DASHBOARD => true,
        Authorization::MANAGE_CONTENT => true,
        Authorization::PUBLISH_CONTENT => true,
        Authorization::MODERATE_COMMUNITY => true,
        Authorization::DELETE_CONTENT => true,
        Authorization::VIEW_AUDIT => true,
        Authorization::MANAGE_USERS => true,
        Authorization::CONFIGURE_SYSTEM => true,
    ],
];

foreach ($expectations as $role => $capabilities) {
    foreach ($capabilities as $capability => $expected) {
        authorization_assert(
            Authorization::allows($role, $capability) === $expected,
            $role . ' capability mismatch for ' . $capability
        );
    }
}

authorization_assert(Authorization::normalizeRole('editor') === 'editor', 'Editor is a valid normalized role.');
authorization_assert(Authorization::normalizeRole('owner') === null, 'Unknown roles are rejected.');
authorization_assert(!Authorization::allows('owner', Authorization::MANAGE_USERS), 'Unknown roles fail closed.');
authorization_assert(!Authorization::allows(null, Authorization::VIEW_PERSONAL_DASHBOARD), 'Guests have no account capabilities.');

$_SESSION = ['user_id' => 8, 'role' => 'editor'];
authorization_assert(is_editor(), 'Editor compatibility helper permits content management.');
authorization_assert(!is_admin(), 'Editor compatibility helper does not grant administrator access.');
authorization_assert(current_user_role() === 'editor', 'Session role is normalized by the compatibility helper.');

$_SESSION = ['user_id' => 9, 'role' => 'admin'];
authorization_assert(is_editor() && is_admin(), 'Administrators retain editor and administrator access.');

$_SESSION = [];
authorization_assert(current_user_role() === null && !is_editor() && !is_admin(), 'Logged-out helpers fail closed.');

fwrite(STDOUT, "PASS: authorization capability matrix and compatibility helpers work.\n");
