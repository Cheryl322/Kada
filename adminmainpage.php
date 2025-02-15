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
   z-index: 1000;
}

.circle-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 30px;
    padding: 100px 40px 80px 40px;
    flex-wrap: nowrap;
    width: 100%;
    max-width: 1600px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.circle {
    flex: 0 0 250px;
    width: 250px;
    height: 250px;
    min-width: 250px;
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
        flex: 0 0 220px;
        width: 220px;
        height: 220px;
        min-width: 220px;
    }
    
    .circle i {
        font-size: 20.2rem;
    }
    
    .circle span {
        font-size: 1.3rem;
    }
}

@media screen and (max-width: 1200px) {
    .circle {
        flex: 0 0 200px;
        width: 200px;
        height: 200px;
        min-width: 200px;
    }
    
    .circle i {
        font-size: 3rem;
    }
    
    .circle span {
        font-size: 1.3rem;
    }
}

@media screen and (max-width: 992px) {
    .circle-container {
        gap: 15px;
        padding-top: 60px;
    }
    
    .circle {
        flex: 0 0 180px;
        width: 180px;
        height: 180px;
        min-width: 180px;
    }
    
    .circle i {
        font-size: 2.5rem;
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
    margin: 80px auto 0;
    position: relative;
    z-index: 1;
    max-width: 1600px;
}

.table-wrapper {
    flex: 1;
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    margin: 0 20px;
}

.table-header {
    margin-bottom: 25px;
}

.table-header h3 {
    font-size: 1.4rem;
    color: #2277d2;
    margin: 0;
    font-weight: 600;
}

.custom-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 8px;
}

.custom-table th {
    background-color: MediumAquamarine;
    color: white;
    padding: 15px;
    font-size: 1.1rem;
}

.custom-table td {
    padding: 12px 15px;
    background-color: #f8f9fa;
    border: none;
    border-top: 1px solid #eee;
}

.custom-table tr:hover td {
    background-color: #f0f0f0;
}

/* Ensure consistent column widths across both tables */
.custom-table th:nth-child(1),
.custom-table td:nth-child(1) {
    width: 8%;
}

.custom-table th:nth-child(2),
.custom-table td:nth-child(2) {
    width: 15%;
}

.custom-table th:nth-child(3),
.custom-table td:nth-child(3) {
    width: 35%;
}

.custom-table th:nth-child(4),
.custom-table td:nth-child(4) {
    width: 17%;
}

.custom-table th:nth-child(5),
.custom-table td:nth-child(5) {
    width: 25%;
}

.see-more-link {
    color: MediumAquamarine;
    font-weight: 500;
    padding: 8px 15px;
    border-radius: 20px;
    transition: all 0.3s ease;
    background-color: #e8f5f1;
}

.see-more-link:hover {
    background-color: MediumAquamarine;
    color: white;
}

/* Add responsive styles */
@media screen and (max-width: 1400px) {
    .tables-container {
        flex-direction: row;
        margin-top: 20px;
        flex-wrap: nowrap;
        padding: 20px 40px;
        width: calc(100% - 80px);
    }
}

@media screen and (max-width: 1000px) {
    .tables-container {
        flex-direction: column;
        padding: 20px;
        width: calc(100% - 40px);
    }
    
    .table-wrapper {
        margin: 10px 0;
        width: 100%;
    }
}

/* Remove all sidebar-related adjustments and transitions */
.sidebar-open .tables-container,
.sidebar-closed .tables-container,
.tables-container.sidebar-closed,
.tables-container {
    margin-left: auto !important;
    margin-right: auto !important;
    width: calc(100% - 80px) !important;
    transform: none !important;
    transition: none !important;
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
    font-size: 3.5rem;
    margin-bottom: 12px;
}

.circle span {
    display: block;
    line-height: 1.3;
    font-size: 1.3rem;
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
    padding-left: 40px;
}

.sidebar-open .tables-container {
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

/* Add a container for all content */
.dashboard-container {
    padding: 4rem 0 4rem;
    margin: 0 auto;
    margin-top: 20px;
    margin-bottom: 100px;
    min-height: calc(100vh - 160px);
    width: 100%;
}

.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    padding: 1rem;
}

.menu-card {
    background: white;
    border-radius: 15px;
    padding: 2rem 1.5rem;
    text-align: center;
    text-decoration: none;
    color: #2c3e50;
    transition: all 0.3s ease;
    border: 2px solid #e9ecef;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.menu-icon {
    width: 80px;
    height: 80px;
    background: #75B798;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.menu-icon i {
    font-size: 2rem;
    color: white;
}

