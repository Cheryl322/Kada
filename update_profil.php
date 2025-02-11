<?php
session_start();
include "dbconnect.php";

if (!isset($_SESSION['employeeID'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi tamat']);
    exit;
}

$response = ['success' => false, 'message' => ''];

try {
    // 获取表单数据
    $employeeID = $_SESSION['employeeID'];
    $homeAddress = $_POST['homeAddress'];
    $homePostcode = $_POST['homePostcode'];
    $homeState = $_POST['homeState'];
    $officeAddress = $_POST['officeAddress'];
    $officePostcode = $_POST['officePostcode'];
    $officeState = $_POST['officeState'];
    $phoneNumber = $_POST['phoneNumber'];
    $email = $_POST['email'];

    // 更新家庭地址
    $sql = "UPDATE tb_member_homeaddress SET 
            homeAddress = ?, 
            homePostcode = ?, 
            homeState = ? 
            WHERE employeeID = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $homeAddress, $homePostcode, $homeState, $employeeID);
    mysqli_stmt_execute($stmt);

    // 更新办公地址
    $sql = "UPDATE tb_member_officeaddress SET 
            officeAddress = ?, 
            officePostcode = ?, 
            officeState = ? 
            WHERE employeeID = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $officeAddress, $officePostcode, $officeState, $employeeID);
    mysqli_stmt_execute($stmt);

    // 更新联系方式
    $sql = "UPDATE tb_member SET 
            phoneNumber = ?, 
            email = ? 
            WHERE employeeID = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $phoneNumber, $email, $employeeID);
    mysqli_stmt_execute($stmt);

    $response['success'] = true;
    $response['message'] = 'Profil berjaya dikemaskini';
} catch (Exception $e) {
    $response['message'] = 'Ralat: ' . $e->getMessage();
}

echo json_encode($response);
?>