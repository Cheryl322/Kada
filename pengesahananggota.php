<?php

include "headermember.php";
include "footer.php";

?>

<style>
        {
           /* background-image: url('assets/images/background.jpg'); */
           background-size: cover;
           background-position: center;
           height: 100vh;
        }
       .login-container {
           background: white;
           padding: 2rem;
           border-radius: 10px;
           box-shadow: 0 0 10px rgba(0,0,0,0.1);
           max-width: 400px;
           width: 90%;
        }
       .logo {
           max-width: 200px;
           margin-bottom: 1rem;
        }
   </style>

<body>
   <div class="container h-100">
       <div class="row h-100 align-items-center justify-content-center">
           <div class="login-container">
               <!-- Logo -->
               <div class="text-center mb-4">
                   <img src="img/kadalogo.jpg" alt="Logo" class="logo">
               </div>
               <h6 style="text-align:center;"> Pengesahan Anggota KADA </h6><br><br>
                <!-- Login Form -->
               <form method="POST" action="loginprocess.php">
                   <!-- <?php if(isset($_GET['error'])): ?>
                       <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                   <?php endif; ?> -->
                    <div class="mb-3">
                       <label for="email" class="form-label">Emel Anggota</label>
                       <input type="email" class="form-control" id="email" name="email" 
                              placeholder="Masukkan emel" required>
                   </div>
                    <div class="mb-3">
                       <label for="password" class="form-label">Kata Laluan</label>
                       <div class="input-group">
                           <input type="password" class="form-control" id="password" name="password" 
                                  placeholder="Masukkan kata laluan" required>
                           <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                               <i class="fas fa-eye"></i>
                           </button>
                       </div>
                       <div class="text-end mt-1">
                           <a href="forgot-password.php" class="text-decoration-none">Lupa kata laluan?</a>
                       </div>
                   </div>
                   <div class="d-grid">
                        <a class="btn btn-primary btn-lg" href="permohonanloan.php" role="button">Masuk</a>
                   </div>
               </form>
           </div>
       </div>
   </div>
</body>