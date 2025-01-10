<?php
session_start();
include "dbconnect.php";

$employeeID = $_POST['employeeID'];
$password = $_POST['password'];

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
        $_SESSION['employeeID'] = $row['employeeID'];  // 这里设置session
        
        // Check user type
        if ($row['employeeID'] == '1234') {
            // Admin
            header('Location: adminpage.php');
        } else {
            // Regular user
            header('Location: mainpage.php');
        }
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