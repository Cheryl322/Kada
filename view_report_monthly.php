<?php
// Add these headers at the very top of the file
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

session_start();
include 'dbconnect.php';

// Get the period from URL parameter
$selectedPeriod = isset($_GET['period']) ? $_GET['period'] : date('Y-m');

// Extract year and month from the period
$year = date('Y', strtotime($selectedPeriod . '-01'));
$month = date('m', strtotime($selectedPeriod . '-01'));

// Fetch data for the current month and year
$currentYear = date('Y');
$currentMonth = date('m');

// Add this function at the top of the file after the includes
function convertMonthToMalay($date) {
    $english_months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
    $malay_months = array('Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember');
    
    return str_replace($english_months, $malay_months, $date);
}

// Update the query to still use English months (as that's what MySQL returns)
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

// Calculate totals
$ytdMembers = 0;
$ytdLoans = 0;
$ytdAmount = 0;
foreach ($monthlyData as $data) {
    $ytdMembers += $data['new_members'];
    $ytdLoans += $data['loan_applications'];
    $ytdAmount += $data['total_loan_amount'];
}

// Add these new queries after the existing queries
$memberQuery = "SELECT 
    m.employeeID,
    m.memberName as name,
    DATE_FORMAT(m.created_at, '%d/%m/%Y') as join_date
FROM tb_member m
WHERE YEAR(m.created_at) = ? AND MONTH(m.created_at) = ?
ORDER BY m.created_at DESC";

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

$memberStmt = mysqli_prepare($conn, $memberQuery);
mysqli_stmt_bind_param($memberStmt, "ss", $year, $month);
mysqli_stmt_execute($memberStmt);
$memberResult = mysqli_stmt_get_result($memberStmt);