.menu-card span {
    font-size: 1.1rem;
    font-weight: 500;
    color: #2c3e50;
}

.menu-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    border-color: #75B798;
}

.menu-card:hover .menu-icon {
    transform: scale(1.1);
    background: #5dbea3;
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: 4rem 2rem 2rem;
    }

    .menu-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .menu-card {
        padding: 1.5rem 1rem;
    }

    .menu-icon {
        width: 60px;
        height: 60px;
    }

    .menu-icon i {
        font-size: 1.5rem;
    }

    .menu-card span {
        font-size: 1rem;
    }
}

.circles-grid {
    display: grid;
    grid-template-rows: repeat(3, auto);
    place-items: center;
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
    margin-bottom: 40px;
}

/* First row - 3 circles */
.row-1 {
    display: flex;
    gap: 2rem;
    justify-content: center;
}

/* Second row - 4 circles */
.row-2 {
    display: flex;
    gap: 2rem;
    justify-content: center;
    margin-top: -2rem; /* Overlap slightly for hexagonal effect */
}

/* Third row - 2 circles */
.row-3 {
    display: flex;
    gap: 2rem;
    justify-content: center;
    margin-top: -2rem; /* Overlap slightly for hexagonal effect */
}

/* HTML Structure */
<div class="circles-grid">
    <div class="row-1">
        <a href="senaraiPermohonanAhli.php" class="circle-item">...</a>
        <a href="senaraiPermohonanPinjaman.php" class="circle-item">...</a>
        <a href="hasilreport.php" class="circle-item">...</a>
    </div>
    <div class="row-2">
        <a href="adminviewreport.php" class="circle-item">...</a>
        <a href="admin_upload_payment.php" class="circle-item">...</a>
        <a href="manage_interest.php" class="circle-item">...</a>
        <a href="berhentiapproval.php" class="circle-item">...</a>
    </div>
</div>

