<?php
session_start();
include "dbconnect.php";
include "headermember.php";

if (!isset($_SESSION['employeeID']) || !isset($_GET['year'])) {
    header("Location: monthly_statements.php");
    exit();
}

$employeeID = $_SESSION['employeeID'];
$year = $_GET['year'];


$sql_member = "SELECT m.*, f.* 
               FROM tb_member m
               LEFT JOIN tb_memberregistration_feesandcontribution f ON m.employeeID = f.employeeID
               WHERE m.employeeID = ?";
$stmt_member = mysqli_prepare($conn, $sql_member);
mysqli_stmt_bind_param($stmt_member, 's', $employeeID);
mysqli_stmt_execute($stmt_member);
$member = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_member));


$sql = "SELECT 
    MONTH(d.Deduct_date) as month,
    SUM(CASE WHEN dt.DeducType_ID = 1 THEN d.Deduct_Amt ELSE 0 END) as modal_syer,
    SUM(CASE WHEN dt.DeducType_ID = 2 THEN d.Deduct_Amt ELSE 0 END) as modal_yuran,
    SUM(CASE WHEN dt.DeducType_ID = 3 THEN d.Deduct_Amt ELSE 0 END) as simpanan_tetap,
    SUM(CASE WHEN dt.DeducType_ID = 4 THEN d.Deduct_Amt ELSE 0 END) as tabung_kebajikan,
    SUM(CASE WHEN dt.DeducType_ID = 5 THEN d.Deduct_Amt ELSE 0 END) as wang_deposit
FROM tb_deduction d
JOIN tb_deduction_type dt ON d.DeducType_ID = dt.DeducType_ID
WHERE d.employeeID = ? 
AND YEAR(d.Deduct_date) = ?
AND dt.DeducType_ID IN (1,2,3,4,5)
GROUP BY MONTH(d.Deduct_date)
ORDER BY month";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'si', $employeeID, $year);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);


$sql_loan = "SELECT 
    l.loanType,
    la.amountRequested,
    la.monthlyInstallments,
    COALESCE((
        SELECT SUM(d.Deduct_Amt)
        FROM tb_deduction d
        JOIN tb_deduction_type dt ON d.DeducType_ID = dt.DeducType_ID
        WHERE d.employeeID = l.employeeID 
        AND dt.typeName = 'Loan Payment'
        AND YEAR(d.Deduct_date) <= ?
    ), 0) as total_paid
FROM tb_loan l
JOIN tb_loanapplication la ON l.loanApplicationID = la.loanApplicationID 
WHERE l.employeeID = ?
AND la.loanStatus = 'Diluluskan'";

$stmt_loan = mysqli_prepare($conn, $sql_loan);
mysqli_stmt_bind_param($stmt_loan, 'is', $year, $employeeID);
mysqli_stmt_execute($stmt_loan);
$loan_result = mysqli_stmt_get_result($stmt_loan);


$monthly_data = [];
$type_totals = array_fill(1, 5, 0); 

while ($row = mysqli_fetch_assoc($result)) {
    $month = $row['month'];
    $monthly_data[$month] = [
        'modal_syer' => $row['modal_syer'],
        'modal_yuran' => $row['modal_yuran'],
        'simpanan_tetap' => $row['simpanan_tetap'],
        'tabung_kebajikan' => $row['tabung_kebajikan'],
        'wang_deposit' => $row['wang_deposit'],
        'total' => $row['modal_syer'] + $row['modal_yuran'] + $row['simpanan_tetap'] + 
                  $row['tabung_kebajikan'] + $row['wang_deposit']
    ];
    

    $type_totals[1] += $row['modal_syer'];
    $type_totals[2] += $row['modal_yuran'];
    $type_totals[3] += $row['simpanan_tetap'];
    $type_totals[4] += $row['tabung_kebajikan'];
    $type_totals[5] += $row['wang_deposit'];
}


$year_total = array_sum($type_totals);

function formatNumber($number) {
    return str_pad($number, 4, '0', STR_PAD_LEFT);
}


$malay_months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Mac', 4 => 'April',
    5 => 'Mei', 6 => 'Jun', 7 => 'Julai', 8 => 'Ogos',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Disember'
];

