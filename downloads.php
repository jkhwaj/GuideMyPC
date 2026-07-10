<?php
include("config.php");
include("includes/header.php");
include("includes/navbar.php");

$result = $conn->query("SELECT * FROM downloads ORDER BY category ASC, name ASC");
?>

<section class="section">
    <p class="section-label">Trusted Downloads</p>
    <h2>Safe tools from official sources</h2>
    <p class="section-desc">
        Download recommended tools only from official websites.
    </p>

    <div class="card-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($download = $result->fetch_assoc()): ?>
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
            <?php endwhile; ?>
        <?php else: ?>
            <p>No downloads available yet.</p>
        <?php endif; ?>
    </div>
</section>

<?php include("includes/footer.php"); ?>