<?php 
session_start();
include 'headermain.php'; 

?>

<style>
body {
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


   <div class="container h-100">
       <div class="row h-100 align-items-center justify-content-center">
           <div class="register-container">
               <!-- Logo -->
               <div class="text-center mb-4">
                   <img src="img/kadalogo.jpg" alt="Logo" class="logo">
               </div>
               
                <!-- Register Form -->  
                <form method="POST" action="registerprocess.php">
                <?php if (isset($_SESSION['message'])): ?>
                    <script>
                        alert("<?php echo htmlspecialchars($_SESSION['message']['text']); ?>");
                    </script>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                    
                    <div class="mb-3">
                       <label for="employeeID" class="form-label">EmployeeID</label>
                       <input type="text" class="form-control" id="employeeID" name="employeeID" 
                              placeholder="Masukkan employee ID" required>
                   </div>
                   
                    <div class="mb-3">
                       <label for="password" class="form-label">Kata Laluan</label>
                       <div class="input-group">
                           <input type="password" class="form-control" id="password" name="password" 
                                  placeholder="Masukkan kata laluan" autocomplete="off" required>
                           <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                               <i class="fas fa-eye"></i>
                           </button>
                       </div>
                   </div>
                    
                    <div class="d-grid">
                        <button type="submit"  class="btn btn-primary btn-lg">Daftar</button>
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

<?php
include "footer.php";
?>