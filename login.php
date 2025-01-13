<?php
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

                <h2 class="text-center mb-4" style="color: #2c5282;">Log Masuk</h2>
                
                <?php if(isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                            echo $_SESSION['error_message']; 
                            unset($_SESSION['error_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <form action="loginprocess.php" method="POST">
                    <div class="form-group">
                        <label for="employeeID" class="form-label">
                            <i class="fas fa-user me-2"></i>Employee ID
                        </label>
                        <input type="text" class="form-control" id="employeeID" name="employeeID" 
                               placeholder="Masukkan employee ID" required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Kata Laluan
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Masukkan kata laluan" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <a href="forgot-password.php" class="forgot-password">
                            <i class="fas fa-question-circle me-1"></i>Lupa kata laluan?
                        </a>
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Log Masuk
                    </button>

                    <div class="text-center mt-4">
                        <p class="mb-0">Belum mempunyai akaun? 
                            <a href="register.php" class="text-decoration-none" style="color: #2c5282;">
                                <i class="fas fa-user-plus me-1"></i>Daftar Sekarang
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Add this to show a modal popup for success message
document.addEventListener('DOMContentLoaded', function() {
    <?php if(isset($_SESSION['success_message'])): ?>
        Swal.fire({
            title: 'Berjaya!',
            text: '<?php echo $_SESSION['success_message']; ?>',
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#2c5282'
        });
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
});
</script>

<?php include 'footer.php'; ?>