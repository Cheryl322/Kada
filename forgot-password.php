<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "headermain.php";
require_once "dbconnect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employeeID = $_POST['employeeID'];
    
    // Check if employee exists
    $sql = "SELECT * FROM tb_employee WHERE employeeID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $employeeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Generate reset token and expiry
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store reset token in database
        $updateSql = "UPDATE tb_employee SET reset_token = ?, reset_expiry = ? WHERE employeeID = ?";
        $updateStmt = mysqli_prepare($conn, $updateSql);
        mysqli_stmt_bind_param($updateStmt, "sss", $token, $expiry, $employeeID);
        
        if (mysqli_stmt_execute($updateStmt)) {
            $_SESSION['success_message'] = "Arahan tetapan semula kata laluan telah dihantar. Sila tetapkan kata laluan baharu anda.";
            header("Location: reset-password.php?token=" . $token);
            exit();
        } else {
            $_SESSION['error_message'] = "Ralat sistem. Sila cuba lagi.";
            header("Location: forgot-password.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "ID Pekerja tidak dijumpai dalam sistem.";
        header("Location: forgot-password.php");
        exit();
    }
}
?>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6">
            <div class="forgot-password-container">
                <img src="img/kadalogo.jpg" alt="KADA Logo" class="logo">
                <h2 class="text-center mb-4">Lupa Kata Laluan</h2>

                <?php if(isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php 
                            echo $_SESSION['error_message']; 
                            unset($_SESSION['error_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php 
                            echo $_SESSION['success_message']; 
                            unset($_SESSION['success_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" id="forgotPasswordForm">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="employeeID" name="employeeID" 
                               placeholder="Masukkan Employee ID" required>
                        <label for="employeeID">
                            <i class="fas fa-id-card"></i> Employee ID
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-paper-plane me-2"></i>Hantar
                    </button>

                    <div class="text-center">
                        <a href="login.php" class="back-to-login">
                            <i class="fas fa-arrow-left me-2"></i>Kembali ke Log Masuk
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('forgotPasswordForm');
    
    form.addEventListener('submit', function(e) {
        const employeeID = document.getElementById('employeeID').value;
        
        if (!employeeID.trim()) {
            e.preventDefault();
            Swal.fire({
                title: 'Ralat!',
                text: 'Sila masukkan ID Pekerja anda',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#2c5282'
            });
        }
    });
});
</script>

<style>
.forgot-password-container {
    background: rgba(255, 255, 255, 0.95);
    padding: 2.5rem;
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.alert {
    border-radius: 10px;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateX(-10px); }
    to { opacity: 1; transform: translateX(0); }
}

.logo {
    max-width: 180px;
    margin: 0 auto 2rem;
    display: block;
}

.btn-primary {
    background: #2c5282;
    border: none;
    padding: 12px;
    font-weight: 500;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: #2b6cb0;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(66, 153, 225, 0.2);
}

.back-to-login {
    color: #2c5282;
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.back-to-login:hover {
    color: #2b6cb0;
    text-decoration: underline;
}

.form-floating .form-control {
    border: 1px solid #e2e8f0;
    padding-left: 2.5rem;
}

.form-floating label {
    padding-left: 2.5rem;
}

.form-floating label i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #718096;
}
</style> 