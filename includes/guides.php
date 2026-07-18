<?php

declare(strict_types=1);

function guide_text(mixed $value, int $maximumLength = 10000): string
{
    return required_string($value, $maximumLength) ?? '';
}

/** @return array{id: int, slug: string}|null */
function guide_public_by_id(mysqli $connection, int $guideId, ?string $slug = null): ?array
{
    $statement = $connection->prepare(
        'SELECT guides.id, guides.slug FROM guides JOIN categories ON categories.id = guides.category_id '
        . 'WHERE guides.id = ? AND guides.is_published = 1 AND categories.is_published = 1 '
        . ($slug === null ? '' : 'AND guides.slug = ? ') . 'LIMIT 1'
    );

    if ($slug === null) {
        $statement->bind_param('i', $guideId);
    } else {
        $statement->bind_param('is', $guideId, $slug);
    }

    $statement->execute();
    $guide = $statement->get_result()->fetch_assoc();
    $statement->close();

    return $guide === null ? null : ['id' => (int) $guide['id'], 'slug' => $guide['slug']];
}

/** @return array{id: int, guide_id: int, slug: string}|null */
function guide_public_step_by_id(mysqli $connection, int $stepId): ?array
{
    $statement = $connection->prepare(
        'SELECT guide_steps.id, guide_steps.guide_id, guides.slug FROM guide_steps '
        . 'JOIN guides ON guides.id = guide_steps.guide_id '
        . 'JOIN categories ON categories.id = guides.category_id '
        . 'WHERE guide_steps.id = ? AND guides.is_published = 1 AND categories.is_published = 1 LIMIT 1'
    );
    $statement->bind_param('i', $stepId);
    $statement->execute();
    $step = $statement->get_result()->fetch_assoc();
    $statement->close();

    return $step === null ? null : ['id' => (int) $step['id'], 'guide_id' => (int) $step['guide_id'], 'slug' => $step['slug']];
}

function guide_safe_url(mixed $value, int $maximumLength = 255): ?string
{
    $url = guide_text($value, $maximumLength);

    if ($url === '') {
        return null;
    }

    $parts = parse_url($url);
    $host = strtolower((string) ($parts['host'] ?? ''));
    $approvedHosts = ['support.microsoft.com', 'support.apple.com', 'images.unsplash.com'];

    if (($parts['scheme'] ?? '') !== 'https' || !in_array($host, $approvedHosts, true)) {
        return null;
    }

    return $url;
}

function guide_safe_source_url(mixed $value, int $maximumLength = 255): ?string
{
    $url = guide_text($value, $maximumLength);

    if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
        return null;
    }

    $parts = parse_url($url);

    if (!is_array($parts)
        || ($parts['scheme'] ?? '') !== 'https'
        || isset($parts['user'])
        || isset($parts['pass'])
        || isset($parts['port'])
        || filter_var((string) ($parts['host'] ?? ''), FILTER_VALIDATE_IP) !== false) {
        return null;
    }

    return $url;
}

function guide_youtube_video_id(mixed $value): ?string
{
    $url = guide_text($value, 500);

    if ($url === '') {
        return null;
    }

    $parts = parse_url($url);

    if (!is_array($parts)
        || ($parts['scheme'] ?? '') !== 'https'
        || isset($parts['user'])
        || isset($parts['pass'])
        || isset($parts['port'])) {
        return null;
    }

    $host = strtolower((string) ($parts['host'] ?? ''));
    $videoId = '';

    if (in_array($host, ['youtube.com', 'www.youtube.com'], true)) {
        parse_str((string) ($parts['query'] ?? ''), $parameters);
        $videoId = is_string($parameters['v'] ?? null) ? $parameters['v'] : '';
    } elseif ($host === 'youtu.be') {
        $videoId = trim((string) ($parts['path'] ?? ''), '/');
    } elseif ($host === 'www.youtube-nocookie.com') {
        $path = explode('/', trim((string) ($parts['path'] ?? ''), '/'));
        $videoId = ($path[0] ?? '') === 'embed' && count($path) === 2 ? $path[1] : '';
    }

    return preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId) === 1 ? $videoId : null;
}

function guide_youtube_watch_url(mixed $value): ?string
{
    $videoId = guide_youtube_video_id($value);

    return $videoId === null ? null : 'https://www.youtube.com/watch?v=' . $videoId;
}

function guide_youtube_embed_url(mixed $value): ?string
{
    $videoId = guide_youtube_video_id($value);

    return $videoId === null ? null : 'https://www.youtube-nocookie.com/embed/' . $videoId;
}

function guide_category_exists(mysqli $connection, int $categoryId): bool
{
    $statement = $connection->prepare('SELECT 1 FROM categories WHERE id = ?');
    $statement->bind_param('i', $categoryId);
    $statement->execute();
    $exists = $statement->get_result()->fetch_row() !== null;
    $statement->close();

    return $exists;
}

