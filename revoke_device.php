<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

require_post();
require_login();
require_csrf();
$deviceId = filter_input(INPUT_POST, 'device_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($deviceId === false || $deviceId === null || !remembered_device_service($conn)->revoke(current_user_id(), $deviceId)) {
    flash('error', 'That signed-in browser was not found.');
} else {
    $currentSelector = $_SESSION['_remember_selector'] ?? null;
    $currentDevices = remembered_device_service($conn)->devicesForUser(current_user_id(), is_string($currentSelector) ? $currentSelector : null);
    if ($currentDevices === [] || !array_filter($currentDevices, static fn (array $device): bool => $device['is_current'])) {
        clear_remembered_device_cookie();
    }
    flash('success', 'The selected browser was signed out.');
}

redirect('devices.php');
