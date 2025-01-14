<?php
session_start();
include "headermain.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once "dbconnect.php";
    
    $employeeID = $_POST['employeeID'];
    $password = $_POST['password'];

    // First check if employeeID exists in employee table
    $checkEmployee = "SELECT * FROM tb_employee WHERE employeeID = ?";
    $stmt = mysqli_prepare($conn, $checkEmployee);
    mysqli_stmt_bind_param($stmt, 's', $employeeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 0) {
        // EmployeeID not found in employee table
        $_SESSION['error_message'] = "ID Pekerja tidak wujud. Sila daftar akaun terlebih dahulu.";
        header("Location: login.php");
        exit();
    }

    // If employeeID exists, proceed with password check
    $sql = "SELECT * FROM tb_employee WHERE employeeID = ? AND password = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $employeeID, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['employeeID'] = $row['employeeID'];
        header("Location: mainpage.php");
        exit();
    } else {
        $_SESSION['error_message'] = "ID Pekerja atau kata laluan tidak sah.";
        header("Location: login.php");
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
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <a href="forgot-password.php" class="forgot-password">Lupa kata laluan?</a>
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

<?php include 'footer.php'; ?>