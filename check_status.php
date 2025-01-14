<?php
include "dbconnect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['employeeID'])) {
    $employeeID = $_POST['employeeID'];
    
    $sql = "SELECT COALESCE(mr.regisStatus, 'Belum Selesai') as regisStatus 
            FROM tb_member m 
            LEFT JOIN tb_memberregistration_memberapplicationdetails mr 
            ON m.employeeID = mr.memberRegistrationID 
            WHERE m.employeeID = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $employeeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $status = mysqli_fetch_assoc($result)['regisStatus'];
    
    echo $status;
} 