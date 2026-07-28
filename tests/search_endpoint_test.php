<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/search.php';

/** @return array{status: int, body: array<string, mixed>} */
function search_endpoint_request(string $endpoint, string $method, string $database, string $payload, string $privatePath): array
{
    $command = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg(__DIR__ . '/search_endpoint_probe.php')
        . ' ' . escapeshellarg($endpoint)
        . ' ' . escapeshellarg($method)
        . ' ' . escapeshellarg($database)
        . ' ' . escapeshellarg(base64_encode($payload))
        . ' ' . escapeshellarg($privatePath);
    exec($command, $lines, $exitCode);
    $output = implode("\n", $lines);

    if ($exitCode !== 0 || preg_match('/\n__STATUS__(\d+)$/', $output, $match) !== 1) {
        throw new RuntimeException('Endpoint probe failed for ' . $endpoint . '.');
    }

    $json = substr($output, 0, (int) strrpos($output, "\n__STATUS__"));
    $body = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

    if (!is_array($body)) {
        throw new RuntimeException('Endpoint did not return a JSON object.');
    }

    return ['status' => (int) $match[1], 'body' => $body];
}

function search_endpoint_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function search_endpoint_assert_envelope(array $response, bool $expectedOk): void
{
    $body = $response['body'];
    search_endpoint_assert(($body['ok'] ?? null) === $expectedOk, 'JSON response has the expected success flag.');
    search_endpoint_assert(
        preg_match('/^[a-f0-9]{24}$/', (string) ($body['meta']['request_id'] ?? '')) === 1,
        'JSON response includes a bounded request ID.'
    );
    search_endpoint_assert(isset($body[$expectedOk ? 'data' : 'error']), 'JSON response includes the expected payload member.');
    search_endpoint_assert(!isset($body[$expectedOk ? 'error' : 'data']), 'JSON response does not mix success and error payloads.');
}

$test = test_database_or_fail();
$database = config_value('DB_TEST_NAME');

if (!is_string($database)) {
    fwrite(STDERR, "FAIL: DB_TEST_NAME is not configured.\n");
    exit(1);
}

$privatePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'guidemypc-search-endpoint-' . bin2hex(random_bytes(6));
$rateLimitPath = $privatePath . DIRECTORY_SEPARATOR . 'rate-limits';
mkdir($rateLimitPath, 0700, true);
$queryToken = '';
for ($index = 0; $index < 12; $index++) {
    $queryToken .= chr(ord('a') + random_int(0, 25));
}
$query = 'endpoint guidance ' . $queryToken;
$queryHash = search_event_hash($query);

