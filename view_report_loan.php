<?php
session_start();
require_once 'dbconnect.php';

// Get the loan application ID from URL
$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id) {
    // Modified query to get both loan and member details
    $query = "SELECT 
                m.memberName,
                m.employeeID,
                m.ic,
                m.no_pf,
                l.loanApplicationID,
                l.loanID,
                l.loanType,
                l.amountRequested,
                l.financingPeriod,
                l.monthlyInstallments,
                DATE_FORMAT(l.created_at, '%d/%m/%Y') as tarikh_pembiayaan
              FROM tb_member m
              INNER JOIN tb_loan l ON m.employeeID = l.employeeID
              WHERE l.loanApplicationID = ?";
              
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result) {
        die("Error fetching data: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($result) === 0) {
        die("No records found for this loan application.");
    }

    $data = mysqli_fetch_assoc($result);
    
    // Check if data exists before accessing array values
    if ($data) {
        $nama = $data['memberName'];
        $noAnggota = $data['employeeID'];
        $noKadPengenalan = $data['ic'];
        $noPF = $data['no_pf'];
    } else {
        // Set default values if no data found
        $nama = '-';
        $noAnggota = '-';
        $noKadPengenalan = '-';
        $noPF = '-';
    }
} else {
    // Set default values if no ID provided
    $nama = '-';
    $noAnggota = '-';
    $noKadPengenalan = '-';
    $noPF = '-';
}

// If download parameter is not set, display HTML view
if (!isset($_GET['download'])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Penyata Kewangan</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            .logo-container {
                text-align: center;
                margin-bottom: 10px;
            }
            .logo-placeholder {
                max-width: 150px;
                margin: 0 auto;
            }
            .statement-container {
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                border-radius: 8px;
                background: white;
            }
            .title-section {
                text-align: center;
                color: rgb(0, 48, 135);
                margin-bottom: 30px;
                padding: 10px 0;
                border-bottom: 2px solid rgb(0, 48, 135);
            }
            .section-header {
                background-color: rgb(0, 48, 135);
                color: white;
                padding: 10px;
                margin-bottom: 15px;
            }
            .table {
                border-color: rgb(0, 48, 135);
            }
            .table th {
                background-color: rgb(240, 240, 250);
                width: 30%;
                border-color: rgb(0, 48, 135);
            }
            .table td {
                border-color: rgb(0, 48, 135);
            }
            .timestamp {
                font-style: italic;
                color: #888;
                margin-top: 20px;
            }
            .action-buttons {
                margin-top: 20px;
                text-align: center;
                padding-top: 20px;
                border-top: 2px solid rgb(0, 48, 135);
            }
            .btn-primary {
                background-color: rgb(0, 48, 135);
                border-color: rgb(0, 48, 135);
            }
            .btn-primary:hover {
                background-color: rgb(0, 38, 115);
                border-color: rgb(0, 38, 115);
            }
            .btn-danger {
                background-color: #dc3545;
                border-color: #dc3545;
                color: white;
            }
            .btn-danger:hover {
                background-color: #bb2d3b;
                border-color: #b02a37;
            }
        </style>
    </head>
    <body class="bg-light">
        <div class="statement-container">
            <div class="logo-container">
                <img src="img/kadalogo.jpg" alt="KADA Logo" class="logo-placeholder">
            </div>
            
            <div class="title-section">
                <h3 class="mb-0">Pengesahan Penyata Kewangan Ahli Koperasi</h3>
                <h3 class="mb-0">Kakitangan KADA Kelantan Berhad</h3>
            </div>
            
            <div class="section-header">
                <h5 class="mb-0">Maklumat Peribadi</h5>
            </div>

            <table class="table table-bordered">
                <tr>
                    <th>Nama</th>
                    <td><?php echo htmlspecialchars($nama); ?></td>
                </tr>
                <tr>
                    <th>No. Anggota</th>
                    <td><?php echo htmlspecialchars($noAnggota); ?></td>
                </tr>
                <tr>
                    <th>No. Kad Pengenalan</th>
                    <td><?php echo htmlspecialchars($noKadPengenalan); ?></td>
                </tr>
                <tr>
                    <th>No. PF</th>
                    <td><?php echo htmlspecialchars($noPF); ?></td>
                </tr>
            </table>

            <div class="section-header">
                <h5 class="mb-0">Maklumat Pembiayaan</h5>
            </div>

            <table class="table table-bordered">
                <tr>
                    <th>No. Pembiayaan</th>
                    <td><?php echo htmlspecialchars($data['loanID']); ?></td>
                </tr>
                <tr>
                    <th>Jenis Pembiayaan</th>
                    <td><?php echo htmlspecialchars($data['loanType']); ?></td>
                </tr>
                <tr>
                    <th>Amaun Dipohon</th>
                    <td>RM <?php echo number_format($data['amountRequested'], 2); ?></td>
                </tr>
                <tr>
                    <th>Tempoh Pembiayaan</th>
                    <td><?php echo htmlspecialchars($data['financingPeriod']); ?> bulan</td>
                </tr>
                <tr>
                    <th>Ansuran Bulanan</th>
                    <td>RM <?php echo number_format($data['monthlyInstallments'], 2); ?></td>
                </tr>
                <tr>
                    <th>Tarikh Pembiayaan</th>
                    <td><?php echo htmlspecialchars($data['tarikh_pembiayaan']); ?></td>
                </tr>
            </table>

            <div class="timestamp">
                Laporan dijana pada: <?php echo date('d/m/Y H:i:s'); ?>
            </div>

            <div class="action-buttons">
                <a href="download_report_loan.php?loanApplicationID=<?php echo htmlspecialchars($data['loanApplicationID']); ?>" class="btn btn-primary">
                    <i class="bi bi-download"></i> Muat Turun PDF
                </a>
                <button type="button" class="btn btn-danger" onclick="window.parent.closeModal()">
                    Kembali
                </button>
            </div>
        </div>

        <!-- Add Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    </body>
    </html>
    <?php
}
?>