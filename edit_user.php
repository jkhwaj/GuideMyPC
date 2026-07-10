<?php
session_start();

include("config.php");
include("includes/header.php");
include("includes/navbar.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: index.php");
    exit;
}

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: admin_users.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $role = $_POST["role"];

    $stmt = $conn->prepare("
        UPDATE users
        SET full_name = ?, email = ?, role = ?
        WHERE id = ?
    ");

    $stmt->bind_param("sssi", $full_name, $email, $role, $id);
    $stmt->execute();

    if ($_SESSION["user_id"] == $id) {
        $_SESSION["full_name"] = $full_name;
        $_SESSION["role"] = $role;
    }

    header("Location: admin_users.php?success=user_updated");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: admin_users.php");
    exit;
}
?>

<section class="auth-page">
    <div class="auth-card">
        <h1>Edit User</h1>
        <p>Update user details and role.</p>

        <form method="POST">
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user["full_name"]); ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user["email"]); ?>" required>

            <label>Role</label>
            <select name="role">
                <option value="user" <?php echo $user["role"] === "user" ? "selected" : ""; ?>>User</option>
                <option value="admin" <?php echo $user["role"] === "admin" ? "selected" : ""; ?>>Admin</option>
            </select>

            <button type="submit">Save Changes</button>
        </form>
    </div>
</section>

<?php include("includes/footer.php"); ?>