try {
    $short = search_endpoint_request('search_suggestions.php', 'GET', $database, 'q=x', $privatePath);
    search_endpoint_assert($short['status'] === 200 && $short['body']['data']['suggestions'] === [], 'Short suggestion queries return an empty JSON list.');
    search_endpoint_assert_envelope($short, true);

    $suggestions = search_endpoint_request('search_suggestions.php', 'GET', $database, 'q=windows', $privatePath);
    search_endpoint_assert($suggestions['status'] === 200, 'Valid suggestion requests succeed.');
    search_endpoint_assert(count($suggestions['body']['data']['suggestions'] ?? []) <= 8, 'Suggestion responses remain bounded to eight items.');
    search_endpoint_assert_envelope($suggestions, true);

    $wrongSuggestionMethod = search_endpoint_request('search_suggestions.php', 'POST', $database, 'q=windows', $privatePath);
    search_endpoint_assert($wrongSuggestionMethod['status'] === 405, 'Suggestion endpoint rejects non-GET requests.');
    search_endpoint_assert(($wrongSuggestionMethod['body']['error']['code'] ?? '') === 'method_not_allowed', 'Suggestion method errors use the stable JSON code.');
    search_endpoint_assert_envelope($wrongSuggestionMethod, false);

    $suggestionRateKey = hash('sha256', 'search-suggestions|127.0.0.77');
    file_put_contents($rateLimitPath . DIRECTORY_SEPARATOR . $suggestionRateKey . '.json', json_encode(array_fill(0, 30, time()), JSON_THROW_ON_ERROR));
    $limitedSuggestion = search_endpoint_request('search_suggestions.php', 'GET', $database, 'q=windows', $privatePath);
    search_endpoint_assert($limitedSuggestion['status'] === 429, 'The thirty-first suggestion request in a minute is rate-limited.');
    search_endpoint_assert(($limitedSuggestion['body']['error']['code'] ?? '') === 'suggestion_rate_limited', 'Suggestion rate limits use the stable JSON code.');
    search_endpoint_assert_envelope($limitedSuggestion, false);

    $wrongEventMethod = search_endpoint_request('search_event.php', 'GET', $database, '', $privatePath);
    search_endpoint_assert($wrongEventMethod['status'] === 405, 'Search event endpoint rejects non-POST requests.');
    search_endpoint_assert(($wrongEventMethod['body']['error']['code'] ?? '') === 'method_not_allowed', 'Event method errors use the stable JSON code.');
    search_endpoint_assert_envelope($wrongEventMethod, false);

    $recorded = search_endpoint_request('search_event.php', 'POST', $database, http_build_query(['query' => $query, 'result_type' => 'guide']), $privatePath);
    search_endpoint_assert($recorded['status'] === 200 && ($recorded['body']['data']['recorded'] ?? null) === true, 'A safe selection event reports that it was recorded.');
    search_endpoint_assert_envelope($recorded, true);

    $discarded = search_endpoint_request('search_event.php', 'POST', $database, http_build_query(['query' => 'student@example.com', 'result_type' => 'guide']), $privatePath);
    search_endpoint_assert(
        $discarded['status'] === 200 && ($discarded['body']['data']['recorded'] ?? null) === false,
        'Privacy-sensitive telemetry is discarded truthfully: ' . json_encode($discarded, JSON_UNESCAPED_SLASHES)
    );
    search_endpoint_assert_envelope($discarded, true);

    $eventRateKey = hash('sha256', 'search-events|127.0.0.77');
    file_put_contents($rateLimitPath . DIRECTORY_SEPARATOR . $eventRateKey . '.json', json_encode(array_fill(0, 60, time()), JSON_THROW_ON_ERROR));
    $limitedEvent = search_endpoint_request('search_event.php', 'POST', $database, http_build_query(['query' => $query, 'result_type' => 'guide']), $privatePath);
    search_endpoint_assert($limitedEvent['status'] === 429, 'The sixty-first event request in a minute is rate-limited.');
    search_endpoint_assert(($limitedEvent['body']['error']['code'] ?? '') === 'search_event_rate_limited', 'Event rate limits use the stable JSON code.');
    search_endpoint_assert_envelope($limitedEvent, false);

    $statement = $test->prepare('SELECT event_count FROM search_events WHERE query_hash = ? AND result_type = ? AND event_state = ?');
    $type = 'guide';
    $state = 'selection';
    $statement->bind_param('sss', $queryHash, $type, $state);
    $statement->execute();
    $stored = $statement->get_result()->fetch_assoc();
    $statement->close();
    search_endpoint_assert(is_array($stored) && (int) $stored['event_count'] === 1, 'The accepted selection event is stored exactly once.');

    $delete = $test->prepare('DELETE FROM search_events WHERE query_hash = ?');
    $delete->bind_param('s', $queryHash);
    $delete->execute();
    $delete->close();
    fwrite(STDOUT, "PASS: Search JSON endpoint methods, envelopes, privacy, storage, and rate limits work.\n");
} catch (Throwable $exception) {
    $delete = $test->prepare('DELETE FROM search_events WHERE query_hash = ?');
    $delete->bind_param('s', $queryHash);
    $delete->execute();
    $delete->close();
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
} finally {
    foreach (glob($rateLimitPath . DIRECTORY_SEPARATOR . '*.json') ?: [] as $path) {
        unlink($path);
    }
    @rmdir($rateLimitPath);
    @rmdir($privatePath);
}
