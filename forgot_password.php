<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
</head>

<?php
session_start();
include "headermain.php";
?>

<style>
.forgot-password-container {
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

.btn-reset {
    background-color: #75B798;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.btn-reset:hover {
    background-color: #5CBA9B;
    color: white;
}
</style>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6">
            <div class="forgot-password-container">
                <h2 class="text-center mb-4">Reset Kata Laluan</h2>
                
                <?php if(isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                            echo $_SESSION['error_message']; 
                            unset($_SESSION['error_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <form action="reset_password.php" method="POST">
                    <div class="mb-4">
                        <label for="employeeID" class="form-label">Employee ID</label>
                        <input type="text" class="form-control" id="employeeID" name="employeeID" required>
                    </div>

                    <div class="mb-4">
                        <label for="newPassword" class="form-label">Kata Laluan Baharu</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="newPassword" name="newPassword" 
                                   pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                        <div class="password-requirements mt-2 small text-danger" style="display: none;">
                            <ul class="ps-3">
                                <li id="length">Sekurang-kurangnya 8 aksara</li>
                                <li id="uppercase">Sekurang-kurangnya 1 huruf besar</li>
                                <li id="lowercase">Sekurang-kurangnya 1 huruf kecil</li>
                                <li id="number">Sekurang-kurangnya 1 nombor</li>
                                <li id="special">Sekurang-kurangnya 1 simbol khas (@$!%*?&)</li>
                            </ul>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-reset">Reset Kata Laluan</button>
                    </div>

                    <div class="text-center mt-3">
                        <a href="login.php" class="text-decoration-none">Kembali ke Log Masuk</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const passwordInput = document.getElementById('newPassword');
const requirements = document.querySelector('.password-requirements');

function checkPassword(password) {
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /\d/.test(password),
        special: /[@$!%*?&]/.test(password)
    };
    
    for (const [req, met] of Object.entries(requirements)) {
        const element = document.getElementById(req);
        if (met) {
            element.style.color = '#75B798';
            element.innerHTML = `✓ ${element.textContent.replace('✓ ', '')}`;
        } else {
            element.style.color = '#dc3545';
            element.innerHTML = element.textContent.replace('✓ ', '');
        }
    }

    return Object.values(requirements).every(Boolean);
}

passwordInput.addEventListener('input', function() {
    if (this.value) {
        requirements.style.display = 'block';
        checkPassword(this.value);
    } else {
        requirements.style.display = 'none';
    }
});

document.getElementById('togglePassword').addEventListener('click', function(e) {
    e.preventDefault();
    const passwordInput = document.getElementById('newPassword');
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

<?php include "footer.php"; ?>
