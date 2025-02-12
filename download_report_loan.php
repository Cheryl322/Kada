<?php
session_start();
require_once 'dbconnect.php';

// Check if loanApplicationID is provided
if (!isset($_GET['loanApplicationID'])) {
    header('HTTP/1.1 400 Bad Request');
    exit('Loan Application ID is required');
}

$loanApplicationID = $_GET['loanApplicationID'];

try {
    // Get loan and member data
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
    mysqli_stmt_bind_param($stmt, "s", $loanApplicationID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    if (!$data) {
        throw new Exception('Loan application not found');
    }

    require_once('tcpdf/tcpdf.php');
    
    // Add error reporting at the start
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Verify database connection
    if (!$conn) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }
    
    // Initialize PDF with proper settings
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('KADA Kelantan');
    $pdf->SetTitle('Pengesahan Penyata Kewangan');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins and add page
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();

    // Add KADA logo with adjusted positioning
    $logoPath = 'img/kadalogo.jpg';
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, 80, 10, 50, 0, 'JPG', '', 'T', false, 300, 'C');
        $pdf->Ln(45); // Increased space after logo from 30 to 45
    }
    
    // Add decorative line
    $pdf->SetLineStyle(array('width' => 0.5, 'color' => array(0, 48, 135)));
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    
    // Add title with enhanced styling
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 48, 135);
    $pdf->Cell(0, 10, 'Pengesahan Penyata Kewangan Ahli Koperasi', 0, 1, 'C');
    $pdf->Cell(0, 10, 'Kakitangan KADA Kelantan Berhad', 0, 1, 'C');
    
    // Add decorative line
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(10);

    // Add "Maklumat Peribadi" heading with styled background
    $pdf->SetFillColor(0, 48, 135);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, ' Maklumat Peribadi', 0, 1, 'L', true);
    $pdf->Ln(5);
    
    // Reset colors for table content
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 11);
    
    // Create table with enhanced styling
    $pdf->SetFillColor(240, 240, 250);
    $pdf->SetDrawColor(0, 48, 135);
    
    // Personal Information table with alternating backgrounds
    $pdf->Cell(60, 10, ' Nama', 1, 0, 'L', true);
    $pdf->Cell(120, 10, ' ' . htmlspecialchars($data['memberName']), 1, 1, 'L');
    
    $pdf->Cell(60, 10, ' No. Anggota', 1, 0, 'L');
    $pdf->Cell(120, 10, ' ' . htmlspecialchars($data['employeeID']), 1, 1, 'L');
    
    $pdf->Cell(60, 10, ' No. Kad Pengenalan', 1, 0, 'L', true);
    $pdf->Cell(120, 10, ' ' . htmlspecialchars($data['ic']), 1, 1, 'L');
    
    $pdf->Cell(60, 10, ' No. PF', 1, 0, 'L');
    $pdf->Cell(120, 10, ' ' . htmlspecialchars($data['no_pf']), 1, 1, 'L');

    $pdf->Ln(10);

    // Add "Maklumat Pembiayaan" heading
    $pdf->SetFillColor(0, 48, 135);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, ' Maklumat Pembiayaan', 0, 1, 'L', true);
    $pdf->Ln(5);

    // Reset colors for table content
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetFillColor(240, 240, 250); // Reset fill color for table rows

    // Loan Information table with matching alternating backgrounds
    $pdf->Cell(60, 10, ' No. Pembiayaan', 1, 0, 'L', true);
    $pdf->Cell(120, 10, ' ' . htmlspecialchars($data['loanID']), 1, 1, 'L');
    
    $pdf->Cell(60, 10, ' Jenis Pembiayaan', 1, 0, 'L');
    $pdf->Cell(120, 10, ' ' . htmlspecialchars($data['loanType']), 1, 1, 'L');
    
    $pdf->Cell(60, 10, ' Amaun Dipohon', 1, 0, 'L', true);
    $pdf->Cell(120, 10, ' RM ' . number_format($data['amountRequested'], 2), 1, 1, 'L');
    
    $pdf->Cell(60, 10, ' Tempoh Pembiayaan', 1, 0, 'L');
    $pdf->Cell(120, 10, ' ' . htmlspecialchars($data['financingPeriod']) . ' bulan', 1, 1, 'L');
    
    $pdf->Cell(60, 10, ' Ansuran Bulanan', 1, 0, 'L', true);
    $pdf->Cell(120, 10, ' RM ' . number_format($data['monthlyInstallments'], 2), 1, 1, 'L');
    
    $pdf->Cell(60, 10, ' Tarikh Pembiayaan', 1, 0, 'L');
    $pdf->Cell(120, 10, ' ' . htmlspecialchars($data['tarikh_pembiayaan']), 1, 1, 'L');

    // Add timestamp with enhanced styling
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->SetTextColor(128, 128, 128);
    $pdf->Cell(0, 10, 'Laporan dijana pada: ' . date('d/m/Y H:i:s'), 0, 1, 'L');

    // Add footer line
    $pdf->SetY(-30);
    $pdf->SetDrawColor(0, 48, 135);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());

    // Clear any output buffers before sending headers
    ob_end_clean();

    // Output the PDF
    header('Content-Type: application/pdf');
    header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
    header('Pragma: public');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
    header('Content-Disposition: attachment; filename="financial_statement_' . $loanApplicationID . '.pdf"');
    
    $pdf->Output('financial_statement_' . $loanApplicationID . '.pdf', 'D');
    exit();

} catch (Exception $e) {
    // Enhanced error handling
    header('HTTP/1.1 500 Internal Server Error');
    error_log('PDF Generation Error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    // In development environment, you might want to show detailed error
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        exit('Error generating PDF: ' . $e->getMessage());
    } else {
        exit('Error generating PDF. Please try again later.');
    }
} finally {
    // Clean up resources
    if (isset($stmt)) {
        mysqli_stmt_close($stmt);
    }
    if (isset($conn)) {
        mysqli_close($conn);
    }
} 