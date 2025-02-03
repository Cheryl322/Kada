<?php
session_start();
include "headermember.php";
?>

<style>
.success-container {
    max-width: 600px;
    margin: 60px auto;
    padding: 30px;
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}

.success-icon {
    font-size: 60px;
    color: #4CAF50;
    margin-bottom: 20px;
}

.success-title {
    color: #2C3E50;
    font-size: 32px;
    font-weight: 600;
    margin-bottom: 15px;
}

.success-message {
    color: #666;
    font-size: 18px;
    margin-bottom: 30px;
    line-height: 1.6;
}

.btn-home {
    background-color: #95D5B2;
    color: white;
    padding: 12px 30px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
}

.btn-home:hover {
    background-color: #74C69D;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.success-footer {
    margin-top: 20px;
    font-size: 14px;
    color: #888;
}

.progress-container {
    margin-bottom: 30px;
}

.progress {
    height: 10px;
    background-color: #e9ecef;
    border-radius: 5px;
    overflow: hidden;
}

.progress-bar {
    width: 100%;
    background-color: #95D5B2;
    animation: progressAnimation 1s ease-in-out;
}

@keyframes progressAnimation {
    from { width: 0; }
    to { width: 100%; }
}
</style>

<div class="container">
    <div class="success-container text-center">
        <!-- Progress Bar -->
        <div class="progress-container">
            <div class="progress">
                <div class="progress-bar"></div>
            </div>
        </div>
        
        <!-- Success Icon -->
        <i class="fas fa-check-circle success-icon"></i>
        
        <!-- Success Title -->
        <h1 class="success-title">Berjaya!</h1>
        
        <!-- Success Message -->
        <p class="success-message">
            <?php 
            if (isset($_SESSION['success_message'])) {
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
            } else {
                echo 'Pendaftaran anda telah berjaya disimpan.';
            }
            ?>
        </p>
        
        <?php if (isset($_SESSION['email_sent']) && $_SESSION['email_sent']): ?>
            <div class="alert alert-success mt-3">
                <i class="fas fa-envelope me-2"></i>
                Email pengesahan telah dihantar.
            </div>
            <?php unset($_SESSION['email_sent']); ?>
        <?php endif; ?>
        
        <!-- Action Button -->
        <a href="mainpage.php" class="btn btn-home">
            <i class="fas fa-home me-2"></i>
            Kembali ke Halaman Utama
        </a>
        
        <!-- Footer Message -->
        <div class="success-footer">
            <p>Terima kasih kerana mendaftar dengan KADA Ahli.</p>
        </div>
    </div>
</div>

<!-- Make sure Font Awesome is included -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<!-- Optional: Add animation library -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

<script>
// Add animation classes when page loads
document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('.success-container');
    container.classList.add('animate__animated', 'animate__fadeIn');
});
</script>

<?php include "footer.php"; ?> 