$loanStmt = mysqli_prepare($conn, $loanQuery);
mysqli_stmt_bind_param($loanStmt, "ss", $year, $month);
mysqli_stmt_execute($loanStmt);
$loanResult = mysqli_stmt_get_result($loanStmt);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ringkasan Laporan KADA</title>
    <style>
        body { 
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 15px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .report-info {
            margin: 20px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 12px 8px; 
            text-align: left; 
        }
        th { 
            background-color: #4a69bd; 
            color: white;
        }
        .total-row { 
            font-weight: bold; 
            background-color: #f8f9fa; 
        }
        .amount { 
            text-align: right; 
        }
        .center { 
            text-align: center; 
        }
        .summary-section {
            margin: 30px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 0.9em;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 200px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            margin-bottom: 10px;
        }
        @media print {
            body { 
                margin: 0;
                padding: 20px;
            }
            button.no-print {
                display: none;
            }
        }
        .detail-tables {
            margin: 30px 0;
        }
        .detail-tables h3 {
            margin-top: 30px;
        }
        .detail-tables table {
            margin: 15px 0;
            font-size: 0.9em;
        }
        .action-buttons {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        .download-btn, .back-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s;
        }
        .download-btn {
            background-color: #4a69bd;
            color: white;
        }
        .download-btn:hover {
            background-color: #3c559c;
        }
        .back-btn {
            background-color: #6c757d;
            color: white;
        }
        .back-btn:hover {
            background-color: #5a6268;
        }
        @media print {
            .action-buttons {
                display: none;
            }
        }
        /* Add some spacing between tables */
        h3 {
            margin-top: 30px;
            margin-bottom: 15px;
        }
        .report-summary h2, 
        .report-summary h3 {
            text-align: left;
            margin-top: 30px;
            margin-bottom: 15px;
            color: #0056b3;
        }
        .report-summary table {
            margin-bottom: 30px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Move buttons to the very top, before the header -->
    <div class="action-buttons" style="position: absolute; top: 20px; left: 20px; z-index: 1000;">
        <button onclick="window.location.href='download_report_monthly.php?period=<?php echo $selectedPeriod; ?>'" class="download-btn">
            <i class="fas fa-download"></i> Muat Turun PDF
        </button>
        <button onclick="goBack()" class="back-btn">
            <i class="fas fa-arrow-left"></i> Kembali
        </button>
    </div>

    <div class="header">
        <img src="img/kadalogo.jpg" alt="KADA Logo" class="logo">
        <h1>KOPERASI KAKITANGAN KADA KELANTAN BERHAD (KADA)</h1>
        <h2>Ringkasan Laporan Bulanan <?php echo convertMonthToMalay(date('F Y', strtotime($selectedPeriod . '-01'))); ?></h2>
    </div>

    <div class="report-info">
        <p><strong>Tarikh Laporan:</strong> <?php echo date('d/m/Y'); ?></p>
        <p><strong>Tempoh Laporan:</strong> <?php echo convertMonthToMalay(date('F Y', strtotime($selectedPeriod . '-01'))); ?></p>
        <p><strong>Status:</strong> Laporan Rasmi</p>
    </div>

    <div class="report-summary">
        <h2 style="text-align: left;">Ringkasan Laporan</h2>
        
        <!-- Monthly Table -->
        <h3 style="text-align: left;">Perincian Bulanan</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th class="center">Jumlah Ahli Baru</th>
                    <th class="center">Jumlah Pembiayaan</th>
                    <th class="amount">Nilai Pembiayaan (RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($monthlyData && count($monthlyData) > 0): ?>
                    <?php foreach ($monthlyData as $data): ?>
                    <tr>
                        <td><?php echo convertMonthToMalay($data['month']); ?></td>
                        <td class="center"><?php echo number_format($data['new_members']); ?></td>
                        <td class="center"><?php echo number_format($data['loan_applications']); ?></td>
                        <td class="amount"><?php echo number_format($data['total_loan_amount'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td>Jumlah</td>
                        <td class="center"><?php echo number_format($ytdMembers); ?></td>
                        <td class="center"><?php echo number_format($ytdLoans); ?></td>
                        <td class="amount"><?php echo number_format($ytdAmount, 2); ?></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">Tiada rekod ditemui</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Member List Table -->
        <h3 style="text-align: left;">Senarai Ahli Baru</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Pekerja</th>
                    <th>Nama</th>
                    <th>Tarikh Daftar</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $hasMemberData = false;
                while ($member = mysqli_fetch_assoc($memberResult)): 
                    $hasMemberData = true;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($member['employeeID']); ?></td>
                        <td><?php echo htmlspecialchars($member['name']); ?></td>
                        <td><?php echo $member['join_date']; ?></td>
                    </tr>
                <?php endwhile; ?>
                <?php if (!$hasMemberData): ?>
                    <tr>
                        <td colspan="3" class="text-center">Tiada rekod ditemui</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Loan Applications Table -->
        <h3 style="text-align: left;">Senarai Permohonan Pembiayaan</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Permohonan</th>
                    <th>Nama Pemohon</th>
                    <th>Jenis Pembiayaan</th>
                    <th class="amount">Jumlah (RM)</th>
                    <th>Tarikh Permohonan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $hasLoanData = false;
                while ($loan = mysqli_fetch_assoc($loanResult)): 
                    $hasLoanData = true;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($loan['loanApplicationID']); ?></td>
                        <td><?php echo htmlspecialchars($loan['name']); ?></td>
                        <td><?php echo htmlspecialchars($loan['loanType']); ?></td>
                        <td class="amount"><?php echo number_format($loan['amountRequested'], 2); ?></td>
                        <td><?php echo $loan['application_date']; ?></td>
                    </tr>
                <?php endwhile; ?>
                <?php if (!$hasLoanData): ?>
                    <tr>
                        <td colspan="5" class="text-center">Tiada rekod ditemui</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="summary-section">
        <h3>Ringkasan Eksekutif</h3>
        <p>Laporan ini merangkumi aktiviti keahlian dan pembiayaan Koperasi KADA bagi tahun <?php echo $year; ?>. 
           Setakat ini, KADA telah mencatatkan:</p>
        <ul>
            <li>Jumlah ahli baru: <?php echo number_format($ytdMembers); ?> orang</li>
            <li>Jumlah permohonan pembiayaan: <?php echo number_format($ytdLoans); ?> permohonan</li>
            <li>Nilai keseluruhan pembiayaan: RM <?php echo number_format($ytdAmount, 2); ?></li>
        </ul>
    </div>

    <div class="summary-section">
        <h3>Analisis dan Pemerhatian</h3>
        <p>Berdasarkan data di atas, prestasi KADA menunjukkan:</p>
        <ul>
            <li>Purata keahlian bulanan: <?php echo number_format($ytdMembers/12, 1); ?> ahli</li>
            <li>Purata pembiayaan bulanan: RM <?php echo number_format($ytdAmount/12, 2); ?></li>
            <li>Nisbah pembiayaan kepada ahli baru: <?php echo $ytdMembers > 0 ? number_format($ytdLoans/$ytdMembers, 2) : 0; ?></li>
        </ul>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <p>Pengerusi KADA</p>
            <p>Tarikh: ________________</p>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <p>Setiausaha</p>
            <p>Tarikh: ________________</p>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <p>Bendahari</p>
            <p>Tarikh: ________________</p>
        </div>
    </div>

    <div class="footer">
        <p>Laporan ini dijana secara automatik oleh Sistem Koperasi KADA pada <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>

    <script>
    // Prevent form resubmission on page refresh or back button
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // Add proper back button handling
    function goBack() {
        window.location.href = 'adminviewreport.php';
    }
    </script>
</body>
</html>