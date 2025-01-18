<?php
session_start();
if (!isset($_SESSION['employeeID']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include "dbconnect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employeeID = $_POST['employeeID'];
    $memberSaving = $_POST['memberSaving'];
    $feeCapital = $_POST['feeCapital'];
    $fixedDeposit = $_POST['fixedDeposit'];
    $contribution = $_POST['contribution'];
    
    // 开始事务
    mysqli_begin_transaction($conn);
    
    try {
        // 更新或插入 financial status
        $sql = "INSERT INTO tb_financialstatus (memberSaving, feeCapital, fixedDeposit, contribution, dateUpdated) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "dddd", $memberSaving, $feeCapital, $fixedDeposit, $contribution);
        mysqli_stmt_execute($stmt);
        
        $accountID = mysqli_insert_id($conn);
        
        // 更新会员与财务状态的关联
        $sql = "INSERT INTO tb_member_financialstatus (employeeID, accountID) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE accountID = VALUES(accountID)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $employeeID, $accountID);
        mysqli_stmt_execute($stmt);
        
        // 提交事务
        mysqli_commit($conn);
        
        $_SESSION['success_message'] = "Status kewangan telah dikemaskini!";
    } catch (Exception $e) {
        // 如果出错，回滚事务
        mysqli_rollback($conn);
        $_SESSION['error_message'] = "Ralat semasa mengemaskini status kewangan!";
    }
    
    mysqli_close($conn);
    header("Location: admin_update_financial.php");
    exit();
}
?> 