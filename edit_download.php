<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/admin.php';
require_admin();

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: admin_downloads.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_csrf();
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $official_url = trim($_POST["official_url"]);
    $category = trim($_POST["category"]);
    $reviewState = (string) ($_POST['review_state'] ?? 'pending');
    $isPublished = isset($_POST['is_published']) ? 1 : 0;
    $policy = new GuideMyPC\Features\Downloads\DownloadPolicy();

    if ($name === '' || $policy->trustedUrl($official_url) === null) {
        abort_request(422, 'invalid_download_url', 'Provide a safe HTTPS official URL.');
    }

    if (!$policy->reviewStateIsValid($reviewState)) {
        abort_request(422, 'invalid_download_review_state', 'Choose a valid review state.');
    }

    try {
        (new GuideMyPC\Features\Downloads\DownloadAdminService($conn))->update(
            $id,
            $name,
            $description,
            $official_url,
            $category,
            $reviewState,
            $isPublished
        );
    } catch (DomainException $exception) {
        abort_request(422, 'duplicate_download', $exception->getMessage());
    }

    redirect('admin_downloads.php');
}

$stmt = $conn->prepare("SELECT * FROM downloads WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$download = $stmt->get_result()->fetch_assoc();

if (!$download) {
    redirect('admin_downloads.php');
}

include("includes/header.php");
include("includes/navbar.php");
?>

<section class="auth-page">
    <div class="auth-card" style="max-width:700px;">
        <h1>Edit Download</h1>
        <p>Update trusted download information.</p>

        <form method="POST">
            <?php echo csrf_field(); ?>
            <label>Software Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($download["name"]); ?>" required>

            <label>Description</label>
            <textarea name="description"><?php echo htmlspecialchars($download["description"]); ?></textarea>

            <label>Official URL</label>
            <input type="url" name="official_url" value="<?php echo htmlspecialchars($download["official_url"]); ?>" required>

            <label>Category</label>
            <input type="text" name="category" value="<?php echo htmlspecialchars($download["category"]); ?>">

            <label>Review state</label>
            <select name="review_state">
                <?php foreach (['pending' => 'Pending review', 'approved' => 'Approved', 'stale' => 'Stale', 'rejected' => 'Rejected', 'archived' => 'Archived'] as $state => $label): ?>
                    <option value="<?php echo e($state); ?>"<?php echo $download['review_state'] === $state ? ' selected' : ''; ?>><?php echo e($label); ?></option>
                <?php endforeach; ?>
            </select>

            <label><input type="checkbox" name="is_published" value="1"<?php echo (int) $download['is_published'] === 1 ? ' checked' : ''; ?>> Publish publicly</label>

            <button type="submit">Save Changes</button>
        </form>
    </div>
</section>

<?php include("includes/footer.php"); ?>
