<?php

declare(strict_types=1);

function guide_text(mixed $value, int $maximumLength = 10000): string
{
    return required_string($value, $maximumLength) ?? '';
}

function guide_safe_url(mixed $value): ?string
{
    $url = guide_text($value, 500);

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

function guide_youtube_embed_url(mixed $value): ?string
{
    $url = guide_text($value, 500);

    if ($url === '') {
        return null;
    }

    $parts = parse_url($url);
    $host = strtolower((string) ($parts['host'] ?? ''));
    $videoId = '';

    if (in_array($host, ['youtube.com', 'www.youtube.com'], true)) {
        parse_str((string) ($parts['query'] ?? ''), $parameters);
        $videoId = is_string($parameters['v'] ?? null) ? $parameters['v'] : '';
    } elseif ($host === 'youtu.be') {
        $videoId = trim((string) ($parts['path'] ?? ''), '/');
    }

    return preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId) === 1
        ? 'https://www.youtube-nocookie.com/embed/' . $videoId
        : null;
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
        $text = guide_text($data['text'] ?? '', 10000);

        if ($text === '') {
            continue;
        }

        $timestamp = filter_var($data['video_timestamp'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 86400]]);
        $steps[] = [
            'text' => $text,
            'title' => guide_text($data['title'] ?? '', 200),
            'expected_result' => guide_text($data['expected_result'] ?? '', 1000),
            'warning_text' => guide_text($data['warning_text'] ?? '', 1000),
            'recovery_text' => guide_text($data['recovery_text'] ?? '', 1000),
            'image_url' => guide_safe_url($data['image_url'] ?? null),
            'image_alt' => guide_text($data['image_alt'] ?? '', 300),
            'video_timestamp' => $timestamp === false ? null : $timestamp,
        ];
    }

    return $steps;
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
        $tool = guide_text($tool, 150);

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
