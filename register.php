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
include 'headermain.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once "dbconnect.php";
    
    $employeeID = $_POST['employeeID'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $admin_key = $_POST['admin_key'];

    // Check if employeeID or email already exists
    $checkQuery = "SELECT * FROM tb_employee WHERE employeeID = ? OR email = ?";
    $checkStmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, "ss", $employeeID, $email);
    mysqli_stmt_execute($checkStmt);
    $result = mysqli_stmt_get_result($checkStmt);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if ($row['employeeID'] === $employeeID) {
            $_SESSION['error_message'] = 'ID Pekerja telah didaftarkan.';
        } else {
            $_SESSION['error_message'] = 'Email telah didaftarkan.';
        }
        header('Location: register.php');
        exit();
    }

    // If admin key is provided, verify it
    if (!empty($admin_key)) {
        if ($admin_key !== "your_admin_key") { // Replace with your actual admin key
            $_SESSION['error_message'] = "Invalid admin key!";
            header("Location: register.php");
            exit();
        }
        $role = "admin";
    } else {
        $role = "user";
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $insertQuery = "INSERT INTO tb_employee (employeeID, email, password, role) VALUES (?, ?, ?, ?)";
    $insertStmt = mysqli_prepare($conn, $insertQuery);
    
    if (!$insertStmt) {
        $_SESSION['error_message'] = 'Sistem ralat.';
        header('Location: register.php');
        exit();
    }
    
    mysqli_stmt_bind_param($insertStmt, "ssss", $employeeID, $email, $hashedPassword, $role);
    
    if (mysqli_stmt_execute($insertStmt)) {
        $_SESSION['success_message'] = 'Pendaftaran berjaya! Sila log masuk.';
        header('Location: login.php');
        exit();
    } else {
        $_SESSION['error_message'] = 'Ralat semasa pendaftaran.';
        header('Location: register.php');
        exit();
    }
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

.register-container {
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
    transition: transform 0.3s ease;
}

.logo:hover {
    transform: scale(1.05);
}

.form-label {
    color: #2c5282;
    font-weight: 500;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-control {
    border: 1px solid #e2e8f0;
    padding: 12px;
    border-radius: 10px;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
}

.form-control:focus {
    border-color: #2c5282;
    box-shadow: 0 0 0 3px rgba(44, 82, 130, 0.1);
    background: white;
}

.input-group .btn {
    border-top-right-radius: 10px !important;
    border-bottom-right-radius: 10px !important;
    border: 1px solid #e2e8f0;
}

.btn-primary {
    background: #2c5282;
    border: none;
    padding: 12px 24px;
    font-weight: 500;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    text-transform: uppercase;
}

.btn-primary:hover {
    background: #1a4971;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.login-link {
    color: #2c5282;
    font-weight: 500;
    transition: color 0.3s ease;
    text-decoration: none;
}

.login-link:hover {
    color: #1a4971;
    text-decoration: underline !important;
}

.alert {
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
    border: none;
    background: #fff5f5;
    color: #c53030;
    border-left: 4px solid #c53030;
}

@media (max-width: 768px) {
    .register-container {
        padding: 30px 20px;
        margin: 20px auto;
    }
}

.password-requirements {
    margin-top: 0.5rem;
    font-size: 0.85rem;
}

.password-requirements ul {
    list-style-type: none;
    padding-left: 0;
    margin-bottom: 0;
}

.password-requirements li {
    margin-bottom: 0.25rem;
}

.text-success {
    color: #28a745 !important;
}

.text-muted {
    color: #6c757d !important;
}
</style>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6">
            <div class="register-container">
                <img src="img/kadalogo.jpg" alt="KADA Logo" class="logo">
                <h2 class="text-center mb-4">Daftar Akaun</h2>
                
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

                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="employeeID" class="form-label">ID Pekerja</label>
                        <input type="text" 
                               class="form-control" 
                               id="employeeID" 
                               name="employeeID" 
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               placeholder="Masukkan email anda"
                               required>
                        <small class="form-text text-muted">
                            Email ini akan digunakan untuk reset kata laluan jika diperlukan.
                        </small>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i>
                            Kata Laluan
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Masukkan kata laluan" 
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
                    
                    <div class="mb-4">
                        <label for="admin_key" class="form-label">
                            <i class="fas fa-eye"></i>
                            Admin Key (Optional)
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="admin_key" name="admin_key" 
                                   placeholder="Masukkan admin key jika mendaftar sebagai admin">
                            <button class="btn btn-outline-secondary" type="button" id="toggleAdminKey">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Daftar
                        </button>
                    </div>

                    <div class="text-center mt-4">
                        <p class="mb-0">Sudah mempunyai akaun? 
                            <a href="login.php" class="login-link">
                                <i class="fas fa-sign-in-alt me-1"></i>Log Masuk
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function validateForm() {
    var employeeID = document.getElementById('employeeID').value;
    var password = document.getElementById('password').value;

    if (employeeID.trim() === '' || password.trim() === '') {
        alert('Please fill in all fields');
        return false;
    }
    return true;
}

const passwordInput = document.getElementById('password');
const requirements = document.querySelector('.password-requirements');

function checkPassword(password) {
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /\d/.test(password),
        special: /[@$!%*?&]/.test(password)
    };
    
    // Update each requirement's display
    for (const [req, met] of Object.entries(requirements)) {
        const element = document.getElementById(req);
        if (met) {
            element.style.color = '#75B798'; // 青色 for met requirements
            element.innerHTML = `✓ ${element.textContent.replace('✓ ', '')}`; // 只添加一个勾号
        } else {
            element.style.color = '#dc3545'; // Red for unmet requirements
            element.innerHTML = element.textContent.replace('✓ ', ''); // 移除勾号
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

// When focus is lost (blur)
passwordInput.addEventListener('blur', function() {
    if (this.value && !checkPassword(this.value)) {
        requirements.style.display = 'block';
    }
});

// When input is focused
passwordInput.addEventListener('focus', function() {
    if (this.value && !checkPassword(this.value)) {
        requirements.style.display = 'block';
    }
});

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

<?php include "footer.php"; ?>