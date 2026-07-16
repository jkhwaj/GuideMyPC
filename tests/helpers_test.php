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

fwrite(STDOUT, "PASS: helper tests completed.\n");
