<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_kada";

// Create connection using mysqli
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset and other important settings
mysqli_set_charset($conn, "utf8mb4");
mysqli_query($conn, "SET SESSION wait_timeout=300");
mysqli_query($conn, "SET SESSION interactive_timeout=300");
mysqli_query($conn, "SET SESSION sql_mode = ''");

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

?>