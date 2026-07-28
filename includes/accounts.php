<?php

declare(strict_types=1);

function normalize_email(mixed $value): ?string
{
    $email = is_string($value) ? mb_strtolower(trim($value)) : '';

    return mb_strlen($email) <= 150 && filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
}

function valid_display_name(mixed $value): ?string
{
    $name = required_string($value, 100);

    return $name !== null && mb_strlen($name) >= 2 ? $name : null;
}

function record_account_event(mysqli $connection, ?int $userId, string $event): void
{
    (new GuideMyPC\Features\Accounts\AccountService($connection))->recordSecurityEvent($userId, $event);
}

function record_user_activity(mysqli $connection, int $userId, string $type, string $subjectType, string $subjectValue): void
{
    (new GuideMyPC\Features\Accounts\AccountService($connection))->recordActivity($userId, $type, $subjectType, $subjectValue);
}

function merge_guest_progress(mysqli $connection, int $userId): void
{
    $guestProgress = $_SESSION['_guest_progress'] ?? [];

    if ($userId < 1 || !is_array($guestProgress)) {
        return;
    }

    (new GuideMyPC\Features\Accounts\AccountService($connection))->mergeGuestProgress($userId, $guestProgress);
    unset($_SESSION['_guest_progress']);
}

function create_password_reset_token(mysqli $connection, int $userId): string
{
    return (new GuideMyPC\Features\Accounts\AccountService($connection))->createPasswordResetToken($userId);
}

function consume_password_reset_token(mysqli $connection, string $token, string $password): ?int
{
    return (new GuideMyPC\Features\Accounts\AccountService($connection))->consumePasswordResetToken($token, $password);
}

function remembered_device_service(mysqli $connection): GuideMyPC\Features\Accounts\RememberedDeviceService
{
    $days = (int) (config_value('REMEMBER_DEVICE_DAYS', '30') ?? '30');
    $limit = (int) (config_value('REMEMBER_DEVICE_LIMIT', '5') ?? '5');
    $pepper = config_value('REMEMBER_TOKEN_PEPPER', '') ?? '';

    if (config_value('APP_ENV', 'local') === 'production' && $pepper === '') {
        throw new RuntimeException('REMEMBER_TOKEN_PEPPER must be configured in production.');
    }

    return new GuideMyPC\Features\Accounts\RememberedDeviceService(
        $connection,
        $pepper,
        min(90, max(1, $days)),
        min(10, max(1, $limit)),
    );
}

function remembered_device_cookie_name(): string
{
    return 'guidemypc_remember';
}

/** @return array{expires: int, path: string, domain: string, secure: bool, httponly: bool, samesite: string} */
function remembered_device_cookie_options(int $expiresAt): array
{
    $secure = filter_var(config_value('SESSION_COOKIE_SECURE'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? is_https_request();
    $domain = config_value('SESSION_COOKIE_DOMAIN', '') ?? '';

    if (config_value('APP_ENV', 'local') === 'production' && !$secure) {
        throw new RuntimeException('SESSION_COOKIE_SECURE must be enabled in production.');
    }

    return [
        'expires' => $expiresAt,
        'path' => '/',
        'domain' => $domain,
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function clear_remembered_device_cookie(): void
{
    setcookie(remembered_device_cookie_name(), '', remembered_device_cookie_options(time() - 3600));
    unset($_COOKIE[remembered_device_cookie_name()]);
    unset($_SESSION['_remember_selector']);
}

function issue_remembered_device(mysqli $connection, int $userId): void
{
    $token = remembered_device_service($connection)->issue($userId);
    setcookie(remembered_device_cookie_name(), $token['cookie'], remembered_device_cookie_options($token['expires_at']));
    $_SESSION['_remember_selector'] = $token['selector'];
}

function establish_account_session(array $user, bool $regenerate = true): void
{
    if ($regenerate) {
        session_regenerate_id(true);
    }

    $_SESSION['user_id'] = (int) $user['user_id'];
    $_SESSION['full_name'] = (string) $user['full_name'];
    $_SESSION['role'] = (string) $user['role'];
}

function restore_remembered_account_session(mysqli $connection): bool
{
    if (is_logged_in()) {
        $authorized = refresh_current_user_authorization($connection);

        if (!$authorized) {
            clear_remembered_device_cookie();
        }

        return $authorized;
    }

    $cookie = $_COOKIE[remembered_device_cookie_name()] ?? null;

    if (!is_string($cookie) || $cookie === '') {
        return false;
    }

    $account = remembered_device_service($connection)->authenticate($cookie);

    if ($account === null) {
        clear_remembered_device_cookie();
        return false;
    }

    establish_account_session($account);
    $_SESSION['_remember_selector'] = $account['selector'];
    setcookie(remembered_device_cookie_name(), $account['cookie'], remembered_device_cookie_options($account['expires_at']));

    return true;
}

function revoke_current_remembered_device(mysqli $connection, int $userId): void
{
    $selector = $_SESSION['_remember_selector'] ?? null;

    if ($userId > 0 && is_string($selector) && preg_match('/^[a-f0-9]{24}$/', $selector) === 1) {
        remembered_device_service($connection)->revokeSelector($userId, $selector);
    }

    clear_remembered_device_cookie();
}
