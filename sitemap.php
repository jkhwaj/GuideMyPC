<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

require_get();

header('Content-Type: application/xml; charset=utf-8');
$urls = (new GuideMyPC\Features\Sitemap\SitemapReadModel($conn))->publicUrls(config_value('APP_URL', ''));
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach ($urls as $url) echo "  <url><loc>" . htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc></url>\n";
echo "</urlset>\n";
