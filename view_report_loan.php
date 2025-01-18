<?php
session_start();
require_once 'dbconnect.php';

// If download parameter is not set, display HTML view
if (!isset($_GET['download'])) {
    $id = $_GET['id'];
    $query = "SELECT m.*, l.* FROM tb_member m 
              LEFT JOIN tb_loan l ON m.employeeID = l.employeeID 
              WHERE m.employeeID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
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
                    <td><?php echo htmlspecialchars($data['memberName']); ?></td>
                </tr>
                <tr>
                    <th>No. Pekerja</th>
                    <td><?php echo htmlspecialchars($data['employeeID']); ?></td>
                </tr>
                <tr>
                    <th>No. Kad Pengenalan</th>
                    <td><?php echo htmlspecialchars($data['ic']); ?></td>
                </tr>
                <tr>
                    <th>No. PF</th>
                    <td><?php echo htmlspecialchars($data['no_pf']); ?></td>
                </tr>
            </table>

            <h5 class="mt-4 mb-3">Maklumat Pembiayaan</h5>
            <table class="table table-bordered">
                <tr>
                    <th style="width: 30%">Jenis Pembiayaan</th>
                    <td><?php echo htmlspecialchars($data['loanType'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Amaun Dipohon</th>
                    <td>RM <?php echo number_format($data['amountRequested'] ?? 0, 2); ?></td>
                </tr>
                <tr>
                    <th>Tempoh Pembiayaan</th>
                    <td><?php echo htmlspecialchars($data['financingPeriod'] ?? '-'); ?> bulan</td>
                </tr>
                <tr>
                    <th>Ansuran Bulanan</th>
                    <td>RM <?php echo number_format($data['monthlyInstallments'] ?? 0, 2); ?></td>
                </tr>
            </table>
            
            <div class="text-muted mt-3">
                <small>Laporan dijana pada: <?php echo date('d/m/Y H:i:s'); ?></small>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
} else {
    // PDF generation code
    $id = $_GET['id'];
    $query = "SELECT m.*, l.* FROM tb_member m 
              LEFT JOIN tb_loan l ON m.employeeID = l.employeeID 
              WHERE m.employeeID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    // Your existing PDF generation code
    // Title
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Pengesahan Penyata Kewangan Ahli Koperasi', 0, 1, 'C');
    $pdf->Cell(0, 10, 'Kakitangan KADA Kelantan Berhad', 0, 1, 'C');
    $pdf->Ln(10);

    // Member details
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetFillColor(245, 245, 245);
    $pdf->SetDrawColor(200, 200, 200);

    // Personal details section
    $pdf->Cell(60, 10, 'Nama', 1, 0, 'L', true);
    $pdf->Cell(130, 10, $data['memberName'], 1, 1, 'L', true);
    
    $pdf->Cell(60, 10, 'No. Anggota', 1, 0, 'L', true);
    $pdf->Cell(130, 10, $data['employeeID'], 1, 1, 'L', true);
    
    $pdf->Cell(60, 10, 'No. Kad Pengenalan', 1, 0, 'L', true);
    $pdf->Cell(130, 10, $data['ic'], 1, 1, 'L', true);
    
    $pdf->Cell(60, 10, 'No. PF', 1, 0, 'L', true);
    $pdf->Cell(130, 10, $data['no_pf'], 1, 1, 'L', true);

    $pdf->Ln(10);

    // Loan details section
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Maklumat Pembiayaan', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 12);

    $pdf->Cell(60, 10, 'Jenis Pembiayaan', 1, 0, 'L', true);
    $pdf->Cell(130, 10, $data['loanType'] ?? '-', 1, 1, 'L', true);
    
    $pdf->Cell(60, 10, 'Amaun Dipohon', 1, 0, 'L', true);
    $pdf->Cell(130, 10, 'RM ' . number_format($data['amountRequested'] ?? 0, 2), 1, 1, 'L', true);
    
    $pdf->Cell(60, 10, 'Tempoh Pembiayaan', 1, 0, 'L', true);
    $pdf->Cell(130, 10, ($data['financingPeriod'] ?? '-') . ' bulan', 1, 1, 'L', true);
    
    $pdf->Cell(60, 10, 'Ansuran Bulanan', 1, 0, 'L', true);
    $pdf->Cell(130, 10, 'RM ' . number_format($data['monthlyInstallments'] ?? 0, 2), 1, 1, 'L', true);

    // Add timestamp at the bottom
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 10, 'Laporan dijana pada: ' . date('d/m/Y H:i:s'), 0, 1, 'L');

    // Output the PDF
    $pdf->Output('financial_statement_' . $id . '.pdf', 'D');
    exit();
}

// Rest of your view logic for normal viewing... 