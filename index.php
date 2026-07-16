<?php
require_once __DIR__ . '/config.php';
include("includes/header.php");
include("includes/navbar.php");
?>

<section class="hero">
    <div class="badge">AI-powered support for PCs, phones, and Wi-Fi</div>

    <h1>Fix your tech problems<br>step by step.</h1>

    <p>
        Search trusted guides, use AI troubleshooting, diagnose common issues,
        watch helpful videos, and download only safe tools.
    </p>

    <div class="search-box">
        <input type="text" placeholder="Describe your problem... example: My Wi-Fi keeps disconnecting">
    </div>

    <div class="hero-buttons">
        <a href="ai.php" class="primary-btn">Ask GuideMyPC AI</a>
        <a href="guides.php" class="secondary-btn">Browse Guides</a>
    </div>
</section>

<section class="section">
    <p class="section-label">Support Categories</p>
    <h2>Choose what you need help with</h2>
    <p class="section-desc">Start with your device or problem area, then follow guided troubleshooting.</p>

    <div class="card-grid">
    <?php
    $sql = "SELECT * FROM categories ORDER BY id ASC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0):
        while ($category = $result->fetch_assoc()):
    ?>
        <a class="card" href="guides.php?category=<?php echo urlencode($category['slug']); ?>">
            <div class="icon"><?php echo e($category['icon']); ?></div>
            <h3><?php echo e($category['name']); ?></h3>
            <p><?php echo e($category['description']); ?></p>
        </a>
    <?php
        endwhile;
    else:
    ?>
        <p>No categories found.</p>
    <?php endif; ?>
</div>
</section>

<?php
include("includes/footer.php");
?>
