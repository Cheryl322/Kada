<?php
session_start();
require_once 'dbconnect.php';
require_once 'email_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    
    // Check if employee exists with this email
    $query = "SELECT * FROM tb_employee WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($conn));
        $_SESSION['error_message'] = 'Sistem ralat.';
        header('Location: forgot_password.php');
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "s", $email);
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Execute failed: " . mysqli_stmt_error($stmt));
        $_SESSION['error_message'] = 'Sistem ralat.';
        header('Location: forgot_password.php');
        exit();
    }
    
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Debug logging
        error_log("Attempting to update token for email: " . $email);
        error_log("Token: " . $token);
        error_log("Expiry: " . $expiry);
        
        // Update employee with reset token
        $updateQuery = "UPDATE tb_employee SET 
                       reset_token = ?,
                       reset_token_expiry = ?
                       WHERE email = ?";
                       
        $updateStmt = mysqli_prepare($conn, $updateQuery);
        
        if (!$updateStmt) {
            error_log("Update prepare failed: " . mysqli_error($conn));
            $_SESSION['error_message'] = 'Sistem ralat.';
            header('Location: forgot_password.php');
            exit();
        }
        
        mysqli_stmt_bind_param($updateStmt, "sss", $token, $expiry, $email);
        
        if (!mysqli_stmt_execute($updateStmt)) {
            error_log("Update execute failed: " . mysqli_stmt_error($updateStmt));
            $_SESSION['error_message'] = 'Ralat semasa mengemaskini token.';
            header('Location: forgot_password.php');
            exit();
        }
        
        // Verify the update
        $verifyQuery = "SELECT reset_token, reset_token_expiry FROM tb_employee WHERE email = ?";
        $verifyStmt = mysqli_prepare($conn, $verifyQuery);
        mysqli_stmt_bind_param($verifyStmt, "s", $email);
        mysqli_stmt_execute($verifyStmt);
        $verifyResult = mysqli_stmt_get_result($verifyStmt);
        $verifyRow = mysqli_fetch_assoc($verifyResult);
        
        error_log("Verification - Token: " . $verifyRow['reset_token']);
        error_log("Verification - Expiry: " . $verifyRow['reset_token_expiry']);

        if ($verifyRow['reset_token'] === NULL || $verifyRow['reset_token_expiry'] === NULL) {
            error_log("Failed to update token - values are NULL");
            $_SESSION['error_message'] = 'Ralat semasa mengemaskini token.';
            header('Location: forgot_password.php');
            exit();
        }

        // Generate reset link
        $resetLink = "http://localhost/KADA/reset_password.php?token=" . $token;
        
        // Create email body with improved styling
        $emailBody = "
<!DOCTYPE html>
<html>
<head>
    <title>Reset Kata Laluan KADA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #4CAF50;
        }
        .header img {
            max-width: 200px;
            height: auto;
        }
        .content {
            padding: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4CAF50;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background-color: #45a049;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eeeeee;
            font-size: 12px;
            color: #666666;
        }
        .link {
            word-break: break-all;
            color: #4CAF50;
        }
        .warning {
            padding: 15px;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <img src='https://kada.gov.my/images/logo.png' alt='KADA Logo'>
            <h2 style='color: #4CAF50;'>Reset Kata Laluan KADA</h2>
        </div>
        
        <div class='content'>
            <p>Assalamualaikum dan Salam Sejahtera,</p>
            
            <p>Kami telah menerima permintaan untuk reset kata laluan bagi akaun KADA anda.</p>
            
            <p>Untuk meneruskan proses reset kata laluan, sila klik butang di bawah:</p>
            
            <div style='text-align: center;'>
                <a href='{$resetLink}' class='button'>RESET KATA LALUAN</a>
            </div>
        
            
            <div class='warning'>
                <strong>Perhatian:</strong>
                <ul>
                    <li>Pautan ini hanya sah untuk tempoh 1 jam dari masa ia dijana.</li>
                    <li>Jika anda tidak meminta reset kata laluan, sila abaikan email ini.</li>
                    <li>Untuk keselamatan akaun anda, sila tukar kata laluan dengan segera.</li>
                </ul>
            </div>
        </div>
        
        <div class='footer'>
            <p>Terima kasih,<br>
            <strong>KADA Team</strong></p>
            <hr>
            <p>Ini adalah email automatik. Sila jangan balas email ini.</p>
            <p>Jika anda memerlukan bantuan, sila hubungi kami di <a href='mailto:support@kada.gov.my'>support@kada.gov.my</a></p>
            <p>&copy; " . date('Y') . " Koperasi Kakitangan KADA. Hak cipta terpelihara.</p>
        </div>
    </div>
</body>
</html>";

        // Create EmailHelper instance
        $emailHelper = new EmailHelper();

        try {
            // Send the email
            $emailHelper->sendEmail($email, "Reset Kata Laluan KADA", $emailBody);
            $_SESSION['success_message'] = 'Pautan reset kata laluan telah dihantar ke email anda.';
            header('Location: forgot_password.php');
            exit();
        } catch (Exception $e) {
            error_log("Failed to send email: " . $e->getMessage());
            $_SESSION['error_message'] = 'Ralat semasa menghantar email. Sila cuba lagi.';
            header('Location: forgot_password.php');
            exit();
        }
    } else {
        $_SESSION['error_message'] = 'Email tidak dijumpai.';
        header('Location: forgot_password.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Kata Laluan - KADA</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .page-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('img/padi.jpg');
            background-size: cover;
            background-position: center;
        }

        .forgot-password-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-container img {
            max-width: 200px;
            height: auto;
        }

        .title {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
        }

        .form-label {
            font-weight: 500;
            color: #555;
        }

        .btn-primary {
            width: 100%;
            padding: 12px;
            background-color: #0d6efd;
            border: none;
            margin-top: 20px;
            font-weight: 500;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #6c757d;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .alert {
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>

<body>
    <?php include "headermain.php"; ?>

    <div class="page-container">
        <div class="forgot-password-container">
            <div class="logo-container">
                <img src="img/kadalogo.jpg" alt="KADA Logo">
            </div>
            
            <h2 class="title">Reset Kata Laluan</h2>
            
            <?php if(isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?php 
                        echo $_SESSION['error_message']; 
                        unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo $_SESSION['success_message']; 
                        unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <form action="forgot_password.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           placeholder="Masukkan email anda"
                           required>
                    <div class="form-text text-muted">
                        Sila masukkan email yang berdaftar dengan akaun anda.
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    Hantar Email Reset
                </button>

                <div class="back-link">
                    <a href="login.php">Kembali ke Log Masuk</a>
                </div>
            </form>
        </div>
    </div>

    <?php include "footer.php"; ?>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
