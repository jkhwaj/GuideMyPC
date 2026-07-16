<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/security.php';
require_once dirname(__DIR__) . '/includes/errors.php';
require_once dirname(__DIR__) . '/includes/search.php';
require_once dirname(__DIR__) . '/includes/knowledge.php';

function assert_same(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, sprintf("FAIL: %s\n", $message));
        exit(1);
    }
}

assert_same('Guide', required_string(' Guide ', 10), 'required_string trims valid values.');
assert_same(null, required_string('Long value', 3), 'required_string rejects long values.');
assert_same(['page' => 2, 'per_page' => 20, 'offset' => 20], pagination_values('2'), 'pagination_values calculates offsets.');
assert_same(['password' => '[redacted]', 'title' => 'Guide'], redact_log_context(['password' => 'secret', 'title' => 'Guide']), 'redact_log_context removes sensitive values.');
assert_same('blue screen', normalize_search_query('  Blue   Screen  '), 'normalize_search_query folds case and whitespace.');
assert_same(false, search_query_is_aggregate_safe('name@example.test'), 'search aggregation rejects email-like queries.');
assert_same(true, search_query_is_aggregate_safe('wifi keeps disconnecting'), 'search aggregation accepts ordinary support queries.');
assert_same('&lt;script&gt;alert(1)&lt;/script&gt;', knowledge_render_content('<script>alert(1)</script>'), 'knowledge rendering escapes stored markup.');
assert_same(null, knowledge_safe_reference_url('javascript:alert(1)'), 'knowledge sources reject non-HTTPS schemes.');
assert_same('https://support.example.test/', knowledge_safe_reference_url('https://support.example.test/'), 'knowledge sources allow HTTPS references.');

fwrite(STDOUT, "PASS: helper tests completed.\n");
