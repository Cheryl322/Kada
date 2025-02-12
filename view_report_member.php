<?php
session_start();
require_once 'dbconnect.php';

// If download parameter is not set, display HTML view
if (!isset($_GET['download'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM tb_member WHERE employeeID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $member = mysqli_fetch_assoc($result);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Penyata Ahli</title>
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
                <h3 class="mb-0">Pengesahan Penyata Ahli Koperasi</h3>
                <h3 class="mb-0">Kakitangan KADA Kelantan Berhad</h3>
            </div>
            
            <div class="section-header">
                <h5 class="mb-0">Maklumat Peribadi</h5>
            </div>

            <table class="table table-bordered">
                <tr>
                    <th>Nama</th>
                    <td><?php echo htmlspecialchars($member['memberName']); ?></td>
                </tr>
                <tr>
                    <th>No. Anggota</th>
                    <td><?php echo htmlspecialchars($member['employeeID']); ?></td>
                </tr>
                <tr>
                    <th>No. Kad Pengenalan</th>
                    <td><?php echo htmlspecialchars($member['ic']); ?></td>
                </tr>
                <tr>
                    <th>No. PF</th>
                    <td><?php echo htmlspecialchars($member['no_pf']); ?></td>
                </tr>
            </table>

            <div class="timestamp">
                Laporan dijana pada: <?php echo date('d/m/Y H:i:s'); ?>
            </div>

            <div class="action-buttons">
                <a href="download_report_ahli.php?employeeID=<?php echo htmlspecialchars($id); ?>" class="btn btn-primary">
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
    exit();
}

if (isset($_GET['download']) && $_GET['download'] === 'true') {
    // Include TCPDF library
    require_once('tcpdf/tcpdf.php');

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('KADA Kelantan');
    $pdf->SetAuthor('KADA Kelantan');
    $pdf->SetTitle('Pengesahan Penyata Ahli');

    // Remove header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 12);

    // Get member data from database
    $id = $_GET['id'];
    $query = "SELECT * FROM tb_member WHERE employeeID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $member = mysqli_fetch_assoc($result);

    // Title
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Pengesahan Penyata Ahli Koperasi Kakitangan', 0, 1, 'C');
    $pdf->Cell(0, 10, 'KADA Kelantan Berhad', 0, 1, 'C');
    $pdf->Ln(10);

    // Member details
    $pdf->SetFont('helvetica', '', 12);
    
    // Create a styled box for member details
    $pdf->SetFillColor(245, 245, 245);
    $pdf->SetDrawColor(200, 200, 200);
    
    // Member details table
    $pdf->Cell(60, 10, 'Nama', 1, 0, 'L', true);
    $pdf->Cell(130, 10, $member['memberName'], 1, 1, 'L', true);
    
    $pdf->Cell(60, 10, 'No. Anggota', 1, 0, 'L', true);
    $pdf->Cell(130, 10, $member['employeeID'], 1, 1, 'L', true);
    
    $pdf->Cell(60, 10, 'No. Kad Pengenalan', 1, 0, 'L', true);
    $pdf->Cell(130, 10, $member['ic'], 1, 1, 'L', true);
    
    $pdf->Cell(60, 10, 'No. PF', 1, 0, 'L', true);
    $pdf->Cell(130, 10, $member['no_pf'], 1, 1, 'L', true);

    // Add timestamp at the bottom
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 10, 'Laporan dijana pada: ' . date('d/m/Y H:i:s'), 0, 1, 'L');

    // Output the PDF
    $pdf->Output('member_statement_' . $id . '.pdf', 'D');
    exit();
}

// Rest of your view logic for normal viewing... 