?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Penyata Tahunan</h2>
        <div class="d-flex gap-2 no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-2"></i>Cetak
            </button>
            <a href="monthly_statements.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="statement-card">
        <!-- Header Section -->
        <div class="statement-header text-center">
            <img src="img/kadalogo.jpg" alt="KADA Logo" class="mb-3">
            <h2 class="mb-2">Koperasi Kakitangan Kada Kelantan Berhad</h2>
            <h4 class="mb-3">PENYATA KEWANGAN TAHUNAN</h4>
            <h5><?php echo $year; ?></h5>
        </div>

        <!-- Member Info Section -->
        <div class="member-info-section">
            <div class="row">
                <div class="col-md-8">
                    <table class="info-table">
                        <tr>
                            <td class="label-column">No. Anggota</td>
                            <td class="separator">:</td>
                            <td><?php echo formatNumber($member['employeeID']); ?></td>
                        </tr>
                        <tr>
                            <td class="label-column">Nama</td>
                            <td class="separator">:</td>
                            <td><?php echo $member['memberName']; ?></td>
                        </tr>
                        <tr>
                            <td class="label-column">No. K/P</td>
                            <td class="separator">:</td>
                            <td><?php echo $member['ic']; ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-4">
                    <div class="print-date text-end">
                        Tarikh Cetak: <?php echo date('d/m/Y'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Yearly Summary Section -->
        <div class="financial-section">
            <h5 class="section-title">RINGKASAN TRANSAKSI BULANAN</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Bulan</th>
                            <th>Modal<br>Syer</th>
                            <th>Modal<br>Yuran</th>
                            <th>Simpanan<br>Tetap</th>
                            <th>Sumbangan<br>Tabung<br>Kebajikan</th>
                            <th>Wang<br>Deposit</th>
                            <th>Jumlah<br>(RM)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $months_with_data = array_keys($monthly_data);
                        sort($months_with_data); 
                        
                        foreach ($months_with_data as $month): 
                            $data = $monthly_data[$month];
                        ?>
                            <tr>
                                <td><?php echo $malay_months[$month]; ?></td>
                                <td class="text-end"><?php echo number_format($data['modal_syer'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($data['modal_yuran'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($data['simpanan_tetap'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($data['tabung_kebajikan'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($data['wang_deposit'], 2); ?></td>
                                <td class="text-end"><strong>
                                    RM <?php echo number_format($data['total'], 2); ?>
                                </strong></td>
                            </tr>
                        <?php endforeach; ?>
                        
                        
                        <tr class="table-light">
                            <td><strong>JUMLAH</strong></td>
                            <?php
                            for ($type = 1; $type <= 5; $type++) {
                                echo "<td class='text-end'><strong>RM " . 
                                     number_format($type_totals[$type], 2) . 
                                     "</strong></td>";
                            }
                            ?>
                            <td class="text-end"><strong>
                                RM <?php echo number_format($year_total, 2); ?>
                            </strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Loan Information Section -->
        <div class="financial-section">
            <h5 class="section-title">MAKLUMAT PINJAMAN</h5>
            <?php if (mysqli_num_rows($loan_result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Jenis Pinjaman</th>
                                <th>Jumlah Pinjaman</th>
                                <th>Bayaran Bulanan</th>
                                <th>Baki Pinjaman</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            while ($loan = mysqli_fetch_assoc($loan_result)): 
                                $currentBalance = $loan['amountRequested'] - $loan['total_paid'];
                            ?>
                                <tr>
                                    <td><?php echo $loan['loanType']; ?></td>
                                    <td class="text-end">RM <?php echo number_format($loan['amountRequested'], 2); ?></td>
                                    <td class="text-end">RM <?php echo number_format($loan['monthlyInstallments'], 2); ?></td>
                                    <td class="text-end">RM <?php echo number_format($currentBalance, 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data-message">
                    <p>Tiada pinjaman aktif</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer Section -->
        <div class="statement-footer">
            <p>Ini adalah cetakan komputer. Tandatangan tidak diperlukan.</p>
        </div>
    </div>
</div>

<style>
.statement-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
    padding: 30px;
    margin-bottom: 30px;
}

.statement-header img {
    height: 80px;
    margin-bottom: 20px;
}

.statement-header h2 {
    color: #2c3e50;
    font-size: 24px;
    font-weight: 600;
}

.statement-header h4 {
    color: #34495e;
    font-size: 20px;
}

.member-info-section {
    margin: 30px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
}

.info-table {
    width: 100%;
}

.info-table td {
    padding: 8px 0;
}

.label-column {
    width: 150px;
    font-weight: 500;
    color: #2c3e50;
}

.separator {
    width: 30px;
    text-align: center;
}

.print-date {
    color: #666;
    font-size: 14px;
}

.section-title {
    color: #2c3e50;
    font-size: 18px;
    font-weight: 600;
    padding-bottom: 10px;
    margin-bottom: 20px;
    border-bottom: 2px solid #eee;
}

.financial-section {
    margin-bottom: 30px;
}

.financial-details {
    background: #fff;
    border-radius: 10px;
    padding: 20px;
}

.statement-footer {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #eee;
    text-align: center;
    color: #666;
}

.table {
    margin-bottom: 0;
}

.table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.table td {
    vertical-align: middle;
}

.btn-primary {
    background: #4CAF50;
    border: none;
}

.btn-primary:hover {
    background: #45a049;
}

.no-data-message {
    text-align: center;
    padding: 30px;
    background: #f8f9fa;
    border-radius: 10px;
    color: #666;
}

.yearly-summary th {
    white-space: nowrap;
    font-size: 14px;
}

.yearly-total {
    font-weight: 600;
    background: #f8f9fa;
}

@media print {
    /* 基本设置 */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Times New Roman', Times, serif;
        color: #000 !important;
    }

    body {
        background: #fff;
        line-height: 1.4;
    }

    /* 页面设置 - 减少上边距 */
    @page {
        size: A4;
        margin: 1.5cm 2cm 2cm 2cm; /* 上右下左 */
    }

    /* 文档容器 */
    .container {
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 0;
    }

    /* 文档卡片 */
    .statement-card {
        box-shadow: none;
        border: none;
        padding: 0;
        margin: 0;
    }

    /* 文档头部 */
    .statement-header {
        text-align: center;
        margin-bottom: 15px;
        padding: 5px 0;
        border-bottom: 1px solid #000;
    }

    .statement-header img {
        height: 55px;
        margin-bottom: 8px;
    }

    .statement-header h2 {
        font-size: 13pt;
        font-weight: bold;
        margin: 3px 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .statement-header h4 {
        font-size: 12pt;
        font-weight: bold;
        margin: 3px 0;
    }

    .statement-header h5 {
        font-size: 11pt;
        margin-top: 2px;
    }

    /* 会员信息部分 */
    .member-info-section {
        margin: 10px 0;
        padding: 0;
        background: none;
    }

    .info-table {
        width: 100%;
        margin-bottom: 10px;
    }

    .info-table td {
        padding: 2px 0;
        font-size: 11pt;
    }

    /* 财务部分 */
    .financial-section {
        margin: 15px 0;
        page-break-inside: avoid;
    }

    .section-title {
        font-size: 12pt;
        font-weight: bold;
        margin-bottom: 10px;
        padding-bottom: 3px;
        border-bottom: 1px solid #000;
    }

    /* 表格样式 */
    .table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    .table th,
    .table td {
        border: 1px solid #000;
        padding: 4px 6px;
        font-size: 10pt;
    }

    .table th {
        background-color: #f5f5f5 !important;
        -webkit-print-color-adjust: exact;
        font-weight: bold;
    }

    /* 年度总结表格特殊样式 */
    .yearly-summary th {
        white-space: nowrap;
        font-size: 10pt;
        text-align: center;
    }

    .yearly-total td {
        font-weight: bold;
        border-top: 2px solid #000;
    }

    /* 金额样式 */
    .detail-amount,
    .transaction-amount {
        font-family: 'Courier New', Courier, monospace;
        text-align: right;
    }

    /* 隐藏网页元素 */
    .no-print,
    .btn,
    .navbar,
    header,
    footer,
    .d-flex.justify-content-between {
        display: none !important;
    }

    /* 表格行样式 */
    .table tr:nth-child(even) {
        background-color: #fafafa !important;
        -webkit-print-color-adjust: exact;
    }

    /* 页脚 */
    .statement-footer {
        margin-top: 20px;
        padding-top: 10px;
        border-top: 1px solid #000;
        text-align: center;
        font-size: 9pt;
        page-break-inside: avoid;
    }

    /* 确保表格不分页 */
    table { 
        page-break-inside: avoid; 
    }

    tr { 
        page-break-inside: avoid; 
    }
}
</style>