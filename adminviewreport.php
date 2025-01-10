<!DOCTYPE html>
<html lang="en">

<head>
  <title>Sistem Koperasi KADA</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="css/bootstrap.min (1).css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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

.navbar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    width: 250px;
    background-color: rgb(34, 119, 210);
    padding-top: 20px;
    margin-top: 60px;
    transform: translateX(-250px);
    transition: transform 0.3s ease-in-out;
}

.navbar.closed {
    transform: translateX(-250px);
}

.navbar:not(.closed) {
    transform: translateX(0);
}

.navbar.initial-state {
    transition: none !important;
}

.container-fluid {
    display: flex;
    flex-direction: column;
    align-items: center;
}
.navbar-nav {
    flex-direction: column;
    width: 100%;
}
.nav-item {
    width: 100%;
}
.nav-link {
    color: white;
    padding: 10px 15px;
}

#sidebarToggle {
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1000;
    display: none;
}

.navbar.closed + #sidebarToggle {
    display: block;
}

#closeSidebar {
    transition: transform 0.3s ease;
}

#closeSidebar:hover {
    transform: scale(1.2);
}

.main-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 60px;
    background-color: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    z-index: 999;
}

.menu-button {
    background: none;
    border: none;
    font-size: 24px;
    color: rgb(34, 119, 210);
    cursor: pointer;
    padding: 10px;
    transition: transform 0.3s ease;
}

.menu-button:hover {
    transform: scale(1.1);
}

.top-right-icons {
    display: flex;
    gap: 20px;
    align-items: center;
}

.icon-button {
    color: rgb(34, 119, 210);
    font-size: 24px;
    cursor: pointer;
    transition: transform 0.3s ease;
    position: relative;
}

.icon-button:hover {
    transform: scale(1.1);
}

.profile-pic {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgb(34, 119, 210);
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: red;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
}

/* Add these new styles */
.header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.home-button {
    color: rgb(34, 119, 210);
    font-size: 24px;
    text-decoration: none;
    transition: transform 0.3s ease;
}

.home-button:hover {
    transform: scale(1.1);
}

/* Add this new style */
.navbar.initial-state {
    transform: translateX(-250px);
}

/* Add these new styles for when sidebar is open */
.sidebar-open .circle-container {
    width: calc(100% - 250px);
    margin-left: 250px;
}

.sidebar-open .tables-container {
    margin-left: 250px;
    max-width: calc(100% - 270px);
}

/* Add/modify these styles */
.main-content {
    position: relative;
    transition: transform 0.3s ease-in-out;
    margin: 80px 20px 20px 20px; /* Top margin to clear header */
    width: calc(100% - 40px);
}

body.sidebar-open .main-content {
    transform: translateX(250px);
    width: calc(100% - 40px); /* Keep original width */
}

/* For the table container */
.table-responsive {
    position: relative;
    transition: transform 0.3s ease-in-out;
    margin: 20px;
    width: calc(100% - 40px);
}

body.sidebar-open .table-responsive {
    transform: translateX(250px);
    width: calc(100% - 40px);
}

/* For the search bar container */
.search-container {
    position: relative;
    transition: transform 0.3s ease-in-out;
    margin: 20px;
    width: calc(100% - 40px);
}

body.sidebar-open .search-container {
    transform: translateX(250px);
    width: calc(100% - 40px);
}

/* For the pagination */
.pagination-container {
    position: relative;
    transition: transform 0.3s ease-in-out;
    margin: 20px;
    width: calc(100% - 40px);
}

body.sidebar-open .pagination-container {
    transform: translateX(250px);
    width: calc(100% - 40px);
}
</style>
</head>

<body>

<div class="main-header">
    <div class="header-left">
        <button class="menu-button" id="menuButton">
            <i class="fas fa-bars"></i>
        </button>
        <a href="adminmainpage.php" class="home-button">
            <i class="fas fa-home"></i>
        </a>
    </div>
    <div class="top-right-icons">
        <div class="icon-button">
            <i class="fas fa-envelope"></i>
        </div>
        <div class="icon-button">
            <i class="fas fa-bell"></i>
            <span class="notification-badge">3</span>
        </div>
        <div class="icon-button">
            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Profile" class="profile-pic">
        </div>
    </div>
</div>

<div class="navbar initial-state closed" id="sidebar">
  <div class="container-fluid">
    <div style="display: flex; width: 100%; align-items: center; margin-bottom: 20px;">
      <i class="fas fa-arrow-left" id="closeSidebar" style="cursor:pointer; font-size: 24px; color: white; position: absolute; left: 20px; top: 20px;"></i>
      <a class="navbar-brand" href="index.php" style="margin: 0 auto;">
        <img src="img/kadalogo.jpg" alt="logo" height="60">
      </a>
    </div>
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link active" href="adminmainpage.php">Laman Utama
          <span class="visually-hidden">(current)</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Ahli Semasa</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Pendaftaran Ahli</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Permohonan Pinjaman</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="hasilreport.php">Hasil Laporan</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="adminviewreport.php">Cek Laporan</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Info KADA</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Media</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Hubungi Kami</a>
      </li>
    </ul>
  </div>
</div>

<div class="main-content">
    <h2 style="color: rgb(34, 119, 210);">Cek Laporan</h2>
    <hr style="border: 1px solid #ddd; margin-top: 10px; margin-bottom: 20px;">
</div>

<div class="search-container" style="display: flex; justify-content: flex-end; margin: 20px 20px 10px;">
    <div class="input-group" style="width: 300px;">
        <input type="text" class="form-control" id="searchInput" placeholder="Cari...">
        <button class="btn btn-outline-primary" type="button">
            <i class="fas fa-search"></i>
        </button>
    </div>
