<?php
session_start();
include "dbconnect.php";

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: login.php');
    exit;
}

$employeeID = (int)mysqli_real_escape_string($conn, trim($_POST['employeeID']));
$password = trim($_POST['password']);

try {
    // Get user data including role
    $sql = "SELECT * FROM tb_employee WHERE employeeID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $employeeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['employeeID'] = $user['employeeID'];
            $_SESSION['role'] = $user['role']; // Store the role in session
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: adminmainpage.php');
            } else {
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

} catch (Exception $e) {
    $_SESSION['error_message'] = "Login failed: " . $e->getMessage();
    header('Location: login.php');
    exit();
} finally {
    if (isset($stmt)) mysqli_stmt_close($stmt);
    mysqli_close($conn);
}

// Debug lines (add these temporarily)
error_log("Login attempt - EmployeeID: " . $employeeID);
error_log("Role: " . $user['role']);
error_log("Session data: " . print_r($_SESSION, true));
?>