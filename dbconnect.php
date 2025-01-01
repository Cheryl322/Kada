<?php
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

?>