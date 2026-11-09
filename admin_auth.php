<?php
session_start();

$admin_username = "admin";
$admin_password = "admin123";

function require_admin()
{
    if (!isset($_SESSION['is_admin'])) {
        header("Location: admin_login.php");
        exit;
    }
}
