<?php
include "dbconnect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId = $_POST['memberId'];
    $status = $_POST['status'];
    $currentDate = date('Y-m-d H:i:s'); // Current timestamp
    
    // First, check if there's an existing record
    $checkSql = "SELECT memberRegistrationID FROM tb_memberregistration_memberapplicationdetails 
                 WHERE memberRegistrationID = ?";
    
    $checkStmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($checkStmt, "i", $memberId);
    mysqli_stmt_execute($checkStmt);
    $result = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Update existing record
        $sql = "UPDATE tb_memberregistration_memberapplicationdetails 
                SET regisStatus = ?, regisDate = ? 
                WHERE memberRegistrationID = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $status, $currentDate, $memberId);
    } else {
        // Insert new record
        $sql = "INSERT INTO tb_memberregistration_memberapplicationdetails 
                (memberRegistrationID, regisDate, regisStatus) 
                VALUES (?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iss", $memberId, $currentDate, $status);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Success";
    } else {
        http_response_code(500);
        echo "Error: " . mysqli_error($conn);
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}