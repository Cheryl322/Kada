<?php
session_start();
include "headermember.php";
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <?php if(isset($_SESSION['success_message'])): ?>
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h2 class="text-success mb-4">Tahniah!</h2>
                        <p class="lead">
                            <?php 
                            echo $_SESSION['success_message'];
                            unset($_SESSION['success_message']); 
                            ?>
                        </p>
                        <div class="mt-4">
                            <a href="index.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-home"></i> Kembali ke Halaman Utama
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="mb-4">
                            <i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                        </div>
                        <h2 class="text-warning mb-4">Harap Maaf</h2>
                        <p class="lead">Tiada mesej kejayaan ditemui.</p>
                        <div class="mt-4">
                            <a href="daftar_ahli.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-arrow-left"></i> Kembali ke Pendaftaran
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?> 