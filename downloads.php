<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/downloads.php';

$policy = new GuideMyPC\Features\Downloads\DownloadPolicy();
$result = $conn->query(
    'SELECT * FROM downloads WHERE ' . $policy->publicWhereClause('downloads') . ' ORDER BY category ASC, name ASC'
);
$downloads = [];

if ($result !== false) {
    while ($download = $result->fetch_assoc()) {
        if ($policy->isPublic($download)) {
            $downloads[] = $download;
        }
    }
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="section">
    <p class="section-label">Trusted Downloads</p>
    <h2>Safe tools from official sources</h2>
    <p class="section-desc">
        Download recommended tools only from official websites.
    </p>

    <div class="card-grid">
        <?php if ($downloads !== []): ?>
            <?php foreach ($downloads as $download): ?>
                <div class="card">
                    <p class="section-label">
                        <?php echo htmlspecialchars($download["category"]); ?>
                    </p>

                    <h3><?php echo htmlspecialchars($download["name"]); ?></h3>

                    <p>
                        <?php echo htmlspecialchars($download["description"]); ?>
                    </p>

                    <br>

                    <a
                        class="primary-btn"
                        href="<?php echo htmlspecialchars($download["official_url"]); ?>"
                        target="_blank">
                        Download
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No downloads available yet.</p>
        <?php endif; ?>
    </div>
</section>

<?php include("includes/footer.php"); ?>
