<?php

include 'headermain.php';
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
                <!-- Login Form -->
               <form method="POST" action="loginprocess.php">
                   <?php if(isset($_GET['error'])): ?>
                       <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                   <?php endif; ?>
                    <div class="mb-3">
                       <label for="email" class="form-label">Emel (User/Admin)</label>
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
                        <a class="btn btn-primary btn-lg" href="mainpage.php" role="button">Log Masuk</a>
                   </div>
                    <!-- <div class="d-grid">
                       <button type="submit" name="login" class="btn btn-primary">Log Masuk</button>
                   </div> -->
                    <div class="text-center mt-3">
                       <p>Sila daftar sekiranya anda belum mempunyai akaun. 
                          <a href="register.php" class="text-decoration-none">Daftar Akaun?</a>
                       </p>
                   </div>
               </form>
           </div>
       </div>
   </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   <script>
       // Toggle password visibility
       document.getElementById('togglePassword').addEventListener('click', function() {
           const password = document.getElementById('password');
           const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
           password.setAttribute('type', type);
           this.querySelector('i').classList.toggle('fa-eye');
           this.querySelector('i').classList.toggle('fa-eye-slash');
       });
   </script>
</body>
