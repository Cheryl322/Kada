<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - Sistem Koperasi KADA</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
</head>

<?php
session_start();
include "headermain.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once "dbconnect.php";
    
    $employeeID = $_POST['employeeID'];
    $password = $_POST['password'];

    // Query to check credentials and get role
    $sql = "SELECT * FROM tb_employee WHERE employeeID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $employeeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        // Verify password using password_verify
        if (password_verify($password, $user['password'])) {
            $_SESSION['employeeID'] = $employeeID;
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: adminmainpage.php");
            } else {
                header("Location: mainpage.php");
            }
            exit();
        } else {
            $_SESSION['error_message'] = "ID Pekerja atau kata laluan tidak sah!";
        }
    } else {
        $_SESSION['error_message'] = "ID Pekerja atau kata laluan tidak sah!";
    }
    
    header("Location: login.php");
    exit();
}
?>

<style>
body {
    background: url('img/padi.jpg') no-repeat center center fixed;
    background-size: cover;
    position: relative;
    font-family: 'Poppins', sans-serif;
    min-height: 100vh;
}

body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(rgba(245, 245, 245, 0.85), rgba(240, 240, 240, 0.8));
    z-index: -1;
}

.login-container {
    background: rgba(255, 255, 255, 0.95);
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.08);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(200,200,200,0.2);
    max-width: 450px;
    width: 90%;
    margin: 40px auto;
}

.logo {
    max-width: 180px;
    margin: 0 auto 30px;
    display: block;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    color: #2c5282;
    font-weight: 500;
    margin-bottom: 8px;
}

.form-control {
    border: 1px solid #e2e8f0;
    padding: 12px;
    border-radius: 10px;
    width: 100%;
}

.btn-login {
    background: #2c5282;
    color: white;
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 10px;
    font-weight: 500;
    margin-top: 20px;
}

.forgot-password {
    color: #2c5282;
    text-decoration: none;
    font-size: 0.9rem;
    float: right;
    margin-top: 10px;
}

.input-group .btn-outline-secondary {
    border-color: #ced4da;
    z-index: 10;
    padding: 0.375rem 0.75rem;  /* 添加内边距 */
}

.input-group .btn-outline-secondary i {
    font-size: 16px;  /* 设置图标大小 */
    line-height: 1;   /* 确保图标垂直居中 */
}
</style>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6">
            <div class="login-container">
                <img src="img/kadalogo.jpg" alt="KADA Logo" class="logo">
                
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

                <h2 class="text-center mb-4" style="color: #2c5282;">Log Masuk</h2>

                <form action="" method="POST">
                    <div class="form-group">
                        <label for="employeeID" class="form-label">Employee ID</label>
                        <input type="text" class="form-control" id="employeeID" name="employeeID" 
                               placeholder="Masukkan employee ID" required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Kata Laluan</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Masukkan kata laluan" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                        <a href="forgot_password.php" class="forgot-password">Lupa kata laluan?</a>
                    </div>

                    <button type="submit" class="btn btn-login">Log Masuk</button>

                    <div class="text-center mt-4">
                        <p class="mb-0">Belum mempunyai akaun? 
                            <a href="register.php" class="text-decoration-none" style="color: #2c5282;">
                                Daftar Sekarang
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
document.getElementById('togglePassword').addEventListener('click', function(e) {
    e.preventDefault();
    const passwordInput = document.getElementById('password');
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    
    // 切换图标
    const icon = this.querySelector('i');
    if (type === 'text') {
        icon.classList.remove('fa-eye-slash');  // 移除闭眼
        icon.classList.add('fa-eye');          // 添加睁眼
    } else {
        icon.classList.remove('fa-eye');       // 移除睁眼
        icon.classList.add('fa-eye-slash');    // 添加闭眼
    }
});
</script>

<?php include 'footer.php'; ?>