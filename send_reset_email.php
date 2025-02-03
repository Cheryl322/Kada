<?php
session_start();
require_once 'email_helper.php';
require_once 'dbconnect.php';

// Set timezone to Kuala Lumpur
date_default_timezone_set('Asia/Kuala_Lumpur');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    
    // Check if email exists in database
    $query = "SELECT employeeID FROM tb_member WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Debug log to verify token and expiry
        error_log("Generated token: " . $token);
        error_log("Expiry time (KL): " . $expiry);
        
        // Save token in database
        $updateQuery = "UPDATE tb_member SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, "sss", $token, $expiry, $email);
        
        // Debug log for database update
        if (mysqli_stmt_execute($stmt)) {
            error_log("Token saved successfully for email: " . $email);
            try {
                $emailHelper = new EmailHelper();
                
                // Create reset link with absolute path
                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/KADA/reset_password.php?token=" . urlencode($token);
                
                $emailBody = "
                    <h2>Reset Kata Laluan KADA Ahli</h2>
                    <p>Anda telah meminta untuk menetapkan semula kata laluan anda.</p>
                    <p>Sila klik pautan di bawah untuk menetapkan semula kata laluan anda:</p>
                    <p style='margin: 25px 0;'>
                        <a href='$resetLink' 
                           style='background-color: #75B798; 
                                  color: white; 
                                  padding: 12px 30px; 
                                  text-decoration: none; 
                                  border-radius: 5px; 
                                  display: inline-block;'>
                            Reset Kata Laluan
                        </a>
                    </p>
                    <p>Pautan ini akan tamat dalam masa 1 jam.</p>
                    <p>Jika anda tidak meminta reset kata laluan, sila abaikan email ini.</p>
                ";
                
                $emailHelper->sendPasswordResetEmail($email, $emailBody);
                $_SESSION['success_message'] = 'Email reset kata laluan telah dihantar. Sila semak inbox anda.';
            } catch (Exception $e) {
                error_log("Email sending error: " . $e->getMessage());
                $_SESSION['error_message'] = 'Ralat menghantar email. Sila cuba lagi.';
            }
        } else {
            error_log("Database update error: " . mysqli_error($conn));
            $_SESSION['error_message'] = 'Ralat sistem. Sila cuba lagi.';
        }
    } else {
        $_SESSION['error_message'] = 'Email tidak dijumpai dalam sistem.';
    }
    
    header('Location: forgot_password.php');
    exit();
} 