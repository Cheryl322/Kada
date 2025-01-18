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

    // Initialize PDF with proper settings
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('KADA Kelantan');
    $pdf->SetTitle('Pengesahan Penyata Ahli');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins and add page
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();
    
    // Query member data
    $query = "SELECT * FROM tb_member WHERE employeeID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $employeeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $member = mysqli_fetch_assoc($result);

    if (!$member) {
        throw new Exception('Member not found');
    }

    // Add title
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Pengesahan Penyata Ahli Koperasi', 0, 1, 'C');
    $pdf->Cell(0, 10, 'Kakitangan KADA Kelantan Berhad', 0, 1, 'C');
    $pdf->Ln(10);

    // Create table for member details
    $pdf->SetFont('helvetica', '', 12);
    
    // Table rows
    $pdf->Cell(60, 10, 'Nama', 1, 0);
    $pdf->Cell(0, 10, htmlspecialchars($member['memberName']), 1, 1);
    
    $pdf->Cell(60, 10, 'No. Anggota', 1, 0);
    $pdf->Cell(0, 10, htmlspecialchars($member['employeeID']), 1, 1);
    
    $pdf->Cell(60, 10, 'No. Kad Pengenalan', 1, 0);
    $pdf->Cell(0, 10, htmlspecialchars($member['ic']), 1, 1);
    
    $pdf->Cell(60, 10, 'No. PF', 1, 0);
    $pdf->Cell(0, 10, htmlspecialchars($member['no_pf']), 1, 1);

    // Add timestamp
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 10, 'Laporan dijana pada: ' . date('d/m/Y H:i:s'), 0, 1);

    // Clear any output buffers
    ob_end_clean();

    // Output the PDF
    header('Content-Type: application/pdf');
    header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
    header('Pragma: public');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
    header('Content-Disposition: attachment; filename="member_statement_' . $employeeID . '.pdf"');
    
    $pdf->Output('member_statement_' . $employeeID . '.pdf', 'D');
    exit();

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    error_log('PDF Generation Error: ' . $e->getMessage());
    exit('Error generating PDF. Please try again later.');
} 