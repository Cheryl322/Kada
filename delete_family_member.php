<?php
session_start();
include "dbconnect.php";

if (isset($_GET['employeeID']) && isset($_GET['icFamilyMember'])) {
    $employeeID = $_GET['employeeID'];
    $icFamilyMember = $_GET['icFamilyMember'];
    $sessionEmployeeID = $_SESSION['employeeID'];

    // 确保只能删除自己的家庭成员
    if ($employeeID === $sessionEmployeeID) {
        $query = "DELETE FROM tb_memberregistration_familymemberinfo WHERE employeeID = ? AND icFamilyMember = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $employeeID, $icFamilyMember);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Maklumat keluarga berjaya dipadam.";
        } else {
            $_SESSION['error'] = "Ralat semasa memadam maklumat keluarga.";
        }
    } else {
        $_SESSION['error'] = "Tidak dibenarkan untuk memadam maklumat ini.";
    }
}

header("Location: profil.php");
exit();
?> 