function guide_exists(mysqli $connection, int $guideId): bool
{
    $statement = $connection->prepare('SELECT 1 FROM guides WHERE id = ?');
    $statement->bind_param('i', $guideId);
    $statement->execute();
    $exists = $statement->get_result()->fetch_row() !== null;
    $statement->close();

    return $exists;
}

/** @return list<array<string, string|int|null>> */
function guide_normalize_steps(mixed $input): array
{
    if (!is_array($input)) {
        return [];
    }

    $steps = [];

    foreach ($input as $step) {
        $data = is_array($step) ? $step : ['text' => $step];
        $id = null;

        if (array_key_exists('id', $data) && $data['id'] !== '') {
            $validatedId = filter_var($data['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($validatedId === false) {
                return [];
            }

            $id = $validatedId;
        }

        $text = guide_text($data['text'] ?? '', 10000);

        if ($text === '') {
            if ($id !== null) {
                return [];
            }

            continue;
        }

        $timestamp = filter_var($data['video_timestamp'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 86400]]);
        $steps[] = [
            'id' => $id,
            'text' => $text,
            'title' => guide_text($data['title'] ?? '', 180),
            'expected_result' => guide_text($data['expected_result'] ?? '', 1000),
            'warning_text' => guide_text($data['warning_text'] ?? '', 1000),
            'recovery_text' => guide_text($data['recovery_text'] ?? '', 1000),
            'image_url' => guide_safe_url($data['image_url'] ?? null, 255),
            'image_alt' => guide_text($data['image_alt'] ?? '', 255),
            'video_timestamp' => $timestamp === false ? null : $timestamp,
        ];
    }

    return $steps;
}

