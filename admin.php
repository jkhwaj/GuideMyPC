<?php
require_once __DIR__ . '/config.php';
require_admin();

include("includes/header.php");
include("includes/navbar.php");

/* Statistics */
$usersCount = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc();
$guidesCount = $conn->query("SELECT COUNT(*) AS total FROM guides")->fetch_assoc();
$categoriesCount = $conn->query("SELECT COUNT(*) AS total FROM categories")->fetch_assoc();
$downloadsCount = $conn->query("SELECT COUNT(*) AS total FROM downloads")->fetch_assoc();
$postsCount = $conn->query("SELECT COUNT(*) AS total FROM community_posts")->fetch_assoc();
$commentsCount = $conn->query("SELECT COUNT(*) AS total FROM community_comments")->fetch_assoc();
$ratingsCount = $conn->query("SELECT COUNT(*) AS total FROM guide_ratings")->fetch_assoc();
$favoritesCount = $conn->query("SELECT COUNT(*) AS total FROM favorites")->fetch_assoc();

/* Latest users */
$latestUsers = $conn->query("
    SELECT id, full_name, email, role, created_at
    FROM users
    ORDER BY created_at DESC
    LIMIT 5
");

/* Latest community posts */
$latestPosts = $conn->query("
    SELECT community_posts.id,
           community_posts.title,
           community_posts.created_at,
           users.full_name
    FROM community_posts
    JOIN users ON community_posts.user_id = users.id
    ORDER BY community_posts.created_at DESC
    LIMIT 5
");

/* Top-rated guides */
$topGuides = $conn->query("
    SELECT guides.id,
           guides.title,
           guides.slug,
           ROUND(AVG(guide_ratings.rating), 1) AS average_rating,
           COUNT(guide_ratings.id) AS total_ratings
    FROM guides
    LEFT JOIN guide_ratings ON guides.id = guide_ratings.guide_id
    GROUP BY guides.id
    ORDER BY average_rating DESC, total_ratings DESC
    LIMIT 5
");
?>

<section class="profile-page">
    <div class="profile-card">
        <h1>Admin Dashboard</h1>

        <p>
            Welcome back,
            <?php echo htmlspecialchars($_SESSION["full_name"]); ?>.
            Manage GuideMyPC from one place.
        </p>

        <div class="profile-info">
            <div>
                <span>Users</span>
                <strong><?php echo $usersCount["total"]; ?></strong>
            </div>

            <div>
                <span>Guides</span>
                <strong><?php echo $guidesCount["total"]; ?></strong>
            </div>

            <div>
                <span>Categories</span>
                <strong><?php echo $categoriesCount["total"]; ?></strong>
            </div>

            <div>
                <span>Downloads</span>
                <strong><?php echo $downloadsCount["total"]; ?></strong>
            </div>

            <div>
                <span>Community Posts</span>
                <strong><?php echo $postsCount["total"]; ?></strong>
            </div>

            <div>
                <span>Comments</span>
                <strong><?php echo $commentsCount["total"]; ?></strong>
            </div>

            <div>
                <span>Guide Ratings</span>
                <strong><?php echo $ratingsCount["total"]; ?></strong>
            </div>

            <div>
                <span>Favorites</span>
                <strong><?php echo $favoritesCount["total"]; ?></strong>
            </div>
        </div>
    </div>

    <div class="admin-dashboard-grid">
        <section class="dashboard-panel">
            <div class="dashboard-panel-header">
                <h2>Latest Users</h2>
                <a href="admin_users.php">View all</a>
            </div>

            <?php if ($latestUsers && $latestUsers->num_rows > 0): ?>
                <div class="dashboard-list">
                    <?php while ($user = $latestUsers->fetch_assoc()): ?>
                        <div class="dashboard-list-item">
                            <div>
                                <strong>
                                    <?php echo htmlspecialchars($user["full_name"]); ?>
                                </strong>

                                <p>
                                    <?php echo htmlspecialchars($user["email"]); ?>
                                </p>
                            </div>

                            <span class="dashboard-badge">
                                <?php echo htmlspecialchars(ucfirst($user["role"])); ?>
                            </span>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="dashboard-empty">No users found.</p>
            <?php endif; ?>
        </section>

        <section class="dashboard-panel">
            <div class="dashboard-panel-header">
                <h2>Latest Community Posts</h2>
                <a href="admin_community.php">View all</a>
            </div>

            <?php if ($latestPosts && $latestPosts->num_rows > 0): ?>
                <div class="dashboard-list">
                    <?php while ($post = $latestPosts->fetch_assoc()): ?>
                        <div class="dashboard-list-item">
                            <div>
                                <strong>
                                    <?php echo htmlspecialchars($post["title"]); ?>
                                </strong>

                                <p>
                                    By <?php echo htmlspecialchars($post["full_name"]); ?>
                                </p>
                            </div>

                            <span class="dashboard-date">
                                <?php echo date("d/m/Y", strtotime($post["created_at"])); ?>
                            </span>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="dashboard-empty">No community posts found.</p>
            <?php endif; ?>
        </section>

        <section class="dashboard-panel dashboard-panel-wide">
            <div class="dashboard-panel-header">
                <h2>Top Rated Guides</h2>
                <a href="admin_guides.php">Manage guides</a>
            </div>

            <?php if ($topGuides && $topGuides->num_rows > 0): ?>
                <div class="dashboard-list">
                    <?php while ($guide = $topGuides->fetch_assoc()): ?>
                        <a
                            class="dashboard-list-item"
                            href="guide.php?slug=<?php echo urlencode($guide["slug"]); ?>"
                        >
                            <div>
                                <strong>
                                    <?php echo htmlspecialchars($guide["title"]); ?>
                                </strong>

                                <p>
                                    <?php echo $guide["total_ratings"]; ?> ratings
                                </p>
                            </div>

                            <span class="dashboard-rating">
                                ⭐ <?php echo $guide["average_rating"] ?? "0"; ?> / 5
                            </span>
                        </a>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="dashboard-empty">No guides found.</p>
            <?php endif; ?>
        </section>
    </div>

    <div class="profile-stats">
        <a class="stat-card" href="admin_guides.php">
            <span>Manage Guides</span>
            <strong>📚 Add / Edit / Delete</strong>
        </a>

        <a class="stat-card" href="admin_downloads.php">
            <span>Manage Downloads</span>
            <strong>📥 Trusted Tools</strong>
        </a>

        <a class="stat-card" href="admin_users.php">
            <span>Manage Users</span>
            <strong>👥 Roles</strong>
        </a>

        <a class="stat-card" href="admin_categories.php">
            <span>Manage Categories</span>
            <strong>🗂 Devices</strong>
        </a>

        <a class="stat-card" href="admin_community.php">
            <span>Manage Community</span>
            <strong>💬 Posts</strong>
        </a>

        <a class="stat-card" href="admin_comments.php">
            <span>Manage Comments</span>
            <strong>🗨️ Comments</strong>
        </a>

        <a class="stat-card" href="community.php">
            <span>View Community</span>
            <strong>🌐 Public Page</strong>
        </a>

        <a class="stat-card" href="downloads.php">
            <span>View Downloads</span>
            <strong>🔗 Public Page</strong>
        </a>
    </div>
</section>

<?php include("includes/footer.php"); ?>
