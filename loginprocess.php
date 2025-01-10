<?php
session_start();
include "dbconnect.php";

$employeeID = $_POST['employeeID'];
$password = $_POST['password'];

// 添加调试信息
error_reporting(E_ALL);
ini_set('display_errors', 1);

// SQL query to select user
$sql = "SELECT * FROM tb_employee 
        WHERE employeeID = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "s", $employeeID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

//Retrieve data
$row = mysqli_fetch_array($result);
$count = mysqli_num_rows($result);

// Rule-based AI login
if ($count == 1) {
    // Verify the password
    if (password_verify($password, $row['password'])) {
        // Set session
        $_SESSION['employeeID'] = $row['employeeID'];
        
        // 添加调试信息
        echo "Login successful. Session employeeID: " . $_SESSION['employeeID'];
        
        // 直接重定向到 profil.php
        header('Location: profil.php');
        exit();
    } else {
        $_SESSION['error_message'] = "Kata laluan salah.";
        header('Location: login.php');
        exit();
    }
} else {
    $_SESSION['error_message'] = "ID pekerja tidak wujud.";
    header('Location: login.php');
    exit();
}

mysqli_close($con);
?>