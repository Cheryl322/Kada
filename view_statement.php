<?php
session_start();
include "dbconnect.php";
include "headermember.php";

if (!isset($_SESSION['employeeID']) || !isset($_GET['month']) || !isset($_GET['year'])) {
    header("Location: monthly_statements.php");
    exit();
}

$employeeID = $_SESSION['employeeID'];
$month = $_GET['month'];
$year = $_GET['year'];

// 获取会员信息
$sql_member = "SELECT m.*, f.* 
               FROM tb_member m
               LEFT JOIN tb_memberregistration_feesandcontribution f ON m.employeeID = f.employeeID
               WHERE m.employeeID = ?";
$stmt_member = mysqli_prepare($conn, $sql_member);
mysqli_stmt_bind_param($stmt_member, 's', $employeeID);
mysqli_stmt_execute($stmt_member);
$member = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_member));


// 修改主查询，添加 CASE 语句来显示正确的名称
$sql = "SELECT d.Deduct_date as transDate, 
        CASE 
            WHEN dt.DeducType_ID = 7 THEN 'Fee Masuk'
            WHEN dt.DeducType_ID = 1 THEN 'Modal Syer'
            WHEN dt.DeducType_ID = 2 THEN 'Modal Yuran'
            WHEN dt.DeducType_ID = 3 THEN 'Simpanan Tetap'
            WHEN dt.DeducType_ID = 4 THEN 'Sumbangan Tabung Kebajikan (AL-ABRAR)'
            WHEN dt.DeducType_ID = 5 THEN 'Wang Deposit Anggota'
            ELSE dt.typeName 
        END as transType,
        d.Deduct_Amt as transAmt 
        FROM tb_deduction d
        JOIN tb_deduction_type dt ON d.DeducType_ID = dt.DeducType_ID
        WHERE d.employeeID = ? 
        AND MONTH(d.Deduct_date) = ? 
        AND YEAR(d.Deduct_date) = ?
        ORDER BY d.Deduct_date DESC";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, 'sii', $employeeID, $month, $year);
    if (!mysqli_stmt_execute($stmt)) {
        echo "<!-- Execute failed: " . mysqli_stmt_error($stmt) . " -->";
    }
    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        echo "<!-- Get result failed: " . mysqli_error($conn) . " -->";
    }
} else {
    echo "<!-- Prepare failed: " . mysqli_error($conn) . " -->";
}

$totalAmount = 0;
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $totalAmount += $row['transAmt'];
    }

    mysqli_data_seek($result, 0);
}


function formatNumber($number) {
    return str_pad($number, 4, '0', STR_PAD_LEFT);
}


$sql_loan = "SELECT 
    l.loanType,
    la.amountRequested,
    la.monthlyInstallments
FROM tb_loan l
JOIN tb_loanapplication la ON l.employeeID = la.employeeID 
WHERE l.employeeID = ?
AND la.loanStatus = 'Diluluskan'";

$stmt_loan = mysqli_prepare($conn, $sql_loan);
mysqli_stmt_bind_param($stmt_loan, 's', $employeeID);
mysqli_stmt_execute($stmt_loan);
$loan_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_loan));

if ($loan_data) {
    $totalLoanAmount = $loan_data['amountRequested'];
    
    // 获取到指定月份为止的所有还款记录
    $sql_payments = "SELECT SUM(d.Deduct_Amt) as total_paid
                    FROM tb_deduction d
                    JOIN tb_deduction_type dt ON d.DeducType_ID = dt.DeducType_ID
                    WHERE d.employeeID = ? 
                    AND dt.typeName = 'Loan Payment'
                    AND (
                        YEAR(d.Deduct_date) < ? 
                        OR (YEAR(d.Deduct_date) = ? AND MONTH(d.Deduct_date) <= ?)
                    )";

    $stmt_payments = mysqli_prepare($conn, $sql_payments);
    mysqli_stmt_bind_param($stmt_payments, 'siii', $employeeID, $year, $year, $month);
    mysqli_stmt_execute($stmt_payments);
    $payments = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_payments));
    
    $total_paid = $payments['total_paid'] ?? 0;
    $currentBalance = $totalLoanAmount - $total_paid;
    
   
}

