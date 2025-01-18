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
    $pdf->SetCreator('Your System');
    $pdf->SetTitle('Financial Statement');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins and add page
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();
    
    // Query loan data
    $query = "SELECT * FROM tb_loan WHERE employeeID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $employeeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $loan = mysqli_fetch_assoc($result);

    // Add financial details to PDF
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Financial Statement', 0, 1, 'C');
    $pdf->Cell(0, 10, 'Employee ID: ' . htmlspecialchars($employeeID), 0, 1);
    
    if ($loan) {
        $pdf->Cell(0, 10, 'Loan ID: ' . htmlspecialchars($loan['loanID']), 0, 1);
        $pdf->Cell(0, 10, 'Loan Amount: RM ' . number_format($loan['loanAmount'], 2), 0, 1);
    } else {
        $pdf->Cell(0, 10, 'No loan records found', 0, 1);
    }

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
    header('HTTP/1.1 500 Internal Server Error');
    error_log('PDF Generation Error: ' . $e->getMessage());
    exit('Error generating PDF. Please try again later.');
} 