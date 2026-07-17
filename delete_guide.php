<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/admin.php';

require_admin_post();

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($id === false) {
    flash('error', 'Choose a valid guide to delete.');
    redirect('admin_guides.php');
}

$deleted = in_transaction($conn, static function () use ($conn, $id): bool {
    $find = $conn->prepare('SELECT slug FROM guides WHERE id = ? FOR UPDATE');
    $find->bind_param('i', $id);
    $find->execute();
    $guide = $find->get_result()->fetch_assoc();
    $find->close();

    if ($guide === null) {
        return false;
    }

    $delete = $conn->prepare('DELETE FROM guides WHERE id = ?');
    $delete->bind_param('i', $id);
    $delete->execute();
    $delete->close();
    admin_audit($conn, 'guide.delete', 'guide', $id, ['slug' => $guide['slug']]);

    return true;
});

flash($deleted ? 'success' : 'error', $deleted ? 'Guide deleted successfully.' : 'That guide no longer exists.');
redirect('admin_guides.php');
