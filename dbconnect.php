<!-- <?php
//Set DB Parameter

$servername ="localhost";
$username ="root";
$password ="";
$dbname ="db_kada";

//Connect DB
$con = mysqli_connect($servername, $username, $password, $dbname);

//Connection Check
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($con, "utf8mb4");

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
?>