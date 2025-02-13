<?php
// Add these headers at the very top of the file
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

session_start();
include 'dbconnect.php';

// Get the year from URL parameter (using first month of year)
$selectedYear = isset($_GET['period']) ? substr($_GET['period'], 0, 4) : date('Y');

// Function to convert month names to Malay
function convertMonthToMalay($date) {
    $english_months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
    $malay_months = array('Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember');
    
    return str_replace($english_months, $malay_months, $date);
}

// Get yearly statistics
$yearlyQuery = "SELECT 
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
WHERE YEAR(created_at) = ?";

$stmt = mysqli_prepare($conn, $yearlyQuery);
mysqli_stmt_bind_param($stmt, "s", $selectedYear);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$yearlyData = mysqli_fetch_assoc($result);

// Get member details for the year
$memberQuery = "SELECT m.employeeID, m.memberName, m.created_at as registration_date 
               FROM tb_member m
               WHERE YEAR(m.created_at) = ?
               ORDER BY m.created_at DESC";

$memberStmt = mysqli_prepare($conn, $memberQuery);
mysqli_stmt_bind_param($memberStmt, "s", $selectedYear);
mysqli_stmt_execute($memberStmt);
$memberResult = mysqli_stmt_get_result($memberStmt);

// Get loan applications for the year
$loanQuery = "SELECT l.loanApplicationID, m.memberName, l.loanType, l.amountRequested, 
              DATE_FORMAT(l.created_at, '%d/%m/%Y') as application_date
              FROM tb_loan l
              JOIN tb_member m ON l.employeeID = m.employeeID
              WHERE YEAR(l.created_at) = ?
              ORDER BY l.created_at DESC";

$loanStmt = mysqli_prepare($conn, $loanQuery);
mysqli_stmt_bind_param($loanStmt, "s", $selectedYear);
mysqli_stmt_execute($loanStmt);
$loanResult = mysqli_stmt_get_result($loanStmt);

// Add this query near the top of the file with other queries
$monthlyBreakdownQuery = "SELECT 
    DATE_FORMAT(created_at, '%m') as month,
    COUNT(DISTINCT CASE WHEN type = 'member' THEN employeeID END) as new_members,
    COUNT(DISTINCT CASE WHEN type = 'loan' THEN loanApplicationID END) as loan_applications,
    SUM(CASE WHEN type = 'loan' THEN amountRequested ELSE 0 END) as total_loan_amount
FROM (
    SELECT employeeID, NULL as loanApplicationID, created_at, 'member' as type, 0 as amountRequested 
    FROM tb_member
    WHERE YEAR(created_at) = ?
    UNION ALL
    SELECT employeeID, loanApplicationID, created_at, 'loan' as type, amountRequested 
    FROM tb_loan
    WHERE YEAR(created_at) = ?
) combined_data
GROUP BY DATE_FORMAT(created_at, '%m')
ORDER BY month ASC";

