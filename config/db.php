<?php

$host = '127.0.0.1';
$db   = 'lms_db';
$user = 'root'; // Change if your database requires a username
$pass = '';     // Change if your database requires a password

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");
    
} catch (\mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>