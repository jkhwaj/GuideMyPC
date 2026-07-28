<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/accounts.php';

use GuideMyPC\Features\Accounts\RememberedDeviceService;
use GuideMyPC\Features\Guides\GuideProgressService;

$test = test_database_or_fail();
$suffix = bin2hex(random_bytes(4));
$userIds = [];
$guideMyPcEnvironment['REMEMBER_TOKEN_PEPPER'] = 'test-pepper';

try {
    foreach (['owner', 'other'] as $name) {
        $email = $name . '-device-' . $suffix . '@example.test';
        $password = password_hash('RememberedPassword1!', PASSWORD_DEFAULT);
        $statement = $test->prepare('INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)');
        $displayName = ucfirst($name) . ' Device Test';
        $statement->bind_param('sss', $displayName, $email, $password);
        $statement->execute();
        $userIds[$name] = $statement->insert_id;
        $statement->close();
    }

    $guide = $test->query("SELECT guides.id AS guide_id, guide_steps.id AS step_id FROM guides JOIN guide_steps ON guide_steps.guide_id = guides.id WHERE guides.slug = 'check-windows-update-issue' LIMIT 1")->fetch_assoc();
    if ($guide === null) {
        throw new RuntimeException('The seeded guide fixture is unavailable for central-account verification.');
    }
    $session = [];
    (new GuideProgressService($test))->save($userIds['owner'], (int) $guide['guide_id'], (int) $guide['step_id'], true, $session);
    $favorite = $test->prepare('INSERT INTO favorites (user_id, guide_id) VALUES (?, ?)');
    $favorite->bind_param('ii', $userIds['owner'], $guide['guide_id']);
    $favorite->execute();
    $favorite->close();
    $rating = $test->prepare('INSERT INTO guide_ratings (user_id, guide_id, rating) VALUES (?, ?, 5)');
    $rating->bind_param('ii', $userIds['owner'], $guide['guide_id']);
    $rating->execute();
    $rating->close();
    $secondConnection = test_database_connection();
    $central = $secondConnection->prepare('SELECT (SELECT COUNT(*) FROM user_progress WHERE user_id = ?) AS progress, (SELECT COUNT(*) FROM favorites WHERE user_id = ?) AS favorites, (SELECT COUNT(*) FROM guide_ratings WHERE user_id = ?) AS ratings');
    $central->bind_param('iii', $userIds['owner'], $userIds['owner'], $userIds['owner']);
    $central->execute();
    $centralData = $central->get_result()->fetch_assoc();
    $central->close();
    $secondConnection->close();

    if ($centralData === null || (int) $centralData['progress'] !== 1 || (int) $centralData['favorites'] !== 1 || (int) $centralData['ratings'] !== 1) {
        throw new RuntimeException('Central account data was not visible from a separate browser/database connection.');
    }

    $service = new RememberedDeviceService($test, 'test-pepper', 30, 5);
    $automatic = $service->issue($userIds['owner']);
    $_SESSION = [];
    $_COOKIE[remembered_device_cookie_name()] = $automatic['cookie'];
    $restored = restore_remembered_account_session($test);

    if (!$restored || current_user_id() !== $userIds['owner'] || ($_SESSION['_remember_selector'] ?? null) !== $automatic['selector'] || $service->authenticate($automatic['cookie']) !== null) {
        throw new RuntimeException('Automatic remembered-browser authentication did not establish and rotate the account session.');
    }

    $switchToken = $service->issue($userIds['owner']);
    $_SESSION['_remember_selector'] = $switchToken['selector'];
    $_SESSION['user_id'] = $userIds['owner'];
    revoke_current_remembered_device($test, current_user_id());
    establish_account_session(['user_id' => $userIds['other'], 'full_name' => 'Other Device Test', 'role' => 'user']);

    if (current_user_id() !== $userIds['other'] || $service->authenticate($switchToken['cookie']) !== null || isset($_SESSION['_remember_selector'])) {
        throw new RuntimeException('A password-login account switch retained the previous remembered browser.');
    }

    $test->query("SET time_zone = '+05:00'");
    $first = $service->issue($userIds['owner'], 'Primary browser');
    [, $rawValidator] = explode('.', $first['cookie'], 2);
    $stored = $test->prepare('SELECT validator_hash FROM account_remember_tokens WHERE selector = ?');
    $stored->bind_param('s', $first['selector']);
    $stored->execute();
    $storedHash = $stored->get_result()->fetch_assoc()['validator_hash'] ?? null;
    $stored->close();

    $authenticated = $service->authenticate($first['cookie']);
    $replayed = $service->authenticate($first['cookie']);

    $expiryIsUtcSafe = (int) $test->query("SELECT expires_at > UTC_TIMESTAMP() + INTERVAL 29 DAY AS valid_expiry FROM account_remember_tokens WHERE selector = '" . $test->real_escape_string($first['selector']) . "'")->fetch_assoc()['valid_expiry'] === 1;

    if (
        $storedHash === null
        || hash_equals($storedHash, $rawValidator)
        || $authenticated === null
        || $authenticated['user_id'] !== $userIds['owner']
        || $authenticated['selector'] !== $first['selector']
        || $replayed !== null
        || !$expiryIsUtcSafe
    ) {
        throw new RuntimeException('Remembered-device tokens were not hash-only, owner-bound, or rotated.');
    }

    $second = $service->issue($userIds['owner'], 'Second browser');
    $third = $service->issue($userIds['owner'], 'Third browser');
    $devices = $service->devicesForUser($userIds['owner'], $authenticated['selector']);

    if (count($devices) !== 4 || !array_filter($devices, static fn (array $device): bool => $device['is_current'])) {
        throw new RuntimeException('Remembered-device rotation or safe device projection failed.');
    }

    if ($service->revoke($userIds['other'], (int) $devices[0]['id'])) {
        throw new RuntimeException('A different account revoked another account device.');
    }

    if (!$service->revokeSelector($userIds['owner'], $second['selector']) || $service->authenticate($second['cookie']) !== null) {
        throw new RuntimeException('Per-device revocation did not block future automatic sign-in.');
    }

    $service->revokeAll($userIds['owner'], 'logout_all');

    if ($service->authenticate($third['cookie']) !== null) {
        throw new RuntimeException('Logout-all did not revoke every remembered device.');
    }

    $expired = $service->issue($userIds['owner']);
    $expire = $test->prepare('UPDATE account_remember_tokens SET expires_at = UTC_TIMESTAMP() - INTERVAL 1 SECOND WHERE selector = ?');
    $expire->bind_param('s', $expired['selector']);
    $expire->execute();
    $expire->close();

    if ($service->authenticate($expired['cookie']) !== null) {
        throw new RuntimeException('Expired remembered devices were accepted.');
    }

    $limitedService = new RememberedDeviceService($test, 'test-pepper', 30, 1);
    $limitedFirst = $limitedService->issue($userIds['other']);
    $limitedSecond = $limitedService->issue($userIds['other']);

    if (count($limitedService->devicesForUser($userIds['other'], null)) !== 1 || $limitedService->authenticate($limitedFirst['cookie']) !== null || $limitedService->authenticate($limitedSecond['cookie']) === null) {
        throw new RuntimeException('Remembered-device limit did not retain only the newest active device.');
    }

    $inactive = $service->issue($userIds['owner']);
    $disable = $test->prepare("UPDATE users SET status = 'disabled' WHERE id = ?");
    $disable->bind_param('i', $userIds['owner']);
    $disable->execute();
    $disable->close();

    if ($service->authenticate($inactive['cookie']) !== null) {
        throw new RuntimeException('Disabled accounts were restored from remembered-device tokens.');
    }

    fwrite(STDOUT, "PASS: remembered-device issue, rotation, ownership, revocation, limit, and disabled-account controls work.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . PHP_EOL);
    $exitCode = 1;
} finally {
    $test->query("SET time_zone = '+00:00'");
    foreach ($userIds as $userId) {
        foreach (['user_progress', 'favorites', 'guide_ratings'] as $table) {
            $delete = $test->prepare('DELETE FROM ' . $table . ' WHERE user_id = ?');
            $delete->bind_param('i', $userId);
            $delete->execute();
            $delete->close();
        }
        $delete = $test->prepare('DELETE FROM account_remember_tokens WHERE user_id = ?');
        $delete->bind_param('i', $userId);
        $delete->execute();
        $delete->close();

        $delete = $test->prepare('DELETE FROM users WHERE id = ?');
        $delete->bind_param('i', $userId);
        $delete->execute();
        $delete->close();
    }
    $test->close();
}

if (isset($exitCode)) {
    exit($exitCode);
}
