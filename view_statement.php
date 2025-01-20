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

// 获取会员的第一次付款日期
$sql_first_payment = "SELECT MIN(transDate) as first_payment 
                     FROM tb_transaction 
                     WHERE employeeID = ?";
$stmt_first = mysqli_prepare($conn, $sql_first_payment);
mysqli_stmt_bind_param($stmt_first, 's', $employeeID);
mysqli_stmt_execute($stmt_first);
$first_payment = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_first))['first_payment'];

// 检查当前选择的月份是否是第一次付款的月份
$is_first_month = false;
if ($first_payment) {
    $first_payment_month = date('m', strtotime($first_payment));
    $first_payment_year = date('Y', strtotime($first_payment));
    $is_first_month = ($month == $first_payment_month && $year == $first_payment_year);
}

// 修改主查询
$sql = "SELECT t.transDate, t.transType, t.transAmt 
        FROM tb_transaction t
        WHERE t.employeeID = ? 
        AND MONTH(t.transDate) = ? 
        AND YEAR(t.transDate) = ?";

// 如果不是第一个月，排除 entry fee 和 deposit
if (!$is_first_month) {
    $sql .= " AND t.transType NOT IN ('Entry Fee', 'Deposit')";
}

$sql .= " ORDER BY t.transDate DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'sii', $employeeID, $month, $year);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// 计算总额
$totalAmount = 0;
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $totalAmount += $row['transAmt'];
    }
    // 重置结果集指针
    mysqli_data_seek($result, 0);
}

// 辅助函数：格式化数字为4位
function formatNumber($number) {
    return str_pad($number, 4, '0', STR_PAD_LEFT);
}

// 获取贷款信息
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
    $sql_payments = "SELECT SUM(transAmt) as total_paid
                    FROM tb_transaction 
                    WHERE employeeID = ? 
                    AND transType = 'Bayaran Ba'
                    AND (
                        YEAR(transDate) < ? 
                        OR (YEAR(transDate) = ? AND MONTH(transDate) <= ?)
                    )";

    $stmt_payments = mysqli_prepare($conn, $sql_payments);
    mysqli_stmt_bind_param($stmt_payments, 'siii', $employeeID, $year, $year, $month);
    mysqli_stmt_execute($stmt_payments);
    $payments = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_payments));
    
    $total_paid = $payments['total_paid'] ?? 0;
    $currentBalance = $totalLoanAmount - $total_paid;
    
   
}

// 获取到选定月份为止的累计储蓄金额
$sql_savings = "SELECT 
    SUM(CASE WHEN transType = 'Simpanan-M' THEN transAmt ELSE 0 END) as modalShare,
    SUM(CASE WHEN transType = 'Simpanan-Y' THEN transAmt ELSE 0 END) as feeCapital,
    SUM(CASE WHEN transType = 'Simpanan-T' THEN transAmt ELSE 0 END) as contribution,
    SUM(CASE WHEN transType = 'Simpanan-S' THEN transAmt ELSE 0 END) as fixedDeposit
FROM tb_transaction 
WHERE employeeID = ? 
AND (
    YEAR(transDate) < ? 
    OR (YEAR(transDate) = ? AND MONTH(transDate) <= ?)
)
AND transType IN ('Simpanan-M', 'Simpanan-Y', 'Simpanan-T', 'Simpanan-S')";

$stmt_savings = mysqli_prepare($conn, $sql_savings);
mysqli_stmt_bind_param($stmt_savings, 'siii', $employeeID, $year, $year, $month);
mysqli_stmt_execute($stmt_savings);
$savings = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_savings));
?>

<div class="container mt-5">
    <div class="mb-4 no-print">
        <a href="monthly_statements.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Cetak
        </button>
    </div>
    
    <div class="card">
        <div class="card-body">
            <!-- Logo 和报表头部 -->
            <div class="text-center mb-4">
                <img src="img/kadalogo.jpg" alt="KADA Logo" class="mb-3" style="height: 80px;">
                <h2 class="mb-2">Koperasi Kakitangan Kada Kelantan Berhad</h2>
                <h4 class="mb-3">PENYATA KEWANGAN BULANAN</h4>
                <h5><?php echo strtoupper(date('F Y', mktime(0, 0, 0, $month, 1, $year))); ?></h5>
            </div>
            
            <!-- 会员信息 -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>No. Anggota</strong></td>
                            <td>: <?php echo formatNumber($member['employeeID']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Nama</strong></td>
                            <td>: <?php echo $member['memberName']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>No. K/P</strong></td>
                            <td>: <?php echo $member['ic']; ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-1">Tarikh Cetak: <?php echo date('d/m/Y'); ?></p>
                </div>
            </div>

            <!-- 储蓄信息 -->
            <div class="mb-4">
                <h5 class="border-bottom pb-2">MAKLUMAT SIMPANAN</h5>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="150">Modal Saham</td>
                                <td>: RM <?php echo number_format($savings['modalShare'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>ModalYuran</td>
                                <td>: RM <?php echo number_format($savings['feeCapital'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>Tabung Anggota</td>
                                <td>: RM <?php echo number_format($savings['contribution'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>Simpanan Tetap</td>
                                <td>: RM <?php echo number_format($savings['fixedDeposit'], 2); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Jumlah</strong></td>
                                <td><strong>: RM <?php echo number_format(
                                    $savings['modalShare'] + 
                                    $savings['feeCapital'] + 
                                    $savings['contribution'] + 
                                    $savings['fixedDeposit'], 
                                    2); ?></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 贷款信息 -->
            <div class="mb-4">
                <h5 class="border-bottom pb-2">MAKLUMAT PINJAMAN</h5>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="150">Jenis Pinjaman</td>
                                <td>: <?php echo $loan_data['loanType'] ?? '-'; ?></td>
                            </tr>
                            <tr>
                                <td>Jumlah Pinjaman</td>
                                <td>: RM <?php echo number_format($totalLoanAmount ?? 0, 2); ?></td>
                            </tr>
                            <tr>
                                <td>Baki Pinjaman</td>
                                <td>: RM <?php echo number_format($currentBalance ?? 0, 2); ?></td>
                            </tr>
                            <tr>
                                <td>Bayaran Bulanan</td>
                                <td>: RM <?php echo number_format($loan_data['monthlyInstallments'] ?? 0, 2); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            
            <div class="mb-4">
                <h5 class="border-bottom pb-2">BUTIRAN TRANSAKSI BULANAN</h5>
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

            <!-- 页脚 -->
            <div class="mt-5 pt-4 border-top">
                <p class="text-center mb-0">Ini adalah cetakan komputer. Tandatangan tidak diperlukan.</p>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        /* 隐藏顶部导航和标题 */
        nav,
        header,
        .navbar,
        .nav,
        .header,
        #header,
        .kada-header,
        .header-section {
            display: none !important;
        }

        /* 移除页面顶部空白 */
        body {
            margin: 0 !important;
            padding: 0 !important;
        }

        .container {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        /* 确保内容从页面顶部开始 */
        .card {
            margin-top: 0 !important;
            border: none !important;
            box-shadow: none !important;
        }

        /* 其他打印样式保持不变... */
        .no-print { 
            display: none !important; 
        }
        
        /* 确保内容区域占满整个页面宽度 */
        .container { 
            width: 100% !important;
            max-width: none !important;
            padding: 20px 40px !important;
        }
    }
    .card-title {
        color: inherit;
        font-weight: bold;
    }
    .text-end {
        text-align: right;
    }
    .table-borderless td {
        padding: 4px 0;
    }
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