// 添加调试日志
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 修改储蓄金额查询，修复参数绑定的类型和数量
$sql_savings = "SELECT 
    (SELECT SUM(Deduct_Amt) FROM tb_deduction 
     WHERE employeeID = ? 
     AND DeducType_ID = 1
     AND (YEAR(Deduct_date) < ? OR (YEAR(Deduct_date) = ? AND MONTH(Deduct_date) <= ?))
    ) as modalShare,
    (SELECT SUM(Deduct_Amt) FROM tb_deduction 
     WHERE employeeID = ? 
     AND DeducType_ID = 2
     AND (YEAR(Deduct_date) < ? OR (YEAR(Deduct_date) = ? AND MONTH(Deduct_date) <= ?))
    ) as feeCapital,
    (SELECT SUM(Deduct_Amt) FROM tb_deduction 
     WHERE employeeID = ? 
     AND DeducType_ID = 4
     AND (YEAR(Deduct_date) < ? OR (YEAR(Deduct_date) = ? AND MONTH(Deduct_date) <= ?))
    ) as contribution,
    (SELECT SUM(Deduct_Amt) FROM tb_deduction 
     WHERE employeeID = ? 
     AND DeducType_ID = 3
     AND (YEAR(Deduct_date) < ? OR (YEAR(Deduct_date) = ? AND MONTH(Deduct_date) <= ?))
    ) as fixedDeposit,
    (SELECT SUM(Deduct_Amt) FROM tb_deduction 
     WHERE employeeID = ? 
     AND DeducType_ID = 5
     AND (YEAR(Deduct_date) < ? OR (YEAR(Deduct_date) = ? AND MONTH(Deduct_date) <= ?))
    ) as deposit
FROM dual";

$stmt_savings = mysqli_prepare($conn, $sql_savings);

// 绑定参数，每组4个参数：employeeID, year, year, month
$bind_params = array();
$types = str_repeat('siis', 5); // 5个查询，每个查询4个参数
for ($i = 0; $i < 5; $i++) {
    $bind_params[] = $employeeID;
    $bind_params[] = $year;
    $bind_params[] = $year;
    $bind_params[] = $month;
}

// 使用 call_user_func_array 来绑定参数
$bind_names[] = $types;
for ($i = 0; $i < count($bind_params); $i++) {
    $bind_name = 'bind' . $i;
    $$bind_name = $bind_params[$i];
    $bind_names[] = &$$bind_name;
}

call_user_func_array(array($stmt_savings, 'bind_param'), $bind_names);
mysqli_stmt_execute($stmt_savings);
$savings = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_savings));

// 添加调试输出
echo "<!-- Debug Info:\n";
echo "Employee ID: " . $employeeID . "\n";
echo "Month: " . $month . "\n";
echo "Year: " . $year . "\n";
echo "Savings Data: " . print_r($savings, true) . "\n";
echo "-->";

// 使用 null 合并运算符，但不设置默认值
$savings['modalShare'] = $savings['modalShare'] ?? 0;    
$savings['feeCapital'] = $savings['feeCapital'] ?? 0;     
$savings['contribution'] = $savings['contribution'] ?? 0;  
$savings['fixedDeposit'] = $savings['fixedDeposit'] ?? 0; 
$savings['deposit'] = $savings['deposit'] ?? 0;           

