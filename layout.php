<?php

function page_header($title, $active = "")
{
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?> - Car Workshop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="container">
        <a class="brand" href="index.php">Car Workshop Appointment</a>
        <nav>
            <a href="index.php" <?php if ($active == "book") echo 'class="active"'; ?>>Book</a>
            <a href="help.php" <?php if ($active == "help") echo 'class="active"'; ?>>Help</a>
            <a href="admin.php" <?php if ($active == "admin") echo 'class="active"'; ?>>Admin</a>
            <?php if (isset($_SESSION['is_admin'])): ?>
                <a href="logout.php">Logout</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
    <?php
}

function page_footer($scripts = [])
{
    ?>
</main>
<footer class="site-footer">
    <div class="container">CSE 391 Assignment 3</div>
</footer>
<?php foreach ($scripts as $file): ?>
<script src="<?= $file ?>"></script>
<?php endforeach; ?>
</body>
</html>
    <?php
}
