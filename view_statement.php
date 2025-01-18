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

// 获取会员信息 - 简化查询
$sql_member = "SELECT * FROM tb_employee WHERE employeeID = ?";
$stmt_member = mysqli_prepare($conn, $sql_member);
mysqli_stmt_bind_param($stmt_member, 's', $employeeID);
mysqli_stmt_execute($stmt_member);
$member = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_member));

// 获取月度交易记录
$sql = "SELECT t.transDate, t.transType, t.transAmt
        FROM tb_transaction t
        WHERE t.employeeID = ? 
        AND MONTH(t.transDate) = ?
        AND YEAR(t.transDate) = ?
        ORDER BY t.transDate";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'sii', $employeeID, $month, $year);
mysqli_stmt_execute($stmt);
$transactions = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

// 计算月度总结
$total_amount = 0;
$total_contribution = 0;
$total_withdrawal = 0;
$latest_balance = 0;
$latest_account1 = 0;
$latest_account2 = 0;

foreach($transactions as $trans) {
    if($trans['transactionType'] == 'Caruman') {
        $total_contribution += $trans['amount'];
    } else {
        $total_withdrawal += $trans['amount'];
    }
    $total_amount += $trans['amount'];
    
    // 获取最新余额
    $latest_account1 = $trans['account1Balance'];
    $latest_account2 = $trans['account2Balance'];
}
$latest_balance = $latest_account1 + $latest_account2;

?>

<div class="container mt-5">
    <div class="mb-4">
        <a href="monthly_statements.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <h3 class="text-center mb-4">PENYATA KIRA-KIRA AHLI TAHUN <?php echo $year; ?></h3>
            
            <!-- 会员信息 -->
            <div class="mb-4">
                <div class="row">
                    <div class="col-12">
                        <p class="mb-1"><strong>No Ahli:</strong> <?php echo $member['employeeID']; ?></p>
                        <p class="mb-1"><strong>No Kad Pengenalan:</strong> <?php echo $member['ic']; ?></p>
                        <p class="mb-1 text-end">Tarikh: <?php echo date('d/m/Y'); ?></p>
                    </div>
                </div>
            </div>

            <!-- 月度总结 -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title">Ringkasan Bulan <?php echo date('F Y', mktime(0, 0, 0, $month, 1, $year)); ?></h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr>
                                        <td>Jumlah Caruman:</td>
                                        <td class="text-end">RM <?php echo number_format($total_contribution, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Jumlah Pengeluaran:</td>
                                        <td class="text-end">RM <?php echo number_format($total_withdrawal, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Baki Akaun 1:</td>
                                        <td class="text-end">RM <?php echo number_format($latest_account1, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Baki Akaun 2:</td>
                                        <td class="text-end">RM <?php echo number_format($latest_account2, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jumlah Baki:</strong></td>
                                        <td class="text-end"><strong>RM <?php echo number_format($latest_balance, 2); ?></strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 交易表格 -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Transaksi</th>
                            <th>Tarikh</th>
                            <th>Bulan Caruman</th>
                            <th>Jumlah (RM)</th>
                            <th colspan="3" class="text-center">Baki Simpanan (RM)</th>
                        </tr>
                        <tr>
                            <th colspan="4"></th>
                            <th>Akaun 1</th>
                            <th>Akaun 2</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($transactions as $trans): ?>
                            <tr>
                                <td><?php echo $trans['transactionType']; ?></td>
                                <td><?php echo date('d/m/y', strtotime($trans['transactionDate'])); ?></td>
                                <td><?php echo date('M-y', strtotime($trans['transactionDate'])); ?></td>
                                <td class="text-end"><?php echo number_format($trans['amount'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($trans['account1Balance'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($trans['account2Balance'], 2); ?></td>
                                <td class="text-end"><?php 
                                    echo number_format(
                                        $trans['account1Balance'] + $trans['account2Balance'], 
                                        2
                                    ); 
                                ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .table th {
        background-color: #f8f9fa;
        font-weight: bold;
    }
    .text-end {
        text-align: right;
    }
    .card-title {
        color: #2c3e50;
        font-weight: bold;
    }
    .table-sm td {
        padding: 0.5rem;
    }
</style> 