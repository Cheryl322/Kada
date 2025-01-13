<?php

// Database credentials
$servername = "localhost";  // Your database server (usually localhost)
$username = "root";         // Your database username (default is root)
$password = "";            // Your database password (default is empty for XAMPP)
$dbname = "db_kada";       // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>