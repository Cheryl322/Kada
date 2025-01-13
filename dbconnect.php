<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "db_kada";

$con = mysqli_connect($host, $user, $password, $database);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($con, "utf8mb4");

?>