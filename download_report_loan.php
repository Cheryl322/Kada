<?php
session_start();
require_once 'dbconnect.php';

// Check if employeeID is provided
if (!isset($_GET['employeeID'])) {
    header('HTTP/1.1 400 Bad Request');
    exit('Employee ID is required');
}

$employeeID = $_GET['employeeID'];

try {
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
    $pdf->SetCreator('Your System');
    $pdf->SetTitle('Financial Statement');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins and add page
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();
    
    // Query loan data and employee data
    $query = "SELECT l.*, m.memberName, m.employeeID, m.ic, m.no_pf 
              FROM tb_loan l 
              JOIN tb_member m ON l.employeeID = m.employeeID 
              WHERE l.employeeID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $employeeID);

    // Add debugging before query execution
    if (!$stmt) {
        throw new Exception("Query preparation failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // Verify data was retrieved
    if (!$data = mysqli_fetch_assoc($result)) {
        throw new Exception("No data found for employee ID: " . $employeeID);
    }

    // Add content to PDF
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Pengesahan Penyata Kewangan Ahli Koperasi Kakitangan KADA', 0, 1, 'C');
    $pdf->Cell(0, 10, 'Kelantan Berhad', 0, 1, 'C');
    $pdf->Ln(10);

    // Maklumat Peribadi section
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Maklumat Peribadi', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 11);
    
    $pdf->Cell(40, 8, 'Nama', 1, 0);
    $pdf->Cell(150, 8, htmlspecialchars($data['memberName']), 1, 1);
    
    $pdf->Cell(40, 8, 'No. Pekerja', 1, 0);
    $pdf->Cell(150, 8, htmlspecialchars($data['employeeID']), 1, 1);
    
    $pdf->Cell(40, 8, 'No. Kad Pengenalan', 1, 0);
    $pdf->Cell(150, 8, htmlspecialchars($data['ic']), 1, 1);
    
    $pdf->Cell(40, 8, 'No. PF', 1, 0);
    $pdf->Cell(150, 8, htmlspecialchars($data['no_pf']), 1, 1);
    
    $pdf->Ln(10);

    // Maklumat Pembiayaan section
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Maklumat Pembiayaan', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 11);
    
    $pdf->Cell(40, 8, 'Jenis Pembiayaan', 1, 0);
    $pdf->Cell(150, 8, $data['loanType'], 1, 1);
    
    $pdf->Cell(40, 8, 'Amaun Dipohon', 1, 0);
    $pdf->Cell(150, 8, 'RM ' . number_format($data['amountRequested'], 2), 1, 1);
    
    $pdf->Cell(40, 8, 'Tempoh Pembiayaan', 1, 0);
    $pdf->Cell(150, 8, $data['financingPeriod'] . ' bulan', 1, 1);
    
    $pdf->Cell(40, 8, 'Ansuran Bulanan', 1, 0);
    $pdf->Cell(150, 8, 'RM ' . number_format($data['monthlyInstallments'], 2), 1, 1);

    // Add timestamp
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 10, 'Laporan dijana pada: ' . date('d/m/Y H:i:s'), 0, 1, 'L');

    // Clear any output buffers
    ob_end_clean();

    // Output the PDF
    header('Content-Type: application/pdf');
    header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
    header('Pragma: public');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
    header('Content-Disposition: attachment; filename="financial_statement_' . $employeeID . '.pdf"');
    
    $pdf->Output('financial_statement_' . $employeeID . '.pdf', 'D');
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