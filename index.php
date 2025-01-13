<?php 

include 'headermain.php';
include "footer.php";

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

.welcome-container {
    padding: 80px 20px;
}

.welcome-card {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 20px;
    padding: 50px 40px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.08);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(200,200,200,0.2);
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
}

.company-logo {
    max-width: 180px;
    margin: 0 auto 30px;
    display: block;
}

.welcome-title {
    color: #1a4971;
    font-size: 2.8rem;
    font-weight: 700;
    margin-bottom: 20px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

.welcome-subtitle {
    color: #2c5282;
    font-size: 1.4rem;
    margin-bottom: 30px;
}

.divider {
    height: 3px;
    background: linear-gradient(to right, #2980b9, #2c5282);
    width: 100px;
    margin: 30px auto;
    border-radius: 2px;
}

.welcome-text {
    color: #2C3E50;
    font-size: 1.1rem;
    margin-bottom: 30px;
}

.btn-container {
    display: flex;
    gap: 20px;
    justify-content: center;
    margin-top: 30px;
}

.btn-custom {
    padding: 12px 35px;
    font-size: 1.1rem;
    border-radius: 10px;
    transition: all 0.3s ease;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.btn-login {
    background: #2c5282;
    color: white;
    border: none;
}

.btn-login:hover {
    background: #1a4971;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.btn-register {
    background: white;
    color: #2c5282;
    border: 2px solid #2c5282;
}

.btn-register:hover {
    background: #f8fafc;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .welcome-container {
        padding: 40px 20px;
    }

    .welcome-card {
        padding: 30px 20px;
    }

    .welcome-title {
        font-size: 2.2rem;
    }

    .welcome-subtitle {
        font-size: 1.2rem;
    }

    .btn-container {
        flex-direction: column;
        gap: 15px;
    }

    .btn-custom {
        width: 100%;
    }
}
</style>

<!-- Add Font Awesome and Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<div class="welcome-container">
    <div class="welcome-card">
        <img src="img/kadalogo.jpg" alt="KADA Logo" class="company-logo">
        <h1 class="welcome-title">Selamat Datang ke Koperasi KADA</h1>
        <p class="welcome-subtitle">Sistem Pengurusan Keahlian KADA</p>
        
        <div class="divider"></div>
        
        <p class="welcome-text">Sila log masuk untuk mengakses sistem.</p>
        
        <div class="btn-container">
            <a href="login.php" class="btn btn-custom btn-login">
                <i class="fas fa-sign-in-alt me-2"></i> Log Masuk
            </a>
            <a href="register.php" class="btn btn-custom btn-register">
                <i class="fas fa-user-plus me-2"></i> Daftar Ahli
            </a>
        </div>
    </div>
</div>

<?php include 'footer.php';?>