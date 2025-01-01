<?php

include 'headermain.php';
include "footer.php";

?>

<style>
       <body> {
           background-size: cover;
           background-position: center;
           height: 100vh;
       }
       .register-container {
           background: white;
           padding: 2rem;
           border-radius: 10px;
           box-shadow: 0 0 10px rgba(0,0,0,0.1);
           max-width: 500px;
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
           <div class="register-container">
               <!-- Logo -->
               <div class="text-center mb-4">
                   <img src="img/kadalogo.jpg" alt="Logo" class="logo">
               </div>
               
               <!-- Registration Form -->
               <!-- <form method="POST" action="#"> -->
                   
                    <div class="mb-3">
                       <label for="name" class="form-label">Nama Penuh</label>
                       <input type="text" class="form-control" id="name" name="name" 
                              placeholder="Masukkan nama penuh" required>
                   </div>
                    <div class="mb-3">
                       <label for="email" class="form-label">Emel</label>
                       <input type="email" class="form-control" id="email" name="email" 
                              placeholder="Masukkan emel" required>
                   </div>
                    <div class="mb-3">
                       <label for="phone" class="form-label">Nombor Telefon</label>
                       <input type="tel" class="form-control" id="phone" name="phone" 
                              placeholder="Masukkan nombor telefon" required>
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
                   </div>
                    <div class="mb-3">
                       <label for="confirm_password" class="form-label">Sahkan Kata Laluan</label>
                       <div class="input-group">
                           <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                  placeholder="Sahkan kata laluan" required>
                           <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                               <i class="fas fa-eye"></i>
                           </button>
                       </div>
                   </div>
                    <div class="d-grid">
                        <a class="btn btn-primary btn-lg" href="login.php" role="button">Daftar</a>
                   </div>
                    <div class="text-center mt-3">
                       <p>Sudah mempunyai akaun? 
                           <a href="login.php" class="text-decoration-none">Log Masuk</a>
                       </p>
                   </div>
               </form>
           </div>
       </div>
   </div>
</body>
</html>
