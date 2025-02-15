<!DOCTYPE html>
<html lang="en">
<head>
  <title>Sistem Koperasi KADA</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="css/bootstrap.min (1).css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

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

/* Dropdown styling */
.dropdown-menu {
    background-color: #ffffff;
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-radius: 8px;
    padding: 8px 0;
}

.dropdown-item {
    color: #5CBA9B;
    padding: 8px 20px;
    transition: all 0.3s ease;
}

.dropdown-item:hover {
    background-color: #e8f5f1;
    color: #3d8b6f;
}


.dropdown-submenu {
    position: relative;
}

.dropdown-submenu .dropdown-menu {
    top: 0;
    left: 100%;
    margin-top: -8px;
    display: none;
}

.dropdown-submenu:hover > .dropdown-menu {
    display: block;
}

.dropdown-submenu .fa-chevron-right {
    float: right;
    margin-top: 4px;
    font-size: 12px;
}


.dropdown-menu {
    animation: fadeIn 0.2s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-item.active, 
.dropdown-item:active {
    background-color: #5CBA9B;
    color: white;
}
</style>

</head>
<body>

<nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="mainpage.php"><img src="img/kadalogo.jpg" alt="logo" height="40"></a>
    <a class="navbar-brand" href="index.php">KADA Pengguna</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor03" aria-controls="navbarColor03" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarColor03">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link active" href="mianpage.php">Laman Utama
            <span class="visually-hidden">(current)</span>
          </a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Perkhidmatan</a>
          <div class="dropdown-menu">
            <div class="dropdown-submenu">
              <a class="dropdown-item" href="daftar_ahli.php">Permohonan Anggota <i class="fas fa-chevron-right"></i></a>
              <ul class="dropdown-menu submenu">
                <li><a class="dropdown-item" href="daftar_ahli.php">Borang Permohonan</a></li>
                <li><a class="dropdown-item" href="statusanggota.php">Status Permohonan</a></li>
              </ul>
            </div>
          </div>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="infokada.php">Info Kada</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Media</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="hubungikami.php">Hubungi Kami</a>
        </li>
        
      </ul>

      <ul class="navbar-nav ms-auto mt-2">
        <li class="nav-item">
            
          <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
            <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"/>
          </svg>
        </li>
        <li class="nav-item">
        <a class="nav-link" href="profil2.php">Profil</a>
        </li>
    
      </ul>
    </div>
  </div>
</nav>

