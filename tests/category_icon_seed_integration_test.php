<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once __DIR__ . '/bootstrap.php';

function category_icon_seed_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function category_icon_seed_run(string $script, string $database): void
{
    $command = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg(dirname(__DIR__) . '/' . $script)
        . ' ' . escapeshellarg('--database=' . $database);
    exec($command, $output, $exitCode);

    if ($exitCode !== 0) {
        throw new RuntimeException($script . ' failed: ' . implode("\n", $output));
    }
}

$test = test_database_or_fail();
$database = config_value('DB_TEST_NAME');
$migrationVersion = '025_category_icon_emoji_correction.sql';
$icons = [
    'windows' => ['legacy' => 'fa-brands fa-windows', 'emoji' => '💻'],
    'macos' => ['legacy' => 'fa-brands fa-apple', 'emoji' => '🍎'],
    'linux' => ['legacy' => 'fa-brands fa-linux', 'emoji' => '🐧'],
    'android' => ['legacy' => 'fa-brands fa-android', 'emoji' => '🤖'],
    'iphone' => ['legacy' => 'fa-brands fa-apple', 'emoji' => '📱'],
    'wifi' => ['legacy' => 'fa-solid fa-wifi', 'emoji' => '📶'],
];

if (!is_string($database)) {
    fwrite(STDERR, "FAIL: DB_TEST_NAME is not configured.\n");
    exit(1);
}

$exitCode = 0;
$originalIcons = [];
$originalLedger = null;

try {
    $select = $test->prepare('SELECT icon FROM categories WHERE slug = ?');
    $set = $test->prepare('UPDATE categories SET icon = ? WHERE slug = ?');
    foreach (array_keys($icons) as $slug) {
        $select->bind_param('s', $slug);
        $select->execute();
        $row = $select->get_result()->fetch_assoc();
        if ($row !== null) {
            $originalIcons[$slug] = (string) $row['icon'];
        }
    }
    $ledgerSelect = $test->prepare('SELECT checksum FROM schema_migrations WHERE version = ?');
    $ledgerSelect->bind_param('s', $migrationVersion);
    $ledgerSelect->execute();
    $originalLedger = $ledgerSelect->get_result()->fetch_assoc();
    $ledgerSelect->close();

    category_icon_seed_run('database/seed.php', $database);
    category_icon_seed_run('database/migrate.php', $database);
    category_icon_seed_run('database/migrate.php', $database);

    foreach ($icons as $slug => $mapping) {
        $select->bind_param('s', $slug);
        $select->execute();
        $row = $select->get_result()->fetch_assoc();
        category_icon_seed_assert(is_array($row) && !str_starts_with((string) $row['icon'], 'fa-'), 'Seeded category icons do not use legacy Font Awesome classes.');
        category_icon_seed_assert(($row['icon'] ?? null) === $mapping['emoji'], 'Seeded category icon matches its approved emoji.');

    }

    $legacy = $icons['windows']['legacy'];
    $slug = 'windows';
    $set->bind_param('ss', $legacy, $slug);
    $set->execute();
    foreach (array_keys($icons) as $slug) {
        if ($slug === 'windows') {
            continue;
        }

        $custom = 'Custom icon ' . $slug;
        $set->bind_param('ss', $custom, $slug);
        $set->execute();
    }
    $ledgerDelete = $test->prepare('DELETE FROM schema_migrations WHERE version = ?');
    $ledgerDelete->bind_param('s', $migrationVersion);
    $ledgerDelete->execute();
    $ledgerDelete->close();

    category_icon_seed_run('database/migrate.php', $database);
    category_icon_seed_run('database/migrate.php', $database);
    $slug = 'windows';
    $select->bind_param('s', $slug);
    $select->execute();
    $row = $select->get_result()->fetch_assoc();
    category_icon_seed_assert(($row['icon'] ?? null) === $icons['windows']['emoji'], 'Forward migration repairs the known legacy icon value.');
    foreach (array_keys($icons) as $slug) {
        if ($slug === 'windows') {
            continue;
        }

        $select->bind_param('s', $slug);
        $select->execute();
        $row = $select->get_result()->fetch_assoc();
        category_icon_seed_assert(($row['icon'] ?? null) === 'Custom icon ' . $slug, 'Forward migration preserves custom category icons.');
    }

    foreach ($icons as $slug => $mapping) {
        $legacy = $mapping['legacy'];
        $set->bind_param('ss', $legacy, $slug);
        $set->execute();
    }

    category_icon_seed_run('database/seed.php', $database);
    category_icon_seed_run('database/seed.php', $database);
    foreach ($icons as $slug => $mapping) {
        $select->bind_param('s', $slug);
        $select->execute();
        $row = $select->get_result()->fetch_assoc();
        category_icon_seed_assert(($row['icon'] ?? null) === $mapping['emoji'], 'Seed reruns repair only the matching legacy icon value.');

        $custom = 'Custom icon ' . $slug;
        $set->bind_param('ss', $custom, $slug);
        $set->execute();
    }

    category_icon_seed_run('database/seed.php', $database);

    foreach ($icons as $slug => $mapping) {
        $select->bind_param('s', $slug);
        $select->execute();
        $row = $select->get_result()->fetch_assoc();
        category_icon_seed_assert(($row['icon'] ?? null) === 'Custom icon ' . $slug, 'Seed reruns preserve custom nonlegacy category icons.');

        $emoji = $mapping['emoji'];
        $set->bind_param('ss', $emoji, $slug);
        $set->execute();
    }

    fwrite(STDOUT, "PASS: category icon migration and repeated seed runs preserve approved and custom values.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . PHP_EOL);
    $exitCode = 1;
} finally {
    try {
        if (isset($set, $select)) {
            foreach ($icons as $slug => $mapping) {
                $icon = $originalIcons[$slug] ?? $mapping['emoji'];
                $set->bind_param('ss', $icon, $slug);
                $set->execute();
            }
            $select->close();
            $set->close();
        }

        $ledgerRestore = $test->prepare('SELECT checksum FROM schema_migrations WHERE version = ?');
        $ledgerRestore->bind_param('s', $migrationVersion);
        $ledgerRestore->execute();
        $ledgerCurrent = $ledgerRestore->get_result()->fetch_assoc();
        $ledgerRestore->close();
        if ($originalLedger === null) {
            $ledgerDelete = $test->prepare('DELETE FROM schema_migrations WHERE version = ?');
            $ledgerDelete->bind_param('s', $migrationVersion);
            $ledgerDelete->execute();
            $ledgerDelete->close();
        } elseif ($ledgerCurrent === null || $ledgerCurrent['checksum'] !== $originalLedger['checksum']) {
            $ledgerRestore = $test->prepare(
                'INSERT INTO schema_migrations (version, checksum) VALUES (?, ?) '
                . 'ON DUPLICATE KEY UPDATE checksum = VALUES(checksum)'
            );
            $checksum = (string) $originalLedger['checksum'];
            $ledgerRestore->bind_param('ss', $migrationVersion, $checksum);
            $ledgerRestore->execute();
            $ledgerRestore->close();
        }
    } catch (Throwable $cleanupException) {
        fwrite(STDERR, 'FAIL: category icon test cleanup failed: ' . $cleanupException->getMessage() . PHP_EOL);
        $exitCode = 1;
    } finally {
        $test->close();
    }
}

exit($exitCode);
