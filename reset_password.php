<?php
session_start();
require_once 'dbconnect.php';

// Set timezone to Kuala Lumpur
date_default_timezone_set('Asia/Kuala_Lumpur');

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $_SESSION['reset_token'] = $token; // Store token in session
    
    // Debug log
    error_log("Received token: " . $token);
    
    // Check if token exists and is not expired
    $query = "SELECT employeeID FROM tb_employee WHERE reset_token = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($conn));
        $_SESSION['error_message'] = 'Sistem ralat.';
        header('Location: forgot_password.php');
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "s", $token);
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Execute failed: " . mysqli_stmt_error($stmt));
        $_SESSION['error_message'] = 'Sistem ralat.';
        header('Location: forgot_password.php');
        exit();
    }
    
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$row = mysqli_fetch_assoc($result)) {
        error_log("Token not found in database: " . $token);
        $_SESSION['error_message'] = 'Token tidak sah.';
        header('Location: forgot_password.php');
        exit();
    }

    // If we get here, token is valid
    error_log("Token is valid for employee ID: " . $row['employeeID']);
} else {
    $_SESSION['error_message'] = 'Token tidak sah.';
    header('Location: forgot_password.php');
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeID = $_POST['employeeID'];
    $newPassword = $_POST['newPassword'];
    $token = $_POST['token'];

    // Debug logging
    error_log("Processing password reset - Employee ID: " . $employeeID);
    error_log("Token received: " . $token);

    // Verify token and employeeID match
    $query = "SELECT employeeID FROM tb_employee WHERE employeeID = ? AND reset_token = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($conn));
        $_SESSION['error_message'] = 'Sistem ralat.';
        header('Location: forgot_password.php');
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "ss", $employeeID, $token);
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Execute failed: " . mysqli_stmt_error($stmt));
        $_SESSION['error_message'] = 'Sistem ralat.';
        header('Location: forgot_password.php');
        exit();
    }
    
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password query
        $updateQuery = "UPDATE tb_employee SET 
                       password = ?, 
                       reset_token = NULL, 
                       reset_token_expiry = NULL 
                       WHERE employeeID = ?";
        
        $updateStmt = mysqli_prepare($conn, $updateQuery);
        
        if (!$updateStmt) {
            error_log("Update prepare failed: " . mysqli_error($conn));
            $_SESSION['error_message'] = 'Sistem ralat.';
            header('Location: reset_password.php?token=' . $token);
            exit();
        }
        
        mysqli_stmt_bind_param($updateStmt, "ss", $hashedPassword, $employeeID);
        
        if (mysqli_stmt_execute($updateStmt)) {
            error_log("Password updated successfully for employee ID: " . $employeeID);
            $_SESSION['success_message'] = 'Kata laluan anda telah berjaya dikemaskini.';
            header('Location: login.php');
            exit();
        } else {
            error_log("Failed to update password: " . mysqli_error($conn));
            $_SESSION['error_message'] = 'Ralat semasa mengemaskini kata laluan.';
            header('Location: reset_password.php?token=' . $token);
            exit();
        }
    } else {
        error_log("Invalid employeeID or token not found");
        $_SESSION['error_message'] = 'ID Pekerja tidak sah atau pautan reset telah tamat tempoh.';
        header('Location: reset_password.php?token=' . $token);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Kata Laluan - KADA Ahli</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    body {
        background: url('img/padi.jpg') no-repeat center center fixed;
        background-size: cover;
        min-height: 100vh;
    }

    .page-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        padding: 40px 0;
        background: rgba(255, 255, 255, 0.1);
    }

    .reset-password-container {
        background: rgba(255, 255, 255, 0.95);
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
        max-width: 500px;
        width: 90%;
        margin: 0 auto;
    }

    .icon-container {
        width: 80px;
        height: 80px;
        background-color: #e8f5e9;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
    }

    .icon-container i {
        font-size: 35px;
        color: #75B798;
    }

    .title {
        color: #2c3e50;
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 25px;
        text-align: center;
    }

    .form-control {
        height: 50px;
        padding: 10px 20px;
        font-size: 16px;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.9);
    }

    .form-control:focus {
        border-color: #75B798;
        box-shadow: 0 0 0 0.2rem rgba(117, 183, 152, 0.25);
    }

    .btn-reset {
        background-color: black;  /* White background */
        color: black;  /* Black text */
        height: 50px;
        font-size: 16px;
        font-weight: 600;
        border: 1px solid #ced4da;  /* Light border */
        border-radius: 10px;
        transition: all 0.3s ease;
        width: 100%;
        margin-top: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-reset:hover {
        background-color: black;  /* Very light gray on hover */
        color: black;  /* Keep text black on hover */
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .btn-reset:active {
        transform: translateY(0);
        background-color: #f8f9fa;
        color: black;  /* Keep text black when clicked */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .btn-reset i {
        margin-right: 10px;
        font-size: 18px;
        color: black;  /* Black icon */
    }

    /* Modal button styles to match */
    .modal .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
        font-weight: 600;
    }

    .modal .btn-primary:hover {
        background-color: #0b5ed7;
        border-color: #0b5ed7;
    }

    .modal .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .form-text {
        color #6c757d;
        font-size: 14px;
        margin-top: 8px;
        display: block;
    }

    .password-toggle {
        cursor: pointer;
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }

    /* Add these styles */
    .modal-footer .btn {
        padding: 8px 16px;
        border-radius: 4px;
        font-weight: 500;
        cursor: pointer !important;
        z-index: 1050;
    }

    .modal-footer .btn-secondary {
        background-color: #6c757d;
        color: white;
        border: none;
    }

    .modal-footer .btn-primary {
        background-color: #0d6efd;
        color: white;
        border: none;
    }

    .modal-footer .btn:hover {
        opacity: 0.9;
    }

    .modal {
        z-index: 1050 !important;
    }

    .modal-backdrop {
        z-index: 1040 !important;
    }

    .modal {
        z-index: 1050 !important;
        pointer-events: auto !important;
    }

    .modal-dialog {
        z-index: 1051 !important;
        pointer-events: auto !important;
    }

    .modal-content {
        z-index: 1052 !important;
        pointer-events: auto !important;
    }

    .modal-footer {
        z-index: 1053 !important;
        pointer-events: auto !important;
    }

    .modal-footer .btn {
        pointer-events: auto !important;
        cursor: pointer !important;
    }

    .modal-backdrop {
        z-index: 1049 !important;
    }

    /* Override any conflicting styles */
    button {
        pointer-events: auto !important;
        cursor: pointer !important;
    }
    </style>
</head>
<body>
    <?php include "headermain.php"; ?>

    <div class="page-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="reset-password-container">
                        <div class="icon-container">
                            <i class="fas fa-key"></i>
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

                        <!-- Update the form and modal HTML -->
                        <form id="resetForm" method="POST">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                            
                            <div class="mb-3">
                                <label for="employeeID" class="form-label">ID Pekerja</label>
                                <input type="text" class="form-control" id="employeeID" name="employeeID" required>
                            </div>

                            <div class="mb-3">
                                <label for="newPassword" class="form-label">Kata Laluan Baru</label>
                                <div class="position-relative">
                                    <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                                    <i class="fas fa-eye password-toggle" onclick="togglePassword('newPassword')"></i>
                                </div>
                            </div>

                            <!-- Password requirements section -->
                            <div class="password-requirements mb-3">
                                <p class="mb-2">Kata laluan mestilah:</p>
                                <p id="length" class="requirement">• Minimum 8 aksara</p>
                                <p id="uppercase" class="requirement">• Sekurang-kurangnya 1 huruf besar</p>
                                <p id="lowercase" class="requirement">• Sekurang-kurangnya 1 huruf kecil</p>
                                <p id="number" class="requirement">• Sekurang-kurangnya 1 nombor</p>
                                <p id="special" class="requirement">• Sekurang-kurangnya 1 simbol (@$!%*?&)</p>
                            </div>

                            <button type="submit" class="btn btn-reset">
                                <i class="fas fa-save"></i> SIMPAN KATA LALUAN BARU
                            </button>
                        </form>

                        <script>
                        document.getElementById('resetForm').addEventListener('submit', function(e) {
                            e.preventDefault(); // Prevent form from submitting immediately
                            
                            const password = document.getElementById('newPassword').value;
                            const employeeID = document.getElementById('employeeID').value;

                            // Validate password
                            if (!validatePassword(password)) {
                                alert('Sila pastikan kata laluan memenuhi semua keperluan.');
                                return;
                            }

                            // Show confirmation using standard confirm dialog
                            if (confirm('Adakah anda pasti untuk menukar kata laluan?')) {
                                this.submit(); // Submit the form if user confirms
                            }
                        });

                        function validatePassword(password) {
                            return password.length >= 8 && 
                                   /[A-Z]/.test(password) && 
                                   /[a-z]/.test(password) && 
                                   /[0-9]/.test(password) && 
                                   /[@$!%*?&]/.test(password);
                        }

                        function togglePassword(inputId) {
                            const input = document.getElementById(inputId);
                            const icon = event.currentTarget;
                            
                            if (input.type === 'password') {
                                input.type = 'text';
                                icon.classList.remove('fa-eye');
                                icon.classList.add('fa-eye-slash');
                            } else {
                                input.type = 'password';
                                icon.classList.remove('fa-eye-slash');
                                icon.classList.add('fa-eye');
                            }
                        }
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Make sure these scripts are included in the correct order -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php include "footer.php"; ?>
</body>
</html> 