@media (max-width: 768px) {
    .top-row, .bottom-row {
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .circle-item {
        width: 150px;
        height: 150px;
    }
}

.circle-item {
    width: 180px;
    height: 180px;
    background:rgb(124, 186, 157);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: white;
    transition: all 0.3s ease;
    border: 3px solid white;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.circle-inner {
    text-align: center;
    padding: 1rem;
}

.circle-inner i {
    font-size: 2.5rem;
    margin-bottom: 0.8rem;
    display: block;
}

.circle-inner span {
    font-size: 1rem;
    font-weight: 500;
    display: block;
    line-height: 1.2;
}

.circle-item:hover {
    transform: translateY(-5px);
    background: #5dbea3;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

@media (max-width: 768px) {
    .circle-item {
        width: 150px;
        height: 150px;
    }

    .circle-inner i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .circle-inner span {
        font-size: 0.9rem;
    }
}

/* Remove any remaining transitions */
.tables-container,
.tables-container *,
.table-wrapper,
.table-wrapper * {
    transition: none !important;
    transform: none !important;
}

/* Ensure navbar has higher z-index */
.navbar {
    z-index: 999 !important; /* Higher z-index for sidebar */
}

.status-container {
    padding: 6px 12px;
    border-radius: 20px;
    text-align: center;
    display: inline-block;
    font-weight: 500;
}

.status-ditolak {
    background-color: #ffebee;
    color: #c62828;
}

.status-diluluskan {
    background-color: #e8f5e9;
    color: #2e7d32;
}

.status-belum-selesai {
    background-color: #fff3e0;
    color: #ef6c00;
}

/* Add margin to prevent content from being hidden behind footer */
.tables-container {
    margin-bottom: 60px; /* Add space for footer */
}
</style>
</head>

<body>
    <?php
    // Include only once
    include "headeradmin.php";
    ?>

    <div class="dashboard-container">
        <div class="circles-grid">
            <div class="row-1">
                <a href="senaraiPermohonanAhli.php" class="circle-item">
                    <div class="circle-inner">
                        <i class="fas fa-user-plus"></i>
                        <span>Pendaftaran Ahli</span>
                    </div>
                </a>
                <a href="senaraiPermohonanPinjaman.php" class="circle-item">
                    <div class="circle-inner">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span>Permohonan Pinjaman</span>
                    </div>
                </a>
                <a href="hasilreport.php" class="circle-item">
                    <div class="circle-inner">
                        <i class="fas fa-file-alt"></i>
                        <span>Hasil Laporan</span>
                    </div>
                </a>
            </div>
            <div class="row-2">
                <a href="adminviewreport.php" class="circle-item">
                    <div class="circle-inner">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Cek Laporan</span>
                    </div>
                </a>
                <a href="admin_upload_payment.php" class="circle-item">
                    <div class="circle-inner">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Rekod Pembayaran</span>
                    </div>
                </a>
                <a href="manage_interest.php" class="circle-item">
                    <div class="circle-inner">
                        <i class="fas fa-percentage"></i>
                        <span>Kadar Faedah</span>
                    </div>
                </a>
                <a href="berhentiapproval.php" class="circle-item">
                    <div class="circle-inner">
                        <i class="fas fa-user-slash"></i>
                        <span>Permohonan Berhenti</span>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="tables-container">
            <div class="table-wrapper">
                <div class="table-header">
                    <h3>Senarai Ahli Semasa</h3>
                    <a href="senaraiPermohonanAhli.php" class="see-more-link">
                        Lihat Semua <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Status</th>
                            <th>Tarikh Daftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include 'dbconnect.php';
                        
                        $sql = "SELECT 
                                    m.employeeID,
                                    m.memberName,
                                    m.created_at,
                                    COALESCE(
                                        REPLACE(
                                            (SELECT regisStatus 
                                             FROM tb_memberregistration_memberapplicationdetails 
                                             WHERE memberRegistrationID = m.employeeID 
                                             ORDER BY regisDate DESC 
                                             LIMIT 1),
                                            'Pending',
                                            'Belum Selesai'
                                        ), 
                                        'Belum Selesai'
                                    ) as regisStatus
                                FROM tb_member m
                                ORDER BY m.created_at DESC 
                                LIMIT 5";
                        
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0) {
                            $count = 1;
                            while($row = $result->fetch_assoc()) {
                                $statusClass = '';
                                if ($row['regisStatus'] == 'Belum Selesai') {
                                    $statusClass = 'status-belum-selesai';
                                } elseif ($row['regisStatus'] == 'Diluluskan') {
                                    $statusClass = 'status-diluluskan';
                                } elseif ($row['regisStatus'] == 'Ditolak') {
                                    $statusClass = 'status-ditolak';
                                }
                                
                                echo "<tr>";
                                echo "<td>" . $count . "</td>";
                                echo "<td>" . $row['employeeID'] . "</td>";
                                echo "<td>" . $row['memberName'] . "</td>";
                                echo "<td><div class='status-container " . $statusClass . "'>" . $row['regisStatus'] . "</div></td>";
                                echo "<td>" . date('d/m/Y', strtotime($row['created_at'])) . "</td>";
                                echo "</tr>";
                                $count++;
                            }
                        } else {
                            echo "<tr><td colspan='5'>Tiada rekod ditemui</td></tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="table-wrapper">
                <div class="table-header">
                    <h3>Senarai Pinjaman Terkini</h3>
                    <a href="senaraiPermohonanPinjaman.php" class="see-more-link">
                        Lihat Semua <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Status</th>
                            <th>Tarikh Daftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include 'dbconnect.php';
                        
                        $sql = "SELECT 
                                    l.loanApplicationID, 
                                    m.memberName, 
                                    l.loanApplicationDate,
                                    COALESCE(
                                        REPLACE(l.loanStatus, 'Pending', 'Belum Selesai'),
                                        'Belum Selesai'
                                    ) as loanStatus
                                FROM tb_loanapplication l
                                JOIN tb_member m ON l.employeeID = m.employeeID
                                ORDER BY l.loanApplicationID DESC 
                                LIMIT 5";
                        
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0) {
                            $count = 1;
                            while($row = $result->fetch_assoc()) {
                                $statusClass = '';
                                if ($row['loanStatus'] == 'Belum Selesai') {
                                    $statusClass = 'status-belum-selesai';
                                } elseif ($row['loanStatus'] == 'Diluluskan') {
                                    $statusClass = 'status-diluluskan';
                                } elseif ($row['loanStatus'] == 'Ditolak') {
                                    $statusClass = 'status-ditolak';
                                }
                                
                                echo "<tr>";
                                echo "<td>" . $count . "</td>";
                                echo "<td>" . $row['loanApplicationID'] . "</td>";
                                echo "<td>" . $row['memberName'] . "</td>";
                                echo "<td><div class='status-container " . $statusClass . "'>" . $row['loanStatus'] . "</div></td>";
                                echo "<td>" . date('d/m/Y', strtotime($row['loanApplicationDate'])) . "</td>";
                                echo "</tr>";
                                $count++;
                            }
                        } else {
                            echo "<tr><td colspan='5'>Tiada rekod ditemui</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <?php include "footer.php"; ?>
</body>
</html>
