<!DOCTYPE html>
<html lang="en">
<head>
  <title>Sistem Koperasi KADA</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="css/bootstrap.min (1).css" rel="stylesheet">
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
</style>

</head>
<body>

<nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php"><img src="img/kadalogo.jpg" alt="logo" height="40"></a>
    <a class="navbar-brand" href="index.php">KADA Ahli</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor03" aria-controls="navbarColor03" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarColor03">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link active" href="index.php">Laman Utama
            <span class="visually-hidden">(current)</span>
          </a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Perkhidmatan</a>
          <div class="dropdown-menu">
            <a class="dropdown dropend" href="#">
              Permohonan Anggota
              <ul class="dropdown dropend">
                <li><a class="dropdown-item" href="daftar_ahli.php">Borang Permohonan</a>
                </li>
                <li><a class="dropdown-item" href="statusanggota.php">Status Permohonan</a>
                </li>
              </ul>
            </a>
            <a class="dropdown-item" href="pengesahananggota.php">Permohonan Pembiayaan</a>
          </div>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="#">Info Kada</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Media</a>
        </li>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Hubungi Kami</a>
        </li>
        
        </ul>
        
        <!-- Add this new ul for right-aligned items -->
        <ul class="navbar-nav ms-auto mt-2">
          <li class="nav-item">
            
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
              <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"/>
            </svg>
          </li>
          <li class="nav-item">
          <a class="nav-link" href="profil.php">Profil</a>
          </li>
        </ul>
      </ul>
  </div>
</nav>

