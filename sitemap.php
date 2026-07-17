<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

header('Content-Type: application/xml; charset=utf-8');
$urls = [application_url('index.php'), application_url('guides.php'), application_url('knowledge.php'), application_url('downloads.php'), application_url('about.php'), application_url('contact.php')];
$guides = $conn->query("SELECT slug, updated_at FROM guides WHERE is_published = 1 ORDER BY updated_at DESC");
while ($guide = $guides->fetch_assoc()) $urls[] = application_url('guide.php?slug=' . rawurlencode($guide['slug']));
$articles = $conn->query("SELECT slug, updated_at FROM knowledge_articles WHERE publication_state = 'published' ORDER BY updated_at DESC");
while ($article = $articles->fetch_assoc()) $urls[] = application_url('knowledge_article.php?slug=' . rawurlencode($article['slug']));
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach ($urls as $url) echo "  <url><loc>" . htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc></url>\n";
echo "</urlset>\n";
