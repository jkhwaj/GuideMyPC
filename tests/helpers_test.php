<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once dirname(__DIR__) . '/bootstrap/test.php';
require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/security.php';
require_once dirname(__DIR__) . '/includes/errors.php';
require_once dirname(__DIR__) . '/includes/search.php';
require_once dirname(__DIR__) . '/includes/guides.php';
require_once dirname(__DIR__) . '/includes/confidence.php';
require_once dirname(__DIR__) . '/includes/accounts.php';
require_once dirname(__DIR__) . '/includes/knowledge.php';
require_once dirname(__DIR__) . '/includes/downloads.php';
require_once dirname(__DIR__) . '/includes/community.php';

function assert_same(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, sprintf("FAIL: %s\n", $message));
        exit(1);
    }
}

assert_same('Guide', required_string(' Guide ', 10), 'required_string trims valid values.');
assert_same(true, test_database_name_is_safe('guidemypc_test', 'guidemypc'), 'Test database names must use the dedicated suffix.');
assert_same(false, test_database_name_is_safe('guidemypc', 'guidemypc'), 'The application database is never a safe test target.');
assert_same(false, test_database_name_is_safe('guidemypc_preview', 'guidemypc'), 'Test database names reject non-test suffixes.');
assert_same('value', GuideMyPC\Core\Environment::value(['KEY' => 'value'], 'KEY'), 'PSR-4 Core classes autoload without Composer.');
assert_same([], load_environment(dirname(__DIR__) . '/missing-test-env'), 'load_environment ignores a missing file.');
assert_same('fallback', config_value('MISSING_TEST_VALUE', 'fallback'), 'config_value preserves its default contract.');
assert_same(true, is_string(private_storage_path('logs')), 'private_storage_path resolves private runtime storage.');
configure_error_handling();
$viewOutput = (static function (): string {
    ob_start();
    (new GuideMyPC\Features\Pages\PageController(new GuideMyPC\Core\View()))->about();

    return (string) ob_get_clean();
})();
assert_same(true, str_contains($viewOutput, '<title>About | GuideMyPC</title>'), 'Pages controller passes explicit metadata to the layout.');
assert_same(true, str_contains($viewOutput, '<h1 id="about-title">Practical technology support</h1>'), 'Pages controller renders the about view.');
assert_same(null, required_string('Long value', 3), 'required_string rejects long values.');
assert_same(['page' => 2, 'per_page' => 20, 'offset' => 20], pagination_values('2'), 'pagination_values calculates offsets.');
assert_same(['password' => '[redacted]', 'title' => 'Guide'], redact_log_context(['password' => 'secret', 'title' => 'Guide']), 'redact_log_context removes sensitive values.');
assert_same('blue screen', normalize_search_query('  Blue   Screen  '), 'normalize_search_query folds case and whitespace.');
assert_same('', GuideMyPC\Features\Search\SearchQuery::normalize(str_repeat('x', 121)), 'Search query normalization rejects values longer than 120 characters.');
assert_same(false, search_query_is_aggregate_safe('name@example.test'), 'search aggregation rejects email-like queries.');
assert_same(true, search_query_is_aggregate_safe('wifi keeps disconnecting'), 'search aggregation accepts ordinary support queries.');
assert_same('https://www.youtube-nocookie.com/embed/M7lc1UVf-VE', guide_youtube_embed_url('https://youtu.be/M7lc1UVf-VE'), 'guide_youtube_embed_url permits a valid YouTube ID.');
assert_same(null, guide_youtube_embed_url('https://example.test/video'), 'guide_youtube_embed_url rejects unapproved video hosts.');
assert_same(null, guide_safe_url('http://support.microsoft.com/unsafe'), 'guide_safe_url requires approved HTTPS URLs.');
assert_same('https://support.microsoft.com/windows', guide_safe_source_url('https://support.microsoft.com/windows'), 'guide_safe_source_url accepts ordinary HTTPS source URLs.');
assert_same(null, guide_safe_source_url('https://user:secret@support.microsoft.com/windows'), 'guide_safe_source_url rejects source URL credentials.');
assert_same(null, guide_safe_source_url('https://support.microsoft.com:8443/windows'), 'guide_safe_source_url rejects unexpected source URL ports.');
assert_same(null, guide_safe_source_url('https://127.0.0.1/windows'), 'guide_safe_source_url rejects IP-literal source URLs.');
$rankedConfidence = confidence_rank(
    [
        ['cause_key' => 'power', 'title' => 'Power', 'explanation' => '', 'minimum_evidence' => 1],
        ['cause_key' => 'display', 'title' => 'Display', 'explanation' => '', 'minimum_evidence' => 2],
    ],
    [
        ['cause_key' => 'power', 'weight' => 3, 'explanation' => 'No power indicators were observed.'],
        ['cause_key' => 'display', 'weight' => 1, 'explanation' => 'The display is unconfirmed.'],
    ]
);
assert_same('power', $rankedConfidence[0]['cause_key'], 'confidence_rank uses deterministic score ordering.');
assert_same('Uncertain', $rankedConfidence[1]['band'], 'confidence_rank avoids a percentage with insufficient evidence.');
assert_same('person@example.test', normalize_email(' Person@Example.Test '), 'normalize_email folds case and whitespace.');
assert_same(null, normalize_email('not-an-email'), 'normalize_email rejects invalid addresses.');
assert_same('&lt;script&gt;alert(1)&lt;/script&gt;', knowledge_render_content('<script>alert(1)</script>'), 'knowledge rendering escapes stored markup.');
assert_same(null, knowledge_safe_reference_url('javascript:alert(1)'), 'knowledge sources reject non-HTTPS schemes.');
assert_same('https://support.example.test/', knowledge_safe_reference_url('https://support.example.test/'), 'knowledge sources allow HTTPS references.');
assert_same('https://downloads.example.test/tool', trusted_download_url('https://downloads.example.test/tool'), 'Download policy permits HTTPS host URLs.');
assert_same(null, trusted_download_url('http://downloads.example.test/tool'), 'Download policy rejects non-HTTPS URLs.');
assert_same(true, download_is_public(['is_published' => 1, 'review_state' => 'approved', 'official_url' => 'https://downloads.example.test/tool']), 'Download policy permits published, approved HTTPS URLs.');
assert_same(false, download_is_public(['is_published' => 0, 'review_state' => 'approved', 'official_url' => 'https://downloads.example.test/tool']), 'Download policy rejects unpublished downloads.');
assert_same(false, download_is_public(['is_published' => 1, 'review_state' => 'pending', 'official_url' => 'https://downloads.example.test/tool']), 'Download policy rejects pending downloads.');
assert_same(true, community_post_is_public(['is_published' => 1]), 'Community policy permits published legacy posts.');
assert_same(false, community_post_is_public(['is_published' => 0]), 'Community policy rejects unpublished legacy posts.');

fwrite(STDOUT, "PASS: helper tests completed.\n");
