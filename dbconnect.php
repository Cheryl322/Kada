<<<<<<< HEAD
<!-- <?php
//Set DB Parameter
=======
<?php
// Database credentials
$servername = "localhost";  // Your database server (usually localhost)
$username = "root";         // Your database username (default is root)
$password = "";            // Your database password (default is empty for XAMPP)
$dbname = "db_kada";       // Your database name
>>>>>>> 88cd7e6 (Describe the changes made)

// Create connection with error reporting
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");

<<<<<<< HEAD
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
// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Uncomment the line below for debugging
// error_log("Database connected successfully");
>>>>>>> 88cd7e6 (Describe the changes made)
?>