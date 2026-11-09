<?php
// admin_auth.php - admin username/password and the "must be logged in" check.
session_start();

// Change the password before submitting
$admin_username = "admin";
$admin_password = "admin123";

// If the admin is not logged in, send them to the login page
function require_admin()
{
    if (!isset($_SESSION['is_admin'])) {
        header("Location: admin_login.php");
        exit;
    }
}
