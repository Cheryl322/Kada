<?php
$host = "localhost";
$user = "root";
$password = ""; // default XAMPP password is blank
$database = "db_kada";

$con = mysqli_connect($host, $user, $password, $database);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to ensure proper handling of special characters
mysqli_set_charset($con, "utf8mb4");

// Add this to test connection
echo "Connected successfully to database";
?>