/** @param array<int|string, mixed> $guestStepIds */
function guide_merge_guest_progress(mysqli $connection, int $userId, int $guideId, array $guestStepIds): void
{
    $merge = $connection->prepare(
        'INSERT INTO user_progress (user_id, guide_step_id, completed) '
        . 'SELECT ?, id, 1 FROM guide_steps WHERE id = ? AND guide_id = ? '
        . 'ON DUPLICATE KEY UPDATE completed = 1'
    );
    $seen = [];

    foreach ($guestStepIds as $guestStepId) {
        $stepId = filter_var($guestStepId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($stepId === false || isset($seen[$stepId])) {
            continue;
        }

        $seen[$stepId] = true;
        $merge->bind_param('iii', $userId, $stepId, $guideId);
        $merge->execute();
    }

    $merge->close();
}

/**
 * Synchronize submitted steps by stable ID. Call this inside the guide update transaction.
 *
 * @param list<array<string, string|int|null>> $steps
 * @return array{added: list<int>, updated: list<int>, deleted: list<int>, deleted_progress: int}
 */
function guide_sync_steps(mysqli $connection, int $guideId, array $steps): array
{
    if ($steps === []) {
        throw new DomainException('A guide must contain at least one step.');
    }

    $statement = $connection->prepare('SELECT * FROM guide_steps WHERE guide_id = ? ORDER BY step_number FOR UPDATE');
    $statement->bind_param('i', $guideId);
    $statement->execute();
    $result = $statement->get_result();
    $existing = [];
    $existingOrder = [];

    while ($row = $result->fetch_assoc()) {
        $stepId = (int) $row['id'];
        $existing[$stepId] = $row;
        $existingOrder[] = $stepId;
    }

    $statement->close();
    $submittedIds = [];

    foreach ($steps as $step) {
        $stepId = $step['id'] ?? null;

        if ($stepId === null) {
            continue;
        }

        $stepId = (int) $stepId;

        if (!isset($existing[$stepId]) || isset($submittedIds[$stepId])) {
            throw new DomainException('A submitted guide step is invalid or belongs to another guide.');
        }

        $submittedIds[$stepId] = true;
    }

    $submittedOrder = array_values(array_map(
        static fn (array $step): int => (int) ($step['id'] ?? 0),
        array_filter($steps, static fn (array $step): bool => ($step['id'] ?? null) !== null)
    ));
    $structuralChange = count($steps) !== count($existing)
        || $submittedOrder !== $existingOrder;

    if ($structuralChange && $existing !== []) {
        $temporaryNumber = max(array_map(static fn (array $step): int => (int) $step['step_number'], $existing)) + count($existing) + count($steps) + 1;
        $move = $connection->prepare('UPDATE guide_steps SET step_number = ? WHERE id = ? AND guide_id = ?');

        foreach (array_keys($existing) as $stepId) {
            $move->bind_param('iii', $temporaryNumber, $stepId, $guideId);
            $move->execute();
            $temporaryNumber++;
        }

        $move->close();
    }

    $insert = $connection->prepare(
        'INSERT INTO guide_steps (guide_id, step_number, step_text, step_title, expected_result, warning_text, recovery_text, image_url, image_alt, video_timestamp) '
        . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $update = $connection->prepare(
        'UPDATE guide_steps SET step_number = ?, step_text = ?, step_title = ?, expected_result = ?, warning_text = ?, recovery_text = ?, image_url = ?, image_alt = ?, video_timestamp = ? WHERE id = ? AND guide_id = ?'
    );
    $added = [];
    $updated = [];

    foreach ($steps as $index => $step) {
        $number = $index + 1;
        $timestamp = $step['video_timestamp'];
        $stepId = $step['id'] ?? null;

        if ($stepId === null) {
            $insert->bind_param(
                'iisssssssi', $guideId, $number, $step['text'], $step['title'], $step['expected_result'], $step['warning_text'],
                $step['recovery_text'], $step['image_url'], $step['image_alt'], $timestamp
            );
            $insert->execute();
            $added[] = $insert->insert_id;
            continue;
        }

        $stepId = (int) $stepId;
        $current = $existing[$stepId];
        $changed = $structuralChange
            || (string) $current['step_text'] !== (string) $step['text']
            || (string) $current['step_title'] !== (string) $step['title']
            || (string) $current['expected_result'] !== (string) $step['expected_result']
            || (string) $current['warning_text'] !== (string) $step['warning_text']
            || (string) $current['recovery_text'] !== (string) $step['recovery_text']
            || (string) $current['image_url'] !== (string) $step['image_url']
            || (string) $current['image_alt'] !== (string) $step['image_alt']
            || ($current['video_timestamp'] === null ? null : (int) $current['video_timestamp']) !== $timestamp;

        if ($changed) {
            $update->bind_param(
                'issssssssii', $number, $step['text'], $step['title'], $step['expected_result'], $step['warning_text'],
                $step['recovery_text'], $step['image_url'], $step['image_alt'], $timestamp, $stepId, $guideId
            );
            $update->execute();
            $updated[] = $stepId;
        }
    }

    $insert->close();
    $update->close();
    $deleted = array_values(array_diff(array_keys($existing), array_keys($submittedIds)));
    $deletedProgress = 0;

    if ($deleted !== []) {
        $progress = $connection->prepare('SELECT COUNT(*) AS total FROM user_progress WHERE guide_step_id = ?');
        $delete = $connection->prepare('DELETE FROM guide_steps WHERE id = ? AND guide_id = ?');

        foreach ($deleted as $stepId) {
            $progress->bind_param('i', $stepId);
            $progress->execute();
            $deletedProgress += (int) ($progress->get_result()->fetch_assoc()['total'] ?? 0);
            $delete->bind_param('ii', $stepId, $guideId);
            $delete->execute();
        }

        $progress->close();
        $delete->close();
    }

    return ['added' => $added, 'updated' => $updated, 'deleted' => $deleted, 'deleted_progress' => $deletedProgress];
}

/** @param list<array<string, string|int|null>> $steps */
function guide_replace_steps(mysqli $connection, int $guideId, array $steps): void
{
    $delete = $connection->prepare('DELETE FROM guide_steps WHERE guide_id = ?');
    $delete->bind_param('i', $guideId);
    $delete->execute();
    $delete->close();

    $insert = $connection->prepare(
        'INSERT INTO guide_steps (guide_id, step_number, step_text, step_title, expected_result, warning_text, recovery_text, image_url, image_alt, video_timestamp) '
        . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $number = 1;

    foreach ($steps as $step) {
        $timestamp = $step['video_timestamp'];
        $insert->bind_param(
            'iisssssssi', $guideId, $number, $step['text'], $step['title'], $step['expected_result'], $step['warning_text'],
            $step['recovery_text'], $step['image_url'], $step['image_alt'], $timestamp
        );
        $insert->execute();
        $number++;
    }

    $insert->close();
}

function guide_replace_tools(mysqli $connection, int $guideId, string $tools): void
{
    $delete = $connection->prepare('DELETE FROM guide_tools WHERE guide_id = ?');
    $delete->bind_param('i', $guideId);
    $delete->execute();
    $delete->close();

    $insert = $connection->prepare('INSERT INTO guide_tools (guide_id, name, sort_order) VALUES (?, ?, ?)');
    $seen = [];
    $position = 1;

    foreach (preg_split('/[\r\n,]+/', $tools) ?: [] as $tool) {
        $tool = guide_text($tool, 120);

        if ($tool === '' || isset($seen[mb_strtolower($tool)])) {
            continue;
        }

        $seen[mb_strtolower($tool)] = true;
        $insert->bind_param('isi', $guideId, $tool, $position);
        $insert->execute();
        $position++;
    }

    $insert->close();
}
