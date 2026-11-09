<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
date_default_timezone_set('Asia/Dhaka');

$conn = new mysqli(
    "mysql-mashfi.alwaysdata.net",
    "mashfi",
    "Mashfi@123",
    "mashfi_cargarage"
);
$conn->set_charset('utf8mb4');
