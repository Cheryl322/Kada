<?php
session_start();
include "dbconnect.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employeeID = $_SESSION['employeeID'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    
    try {
        // 验证当前密码
        $checkPassword = "SELECT password FROM tb_employee WHERE employeeID = ?";
        $stmt = mysqli_prepare($conn, $checkPassword);
        mysqli_stmt_bind_param($stmt, "s", $employeeID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        if (!$user) {
            throw new Exception('Pengguna tidak dijumpai');
        }
        
        // 直接比较密码
        if ($currentPassword !== $user['password']) {
            throw new Exception('Kata laluan semasa tidak tepat');
        }
        
        // 更新密码 - 不使用 password_hash
        $updatePassword = "UPDATE tb_employee SET password = ? WHERE employeeID = ?";
        $stmt = mysqli_prepare($conn, $updatePassword);
        mysqli_stmt_bind_param($stmt, "ss", $newPassword, $employeeID);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                'success' => true,
                'message' => 'Kata laluan berjaya dikemaskini'
            ]);
        } else {
            throw new Exception('Ralat semasa mengemaskini kata laluan');
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Kaedah permintaan tidak sah'
    ]);
}
?> 