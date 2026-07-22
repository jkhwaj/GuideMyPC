<?php

declare(strict_types=1);

define('GUIDEMYPC_JSON_ENDPOINT', true);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/search.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    abort_request(405, 'method_not_allowed', 'This endpoint accepts search suggestions by GET only.');
}

$query = normalize_search_query(required_string($_GET['q'] ?? null, 60));

if (mb_strlen($query) < 2) {
    json_response(200, ['suggestions' => []]);
}

if (!rate_limit_allows('search-suggestions', 30, 60)) {
    abort_request(429, 'suggestion_rate_limited', 'Too many suggestion requests. Please wait a moment and try again.');
}

json_response(200, ['suggestions' => search_suggestions($conn, $query)]);
