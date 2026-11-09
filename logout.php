<?php
// Log the admin out and return to the booking page.
session_start();
$_SESSION = [];
session_destroy();
header('Location: index.php');
exit;
