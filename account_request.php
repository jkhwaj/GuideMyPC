<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/accounts.php';

require_post();
require_csrf();
require_login();
$userId = current_user_id();
$type = required_string($_POST['request_type'] ?? null, 20) ?? '';

if (!in_array($type, ['export', 'deletion'], true)) {
    abort_request(422, 'invalid_account_request', 'Choose a valid account request.');
}

$statement = $conn->prepare("SELECT id FROM user_data_requests WHERE user_id = ? AND request_type = ? AND request_status = 'requested' LIMIT 1");
$statement->bind_param('is', $userId, $type);
$statement->execute();
$exists = $statement->get_result()->num_rows > 0;
$statement->close();

if (!$exists) {
    $insert = $conn->prepare('INSERT INTO user_data_requests (user_id, request_type) VALUES (?, ?)');
    $insert->bind_param('is', $userId, $type);
    $insert->execute();
    $insert->close();
    record_account_event($conn, $userId, $type === 'export' ? 'export_request' : 'deletion_request');
}

flash('success', $type === 'export' ? 'Your data export request was recorded. An operator must prepare the export in this prototype.' : 'Your deletion request was recorded. An operator must verify and complete it before data is removed.');
redirect('profile.php');
