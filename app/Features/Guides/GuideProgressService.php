<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Guides;

use mysqli;

final class GuideProgressService
{
    public function __construct(private readonly mysqli $connection)
    {
    }

    /** @param array<string, mixed> $session */
    public function save(int $userId, int $guideId, int $stepId, bool $completed, array &$session): void
    {
        if ($userId === 0) {
            self::saveGuestProgress($session, $guideId, $stepId, $completed);

            return;
        }

        if ($completed) {
            $statement = $this->connection->prepare(
                'INSERT INTO user_progress (user_id, guide_step_id, completed) '
                . 'VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE completed = 1'
            );
        } else {
            $statement = $this->connection->prepare(
                'DELETE FROM user_progress WHERE user_id = ? AND guide_step_id = ?'
            );
        }

        $statement->bind_param('ii', $userId, $stepId);
        $statement->execute();
        $statement->close();
    }

    /** @param array<string, mixed> $session */
    public static function saveGuestProgress(array &$session, int $guideId, int $stepId, bool $completed): void
    {
        $session['_guest_progress'][$guideId] = $session['_guest_progress'][$guideId] ?? [];

        if ($completed) {
            $session['_guest_progress'][$guideId][$stepId] = true;

            return;
        }

        unset($session['_guest_progress'][$guideId][$stepId]);
    }
}
