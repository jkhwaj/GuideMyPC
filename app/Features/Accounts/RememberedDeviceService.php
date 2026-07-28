<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Accounts;

use mysqli;
use Throwable;

final class RememberedDeviceService
{
    public function __construct(
        private readonly mysqli $connection,
        private readonly string $pepper,
        private readonly int $lifetimeDays = 30,
        private readonly int $maximumDevices = 5,
    ) {
    }

    /**
     * @return array{selector: string, cookie: string, expires_at: int}
     */
    public function issue(int $userId, string $deviceLabel = 'This browser'): array
    {
        $selector = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(32));
        $expiresAt = time() + ($this->lifetimeDays * 86400);
        $hash = $this->validatorHash($validator);
        $deviceLabel = $this->deviceLabel($deviceLabel);
        $lifetimeDays = $this->lifetimeDays;

        $this->connection->begin_transaction();

        try {
            $statement = $this->connection->prepare('INSERT INTO account_remember_tokens (user_id, selector, validator_hash, device_label, expires_at) VALUES (?, ?, ?, ?, DATE_ADD(UTC_TIMESTAMP(), INTERVAL ? DAY))');
            $statement->bind_param('isssi', $userId, $selector, $hash, $deviceLabel, $lifetimeDays);
            $statement->execute();
            $statement->close();
            $this->enforceDeviceLimit($userId);
            $this->connection->commit();
        } catch (Throwable $exception) {
            $this->connection->rollback();
            throw $exception;
        }

