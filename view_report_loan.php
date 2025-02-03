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
    </head>
    <body class="p-4">
        <h3 class="text-center mb-5">Pengesahan Penyata Kewangan Ahli Koperasi Kakitangan KADA Kelantan Berhad</h3>
        
        <div class="container">
            <h5 class="mb-3">Maklumat Peribadi</h5>
            <table class="table table-bordered">
                <tr>
                    <th style="width: 30%">Nama</th>
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

            <?php if ($data) { ?>
                <h5 class="mt-4 mb-3">Maklumat Pembiayaan</h5>
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 30%">No. Pembiayaan</th>
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
            <?php } ?>
            
            <div class="text-muted mt-3">
                <small>Laporan dijana pada: <?php echo date('d/m/Y H:i:s'); ?></small>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>