<?php
session_start();
require_once 'dbconnect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employeeID = $_POST['employeeID'];
    $status = $_POST['status'];
    
    // 记录接收到的数据
    error_log("Updating status for employeeID: $employeeID to status: $status");
    
    // 检查是否已存在状态记录
    $checkSql = "SELECT * FROM tb_member_status WHERE employeeID = ?";
    $checkStmt = mysqli_prepare($conn, $checkSql);
    
    if (!$checkStmt) {
        error_log("Prepare check statement failed: " . mysqli_error($conn));
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }
    
    mysqli_stmt_bind_param($checkStmt, "s", $employeeID);
    mysqli_stmt_execute($checkStmt);
    $result = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($result) > 0) {
        // 更新现有记录
        $sql = "UPDATE tb_member_status SET status = ? WHERE employeeID = ?";
    } else {
        // 插入新记录
        $sql = "INSERT INTO tb_member_status (employeeID, status) VALUES (?, ?)";
    }
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        error_log("Prepare statement failed: " . mysqli_error($conn));
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }
    
    mysqli_stmt_bind_param($stmt, "ss", $status, $employeeID);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
        error_log("Status updated successfully");
    } else {
        error_log("Query failed: " . mysqli_error($conn));
        echo json_encode([
            'success' => false, 
            'message' => 'Database error: ' . mysqli_error($conn)
        ]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request method'
    ]);
}

mysqli_close($conn);
?> 