        return [
            'selector' => $selector,
            'cookie' => $selector . '.' . $validator,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * @return array{user_id: int, full_name: string, role: string, selector: string, cookie: string, expires_at: int}|null
     */
    public function authenticate(string $cookie): ?array
    {
        [$selector, $validator] = $this->parseCookie($cookie);

        if ($selector === null || $validator === null) {
            return null;
        }

        $this->connection->begin_transaction();

        try {
            $statement = $this->connection->prepare("SELECT tokens.user_id, tokens.validator_hash, users.full_name, users.role FROM account_remember_tokens AS tokens JOIN users ON users.id = tokens.user_id WHERE tokens.selector = ? AND tokens.revoked_at IS NULL AND tokens.expires_at > UTC_TIMESTAMP() AND users.status = 'active' AND users.deleted_at IS NULL LIMIT 1 FOR UPDATE");
            $statement->bind_param('s', $selector);
            $statement->execute();
            $token = $statement->get_result()->fetch_assoc();
            $statement->close();

            if ($token === null || !hash_equals((string) $token['validator_hash'], $this->validatorHash($validator))) {
                $this->connection->commit();
                return null;
            }

            $nextValidator = bin2hex(random_bytes(32));
            $nextExpiresAt = time() + ($this->lifetimeDays * 86400);
            $nextHash = $this->validatorHash($nextValidator);
            $lifetimeDays = $this->lifetimeDays;
            $update = $this->connection->prepare('UPDATE account_remember_tokens SET validator_hash = ?, last_used_at = UTC_TIMESTAMP(), expires_at = DATE_ADD(UTC_TIMESTAMP(), INTERVAL ? DAY) WHERE selector = ?');
            $update->bind_param('sis', $nextHash, $lifetimeDays, $selector);
            $update->execute();
            $update->close();
            $this->connection->commit();

            return [
                'user_id' => (int) $token['user_id'],
                'full_name' => (string) $token['full_name'],
                'role' => (string) $token['role'],
                'selector' => $selector,
                'cookie' => $selector . '.' . $nextValidator,
                'expires_at' => $nextExpiresAt,
            ];
        } catch (Throwable $exception) {
            $this->connection->rollback();
            throw $exception;
        }
    }

    /** @return list<array{id: int, device_label: string, created_at: string, last_used_at: ?string, expires_at: string, is_current: bool}> */
    public function devicesForUser(int $userId, ?string $currentSelector): array
    {
        $statement = $this->connection->prepare('SELECT id, selector, device_label, created_at, last_used_at, expires_at FROM account_remember_tokens WHERE user_id = ? AND revoked_at IS NULL AND expires_at > UTC_TIMESTAMP() ORDER BY last_used_at DESC, created_at DESC');
        $statement->bind_param('i', $userId);
        $statement->execute();
        $result = $statement->get_result();
        $devices = [];

        while ($row = $result->fetch_assoc()) {
            $devices[] = [
                'id' => (int) $row['id'],
                'device_label' => (string) $row['device_label'],
                'created_at' => (string) $row['created_at'],
                'last_used_at' => $row['last_used_at'] === null ? null : (string) $row['last_used_at'],
                'expires_at' => (string) $row['expires_at'],
                'is_current' => $currentSelector !== null && hash_equals($currentSelector, (string) $row['selector']),
            ];
        }

        $statement->close();

        return $devices;
    }

    public function revoke(int $userId, int $deviceId, string $reason = 'revoked'): bool
    {
        $statement = $this->connection->prepare('UPDATE account_remember_tokens SET revoked_at = UTC_TIMESTAMP(), revoked_reason = ? WHERE id = ? AND user_id = ? AND revoked_at IS NULL');
        $statement->bind_param('sii', $reason, $deviceId, $userId);
        $statement->execute();
        $revoked = $statement->affected_rows === 1;
        $statement->close();

        return $revoked;
    }

    public function revokeSelector(int $userId, string $selector, string $reason = 'logout'): bool
    {
        $statement = $this->connection->prepare('UPDATE account_remember_tokens SET revoked_at = UTC_TIMESTAMP(), revoked_reason = ? WHERE selector = ? AND user_id = ? AND revoked_at IS NULL');
        $statement->bind_param('ssi', $reason, $selector, $userId);
        $statement->execute();
        $revoked = $statement->affected_rows === 1;
        $statement->close();

        return $revoked;
    }

    public function revokeAll(int $userId, string $reason): void
    {
        $statement = $this->connection->prepare('UPDATE account_remember_tokens SET revoked_at = UTC_TIMESTAMP(), revoked_reason = ? WHERE user_id = ? AND revoked_at IS NULL');
        $statement->bind_param('si', $reason, $userId);
        $statement->execute();
        $statement->close();
    }

    private function enforceDeviceLimit(int $userId): void
    {
        $statement = $this->connection->prepare('SELECT id FROM account_remember_tokens WHERE user_id = ? AND revoked_at IS NULL AND expires_at > UTC_TIMESTAMP() ORDER BY COALESCE(last_used_at, created_at) DESC, id DESC FOR UPDATE');
        $statement->bind_param('i', $userId);
        $statement->execute();
        $result = $statement->get_result();
        $keep = 0;
        $revoke = [];

        while ($row = $result->fetch_assoc()) {
            $keep++;
            if ($keep > $this->maximumDevices) {
                $revoke[] = (int) $row['id'];
            }
        }
        $statement->close();

        if ($revoke === []) {
            return;
        }

        $statement = $this->connection->prepare("UPDATE account_remember_tokens SET revoked_at = UTC_TIMESTAMP(), revoked_reason = 'device_limit' WHERE id = ?");
        foreach ($revoke as $id) {
            $statement->bind_param('i', $id);
            $statement->execute();
        }
        $statement->close();
    }

    /** @return array{0: ?string, 1: ?string} */
    private function parseCookie(string $cookie): array
    {
        $parts = explode('.', $cookie, 2);

        if (count($parts) !== 2 || preg_match('/^[a-f0-9]{24}$/', $parts[0]) !== 1 || preg_match('/^[a-f0-9]{64}$/', $parts[1]) !== 1) {
            return [null, null];
        }

        return [$parts[0], $parts[1]];
    }

    private function validatorHash(string $validator): string
    {
        return $this->pepper === ''
            ? hash('sha256', $validator)
            : hash_hmac('sha256', $validator, $this->pepper);
    }

    private function deviceLabel(string $label): string
    {
        $label = trim(preg_replace('/[\p{C}<>]+/u', '', $label) ?? '');

        return $label === '' ? 'This browser' : mb_substr($label, 0, 100);
    }
}
