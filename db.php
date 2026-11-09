<?php
// Database connection - included by every page that needs MySQL.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
date_default_timezone_set('Asia/Dhaka');

$conn = new mysqli(
    "mysql-mashfi.alwaysdata.net",  // host
    "mashfi",                        // username
    "Mashfi@123",                      // password
    "mashfi_cargarage"                // database name
);
$conn->set_charset('utf8mb4');
