<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/search.php';

require_post();

if (!rate_limit_allows('search-events', 60, 60)) {
    abort_request(429, 'search_event_rate_limited', 'Too many search events. Please wait a moment and try again.');
}

$query = normalize_search_query(required_string($_POST['query'] ?? null, 120));
$resultType = required_string($_POST['result_type'] ?? null, 20) ?? '';
record_search_event($conn, $query, 0, $resultType, 'selection');
json_response(200, ['recorded' => true]);
