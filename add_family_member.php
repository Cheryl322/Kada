<?php
session_start();
include "dbconnect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employeeID = $_SESSION['employeeID'];
    $name = $_POST['name'];
    $icFamilyMember = $_POST['icFamilyMember'];
    $relationship = $_POST['relationship'];

    $query = "INSERT INTO tb_memberregistration_familymemberinfo (employeeID, name, icFamilyMember, relationship) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $employeeID, $name, $icFamilyMember, $relationship);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Maklumat keluarga berjaya ditambah.";
    } else {
        $_SESSION['error'] = "Ralat semasa menambah maklumat keluarga.";
    }

    header("Location: profil.php");
    exit();
}
?> 