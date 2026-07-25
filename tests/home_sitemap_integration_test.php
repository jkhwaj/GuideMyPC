<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once __DIR__ . '/bootstrap.php';

use GuideMyPC\Features\Home\HomeReadModel;
use GuideMyPC\Features\Sitemap\SitemapReadModel;

function home_sitemap_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$test = test_database_or_fail();
$test->begin_transaction();
$token = bin2hex(random_bytes(5));

try {
    $publicCategorySlug = 'home-sitemap-public-' . $token;
    $hiddenCategorySlug = 'home-sitemap-hidden-' . $token;
    $category = $test->prepare('INSERT INTO categories (name, slug, description, is_published) VALUES (?, ?, ?, ?)');
    $description = 'Temporary Home and Sitemap integration fixture.';
    $publicName = 'Home Sitemap Public ' . $token;
    $published = 1;
    $category->bind_param('sssi', $publicName, $publicCategorySlug, $description, $published);
    $category->execute();
    $publicCategoryId = $category->insert_id;
    $hiddenName = 'Home Sitemap Hidden ' . $token;
    $hidden = 0;
    $category->bind_param('sssi', $hiddenName, $hiddenCategorySlug, $description, $hidden);
    $category->execute();
    $hiddenCategoryId = $category->insert_id;
    $category->close();

    $guide = $test->prepare('INSERT INTO guides (category_id, title, slug, description, is_published) VALUES (?, ?, ?, ?, ?)');
    $visibleGuideSlug = 'home-sitemap-guide-' . $token;
    $visibleGuideTitle = 'Home Sitemap Guide ' . $token;
    $guide->bind_param('isssi', $publicCategoryId, $visibleGuideTitle, $visibleGuideSlug, $description, $published);
    $guide->execute();
    $hiddenGuideSlug = 'home-sitemap-hidden-guide-' . $token;
    $hiddenGuideTitle = 'Home Sitemap Hidden Guide ' . $token;
    $guide->bind_param('isssi', $publicCategoryId, $hiddenGuideTitle, $hiddenGuideSlug, $description, $hidden);
    $guide->execute();
    $categoryHiddenGuideSlug = 'home-sitemap-category-hidden-guide-' . $token;
    $categoryHiddenGuideTitle = 'Home Sitemap Category Hidden Guide ' . $token;
    $guide->bind_param('isssi', $hiddenCategoryId, $categoryHiddenGuideTitle, $categoryHiddenGuideSlug, $description, $published);
    $guide->execute();
    $guide->close();
    $test->query("UPDATE guides SET featured_order = 0 WHERE slug = '" . $test->real_escape_string($visibleGuideSlug) . "'");

    $article = $test->prepare("INSERT INTO knowledge_articles (category_id, article_type, title, slug, summary, content, publication_state, published_at) VALUES (?, 'explanation', ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
    $visibleArticleSlug = 'home-sitemap-article-' . $token;
    $visibleArticleTitle = 'Home Sitemap Article ' . $token;
    $publishedState = 'published';
    $article->bind_param('isssss', $publicCategoryId, $visibleArticleTitle, $visibleArticleSlug, $description, $description, $publishedState);
    $article->execute();
    $draftArticleSlug = 'home-sitemap-draft-' . $token;
    $draftArticleTitle = 'Home Sitemap Draft ' . $token;
    $draftState = 'draft';
    $article->bind_param('isssss', $publicCategoryId, $draftArticleTitle, $draftArticleSlug, $description, $description, $draftState);
    $article->execute();
    $hiddenArticleSlug = 'home-sitemap-hidden-article-' . $token;
    $hiddenArticleTitle = 'Home Sitemap Hidden Article ' . $token;
    $article->bind_param('isssss', $hiddenCategoryId, $hiddenArticleTitle, $hiddenArticleSlug, $description, $description, $publishedState);
    $article->execute();
    $article->close();

    $download = $test->prepare("INSERT INTO downloads (name, description, official_url, category, is_published, review_state) VALUES (?, ?, ?, 'Support', ?, ?)");
    $visibleDownloadName = 'Home Sitemap Download ' . $token;
    $visibleDownloadUrl = 'https://downloads.example.test/' . $token;
    $approved = 'approved';
    $download->bind_param('sssis', $visibleDownloadName, $description, $visibleDownloadUrl, $published, $approved);
    $download->execute();
    $unsafeDownloadName = 'Home Sitemap Unsafe Download ' . $token;
    $unsafeDownloadUrl = 'http://downloads.example.test/' . $token;
    $download->bind_param('sssis', $unsafeDownloadName, $description, $unsafeDownloadUrl, $published, $approved);
    $download->execute();
    $download->close();
    $test->query("UPDATE downloads SET featured_order = 0 WHERE name = '" . $test->real_escape_string($visibleDownloadName) . "'");

    $user = $test->prepare('INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)');
    $userName = 'Home Sitemap User ' . $token;
    $email = 'home-sitemap-' . $token . '@example.test';
    $password = password_hash('HomeSitemapPassword1!', PASSWORD_DEFAULT);
    $user->bind_param('sss', $userName, $email, $password);
    $user->execute();
    $userId = $user->insert_id;
    $user->close();

    $post = $test->prepare('INSERT INTO community_posts (user_id, title, content, is_published) VALUES (?, ?, ?, ?)');
    $visiblePostTitle = 'Home Sitemap Post ' . $token;
    $post->bind_param('issi', $userId, $visiblePostTitle, $description, $published);
    $post->execute();
    $hiddenPostTitle = 'Home Sitemap Hidden Post ' . $token;
    $post->bind_param('issi', $userId, $hiddenPostTitle, $description, $hidden);
    $post->execute();
    $post->close();

    $home = (new HomeReadModel($test))->content();
    home_sitemap_assert(in_array($publicCategorySlug, array_column($home['categories'], 'slug'), true), 'Home includes published categories.');
    home_sitemap_assert(!in_array($hiddenCategorySlug, array_column($home['categories'], 'slug'), true), 'Home excludes unpublished categories.');
    home_sitemap_assert(in_array($visibleGuideSlug, array_column($home['popularGuides'], 'slug'), true), 'Home includes published guides in published categories.');
    home_sitemap_assert(!in_array($hiddenGuideSlug, array_column($home['popularGuides'], 'slug'), true) && !in_array($categoryHiddenGuideSlug, array_column($home['popularGuides'], 'slug'), true), 'Home excludes unpublished and hidden-category guides.');
    home_sitemap_assert(in_array($visibleDownloadName, array_column($home['downloads'], 'name'), true) && !in_array($unsafeDownloadName, array_column($home['downloads'], 'name'), true), 'Home applies the approved Download policy.');
    home_sitemap_assert(in_array($visiblePostTitle, array_column($home['communityPosts'], 'title'), true) && !in_array($hiddenPostTitle, array_column($home['communityPosts'], 'title'), true), 'Home excludes unpublished Community posts.');

    $urls = (new SitemapReadModel($test))->publicUrls('https://example.test/GuideMyPC');
    home_sitemap_assert(in_array('https://example.test/GuideMyPC/index.php', $urls, true), 'Sitemap retains static legacy paths under an application subdirectory.');
    home_sitemap_assert(in_array('https://example.test/GuideMyPC/contact.php', $urls, true), 'Sitemap retains the Contact route after Donate retirement.');
    home_sitemap_assert(!in_array('https://example.test/GuideMyPC/ai.php', $urls, true), 'Sitemap excludes the retired AI route.');
    home_sitemap_assert(!in_array('https://example.test/GuideMyPC/donate.php', $urls, true), 'Sitemap excludes the retired Donate route.');
    home_sitemap_assert(!in_array('https://example.test/GuideMyPC/admin_reports.php', $urls, true), 'Sitemap does not advertise an excluded Reports route.');
    home_sitemap_assert(in_array('https://example.test/GuideMyPC/guide.php?slug=' . rawurlencode($visibleGuideSlug), $urls, true), 'Sitemap includes published Guide URLs.');
    home_sitemap_assert(!in_array('https://example.test/GuideMyPC/guide.php?slug=' . rawurlencode($hiddenGuideSlug), $urls, true) && !in_array('https://example.test/GuideMyPC/guide.php?slug=' . rawurlencode($categoryHiddenGuideSlug), $urls, true), 'Sitemap excludes unpublished and hidden-category Guides.');
    home_sitemap_assert(in_array('https://example.test/GuideMyPC/knowledge_article.php?slug=' . rawurlencode($visibleArticleSlug), $urls, true), 'Sitemap includes published Knowledge URLs.');
    home_sitemap_assert(!in_array('https://example.test/GuideMyPC/knowledge_article.php?slug=' . rawurlencode($draftArticleSlug), $urls, true) && !in_array('https://example.test/GuideMyPC/knowledge_article.php?slug=' . rawurlencode($hiddenArticleSlug), $urls, true), 'Sitemap excludes draft and hidden-category Knowledge URLs.');

    $test->rollback();
    fwrite(STDOUT, "PASS: Home and Sitemap projections preserve publication policies and legacy URLs.\n");
} catch (Throwable $exception) {
    $test->rollback();
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
} finally {
    $test->close();
}
