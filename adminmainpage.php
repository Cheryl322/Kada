<?php
session_start();

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

.circle-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 30px;
    padding: 100px 40px 40px 40px;
    flex-wrap: nowrap;
    width: 100%;
    max-width: 1600px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.circle {
    flex: 0 0 200px;
    width: 200px;
    height: 200px;
    min-width: 200px;
    position: relative;
    z-index: 2;
    background: MediumAquamarine;
    border-radius: 50%;
    overflow: visible;
    cursor: pointer;
    transition: transform 0.3s ease;
    display: flex;
    justify-content: center;
    align-items: center;
    text-decoration: none;
    color: white;
    font-size: 1.2rem;
    font-weight: bold;
    text-align: center;
    padding: 15px;
    line-height: 1.2;
    border: 4px solid white;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}

.circle:hover {
    transform: scale(1.05);
    background: #5dbea3;
}

/* Make circles responsive */
@media screen and (max-width: 1400px) {
    .circle-container {
        gap: 20px;
        padding-top: 80px;
    }
    
    .circle {
        flex: 0 0 180px;
        width: 180px;
        height: 180px;
        min-width: 180px;
    }
}

@media screen and (max-width: 1200px) {
    .circle {
        flex: 0 0 160px;
        width: 160px;
        height: 160px;
        min-width: 160px;
    }
    
    .circle i {
        font-size: 2rem;
    }
    
    .circle span {
        font-size: 1rem;
    }
}

@media screen and (max-width: 992px) {
    .circle-container {
        padding-top: 60px;
    }
    
    .circle {
        flex: 0 0 140px;
        width: 140px;
        height: 140px;
        min-width: 140px;
    }
    
    .circle i {
        font-size: 1.8rem;
    }
    
    .circle span {
        font-size: 0.9rem;
    }
}

@media screen and (max-width: 768px) {
    .circle-container {
        flex-wrap: wrap;
        justify-content: center;
    }
}

/* Add these table styles */
.tables-container {
    display: flex;
    gap: 30px;
    padding: 20px 40px;
    width: calc(100% - 80px);
    margin: 0 auto;
    justify-content: flex-start;
    flex-wrap: nowrap;
    transition: transform 0.3s ease-in-out;
    position: relative;
}

.table-wrapper {
    flex: 0 0 calc(50% - 15px);
    width: calc(50% - 15px);
    max-width: calc(50% - 15px);
    min-width: 0;
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
}

.custom-table th {
    background-color: MediumAquamarine;
    color: white;
}

.custom-table td {
    border-bottom: 1px solid #ddd;
}

/* Ensure consistent column widths across both tables */
.custom-table th:nth-child(1),
.custom-table td:nth-child(1) {
    width: 10%;
}

.custom-table th:nth-child(2),
.custom-table td:nth-child(2) {
    width: 20%;
}

.custom-table th:nth-child(3),
.custom-table td:nth-child(3) {
    width: 45%;
}

.custom-table th:nth-child(4),
.custom-table td:nth-child(4) {
    width: 25%;
}

.see-more-link {
    color: rgb(34, 119, 210);
    text-decoration: none;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.see-more-link:hover {
    color: MediumAquamarine;
}

/* Add responsive styles */
@media screen and (max-width: 1400px) {
    .circle-container {
        min-height: auto;
        padding: 20px 10px;
        margin-bottom: 40px;
    }

    .tables-container {
        flex-direction: row;
        margin-top: 0;
        flex-wrap: nowrap;
        padding-top: 20px;
    }
}

@media screen and (max-width: 1000px) {
    .tables-container {
        flex-direction: column;
        width: calc(100% - 40px);
    }
    
    .table-wrapper {
        max-width: 100%;
    }
    
    .sidebar-open .tables-container {
        width: calc(100% - 290px);
    }
}

.tables-container.sidebar-closed {
    margin-left: 0;
    width: 100%;
}

/* Add these styles */
.circle-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 10px;
    max-width: 100%;
    position: relative;
    z-index: 3;
}

.circle i {
    font-size: 2.2rem;
    margin-bottom: 5px;
}

.circle span {
    display: block;
    line-height: 1.3;
    font-size: 1.1rem;
    word-wrap: break-word;
    max-width: 100%;
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
    transform: translateX(250px);
    padding-left: 40px;
}

.sidebar-open .tables-container {
    transform: translateX(200px);
    width: calc(100% - 250px);
    margin-right: 40px;
}

/* Add these new styles for the dropdown */
.profile-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: none;
    min-width: 120px;
    z-index: 1000;
}

.profile-dropdown.show {
    display: block;
}

.profile-dropdown a {
    display: block;
    padding: 8px 12px;
    color: #333;
    text-decoration: none;
    transition: background-color 0.3s;
    font-size: 14px;
}

.profile-dropdown a:hover {
    background-color: #f5f5f5;
}

.profile-dropdown a i {
    margin-right: 6px;
    color: rgb(34, 119, 210);
    font-size: 12px;
}