?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Penyata Kewangan</h2>
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
            <h4 class="mb-3">PENYATA KEWANGAN BULANAN</h4>
            <h5><?php echo strtoupper(date('F Y', mktime(0, 0, 0, $month, 1, $year))); ?></h5>
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

        <!-- Financial Summary Section -->
        <div class="financial-section">
            <h5 class="section-title">MAKLUMAT SIMPANAN</h5>
            <div class="financial-details">
                <div class="row">
                    <div class="col-md-8"> 
                        <table class="table table-borderless financial-table">
                            <tr>
                                <td style="width: 300px;">Modal Syer</td>
                                <td style="width: 30px;">:</td>
                                <td style="width: 150px;">RM <?php echo number_format($savings['modalShare'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>Modal Yuran</td>
                                <td>:</td>
                                <td>RM <?php echo number_format($savings['feeCapital'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>Sumbangan Tabung Kebajikan (AL-ABRAR)</td>
                                <td>:</td>
                                <td>RM <?php echo number_format($savings['contribution'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>Simpanan Tetap</td>
                                <td>:</td>
                                <td>RM <?php echo number_format($savings['fixedDeposit'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>Wang Deposit Anggota</td>
                                <td>:</td>
                                <td>RM <?php echo number_format($savings['deposit'], 2); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Jumlah</strong></td>
                                <td><strong>:</strong></td>
                                <td><strong>RM <?php echo number_format(
                                    $savings['modalShare'] + 
                                    $savings['feeCapital'] + 
                                    $savings['contribution'] + 
                                    $savings['deposit'] +
                                    $savings['fixedDeposit'], 
                                    2); ?></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loan Information Section -->
        <div class="financial-section">
            <h5 class="section-title">MAKLUMAT PINJAMAN</h5>
            <div class="financial-details">
                <div class="row">
                    <div class="col-md-8"> 
                        <table class="table table-borderless financial-table">
                            <tr>
                                <td style="width: 300px;">Jenis Pinjaman</td>
                                <td style="width: 30px;">:</td>
                                <td style="width: 150px;"><?php echo $loan_data['loanType'] ?? '-'; ?></td>
                            </tr>
                            <tr>
                                <td>Jumlah Pinjaman</td>
                                <td>:</td>
                                <td>RM <?php echo number_format($totalLoanAmount ?? 0, 2); ?></td>
                            </tr>
                            <tr>
                                <td>Baki Pinjaman</td>
                                <td>:</td>
                                <td>RM <?php echo number_format($currentBalance ?? 0, 2); ?></td>
                            </tr>
                            <tr>
                                <td>Bayaran Bulanan</td>
                                <td>:</td>
                                <td>RM <?php echo number_format($loan_data['monthlyInstallments'] ?? 0, 2); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Transactions Section -->
        <div class="financial-section">
            <h5 class="section-title">BUTIRAN TRANSAKSI BULANAN</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Tarikh</th>
                            <th>Jenis Transaksi</th>
                            <th class="text-end">Amaun (RM)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if (mysqli_num_rows($result) > 0): 
                            while ($row = mysqli_fetch_assoc($result)): 
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo isset($row['transDate']) ? date('d/m/Y', strtotime($row['transDate'])) : '-'; ?></td>
                                <td><?php echo $row['transType'] ?? '-'; ?></td>
                                <td class="text-end"><?php echo isset($row['transAmt']) ? number_format($row['transAmt'], 2) : '0.00'; ?></td>
                            </tr>
                        <?php 
                            endwhile;
                            if ($totalAmount > 0):
                        ?>
                            <tr class="table-light">
                                <td colspan="3"><strong>JUMLAH</strong></td>
                                <td class="text-end"><strong>RM <?php echo number_format($totalAmount, 2); ?></strong></td>
                            </tr>
                        <?php 
                            endif;
                        else: 
                        ?>
                            <tr>
                                <td colspan="4" class="text-center">Tiada transaksi untuk bulan ini</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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

.statement-header h5 {
    color: #34495e;
    font-size: 18px;
    font-weight: 400;
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
        margin: 1.5cm 2cm 2cm 2cm; /* 上右下左 - 减少上边距 */
    }

    /* 文档容器 - 移除额外padding */
    .container {
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 0;
    }

    /* 文档卡片 - 移除额外空间 */
    .statement-card {
        box-shadow: none;
        border: none;
        padding: 0;
        margin: 0;
    }

    /* 文档头部 - 进一步压缩 */
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

    /* 会员信息部分 - 减少间距 */
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

    /* 财务部分 - 优化间距 */
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

    /* 表格样式 - 压缩行高 */
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

    /* 隐藏网页元素 */
    .no-print,
    .btn,
    .navbar,
    header,
    footer,
    .d-flex.justify-content-between {
        display: none !important;
    }

    /* 金额样式 */
    .detail-amount,
    .transaction-amount {
        font-family: 'Courier New', Courier, monospace;
        font-weight: normal;
    }

    /* 表格行样式 */
    .table tr:nth-child(even) {
        background-color: #fafafa !important;
        -webkit-print-color-adjust: exact;
    }

    /* 总计行样式 */
    .yearly-total td,
    .table tr.total td {
        font-weight: bold;
        border-top: 2px solid #000;
    }
}

/* 屏幕显示样式保持不变
// ...existing code... */
</style>

<!-- 添加调试信息 -->
<?php
echo "<!-- DEBUG Info:\n";
echo "Month: $month\n";
echo "Year: $year\n";
echo "Total Loan: $totalLoanAmount\n";
echo "Total Paid: $total_paid\n";
echo "Current Balance: $currentBalance\n";
echo "-->";
?>