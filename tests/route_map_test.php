<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

$root = dirname(__DIR__);
$routeMaps = [
    'web' => require $root . '/routes/web.php',
    'admin' => require $root . '/routes/admin.php',
    'api' => require $root . '/routes/api.php',
];
$expectedRoutes = [
    'web' => [
        'index.php', 'about.php', 'contact.php', 'disclaimer.php', 'privacy.php', 'terms.php',
        'guides.php', 'guide.php', 'knowledge.php', 'knowledge_article.php', 'glossary.php', 'error-code.php',
        'downloads.php', 'search.php', 'diagnostic.php', 'diagnostic_action.php', 'login.php', 'register.php',
        'forgot_password.php', 'reset_password.php', 'settings.php', 'profile.php', 'devices.php', 'revoke_device.php', 'logout_all.php', 'dashboard.php', 'logout.php',
        'account_request.php', 'save_progress.php', 'toggle_favorite.php', 'rate_guide.php', 'community.php',
        'toggle_like.php', 'sitemap.php',
    ],
    'admin' => [
        'admin.php', 'admin_categories.php', 'admin_guides.php', 'admin_downloads.php', 'admin_users.php',
        'admin_community.php', 'admin_comments.php', 'add_category.php', 'add_download.php', 'add_guide.php',
        'edit_category.php', 'edit_download.php', 'edit_guide.php', 'edit_user.php', 'delete_category.php',
        'delete_comment.php', 'delete_download.php', 'delete_guide.php', 'delete_post.php', 'delete_user.php',
    ],
    'api' => ['search_event.php', 'search_suggestions.php'],
];

foreach ($expectedRoutes as $group => $expected) {
    $actual = array_keys($routeMaps[$group]);

    sort($expected);
    sort($actual);

    if ($actual !== $expected) {
        fwrite(STDERR, sprintf("FAIL: %s route map does not match the approved legacy route list.\n", $group));
        exit(1);
    }

    foreach ($routeMaps[$group] as $route => $allowed) {
        if ($allowed !== true || basename($route) !== $route || !is_file($root . DIRECTORY_SEPARATOR . $route)) {
            fwrite(STDERR, sprintf("FAIL: %s route map contains an invalid dispatch target: %s\n", $group, $route));
            exit(1);
        }
    }
}

$retiredRoutes = ['ai.php', 'donate.php'];

foreach ($retiredRoutes as $route) {
    foreach ($routeMaps as $group => $routeMap) {
        if (isset($routeMap[$route])) {
            fwrite(STDERR, sprintf("FAIL: retired route %s remains in the %s route map.\n", $route, $group));
            exit(1);
        }
    }

    if (is_file($root . DIRECTORY_SEPARATOR . $route)) {
        fwrite(STDERR, sprintf("FAIL: retired route target still exists: %s\n", $route));
        exit(1);
    }

    $probe = __DIR__ . DIRECTORY_SEPARATOR . 'retired_route_probe.php';
    $output = [];
    $exitCode = 0;
    exec(
        escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg($probe)
        . ' ' . escapeshellarg($route),
        $output,
        $exitCode
    );
    $response = implode("\n", $output);

    if (
        $exitCode !== 0
        || !str_contains($response, '<!-- test-status:404 -->')
        || !str_contains($response, '<title>Page not found | GuideMyPC</title>')
        || !str_contains($response, 'The requested page was not found.')
    ) {
        fwrite(STDERR, sprintf("FAIL: retired route %s did not return the standard safe 404 response.\n", $route));
        exit(1);
    }
}

fwrite(STDOUT, "PASS: legacy route maps match the approved dispatch targets.\n");