/* Add styles for when sidebar is closed */
.sidebar-closed .circle-container {
  padding-left: 40px;
}

.sidebar-closed .tables-container {
  padding-left: 40px;
}

/* Add container width constraints */
@media screen and (min-width: 1200px) {
  .circle-container {
    max-width: 1000px;
    margin-left: auto;
    margin-right: auto;
  }
}

/* Update media queries for responsiveness */
@media screen and (max-width: 1400px) {
  .sidebar-open .circle-container,
  .sidebar-open .tables-container {
    padding-left: 40px;
  }
}

@media screen and (max-width: 1000px) {
  .sidebar-open .circle-container,
  .sidebar-open .tables-container {
    padding-left: 40px;
  }
}

/* Add transition for smooth movement */
.circle-container,
.tables-container {
  transition: transform 0.3s ease-in-out;
}

/* Ensure table content doesn't overflow */
.custom-table {
  width: 100%;
  table-layout: fixed;
}

.custom-table td {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Ensure the container doesn't overflow */
@media screen and (min-width: 1001px) {
  .tables-container {
    max-width: calc(100vw - 80px);
  }
  
  .sidebar-open .tables-container {
    max-width: calc(100vw - 200px);
  }
  }

/* First table (Senarai Ahli) column widths */
.table-wrapper:first-child .custom-table th:nth-child(1),
.table-wrapper:first-child .custom-table td:nth-child(1) {
    width: 10%;
}

.table-wrapper:first-child .custom-table th:nth-child(2),
.table-wrapper:first-child .custom-table td:nth-child(2) {
    width: 15%;
}

.table-wrapper:first-child .custom-table th:nth-child(3),
.table-wrapper:first-child .custom-table td:nth-child(3) {
    width: 50%;
}

.table-wrapper:first-child .custom-table th:nth-child(4),
.table-wrapper:first-child .custom-table td:nth-child(4) {
    width: 25%;
}

/* Second table (Senarai Pinjaman) column widths */
.table-wrapper:last-child .custom-table th:nth-child(1),
.table-wrapper:last-child .custom-table td:nth-child(1) {
    width: 10%;
}

.table-wrapper:last-child .custom-table th:nth-child(2),
.table-wrapper:last-child .custom-table td:nth-child(2) {
    width: 15%;
}

.table-wrapper:last-child .custom-table th:nth-child(3),
.table-wrapper:last-child .custom-table td:nth-child(3) {
    width: 50%;
}

.table-wrapper:last-child .custom-table th:nth-child(4),
.table-wrapper:last-child .custom-table td:nth-child(4) {
    width: 25%;
}
</style>
</head>

<body>
    <?php
    // Include only once
    include "headeradmin.php";
    ?>

    <div class="circle-container">
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
        <a href="admin_upload_payment.php" class="circle">
            <div class="circle-content">
                <i class="fas fa-money-bill-wave mb-2"></i>
                <span>Rekod Pembayaran</span>
            </div>
        </a>
    </div>

    <div class="tables-container">
        <div class="table-wrapper">
            <div class="table-header">
                <h3>Senarai Ahli Semasa</h3>
                <a href="senaraiahli.php" class="see-more-link">
                    Lihat Semua <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Tarikh Daftar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    include 'dbconnect.php';
                    
                    $sql = "SELECT employeeID,memberName, created_at FROM tb_member ORDER BY created_at DESC LIMIT 3";
                    $result = $conn->query($sql);
                    
                    if ($result->num_rows > 0) {
                        $count = 1;
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $count . "</td>";
                            echo "<td>" . $row['employeeID'] . "</td>";
                            echo "<td>" . $row['memberName'] . "</td>";
                            echo "<td>" . date('d/m/Y', strtotime($row['created_at'])) . "</td>";
                            echo "</tr>";
                            $count++;
                        }
                    } else {
                        echo "<tr><td colspan='3'>Tiada rekod ditemui</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>

        <div class="table-wrapper">
            <div class="table-header">
                <h3>Senarai Pinjaman Terkini</h3>
                <a href="senaraipembiayaan.php" class="see-more-link">
                    Lihat Semua <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Tarikh Daftar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    include 'dbconnect.php';
                    
                    $sql = "SELECT l.loanApplicationID, m.memberName, l.created_at 
                           FROM tb_loan l
                           JOIN tb_member m ON l.employeeID = m.employeeID
                           ORDER BY l.loanApplicationID DESC LIMIT 3";
                    $result = $conn->query($sql);
                    
                    if ($result->num_rows > 0) {
                        $count = 1;
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $count . "</td>";
                            echo "<td>" . $row['loanApplicationID'] . "</td>";
                            echo "<td>" . $row['memberName'] . "</td>";
                            echo "<td>" . date('d/m/Y', strtotime($row['created_at'])) . "</td>";
                            echo "</tr>";
                            $count++;
                        }
                    } else {
                        echo "<tr><td colspan='4'>Tiada rekod ditemui</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

    <?php include "footer.php"; ?>
</body>
</html>
