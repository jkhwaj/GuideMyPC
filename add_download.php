<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/admin.php';
require_admin();

$message = "";

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
        $message = 'Name and a safe HTTPS official URL are required.';
    } elseif (!$policy->reviewStateIsValid($reviewState)) {
        $message = 'Choose a valid review state.';
    } else {
        try {
            (new GuideMyPC\Features\Downloads\DownloadAdminService($conn))->create(
                $name,
                $description,
                $official_url,
                $category,
                $reviewState,
                $isPublished
            );
            redirect('admin_downloads.php');
        } catch (DomainException $exception) {
            $message = $exception->getMessage();
        }
    }
}

include("includes/header.php");
include("includes/navbar.php");
?>

<section class="auth-page">
    <div class="auth-card" style="max-width:700px;">
        <h1>Add Download</h1>
        <p>Add trusted official software download links.</p>

        <?php if ($message != ""): ?>
            <div class="auth-message"><?php echo e($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <?php echo csrf_field(); ?>
            <label>Software Name</label>
            <input type="text" name="name" placeholder="Example: Malwarebytes" required>

            <label>Description</label>
            <textarea name="description" placeholder="Short description of the tool"></textarea>

            <label>Official URL</label>
            <input type="url" name="official_url" placeholder="https://example.com" required>

            <label>Category</label>
            <input type="text" name="category" placeholder="Example: Security, Browser, Utility">

            <label>Review state</label>
            <select name="review_state">
                <option value="pending">Pending review</option>
                <option value="approved">Approved</option>
                <option value="stale">Stale</option>
                <option value="rejected">Rejected</option>
                <option value="archived">Archived</option>
            </select>

            <label><input type="checkbox" name="is_published" value="1"> Publish publicly</label>

            <button type="submit">Save Download</button>
        </form>
    </div>
</section>

<?php include("includes/footer.php"); ?>
