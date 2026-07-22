<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Diagnostics;

use mysqli;

final class DiagnosticRepository
{
    public function __construct(private readonly mysqli $connection)
    {
    }

    /** @return array<string, mixed>|null */
    public function publishedFlow(string $slug): ?array
    {
        $statement = $this->connection->prepare(
            "SELECT diagnostic_flows.*, diagnostic_flow_versions.id AS version_id, diagnostic_flow_versions.initial_node_key FROM diagnostic_flows JOIN diagnostic_flow_versions ON diagnostic_flow_versions.flow_id = diagnostic_flows.id LEFT JOIN categories ON categories.id = diagnostic_flows.category_id WHERE diagnostic_flows.slug = ? AND diagnostic_flows.publication_state = 'published' AND diagnostic_flow_versions.publication_state = 'published' AND (diagnostic_flows.category_id IS NULL OR categories.is_published = 1) ORDER BY diagnostic_flow_versions.version_number DESC LIMIT 1"
        );
        $statement->bind_param('s', $slug);
        $statement->execute();
        $flow = $statement->get_result()->fetch_assoc();
        $statement->close();

        return is_array($flow) ? $flow : null;
    }

    /** @return array<string, mixed>|null */
    public function node(int $versionId, string $nodeKey): ?array
    {
        $statement = $this->connection->prepare('SELECT * FROM diagnostic_nodes WHERE version_id = ? AND node_key = ? LIMIT 1');
        $statement->bind_param('is', $versionId, $nodeKey);
        $statement->execute();
        $node = $statement->get_result()->fetch_assoc();
        $statement->close();

        return is_array($node) ? $node : null;
    }
}
