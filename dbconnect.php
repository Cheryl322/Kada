<<<<<<< HEAD
<!-- <?php
// Set DB Parameter
=======
<?php

// Database credentials
$servername = "localhost";  // Your database server (usually localhost)
$username = "root";         // Your database username (default is root)
$password = "";            // Your database password (default is empty for XAMPP)
$dbname = "db_kada";       // Your database name
>>>>>>> c6f7562e84b8058c24287ee6cc92ceb05125d139

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

<<<<<<< HEAD
// Set charset
mysqli_set_charset($conn, "utf8mb4");

?> -->

<?php
try {
    $host = "localhost";
    $dbname = "db_kada";
    $username = "root";
    $password = "";  // 如果有密码请填写

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
=======
>>>>>>> c6f7562e84b8058c24287ee6cc92ceb05125d139
?>