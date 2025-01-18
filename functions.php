<?php
// 数据库连接检查函数
function checkDbConnection($conn) {
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
}

// 记录交易函数
function recordTransaction($conn, $employeeID, $transType, $transAmt, $transDate) {
    $sql = "INSERT INTO tb_transaction (employeeID, transType, transAmt, transDate) 
            VALUES (?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'isds', $employeeID, $transType, $transAmt, $transDate);
    return mysqli_stmt_execute($stmt);
}

// 验证管理员权限
function checkAdminAccess() {
    if (!isset($_SESSION['employeeID']) || $_SESSION['role'] !== 'admin') {
        header('Location: login.php');
        exit();
    }
}

// 格式化日期
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

// 格式化金额
function formatAmount($amount) {
    return number_format($amount, 2);
}
?> 