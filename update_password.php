<?php
session_start();
include "dbconnect.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employeeID = $_SESSION['employeeID'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    
    try {
        // Get the current hashed password
        $checkPassword = "SELECT password FROM tb_employee WHERE employeeID = ?";
        $stmt = mysqli_prepare($conn, $checkPassword);
        mysqli_stmt_bind_param($stmt, "s", $employeeID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        if (!$user) {
            throw new Exception('Pengguna tidak dijumpai');
        }
        
        // Verify the current password using password_verify
        if (!password_verify($currentPassword, $user['password'])) {
            throw new Exception('Kata laluan semasa tidak tepat');
        }
        
        // Hash the new password before saving
        $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update with the new hashed password
        $updatePassword = "UPDATE tb_employee SET password = ? WHERE employeeID = ?";
        $stmt = mysqli_prepare($conn, $updatePassword);
        mysqli_stmt_bind_param($stmt, "ss", $hashedNewPassword, $employeeID);
        
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