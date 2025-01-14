<?php
include "dbconnect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId = $_POST['memberId'];
    $status = $_POST['status'];
    
    $sql = "INSERT INTO tb_memberregistration_memberapplicationdetails 
            (memberRegistrationID, regisDate, regisStatus) 
            VALUES (?, NOW(), ?)
            ON DUPLICATE KEY UPDATE regisStatus = ?";
            
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iss", $memberId, $status, $status);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Success";
    } else {
        http_response_code(500);
        echo "Error: " . mysqli_error($conn);
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}