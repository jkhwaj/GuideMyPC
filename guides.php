<?php
include("config.php");
include("includes/header.php");
include("includes/navbar.php");

$categorySlug = $_GET['category'] ?? '';
$search = trim($_GET['search'] ?? '');

$categorySql = "SELECT * FROM categories WHERE slug = ?";
$stmt = $conn->prepare($categorySql);
$stmt->bind_param("s", $categorySlug);
$stmt->execute();
$categoryResult = $stmt->get_result();
$category = $categoryResult->fetch_assoc();

if (!$category) {
    echo "<section class='section'><h2>Category not found</h2></section>";
    include("includes/footer.php");
    exit;
}

if ($search !== '') {
    $searchTerm = "%" . $search . "%";

    $guidesSql = "
        SELECT guides.*,
               ROUND(AVG(guide_ratings.rating), 1) AS average_rating,
               COUNT(guide_ratings.id) AS total_ratings
        FROM guides
        LEFT JOIN guide_ratings ON guides.id = guide_ratings.guide_id
        WHERE guides.category_id = ?
        AND (guides.title LIKE ? OR guides.description LIKE ?)
        GROUP BY guides.id
    ";

    $stmt = $conn->prepare($guidesSql);
    $stmt->bind_param("iss", $category['id'], $searchTerm, $searchTerm);
} else {
    $guidesSql = "
        SELECT guides.*,
               ROUND(AVG(guide_ratings.rating), 1) AS average_rating,
               COUNT(guide_ratings.id) AS total_ratings
        FROM guides
        LEFT JOIN guide_ratings ON guides.id = guide_ratings.guide_id
        WHERE guides.category_id = ?
        GROUP BY guides.id
    ";

    $stmt = $conn->prepare($guidesSql);
    $stmt->bind_param("i", $category['id']);
}

$stmt->execute();
$guidesResult = $stmt->get_result();
?>

<section class="section">
    <p class="section-label">Guides</p>
    <h2><?php echo htmlspecialchars($category['name']); ?> Troubleshooting</h2>
    <p class="section-desc"><?php echo htmlspecialchars($category['description']); ?></p>

    <form method="GET" class="guide-search">
        <input type="hidden" name="category" value="<?php echo htmlspecialchars($categorySlug); ?>">

        <input
            type="text"
            name="search"
            value="<?php echo htmlspecialchars($search); ?>"
            placeholder="Search guides... example: slow, update, virus">

        <button type="submit">Search</button>

        <?php if ($search !== ''): ?>
            <a href="guides.php?category=<?php echo htmlspecialchars($categorySlug); ?>">Clear</a>
        <?php endif; ?>
    </form>

    <div class="card-grid">
        <?php if ($guidesResult && $guidesResult->num_rows > 0): ?>
            <?php while ($guide = $guidesResult->fetch_assoc()): ?>
                <a class="card" href="guide.php?slug=<?php echo htmlspecialchars($guide['slug']); ?>">
                    <h3><?php echo htmlspecialchars($guide['title']); ?></h3>

                    <p><?php echo htmlspecialchars($guide['description']); ?></p>

                    <p class="meta">
                        ⭐ <?php echo $guide['average_rating'] ?? "0"; ?> / 5
                        (<?php echo $guide['total_ratings']; ?> ratings)
                    </p>

                    <p class="meta">
                        <?php echo htmlspecialchars($guide['difficulty']); ?> •
                        <?php echo htmlspecialchars($guide['estimated_time']); ?> •
                        <?php echo htmlspecialchars($guide['risk_level']); ?> Risk
                    </p>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No guides found matching your search.</p>
        <?php endif; ?>
    </div>
</section>

<?php include("includes/footer.php"); ?>