<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/admin.php';
require_once __DIR__ . '/includes/accounts.php';
require_admin();

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: admin_users.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    redirect('admin_users.php');
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_csrf();
    $full_name = valid_display_name($_POST["full_name"] ?? null);
    $email = normalize_email($_POST['email'] ?? null);
    $role = GuideMyPC\Security\Authorization::normalizeRole($_POST["role"] ?? null);

    if ($full_name === null || $email === null || $role === null) {
        $message = 'Enter a valid name, email address, and role.';
        $user['full_name'] = is_string($_POST['full_name'] ?? null) ? trim($_POST['full_name']) : $user['full_name'];
        $user['email'] = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : $user['email'];
    } else {
        $conflictStatement = $conn->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
        $conflictStatement->bind_param('si', $email, $id);
        $conflictStatement->execute();
        $emailConflict = $conflictStatement->get_result()->fetch_assoc() !== null;
        $conflictStatement->close();

        if ($emailConflict) {
            $message = 'That email address is already used by another account.';
            $user['full_name'] = $full_name;
            $user['email'] = $email;
        } else {
            in_transaction($conn, static function () use ($conn, $full_name, $email, $role, $id): void {
                $stmt = $conn->prepare("
                    UPDATE users
                    SET full_name = ?, email = ?, role = ?
                    WHERE id = ?
                ");

                $stmt->bind_param("sssi", $full_name, $email, $role, $id);
                $stmt->execute();
                $stmt->close();
                admin_audit($conn, 'user.updated', 'user', $id, ['role' => $role]);
            });

            if ((int) $_SESSION["user_id"] === $id) {
                $_SESSION["full_name"] = $full_name;
                $_SESSION["role"] = $role;
                session_regenerate_id(true);
            }

            redirect('admin_users.php?success=user_updated');
        }
    }
}

include("includes/header.php");
include("includes/navbar.php");
?>

<section class="auth-page">
    <div class="auth-card">
        <h1>Edit User</h1>
        <p>Update user details and role.</p>

        <?php if ($message !== ''): ?>
            <div class="auth-message" role="alert"><?php echo e($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <?php echo csrf_field(); ?>
            <label for="user-full-name">Full Name</label>
            <input id="user-full-name" type="text" name="full_name" maxlength="100" value="<?php echo e($user["full_name"]); ?>" required>

            <label for="user-email">Email</label>
            <input id="user-email" type="email" name="email" maxlength="150" value="<?php echo e($user["email"]); ?>" required>

            <label for="user-role">Role</label>
            <select id="user-role" name="role" required>
                <option value="user" <?php echo $user["role"] === "user" ? "selected" : ""; ?>>User</option>
                <option value="editor" <?php echo $user["role"] === "editor" ? "selected" : ""; ?>>Editor</option>
                <option value="admin" <?php echo $user["role"] === "admin" ? "selected" : ""; ?>>Admin</option>
            </select>

            <button type="submit">Save Changes</button>
        </form>
    </div>
</section>

<?php include("includes/footer.php"); ?>
