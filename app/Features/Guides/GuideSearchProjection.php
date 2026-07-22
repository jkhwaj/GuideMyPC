<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Guides;

use mysqli;

final class GuideSearchProjection
{
    public function __construct(private readonly mysqli $connection)
    {
    }

    public function rebuildGuide(int $guideId): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO guide_search_documents (guide_id, search_text) '
            . 'SELECT guides.id, CONCAT_WS("\\n", guides.title, guides.description, guides.content, guides.platform_version, guides.required_tools, guides.prerequisites, guides.backup_warning, guides.next_actions, '
            . '(SELECT GROUP_CONCAT(guide_tools.name ORDER BY guide_tools.sort_order SEPARATOR "\\n") FROM guide_tools WHERE guide_tools.guide_id = guides.id), '
            . '(SELECT GROUP_CONCAT(CONCAT_WS("\\n", guide_steps.step_title, guide_steps.step_text, guide_steps.expected_result, guide_steps.warning_text, guide_steps.recovery_text) ORDER BY guide_steps.step_number SEPARATOR "\\n") FROM guide_steps WHERE guide_steps.guide_id = guides.id)) '
            . 'FROM guides WHERE guides.id = ? '
            . 'ON DUPLICATE KEY UPDATE search_text = VALUES(search_text)'
        );
        $statement->bind_param('i', $guideId);
        $statement->execute();
        $statement->close();
    }

    public function rebuildAll(): int
    {
        $result = $this->connection->query('SELECT id FROM guides ORDER BY id');
        $count = 0;

        while ($guide = $result->fetch_assoc()) {
            $this->rebuildGuide((int) $guide['id']);
            $count++;
        }

        return $count;
    }
}
