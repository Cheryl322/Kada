<?php 
session_start();
include 'headermain.php'; 

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
</style>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6">
            <div class="register-container">
                <img src="img/kadalogo.jpg" alt="KADA Logo" class="logo">
                
                <h2 class="text-center mb-4" style="color: #2c5282; font-weight: 600;">Daftar Akaun</h2>
                
                <form method="POST" action="registerprocess.php">
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($_SESSION['message']['text']); ?>
                        </div>
                        <?php unset($_SESSION['message']); ?>
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <label for="employeeID" class="form-label">
                            <i class="fas fa-id-card"></i>
                            Employee ID
                        </label>
                        <input type="text" class="form-control" id="employeeID" name="employeeID" 
                               placeholder="Masukkan employee ID" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i>
                            Kata Laluan
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Masukkan kata laluan" autocomplete="off" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
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
document.getElementById('togglePassword').addEventListener('click', function() {
    const password = document.getElementById('password');
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    this.querySelector('i').classList.toggle('fa-eye');
    this.querySelector('i').classList.toggle('fa-eye-slash');
});
</script>

<?php include "footer.php"; ?>