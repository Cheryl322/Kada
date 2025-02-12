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
    // Get member data first
    $query = "SELECT * FROM tb_member WHERE employeeID = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, 's', $employeeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $member = mysqli_fetch_assoc($result);

    if (!$member) {
        throw new Exception('Member not found');
    }

    require_once('tcpdf/tcpdf.php');

    // Initialize PDF
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
    
    // Add KADA logo
    $logoPath = 'img/kadalogo.jpg';
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, 80, 10, 50, 0, 'JPG', '', 'T', false, 300, 'C');
        $pdf->Ln(45);
    }
    
    // Add decorative line
    $pdf->SetLineStyle(array('width' => 0.5, 'color' => array(0, 48, 135)));
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    
    // Add title with enhanced styling
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 48, 135);
    $pdf->Cell(0, 10, 'Pengesahan Penyata Ahli Koperasi', 0, 1, 'C');
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
    
    // Debug: Print member data to error log
    error_log('Member Data: ' . print_r($member, true));
    
    // Table rows with alternating background and data
    $pdf->Cell(60, 10, ' Nama', 1, 0, 'L', true);
    $pdf->Cell(120, 10, ' ' . $member['memberName'], 1, 1, 'L');
    
    $pdf->Cell(60, 10, ' No. Anggota', 1, 0, 'L');
    $pdf->Cell(120, 10, ' ' . $member['employeeID'], 1, 1, 'L');
    
    $pdf->Cell(60, 10, ' No. Kad Pengenalan', 1, 0, 'L', true);
    $pdf->Cell(120, 10, ' ' . $member['ic'], 1, 1, 'L');
    
    $pdf->Cell(60, 10, ' No. PF', 1, 0, 'L');
    $pdf->Cell(120, 10, ' ' . $member['no_pf'], 1, 1, 'L');

    // Add timestamp with enhanced styling
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->SetTextColor(128, 128, 128);
    $pdf->Cell(0, 10, 'Laporan dijana pada: ' . date('d/m/Y H:i:s'), 0, 1, 'L');

    // Add footer line
    $pdf->SetY(-30);
    $pdf->SetDrawColor(0, 48, 135);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());

    // Clear any output buffers
    ob_end_clean();

    // Output the PDF
    $pdf->Output('member_statement_' . $employeeID . '.pdf', 'D');
    exit();

} catch (Exception $e) {
    error_log('PDF Generation Error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    exit('Error generating PDF: ' . $e->getMessage());
} 