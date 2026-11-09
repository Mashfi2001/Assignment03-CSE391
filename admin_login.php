<?php
// admin_login.php - login form for the admin.
require 'functions.php';
require 'layout.php';
require 'admin_auth.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? "");
    $password = $_POST['password'] ?? "";

    if ($username == $admin_username && $password == $admin_password) {
        $_SESSION['is_admin'] = true;          // remember the admin is logged in
        header("Location: admin.php");
        exit;
    } else {
        $error = "Wrong username or password.";
    }
}

page_header("Admin Login", "admin");
?>

<form class="card narrow" method="post" action="admin_login.php">
    <h1>Admin Login</h1>

    <?php if ($error != ""): ?>
        <div class="alert error"><?= h($error) ?></div>
    <?php endif; ?>

    <label for="username">Username</label>
    <input id="username" name="username" type="text">

    <label for="password">Password</label>
    <input id="password" name="password" type="password">

    <button type="submit" class="btn">Log in</button>
</form>

<?php page_footer(); ?>