$monthlyStmt = mysqli_prepare($conn, $monthlyBreakdownQuery);
mysqli_stmt_bind_param($monthlyStmt, "ss", $selectedYear, $selectedYear);
mysqli_stmt_execute($monthlyStmt);
$monthlyBreakdown = mysqli_stmt_get_result($monthlyStmt);
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
            body { 
                margin: 0;
                padding: 20px;
            }
            button.no-print {
                display: none;
            }
            .page-break {
                page-break-before: always;
            }
        }
        .report-summary h3 {
            margin-top: 30px;
            margin-bottom: 15px;
            color: #0056b3;
        }
        .report-summary table {
            margin-bottom: 30px;
        }
        .report-section {
            margin: 30px 0;
            padding: 20px;
            border-radius: 5px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Add this div at the very top of the body -->
    <div class="action-buttons" style="position: absolute; top: 20px; left: 20px; z-index: 1000;">
        <button onclick="window.location.href='download_report_yearly.php?period=<?php echo $selectedYear; ?>'" class="download-btn">
            <i class="fas fa-download"></i> Muat Turun PDF
        </button>
        <button onclick="goBack()" class="back-btn">
            <i class="fas fa-arrow-left"></i> Kembali
        </button>
    </div>

    <div class="header">
        <img src="img/kadalogo.jpg" alt="KADA Logo" class="logo">
        <h1>KOPERASI KAKITANGAN KADA KELANTAN BERHAD (KADA)</h1>
        <h2>Ringkasan Laporan Tahunan <?php echo $selectedYear; ?></h2>
    </div>

    <div class="report-info">
        <p><strong>Tarikh Laporan:</strong> <?php echo date('d/m/Y'); ?></p>
        <p><strong>Tempoh Laporan:</strong> Tahun <?php echo $selectedYear; ?></p>
        <p><strong>Status:</strong> Laporan Rasmi</p>
    </div>

    <div class="summary-section">
        <h3>Ringkasan Eksekutif</h3>
        <p>Laporan ini merangkumi aktiviti keahlian dan pembiayaan Koperasi KADA bagi tahun <?php echo $selectedYear; ?>. 
           Setakat ini, KADA telah mencatatkan:</p>
        <ul>
            <li>Jumlah ahli baru: <?php echo number_format($yearlyData['new_members']); ?> orang</li>
            <li>Jumlah permohonan pembiayaan: <?php echo number_format($yearlyData['loan_applications']); ?> permohonan</li>
            <li>Nilai keseluruhan pembiayaan: RM <?php echo number_format($yearlyData['total_loan_amount'], 2); ?></li>
        </ul>
    </div>

    <div class="detail-tables">
        <h3>Perincian Bulanan Tahun <?php echo $selectedYear; ?></h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th>Jumlah Ahli Baru</th>
                    <th>Jumlah Pembiayaan</th>
                    <th>Nilai Pembiayaan (RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalMembers = 0;
                $totalLoans = 0;
                $totalAmount = 0;
                
                // Initialize array with all months
                $months = array(
                    '01' => 'Januari',
                    '02' => 'Februari',
                    '03' => 'Mac',
                    '04' => 'April',
                    '05' => 'Mei',
                    '06' => 'Jun',
                    '07' => 'Julai',
                    '08' => 'Ogos',
                    '09' => 'September',
                    '10' => 'Oktober',
                    '11' => 'November',
                    '12' => 'Disember'
                );
                
                // Create an array to store the data
                $monthlyData = array();
                while ($row = mysqli_fetch_assoc($monthlyBreakdown)) {
                    $monthlyData[$row['month']] = $row;
                }
                
                // Display all months, even if no data
                foreach ($months as $monthNum => $monthName):
                    $data = isset($monthlyData[$monthNum]) ? $monthlyData[$monthNum] : array(
                        'new_members' => 0,
                        'loan_applications' => 0,
                        'total_loan_amount' => 0
                    );
                    
                    $totalMembers += $data['new_members'];
                    $totalLoans += $data['loan_applications'];
                    $totalAmount += $data['total_loan_amount'];
                ?>
                    <tr>
                        <td><?php echo $monthName; ?></td>
                        <td><?php echo number_format($data['new_members']); ?></td>
                        <td><?php echo number_format($data['loan_applications']); ?></td>
                        <td><?php echo number_format($data['total_loan_amount'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                
                <tr class="total-row">
                    <td>Jumlah Keseluruhan</td>
                    <td><?php echo number_format($totalMembers); ?></td>
                    <td><?php echo number_format($totalLoans); ?></td>
                    <td><?php echo number_format($totalAmount, 2); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="detail-tables">
        <h3>Senarai Ahli Baru</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Pekerja</th>
                    <th>Nama</th>
                    <th>Tarikh Daftar</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($member = mysqli_fetch_assoc($memberResult)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($member['employeeID']); ?></td>
                    <td><?php echo htmlspecialchars($member['memberName']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($member['registration_date'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h3 class="page-break">Senarai Permohonan Pembiayaan</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Permohonan</th>
                    <th>Nama Pemohon</th>
                    <th>Jenis</th>
                    <th class="amount">Jumlah (RM)</th>
                    <th>Tarikh</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($loan = mysqli_fetch_assoc($loanResult)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($loan['loanApplicationID']); ?></td>
                    <td><?php echo htmlspecialchars($loan['memberName']); ?></td>
                    <td><?php echo htmlspecialchars($loan['loanType']); ?></td>
                    <td class="amount"><?php echo number_format($loan['amountRequested'], 2); ?></td>
                    <td><?php echo $loan['application_date']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
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
    function goBack() {
        window.location.href = 'adminviewreport.php';
    }
    </script>
</body>
</html> 