<!DOCTYPE html>
<html lang="en">
<head>
  <title>Sistem Koperasi KADA</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="css/bootstrap.min (1).css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>




<style>
.footer {
   position: fixed;
   left: 0;
   bottom: 0;
   width: 100%;
   background-color: MediumAquamarine;
   color: white;
   text-align: center;
}




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




.dropdown-menu {
    padding: 0.5rem 0;
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 0.5rem;
    background-color: white;
}


.dropdown-header {
    background-color: #f8f9fa;
    padding: 0.5rem 1rem;
    font-weight: 600;
    color: #000;
}


.dropdown-item {
    padding: 0.7rem 1.5rem;
    transition: all 0.2s ease;
    color: #000;
}


.dropdown-item:hover {
    padding-left: 2rem;
    background-color: #f8f9fa;
    color: #000;
}


.dropdown-item i {
    margin-right: 0.5rem;
    width: 20px;
    text-align: center;
    color: #000;
}


.dropdown-divider {
    margin: 0.5rem 0;
}




</style>




</head>
<body>




<nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="mainpage.php"><img src="img/kadalogo.jpg" alt="logo" height="40"></a>
    <a class="navbar-brand" href="mainpage.php">KADA Ahli</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor03" aria-controls="navbarColor03" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarColor03">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link active" href="mainpage.php">Laman Utama
            <span class="visually-hidden">(current)</span>
          </a>
        </li>




        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-services"></i> Perkhidmatan
          </a>
          <div class="dropdown-menu">
            <div class="dropdown-header">Permohonan Anggota</div>
            <a class="dropdown-item" href="daftar_ahli.php">
              <i class="fas fa-user-plus"></i> Borang Permohonan
            </a>
            <a class="dropdown-item" href="statusanggota.php">
              <i class="fas fa-search"></i> Status Permohonan
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="permohonanloan.php">
              <i class="fas fa-money-bill-wave"></i> Permohonan Pembiayaan
            </a>
            <a class="dropdown-item" href="permohonanberhenti.php">
              <i class="fas fa-user-minus"></i> Permohonan Berhenti
            </a>
          </div>
        </li>




       
   
        </li>
        <li class="nav-item">
          <a class="nav-link" href="hubungikamimember.php">Hubungi Kami</a>
        </li>
       
        </ul>
       
        <!-- Add this new ul for right-aligned items -->
        <ul class="navbar-nav ms-auto mt-2">
          <li class="nav-item" id="simpleHeader">
            <div style="position: relative;">
              <a class="nav-link" href="#" onclick="toggleMenu()" style="cursor: pointer;">Profil</a>
              <div id="logoutMenu" style="display: none; position: absolute; top: 100%; right: 0; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 5px; min-width: 150px; z-index: 1000;">
                <a href="profil.php" style="display: flex; align-items: center; padding: 10px 15px; color: #000; text-decoration: none;">
                  <i class="fas fa-user" style="margin-right: 8px;"></i> Profil
                </a>
                <a href="logout.php" style="display: flex; align-items: center; padding: 10px 15px; color: #dc3545; text-decoration: none;">
                  <i class="fas fa-sign-out-alt" style="margin-right: 8px;"></i> Log Keluar
                </a>
              </div>
            </div>
          </li>
        </ul>
      </ul>
      <br><br>
  </div>
</nav>




<script>
function toggleMenu() {
    const menu = document.getElementById('logoutMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}




// Close menu when clicking outside
document.addEventListener('click', function(e) {
    const menu = document.getElementById('logoutMenu');
    const header = document.getElementById('simpleHeader');
    if (!header.contains(e.target)) {
        menu.style.display = 'none';
    }
});
</script>
















