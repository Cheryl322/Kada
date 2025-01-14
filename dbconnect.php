<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_kada";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set MySQL session variables
mysqli_query($conn, "SET SESSION wait_timeout=300");
mysqli_query($conn, "SET SESSION interactive_timeout=300");
mysqli_query($conn, "SET SESSION sql_mode = ''");

// Set character set
mysqli_set_charset($conn, "utf8mb4");

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

?>