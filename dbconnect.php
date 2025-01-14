<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_kada";

// Create connection with increased timeout
$conn = mysqli_init();
if (!$conn) {
    die("mysqli_init failed");
}

mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 300);
mysqli_options($conn, MYSQLI_INIT_COMMAND, "SET FOREIGN_KEY_CHECKS=1;");

if (!mysqli_real_connect($conn, $servername, $username, $password, $dbname)) {
    die("Connect Error: " . mysqli_connect_error());
}

// Set charset and other important settings
mysqli_set_charset($conn, "utf8mb4");
mysqli_query($conn, "SET SESSION wait_timeout=300");
mysqli_query($conn, "SET SESSION interactive_timeout=300");
mysqli_query($conn, "SET SESSION sql_mode = ''");

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

?>