</div>

<div class="table-responsive" style="margin: 20px;">
    <table class="table table-bordered table-hover" id="dataTable">
        <thead class="table-light">
            <tr>
                <th>No.</th>
                <th>Nama</th>
                <th>Status</th>
                <th>Tarikh Daftar</th>
                <th>Penyata Ahli</th>
                <th>Tarikh Pembiayaan</th>
                <th>Penyata Kewangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Ahmad bin Abdullah</td>
                <td>Aktif</td>
                <td>01/03/2024</td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-primary btn-sm" onclick="viewMemberStatement(1)">
                            <i class="fas fa-file-alt"></i> Lihat Penyata
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="downloadMemberStatement(1)">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </td>
                <td>15/03/2024</td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-success btn-sm" onclick="viewFinancialStatement(1)">
                            <i class="fas fa-file-invoice-dollar"></i> Lihat Penyata
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="downloadFinancialStatement(1)">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td>Siti Aminah binti Hassan</td>
                <td>Aktif</td>
                <td>03/03/2024</td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-primary btn-sm" onclick="viewMemberStatement(2)">
                            <i class="fas fa-file-alt"></i> Lihat Penyata
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="downloadMemberStatement(2)">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </td>
                <td>18/03/2024</td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-success btn-sm" onclick="viewFinancialStatement(2)">
                            <i class="fas fa-file-invoice-dollar"></i> Lihat Penyata
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="downloadFinancialStatement(2)">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <tr>
                <td>3</td>
                <td>Mohd Razak bin Ibrahim</td>
                <td>Tidak Aktif</td>
                <td>05/03/2024</td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-primary btn-sm" onclick="viewMemberStatement(3)">
                            <i class="fas fa-file-alt"></i> Lihat Penyata
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="downloadMemberStatement(3)">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </td>
                <td>20/03/2024</td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-success btn-sm" onclick="viewFinancialStatement(3)">
                            <i class="fas fa-file-invoice-dollar"></i> Lihat Penyata
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="downloadFinancialStatement(3)">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <tr>
                <td>4</td>
                <td>Nurul Izzah binti Kamal</td>
                <td>Aktif</td>
                <td>07/03/2024</td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-primary btn-sm" onclick="viewMemberStatement(4)">
                            <i class="fas fa-file-alt"></i> Lihat Penyata
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="downloadMemberStatement(4)">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </td>
                <td>22/03/2024</td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-success btn-sm" onclick="viewFinancialStatement(4)">
                            <i class="fas fa-file-invoice-dollar"></i> Lihat Penyata
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="downloadFinancialStatement(4)">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <tr>
                <td>5</td>
                <td>Tan Wei Ming</td>
                <td>Aktif</td>
                <td>10/03/2024</td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-primary btn-sm" onclick="viewMemberStatement(5)">
                            <i class="fas fa-file-alt"></i> Lihat Penyata
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="downloadMemberStatement(5)">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </td>
                <td>25/03/2024</td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-success btn-sm" onclick="viewFinancialStatement(5)">
                            <i class="fas fa-file-invoice-dollar"></i> Lihat Penyata
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="downloadFinancialStatement(5)">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="pagination-container" style="margin: 20px; display: flex; justify-content: flex-end;">
    <nav aria-label="Page navigation">
        <ul class="pagination">
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1">Previous</a>
            </li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item">
                <a class="page-link" href="#">Next</a>
            </li>
        </ul>
    </nav>
</div>

<div class="modal fade" id="statementModal" tabindex="-1" aria-labelledby="statementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statementModalLabel">Penyata</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="statementImage" src="" alt="Penyata" style="width: 100%; height: auto;">
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const menuButton = document.getElementById('menuButton');
    const closeSidebar = document.getElementById('closeSidebar');
    
    // Remove initial-state class after a brief delay
    setTimeout(() => {
        sidebar.classList.remove('initial-state');
    }, 100);

    menuButton.addEventListener('click', function() {
        sidebar.classList.remove('closed');
        document.body.classList.add('sidebar-open');
    });

    closeSidebar.addEventListener('click', function() {
        sidebar.classList.add('closed');
        document.body.classList.remove('sidebar-open');
    });
});

function viewMemberStatement(id) {
    // Set the image source based on the member ID
    const imagePath = `statements/member_${id}.jpg`; // Adjust path as needed
    document.getElementById('statementImage').src = imagePath;
    document.getElementById('statementModalLabel').textContent = 'Penyata Ahli';
    
    // Show the modal
    new bootstrap.Modal(document.getElementById('statementModal')).show();
}

function downloadMemberStatement(id) {
    // Add your logic to download member statement
    alert('Downloading member statement for ID: ' + id);
}

function viewFinancialStatement(id) {
    // Set the image source based on the member ID
    const imagePath = `statements/financial_${id}.jpg`; // Adjust path as needed
    document.getElementById('statementImage').src = imagePath;
    document.getElementById('statementModalLabel').textContent = 'Penyata Kewangan';
    
    // Show the modal
    new bootstrap.Modal(document.getElementById('statementModal')).show();
}

function downloadFinancialStatement(id) {
    // Add your logic to download financial statement
    alert('Downloading financial statement for ID: ' + id);
}

document.getElementById('searchInput').addEventListener('keyup', function() {
    let searchText = this.value.toLowerCase();
    let table = document.getElementById('dataTable');
    let rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        let row = rows[i];
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchText) ? '' : 'none';
    }
});
</script>

<?php include 'footer.php';?>

