<!DOCTYPE html>
<html lang="en">
<head>
  <title>Sistem KADA</title>
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

/* Submenu styling */
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

/* Animation */
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

/* Active state */
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
              <a class="dropdown-item" href="#">Permohonan Anggota <i class="fas fa-chevron-right"></i></a>
              <ul class="dropdown-menu submenu">
                <li><a class="dropdown-item" href="#">Borang Permohonan</a></li>
                <li><a class="dropdown-item" href="#">Status Permohonan</a></li>
              </ul>
            </div>
          </div>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="mainpage.php">Info Kada</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Media</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="hubungikami.php">Hubungi Kami</a>
        </li>
        <li class="nav navbar-nav navbar-right">
          <li>
          <a class="nav-link" href="#">Pemberitahuan</a>
          </li>
          <li>
          <a class="nav-link" href="profil2.php">Profil</a>
          </li>
        </li>
      </ul>
    </div>
  </div>
</nav>

