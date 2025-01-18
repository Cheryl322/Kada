<?php
session_start();
include "dbconnect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employeeID = $_POST['employeeID'];
    $newPassword = $_POST['newPassword'];

    // 验证密码格式
    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $newPassword)) {
        $_SESSION['error_message'] = "Kata laluan tidak memenuhi keperluan keselamatan";
        header("Location: forgot_password.php");
        exit();
    }

    // 更新密码
    $sql = "UPDATE tb_employee SET password = ? WHERE employeeID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $newPassword, $employeeID);

    if (mysqli_stmt_execute($stmt)) {
        // 显示成功消息
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Berjaya!',
                    text: 'Kata laluan anda telah dikemas kini. Sila log masuk dengan kata laluan baharu.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    window.location.href = 'login.php';
                });
            });
        </script>";
    } else {
        $_SESSION['error_message'] = "Ralat semasa mengemaskini kata laluan";
        header("Location: forgot_password.php");
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    header("Location: forgot_password.php");
}
?> 