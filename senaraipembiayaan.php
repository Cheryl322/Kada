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

.custom-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.custom-table th, 
.custom-table td {
    padding: 12px;
    text-align: left;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    border: 1px solid #ddd;
}

.custom-table th {
    background-color: MediumAquamarine;
    color: white;
    font-weight: 500;
}

.custom-table tr:nth-child(even) {
    background-color: #f8f9fa;
}

.custom-table tr:hover {
    background-color: #f5f5f5;
}

.table-wrapper {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.table-header h3 {
    color: rgb(34, 119, 210);
    margin: 0;
}

.search-container input {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 8px 12px;
}

.search-container input:focus {
    outline: none;
    border-color: MediumAquamarine;
    box-shadow: 0 0 5px rgba(102, 205, 170, 0.3);
}

/* Column widths for loan table */
.custom-table th:nth-child(1),
.custom-table td:nth-child(1) {
    width: 5%;  /* No. */
}

.custom-table th:nth-child(2),
.custom-table td:nth-child(2) {
    width: 10%; /* Jenis Pinjaman */
}

.custom-table th:nth-child(3),
.custom-table td:nth-child(3) {
    width: 8%;  /* ID Pinjaman */
}

.custom-table th:nth-child(4),
.custom-table td:nth-child(4) {
    width: 8%;  /* ID Permohonan */
}

.custom-table th:nth-child(5),
.custom-table td:nth-child(5) {
    width: 8%;  /* ID Pekerja */
}

.custom-table th:nth-child(6),
.custom-table td:nth-child(6) {
    width: 10%; /* Jumlah Dipohon */
}

.custom-table th:nth-child(7),
.custom-table td:nth-child(7) {
    width: 8%;  /* Tempoh Pembiayaan */
}

.custom-table th:nth-child(8),
.custom-table td:nth-child(8) {
    width: 8%;  /* Ansuran Bulanan */
}

.custom-table th:nth-child(9),
.custom-table td:nth-child(9) {
    width: 12%; /* Nama Majikan */
}

.custom-table th:nth-child(10),
.custom-table td:nth-child(10) {
    width: 8%;  /* IC Majikan */
}

.custom-table th:nth-child(11),
.custom-table td:nth-child(11) {
    width: 8%;  /* Gaji Pokok */
}

.custom-table th:nth-child(12),
.custom-table td:nth-child(12) {
    width: 8%;  /* Gaji Bersih */
}

.custom-table th:nth-child(13),
.custom-table td:nth-child(13) {
    width: 8%;  /* Fail Gaji Pokok */
}

.custom-table th:nth-child(14),
.custom-table td:nth-child(14) {
    width: 8%;  /* Fail Gaji Bersih */
}

/* Add responsive styles */
@media screen and (max-width: 1200px) {
    .table-responsive {
        overflow-x: auto;
    }
    
    .custom-table {
        min-width: 1200px;
    }
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
        <a class="nav-link" href="senaraiahli.php">Ahli Semasa</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="senaraipembiayaan.php">Permohonan Pinjaman</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="hasilreport.php">Hasil Laporan</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="adminviewreport.php">Cek Laporan</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="login.php">Log Keluar</a>
      </li>
    </ul>
  </div>
</div>

<div class="tables-container" style="margin-top: 80px; padding: 20px;">
    <div class="table-wrapper" style="width: 100%; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <div class="table-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="color: rgb(34, 119, 210); margin: 0;">Senarai Permohonan Pinjaman</h3>
            <div class="search-container">
                <input type="text" id="searchInput" class="form-control" style="width: 300px;" placeholder="Cari...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="custom-table" id="dataTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Jenis Pinjaman</th>
                        <th>ID Pinjaman</th>
                        <th>ID Permohonan</th>
                        <th>ID Pekerja</th>
                        <th>Jumlah Dipohon</th>
                        <th>Tempoh Pembiayaan</th>
                        <th>Ansuran Bulanan</th>
                        <th>Nama Majikan</th>
                        <th>IC Majikan</th>
                        <th>Gaji Pokok</th>
                        <th>Gaji Bersih</th>
                        <th>Fail Gaji Pokok</th>
                        <th>Fail Gaji Bersih</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    include 'dbconnect.php';

                    $sql = "SELECT * FROM tb_loan";
                    $result = $conn->query($sql);
                    
                    if ($result->num_rows > 0) {
                        $count = 1;
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $count . "</td>";
                            echo "<td>" . (empty($row['loanType']) ? '-' : $row['loanType']) . "</td>";
                            echo "<td>" . $row['loanID'] . "</td>";
                            echo "<td>" . $row['loanApplicationID'] . "</td>";
                            echo "<td>" . $row['employeeID'] . "</td>";
                            echo "<td>" . $row['amountRequested'] . "</td>";
                            echo "<td>" . $row['financingPeriod'] . "</td>";
                            echo "<td>" . $row['monthlyInstallments'] . "</td>";
                            echo "<td>" . $row['employerName'] . "</td>";
                            echo "<td>" . $row['employerIC'] . "</td>";
                            echo "<td>" . $row['basicSalary'] . "</td>";
                            echo "<td>" . $row['netSalary'] . "</td>";
                            echo "<td>" . $row['basicSalaryFile'] . "</td>";
                            echo "<td>" . $row['netSalaryFile'] . "</td>";
                            echo "</tr>";
                            $count++;
                        }
                    } else {
                        echo "<tr><td colspan='14'>Tiada rekod ditemui</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add this JavaScript code before the closing body tag -->
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
        sidebar.classList.toggle('closed');
        document.body.classList.toggle('sidebar-open');
    });

    closeSidebar.addEventListener('click', function() {
        sidebar.classList.add('closed');
        document.body.classList.remove('sidebar-open');
    });

    // Add search functionality
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('dataTable');

    searchInput.addEventListener('keyup', function() {
        const searchTerm = searchInput.value.toLowerCase();
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) { // Start from 1 to skip header row
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;

            for (let cell of cells) {
                if (cell.textContent.toLowerCase().includes(searchTerm)) {
                    found = true;
                    break;
                }
            }

            row.style.display = found ? '' : 'none';
        }
    });
});
</script>

<?php include 'footer.php';?>

