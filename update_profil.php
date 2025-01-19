<?php
session_start();
include "dbconnect.php";

if (!isset($_SESSION['employeeID'])) {
    header('Location: login.php');
    exit();
}

try {
    $employeeID = $_SESSION['employeeID'];
    
    // 更新 tb_member 表 - 修正参数数量
    $sqlMember = "UPDATE tb_member SET 
                  memberName = ?,
                  email = ?,
                  ic = ?,
                  maritalStatus = ?,
                  sex = ?,
                  religion = ?,
                  nation = ?,
                  phoneNumber = ?
                  WHERE employeeID = ?";
            
    $stmtMember = mysqli_prepare($conn, $sqlMember);
    mysqli_stmt_bind_param($stmtMember, "ssssssssi",
        $_POST['memberName'],
        $_POST['email'],
        $_POST['ic'],
        $_POST['maritalStatus'],
        $_POST['sex'],
        $_POST['religion'],
        $_POST['nation'],
        $_POST['phoneNumber'],
        $employeeID
    );
    
    // 更新家庭地址
    $sqlHome = "UPDATE tb_member_homeaddress SET 
                homeAddress = ?, 
                homePostcode = ?,
                homeState = ?
                WHERE employeeID = ?";
            
    $stmtHome = mysqli_prepare($conn, $sqlHome);
    mysqli_stmt_bind_param($stmtHome, "sssi",
        $_POST['homeAddress'],
        $_POST['homePostcode'],
        $_POST['homeState'],
        $employeeID
    );
    
    // 更新办公室地址
    $sqlOffice = "UPDATE tb_member_officeaddress SET 
                  officeAddress = ?,
                  officePostcode = ?,
                  officeState = ?
                  WHERE employeeID = ?";
            
    $stmtOffice = mysqli_prepare($conn, $sqlOffice);
    mysqli_stmt_bind_param($stmtOffice, "sssi",
        $_POST['officeAddress'],
        $_POST['officePostcode'],
        $_POST['officeState'],
        $employeeID
    );
    
    // 执行更新
    $successMember = mysqli_stmt_execute($stmtMember);
    $successHome = mysqli_stmt_execute($stmtHome);
    $successOffice = mysqli_stmt_execute($stmtOffice);
    
    if ($successMember && $successHome && $successOffice) {
        $_SESSION['success_message'] = "Profil berjaya dikemaskini!";
        // 根据来源页面决定重定向目标
        $redirect_page = $_POST['source_page'] ?? 'profil.php';
        header("Location: $redirect_page");
        exit();
    } else {
        throw new Exception("Ralat semasa mengemaskini data");
    }
    
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    // 错误时也使用相同的重定向逻辑
    $redirect_page = $_POST['source_page'] ?? 'profil.php';
    header("Location: $redirect_page");
    exit();
}

mysqli_close($conn);
?>