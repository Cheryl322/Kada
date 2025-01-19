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

// 获取月度交易记录
$sql_trans = "SELECT * FROM tb_transaction 
              WHERE employeeID = ? 
              AND MONTH(transDate) = ? 
              AND YEAR(transDate) = ?
              ORDER BY transDate";
$stmt_trans = mysqli_prepare($conn, $sql_trans);
mysqli_stmt_bind_param($stmt_trans, 'sii', $employeeID, $month, $year);
mysqli_stmt_execute($stmt_trans);
$transactions = mysqli_fetch_all(mysqli_stmt_get_result($stmt_trans), MYSQLI_ASSOC);

// 计算总额
$total_transactions = 0;
foreach($transactions as $trans) {
    $total_transactions += $trans['transAmt'];
}

// 辅助函数：格式化数字为4位
function formatNumber($number) {
    return str_pad($number, 4, '0', STR_PAD_LEFT);
}

// 获取贷款信息
$sql_loan = "SELECT 
    la.*,
    l.loanID,
    COALESCE(l.balance, la.amountRequested) as balance,
    COALESCE(l.loanType, 'Unknown') as loanType,
    la.amountRequested,
    la.monthlyInstallments
FROM tb_loanapplication la
LEFT JOIN tb_loan l ON l.loanApplicationID = la.loanApplicationID
WHERE la.employeeID = ? 
    AND la.loanStatus = 'Diluluskan'
ORDER BY la.loanApplicationID DESC 
LIMIT 1";

$stmt_loan = mysqli_prepare($conn, $sql_loan);
mysqli_stmt_bind_param($stmt_loan, 's', $employeeID);
mysqli_stmt_execute($stmt_loan);
$loan_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_loan));
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
                                <td>: RM <?php echo number_format($member['modalShare'] ?? 0, 2); ?></td>
                            </tr>
                            <tr>
                                <td>ModalYuran</td>
                                <td>: RM <?php echo number_format($member['feeCapital'] ?? 0, 2); ?></td>
                            </tr>
                            <tr>
                                <td>Tabung Anggota</td>
                                <td>: RM <?php echo number_format($member['contribution'] ?? 0, 2); ?></td>
                            </tr>
                            <tr>
                                <td>Simpanan Tetap</td>
                                <td>: RM <?php echo number_format($member['fixedDeposit'] ?? 0, 2); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Jumlah</strong></td>
                                <td><strong>: RM <?php echo number_format(
                                    ($member['modalShare'] ?? 0) + 
                                    ($member['feeCapital'] ?? 0) + 
                                    ($member['contribution'] ?? 0) + 
                                    ($member['fixedDeposit'] ?? 0), 
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
                            <?php if ($loan_data): ?>
                                <tr>
                                    <td width="150">Jenis Pinjaman</td>
                                    <td>: <?php echo $loan_data['loanType']; ?></td>
                                </tr>
                                <tr>
                                    <td>Jumlah Pinjaman</td>
                                    <td>: RM <?php echo number_format($loan_data['amountRequested'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td>Baki Pinjaman</td>
                                    <td>: RM <?php echo number_format($loan_data['balance'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td>Bayaran Bulanan</td>
                                    <td>: RM <?php echo number_format($loan_data['monthlyInstallments'], 2); ?></td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2">Tiada pinjaman aktif</td>
                                </tr>
                            <?php endif; ?>
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
                            <?php if (count($transactions) > 0): ?>
                                <?php foreach($transactions as $index => $trans): ?>
                                <tr>
                                    <td><?php echo formatNumber($index + 1); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($trans['transDate'])); ?></td>
                                    <td><?php echo $trans['transType']; ?></td>
                                    <td class="text-end"><?php echo number_format($trans['transAmt'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="table-light">
                                    <td colspan="3"><strong>JUMLAH</strong></td>
                                    <td class="text-end"><strong>RM <?php echo number_format($total_transactions, 2); ?></strong></td>
                                </tr>
                            <?php else: ?>
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