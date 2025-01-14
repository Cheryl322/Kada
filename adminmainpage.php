<?php
session_start();

include "headeradmin.php";

// Debug lines
error_log("Admin page access - Session data: " . print_r($_SESSION, true));

// Check if user is logged in and is an admin
if (!isset($_SESSION['employeeID']) || $_SESSION['role'] !== 'admin') {
    error_log("Access denied - employeeID: " . (isset($_SESSION['employeeID']) ? $_SESSION['employeeID'] : 'not set'));
    error_log("Access denied - role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'not set'));
    header('Location: login.php');
    exit();
}
?>


<div class="circle-container">
  <a href="page1.php" class="circle">
    <div class="circle-content">
      <i class="fas fa-users mb-2"></i>
      <span>Ahli Semasa</span>
    </div>
  </a>
  <a href="senaraiPermohonanAhli.php" class="circle">
    <div class="circle-content">
      <i class="fas fa-user-plus mb-2"></i>
      <span>Pendaftaran Ahli</span>
    </div>
  </a>
  <a href="senaraiPermohonanPinjaman.php" class="circle">
    <div class="circle-content">
      <i class="fas fa-hand-holding-usd mb-2"></i>
      <span>Permohonan Pinjaman</span>
    </div>
  </a>
  <a href="hasilreport.php" class="circle">
    <div class="circle-content">
      <i class="fas fa-file-alt mb-2"></i>
      <span>Hasil Laporan</span>
    </div>
  </a>
  <a href="adminviewreport.php" class="circle">
    <div class="circle-content">
      <i class="fas fa-clipboard-check mb-2"></i>
      <span>Cek Laporan</span>
    </div>
  </a>
</div>

<div class="tables-container">
    <div class="table-wrapper">
        <div class="table-header">
            <h3>Senarai Ahli Semasa</h3>
            <a href="senarai_ahli.php" class="see-more-link">
                Lihat Semua <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <table class="custom-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama</th>
                    <th>Status</th>
                    <th>Tarikh Daftar</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>A001</td>
                    <td>Ahmad bin Abdullah</td>
                    <td>Lulus</td>
                    <td>01/03/2024</td>
                </tr>
                <tr>
                    <td>A002</td>
                    <td>Siti Aminah</td>
                    <td>Lulus</td>
                    <td>02/03/2024</td>
                </tr>
                <tr>
                    <td>A003</td>
                    <td>Raj Kumar</td>
                    <td>Lulus</td>
                    <td>03/03/2024</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="table-wrapper">
        <div class="table-header">
            <h3>Senarai Pinjaman Terkini</h3>
            <a href="senarai_pinjaman.php" class="see-more-link">
                Lihat Semua <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <table class="custom-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama</th>
                    <th>Status</th>
                    <th>Tarikh Pinjaman</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>P001</td>
                    <td>Muhammad Ali</td>
                    <td>Dalam Proses</td>
                    <td>01/03/2024</td>
                </tr>
                <tr>
                    <td>P002</td>
                    <td>Lee Wei Ming</td>
                    <td>Lulus</td>
                    <td>02/03/2024</td>
                </tr>
                <tr>
                    <td>P003</td>
                    <td>Sarah Abdullah</td>
                    <td>Dalam Proses</td>
                    <td>03/03/2024</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const menuButton = document.getElementById('menuButton');
    const closeSidebar = document.getElementById('closeSidebar');
    const mainContent = document.body;
    const circleContainer = document.querySelector('.circle-container');

    // Remove initial-state class after a brief delay
    setTimeout(() => {
        sidebar.classList.remove('initial-state');
    }, 100);

    function toggleSidebar() {
        sidebar.classList.toggle('closed');
        mainContent.classList.toggle('sidebar-open');
        
        // Add transition class to ensure smooth animation
        circleContainer.style.transition = 'all 0.3s ease-in-out';
    }

    menuButton.addEventListener('click', toggleSidebar);
    closeSidebar.addEventListener('click', toggleSidebar);
});
</script>

<?php include 'footer.php';?>