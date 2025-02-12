<?php
session_start();
require_once 'dbconnect.php';

// Get the period from URL parameter
$selectedPeriod = isset($_GET['period']) ? $_GET['period'] : date('Y-m');

// Extract year and month from the period
$year = date('Y', strtotime($selectedPeriod . '-01'));
$month = date('m', strtotime($selectedPeriod . '-01'));

try {
    // Reuse the same queries from view_report_monthly.php
    $query = "SELECT 
        DATE_FORMAT(created_at, '%M %Y') as month,
        COUNT(DISTINCT CASE WHEN type = 'member' THEN employeeID END) as new_members,
        COUNT(DISTINCT CASE WHEN type = 'loan' THEN loanApplicationID END) as loan_applications,
        SUM(CASE WHEN type = 'loan' THEN amountRequested ELSE 0 END) as total_loan_amount
    FROM (
        SELECT employeeID, NULL as loanApplicationID, created_at, 'member' as type, 0 as amountRequested 
        FROM tb_member
        UNION ALL
        SELECT employeeID, loanApplicationID, created_at, 'loan' as type, amountRequested 
        FROM tb_loan
    ) combined_data
    WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY created_at DESC";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $year, $month);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $monthlyData = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // Get member details
    $memberQuery = "SELECT 
        m.employeeID,
        m.memberName as name,
        DATE_FORMAT(m.created_at, '%d/%m/%Y') as join_date
    FROM tb_member m
    WHERE YEAR(m.created_at) = ? AND MONTH(m.created_at) = ?
    ORDER BY m.created_at DESC";

    $memberStmt = mysqli_prepare($conn, $memberQuery);
    mysqli_stmt_bind_param($memberStmt, "ss", $year, $month);
    mysqli_stmt_execute($memberStmt);
    $memberResult = mysqli_stmt_get_result($memberStmt);

    // Get loan details
    $loanQuery = "SELECT 
        l.loanApplicationID,
        m.memberName as name,
        l.amountRequested,
        l.loanType,
        DATE_FORMAT(l.created_at, '%d/%m/%Y') as application_date
    FROM tb_loan l
    JOIN tb_member m ON l.employeeID = m.employeeID
    WHERE YEAR(l.created_at) = ? AND MONTH(l.created_at) = ?
    ORDER BY l.created_at DESC";

    $loanStmt = mysqli_prepare($conn, $loanQuery);
    mysqli_stmt_bind_param($loanStmt, "ss", $year, $month);
    mysqli_stmt_execute($loanStmt);
    $loanResult = mysqli_stmt_get_result($loanStmt);

    // Calculate totals
    $ytdMembers = 0;
    $ytdLoans = 0;
    $ytdAmount = 0;
    foreach ($monthlyData as $data) {
        $ytdMembers += $data['new_members'];
        $ytdLoans += $data['loan_applications'];
        $ytdAmount += $data['total_loan_amount'];
    }

    require_once('tcpdf/tcpdf.php');
    
    // Initialize PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('KADA Kelantan');
    $pdf->SetTitle('Ringkasan Laporan Bulanan KADA');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();

    // Add KADA logo
    $logoPath = 'img/kadalogo.jpg';
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, 80, 10, 50, 0, 'JPG', '', 'T', false, 300, 'C');
        $pdf->Ln(45);
    }

    // Add title with reduced spacing
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 48, 135);
    $pdf->Cell(0, 10, 'KOPERASI KAKITANGAN KADA KELANTAN BERHAD (KADA)', 0, 1, 'C');
    $pdf->Cell(0, 10, 'Ringkasan Laporan Bulanan ' . convertMonthToMalay(date('F Y', strtotime($selectedPeriod . '-01'))), 0, 1, 'C');
    
    // Reduce spacing before report info
    $pdf->Ln(5); // Changed from 10
    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 8, 'Tarikh Laporan: ' . date('d/m/Y'), 0, 1);
    $pdf->Cell(0, 8, 'Tempoh Laporan: ' . convertMonthToMalay(date('F Y', strtotime($selectedPeriod . '-01'))), 0, 1);
    $pdf->Cell(0, 8, 'Status: Laporan Rasmi', 0, 1);

    // Add summary section
    $pdf->Ln(5); // Changed from 10
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(0, 48, 135);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 10, ' Ringkasan Eksekutif', 0, 1, 'L', true);
    
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Ln(3); // Changed from 5
    $pdf->MultiCell(0, 8, "Laporan ini merangkumi aktiviti keahlian dan pembiayaan Koperasi KADA bagi tahun $year. Setakat ini, KADA telah mencatatkan:", 0, 'L');
    $pdf->Ln(2);
    $pdf->Cell(0, 8, "• Jumlah ahli baru: " . number_format($ytdMembers) . " orang", 0, 1);
    $pdf->Cell(0, 8, "• Jumlah permohonan pembiayaan: " . number_format($ytdLoans) . " permohonan", 0, 1);
    $pdf->Cell(0, 8, "• Nilai keseluruhan pembiayaan: RM " . number_format($ytdAmount, 2), 0, 1);

    // Add monthly details table
    $pdf->Ln(5); // Changed from 10
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(0, 48, 135);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 10, ' Perincian Bulanan', 0, 1, 'L', true);
    
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Ln(3); // Changed from 5

    // Table headers
    $pdf->SetFillColor(240, 240, 250);
    $pdf->Cell(50, 10, 'Bulan', 1, 0, 'C', true);
    $pdf->Cell(45, 10, 'Jumlah Ahli Baru', 1, 0, 'C', true);
    $pdf->Cell(45, 10, 'Jumlah Pembiayaan', 1, 0, 'C', true);
    $pdf->Cell(45, 10, 'Nilai (RM)', 1, 1, 'C', true);

    // Table data
    foreach ($monthlyData as $data) {
        $pdf->Cell(50, 10, convertMonthToMalay($data['month']), 1, 0, 'L');
        $pdf->Cell(45, 10, number_format($data['new_members']), 1, 0, 'C');
        $pdf->Cell(45, 10, number_format($data['loan_applications']), 1, 0, 'C');
        $pdf->Cell(45, 10, number_format($data['total_loan_amount'], 2), 1, 1, 'R');
    }

    // Add member list
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(0, 48, 135);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 10, ' Senarai Ahli Baru', 0, 1, 'L', true);
    
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Ln(3); // Changed from 5

    // Member table headers
    $pdf->SetFillColor(240, 240, 250);
    $pdf->Cell(40, 10, 'ID Pekerja', 1, 0, 'C', true);
    $pdf->Cell(100, 10, 'Nama', 1, 0, 'C', true);
    $pdf->Cell(45, 10, 'Tarikh Daftar', 1, 1, 'C', true);

    // Member table data
    while ($member = mysqli_fetch_assoc($memberResult)) {
        $pdf->Cell(40, 10, $member['employeeID'], 1, 0, 'L');
        $pdf->Cell(100, 10, $member['name'], 1, 0, 'L');
        $pdf->Cell(45, 10, $member['join_date'], 1, 1, 'C');
    }

    // Add loan list
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(0, 48, 135);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 10, ' Senarai Permohonan Pembiayaan', 0, 1, 'L', true);
    
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Ln(3); // Changed from 5

    // Loan table headers
    $pdf->SetFillColor(240, 240, 250);
    $pdf->Cell(35, 10, 'ID Permohonan', 1, 0, 'C', true);
    $pdf->Cell(60, 10, 'Nama Pemohon', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Jenis', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Jumlah (RM)', 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'Tarikh', 1, 1, 'C', true);

    // Loan table data
    while ($loan = mysqli_fetch_assoc($loanResult)) {
        $pdf->Cell(35, 10, $loan['loanApplicationID'], 1, 0, 'L');
        $pdf->Cell(60, 10, $loan['name'], 1, 0, 'L');
        $pdf->Cell(35, 10, $loan['loanType'], 1, 0, 'L');
        $pdf->Cell(35, 10, number_format($loan['amountRequested'], 2), 1, 0, 'R');
        $pdf->Cell(20, 10, $loan['application_date'], 1, 1, 'C');
    }

    // Add Analysis section and signature on the same page (LAST PAGE)
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(0, 48, 135);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 10, ' Analisis dan Pemerhatian', 0, 1, 'L', true);
    
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Ln(3);
    
    $pdf->MultiCell(0, 8, 'Berdasarkan data di atas, prestasi KADA menunjukkan:', 0, 'L');
    $pdf->Ln(2);
    
    // Calculate the averages and ratios
    $monthlyMemberAvg = $ytdMembers/12;
    $monthlyLoanAvg = $ytdAmount/12;
    $loanMemberRatio = $ytdMembers > 0 ? $ytdLoans/$ytdMembers : 0;
    
    // Add bullet points for analysis
    $pdf->Cell(0, 8, "• Purata keahlian bulanan: " . number_format($monthlyMemberAvg, 1) . " ahli", 0, 1);
    $pdf->Cell(0, 8, "• Purata pembiayaan bulanan: RM " . number_format($monthlyLoanAvg, 2), 0, 1);
    $pdf->Cell(0, 8, "• Nisbah pembiayaan kepada ahli baru: " . number_format($loanMemberRatio, 2), 0, 1);

    // Add space before signatures
    $pdf->Ln(15);
    
    // Create three signature boxes with adjusted spacing
    $signatureWidth = 55;
    $spacing = 10;
    $startX = ($pdf->getPageWidth() - (3 * $signatureWidth + 2 * $spacing)) / 2;
    
    for ($i = 0; $i < 3; $i++) {
        $x = $startX + ($signatureWidth + $spacing) * $i;
        $pdf->SetX($x);
        $pdf->Cell($signatureWidth, 0, '', 'T', 0, 'C');
    }
    
    $pdf->Ln(5);
    
    // Add signature titles
    $pdf->SetX($startX);
    $pdf->Cell($signatureWidth, 10, 'Pengerusi KADA', 0, 0, 'C');
    $pdf->SetX($startX + $signatureWidth + $spacing);
    $pdf->Cell($signatureWidth, 10, 'Setiausaha', 0, 0, 'C');
    $pdf->SetX($startX + 2 * ($signatureWidth + $spacing));
    $pdf->Cell($signatureWidth, 10, 'Bendahari', 0, 1, 'C');
    
    $pdf->Ln(5);
    
    // Add date lines
    $pdf->SetX($startX);
    $pdf->Cell($signatureWidth, 10, 'Tarikh: ____________', 0, 0, 'C');
    $pdf->SetX($startX + $signatureWidth + $spacing);
    $pdf->Cell($signatureWidth, 10, 'Tarikh: ____________', 0, 0, 'C');
    $pdf->SetX($startX + 2 * ($signatureWidth + $spacing));
    $pdf->Cell($signatureWidth, 10, 'Tarikh: ____________', 0, 1, 'C');

    // Add footer text at the bottom of the page
    $pdf->SetY(-20); // Position closer to bottom
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->SetTextColor(128, 128, 128);
    $pdf->Cell(0, 10, 'Laporan ini dijana secara automatik oleh Sistem Koperasi KADA pada ' . date('d/m/Y H:i:s'), 0, 1, 'L');

    // Output the PDF
    $pdf->Output('laporan_bulanan_' . $selectedPeriod . '.pdf', 'D');
    exit();

} catch (Exception $e) {
    error_log('PDF Generation Error: ' . $e->getMessage());
    exit('Error generating PDF. Please try again later.');
} finally {
    if (isset($stmt)) mysqli_stmt_close($stmt);
    if (isset($memberStmt)) mysqli_stmt_close($memberStmt);
    if (isset($loanStmt)) mysqli_stmt_close($loanStmt);
    if (isset($conn)) mysqli_close($conn);
}

function convertMonthToMalay($date) {
    $english_months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
    $malay_months = array('Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember');
    
    return str_replace($english_months, $malay_months, $date);
}
?> 