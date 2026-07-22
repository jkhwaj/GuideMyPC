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
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
$_SERVER['REQUEST_METHOD'] = 'POST';
assert_same(true, request_method_is('POST'), 'request_method_is accepts the current request method.');
assert_same(false, request_method_is('GET'), 'request_method_is rejects a different request method.');

if ($requestMethod === null) {
    unset($_SERVER['REQUEST_METHOD']);
} else {
    $_SERVER['REQUEST_METHOD'] = $requestMethod;
}

assert_same('value', GuideMyPC\Core\Environment::value(['KEY' => 'value'], 'KEY'), 'PSR-4 Core classes autoload without Composer.');
assert_same(true, class_exists(GuideMyPC\Features\Diagnostics\DiagnosticRepository::class), 'Diagnostic repository autoloads through Composer.');
assert_same('https://example.test/guides.php', GuideMyPC\Core\Url::applicationUrl('https://example.test/', '/guides.php'), 'Core URL generation preserves the legacy path contract.');
assert_same('https://example.test/css/style.css?v=2', GuideMyPC\Core\Url::assetUrl('https://example.test', '2', 'css/style.css'), 'Core asset URLs preserve the version query contract.');
try {
    GuideMyPC\Core\Database::connect(['host' => null, 'user' => null, 'password' => '', 'database' => null, 'port' => '3306']);
    assert_same(true, false, 'Core database factory rejects incomplete configuration.');
} catch (RuntimeException $exception) {
    assert_same('Database configuration is incomplete.', $exception->getMessage(), 'Core database factory preserves incomplete configuration errors.');
}
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
$contactViewOutput = (static function (): string {
    ob_start();
    (new GuideMyPC\Features\Pages\PageController(new GuideMyPC\Core\View()))->contact();

    return (string) ob_get_clean();
})();
assert_same(true, str_contains($contactViewOutput, '<title>Contact | GuideMyPC</title>'), 'Pages controller passes Contact metadata to the layout.');
assert_same(true, str_contains($contactViewOutput, '<h1 id="contact-title">Contact support</h1>'), 'Pages controller renders the contact view.');
foreach ([
    'privacy' => ['Privacy | GuideMyPC', 'privacy-heading', 'privacy.php'],
    'terms' => ['Terms | GuideMyPC', 'terms-heading', 'terms.php'],
    'disclaimer' => ['Disclaimer | GuideMyPC', 'disclaimer-heading', 'disclaimer.php'],
    'donate' => ['Support GuideMyPC | GuideMyPC', 'donate-heading', 'donate.php'],
    'ai' => ['AI Assistant | GuideMyPC', 'ai-title', 'ai.php'],
] as $method => [$title, $headingId, $canonicalPath]) {
    $staticPageOutput = (static function () use ($method): string {
        ob_start();
        (new GuideMyPC\Features\Pages\PageController(new GuideMyPC\Core\View()))->{$method}();

        return (string) ob_get_clean();
    })();

    assert_same(true, str_contains($staticPageOutput, '<title>' . $title . '</title>'), sprintf('Pages controller preserves %s metadata.', $method));
    assert_same(true, str_contains($staticPageOutput, 'href="' . e(application_url($canonicalPath)) . '"'), sprintf('Pages controller preserves the %s canonical URL.', $method));
    assert_same(true, str_contains($staticPageOutput, 'id="' . $headingId . '"'), sprintf('Pages controller renders the %s view.', $method));
}
assert_same(null, required_string('Long value', 3), 'required_string rejects long values.');
assert_same(['page' => 2, 'per_page' => 20, 'offset' => 20], pagination_values('2'), 'pagination_values calculates offsets.');
assert_same(['password' => '[redacted]', 'title' => 'Guide'], redact_log_context(['password' => 'secret', 'title' => 'Guide']), 'redact_log_context removes sensitive values.');
assert_same('blue screen', normalize_search_query('  Blue   Screen  '), 'normalize_search_query folds case and whitespace.');
assert_same('', GuideMyPC\Features\Search\SearchQuery::normalize(str_repeat('x', 121)), 'Search query normalization rejects values longer than 120 characters.');
assert_same(false, search_query_is_aggregate_safe('name@example.test'), 'search aggregation rejects email-like queries.');
assert_same(true, search_query_is_aggregate_safe('wifi keeps disconnecting'), 'search aggregation accepts ordinary support queries.');
assert_same('https://www.youtube-nocookie.com/embed/M7lc1UVf-VE', guide_youtube_embed_url('https://youtu.be/M7lc1UVf-VE'), 'guide_youtube_embed_url permits a valid YouTube ID.');
assert_same('https://www.youtube.com/watch?v=M7lc1UVf-VE', guide_youtube_watch_url('https://www.youtube-nocookie.com/embed/M7lc1UVf-VE'), 'guide_youtube_watch_url normalizes legacy stored embeds.');
assert_same('https://www.youtube-nocookie.com/embed/M7lc1UVf-VE', guide_youtube_embed_url('https://www.youtube-nocookie.com/embed/M7lc1UVf-VE'), 'guide_youtube_embed_url renders legacy stored embeds.');
assert_same(null, guide_youtube_embed_url('https://example.test/video'), 'guide_youtube_embed_url rejects unapproved video hosts.');
assert_same(null, guide_youtube_embed_url('http://youtu.be/M7lc1UVf-VE'), 'guide_youtube_embed_url requires HTTPS.');
assert_same(null, guide_safe_url('http://support.microsoft.com/unsafe'), 'guide_safe_url requires approved HTTPS URLs.');
assert_same('https://support.microsoft.com/windows', guide_safe_source_url('https://support.microsoft.com/windows'), 'guide_safe_source_url accepts ordinary HTTPS source URLs.');
assert_same(null, guide_safe_source_url('https://user:secret@support.microsoft.com/windows'), 'guide_safe_source_url rejects source URL credentials.');
assert_same(null, guide_safe_source_url('https://support.microsoft.com:8443/windows'), 'guide_safe_source_url rejects unexpected source URL ports.');
assert_same(null, guide_safe_source_url('https://127.0.0.1/windows'), 'guide_safe_source_url rejects IP-literal source URLs.');
$guestProgressSession = [];
GuideMyPC\Features\Guides\GuideProgressService::saveGuestProgress($guestProgressSession, 12, 34, true);
assert_same([12 => [34 => true]], $guestProgressSession['_guest_progress'], 'Guide progress service stores guest progress by guide and step.');
GuideMyPC\Features\Guides\GuideProgressService::saveGuestProgress($guestProgressSession, 12, 34, false);
assert_same([12 => []], $guestProgressSession['_guest_progress'], 'Guide progress service preserves the legacy empty guest guide state when a step is cleared.');
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
assert_same(true, (new GuideMyPC\Features\Downloads\DownloadPolicy())->reviewStateIsValid('approved'), 'Download policy accepts approved review states.');
assert_same(false, (new GuideMyPC\Features\Downloads\DownloadPolicy())->reviewStateIsValid('public'), 'Download policy rejects unknown review states.');
assert_same(true, community_post_is_public(['is_published' => 1]), 'Community policy permits published legacy posts.');
assert_same(false, community_post_is_public(['is_published' => 0]), 'Community policy rejects unpublished legacy posts.');

fwrite(STDOUT, "PASS: helper tests completed.\n");
