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
