<?php
session_start();
include "headermain.php";
require_once "dbconnect.php";

if (!isset($_GET['token'])) {
    header("Location: login.php");
    exit();
}

$token = $_GET['token'];

// Verify token and check expiry
$sql = "SELECT * FROM tb_employee WHERE reset_token = ? AND reset_expiry > NOW()";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) != 1) {
    $_SESSION['error_message'] = "Pautan tetapan semula tidak sah atau telah tamat tempoh.";
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password === $confirm_password) {
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password and clear reset token
        $updateSql = "UPDATE tb_employee SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE reset_token = ?";
        $updateStmt = mysqli_prepare($conn, $updateSql);
        mysqli_stmt_bind_param($updateStmt, "ss", $hashed_password, $token);
        mysqli_stmt_execute($updateStmt);

        $_SESSION['success_message'] = "Kata laluan anda telah berjaya ditetapkan semula. Sila log masuk.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Kata laluan tidak sepadan.";
    }
}
?>

<!-- Add the same HTML/CSS structure as forgot-password.php but with password reset form --> 