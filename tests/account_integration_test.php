<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/accounts.php';

$test = new mysqli(
    config_value('DB_HOST'),
    config_value('DB_USER'),
    config_value('DB_PASSWORD', ''),
    'guidemypc_knowledge_test',
    (int) config_value('DB_PORT', '3306')
);
$test->set_charset('utf8mb4');
$email = 'account-test-' . bin2hex(random_bytes(4)) . '@example.test';
$name = 'Account Test';
$password = password_hash('InitialPassword1!', PASSWORD_DEFAULT);
$insert = $test->prepare('INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)');
$insert->bind_param('sss', $name, $email, $password);
$insert->execute();
$userId = $insert->insert_id;
$insert->close();

try {
    $token = create_password_reset_token($test, $userId);
    $resetUserId = consume_password_reset_token($test, $token, 'ReplacementPassword1!');
    $reuse = consume_password_reset_token($test, $token, 'AnotherPassword1!');
    $user = $test->query('SELECT password FROM users WHERE id = ' . $userId)->fetch_assoc();
    $step = $test->query("SELECT guide_steps.id FROM guide_steps JOIN guides ON guide_steps.guide_id = guides.id WHERE guides.slug = 'check-windows-update-issue' LIMIT 1")->fetch_assoc();

    if ($resetUserId !== $userId || $reuse !== null || $user === null || !password_verify('ReplacementPassword1!', $user['password']) || $step === null) {
        throw new RuntimeException('Password reset token was not expiring, single use, or correctly consumed.');
    }

    $_SESSION['_guest_progress'] = [1 => [(int) $step['id'] => true]];
    merge_guest_progress($test, $userId);
    $progress = (int) $test->query('SELECT COUNT(*) AS total FROM user_progress WHERE user_id = ' . $userId)->fetch_assoc()['total'];
    record_user_activity($test, $userId, 'guide_view', 'guide', 'check-windows-update-issue');
    $activity = (int) $test->query('SELECT COUNT(*) AS total FROM user_activity WHERE user_id = ' . $userId)->fetch_assoc()['total'];

    if ($progress !== 1 || $activity !== 1 || isset($_SESSION['_guest_progress'])) {
        throw new RuntimeException('Guest progress merge or account activity recording failed.');
    }

    fwrite(STDOUT, "PASS: reset token, guest progress merge, and account activity work.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . PHP_EOL);
    $exitCode = 1;
} finally {
    $delete = $test->prepare('DELETE FROM users WHERE id = ?');
    $delete->bind_param('i', $userId);
    $delete->execute();
    $delete->close();
    $test->close();
}

if (isset($exitCode)) {
    exit($exitCode);
}
