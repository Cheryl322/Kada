<?php
session_start();

if (!isset($_SESSION['employeeID'])) {
    header("Location: login.php");
    exit();
}

include "dbconnect.php";
include "headermember.php";
$employeeID = $_SESSION['employeeID'];

// 获取账号信息
$sqlBank = "SELECT accountNo FROM tb_bank WHERE employeeID = ? ORDER BY bankID DESC LIMIT 1";
$stmt = mysqli_prepare($conn, $sqlBank);
mysqli_stmt_bind_param($stmt, "s", $employeeID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
$accountNo = $row ? $row['accountNo'] : '-';
$lastUpdate = date('d M Y, h:i A');

// 获取各类型储蓄的最新总额
$sql_savings = "SELECT 
    (SELECT SUM(Deduct_Amt) FROM tb_deduction WHERE employeeID = ? AND DeducType_ID = 1) as modal_saham,
    (SELECT SUM(Deduct_Amt) FROM tb_deduction WHERE employeeID = ? AND DeducType_ID = 2) as modal_yuran,
    (SELECT SUM(Deduct_Amt) FROM tb_deduction WHERE employeeID = ? AND DeducType_ID = 3) as simpanan_tetap,
    (SELECT SUM(Deduct_Amt) FROM tb_deduction WHERE employeeID = ? AND DeducType_ID = 4) as tabung_anggota,
    (SELECT SUM(Deduct_Amt) FROM tb_deduction WHERE employeeID = ? AND DeducType_ID = 5) as wang_deposit";

$stmt_savings = mysqli_prepare($conn, $sql_savings);
mysqli_stmt_bind_param($stmt_savings, 'sssss', $employeeID, $employeeID, $employeeID, $employeeID, $employeeID);
mysqli_stmt_execute($stmt_savings);
$savings = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_savings));

// 计算总储蓄
$totalSavings = array_sum($savings);

// 获取贷款信息
$sql_loans = "SELECT 
    l.loanType,
    l.balance,
    la.amountRequested,
    COALESCE((
        SELECT SUM(d.Deduct_Amt)
        FROM tb_deduction d
        WHERE d.employeeID = l.employeeID 
        AND d.DeducType_ID = 6
    ), 0) as total_repaid
FROM tb_loan l
JOIN tb_loanapplication la ON l.employeeID = la.employeeID 
WHERE l.employeeID = ?
AND la.loanStatus = 'Diluluskan'";

$stmt_loans = mysqli_prepare($conn, $sql_loans);
mysqli_stmt_bind_param($stmt_loans, 's', $employeeID);
mysqli_stmt_execute($stmt_loans);
$loans_result = mysqli_stmt_get_result($stmt_loans);

// 初始化贷款变量
$totalAllLoans = 0;
$loanTypes = [];

while ($loan = mysqli_fetch_assoc($loans_result)) {
    $totalAllLoans += $loan['amountRequested'];
    $loanTypes[] = $loan['loanType'];
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Penyata Kewangan</h2>
    </div>

    <!-- Navigation Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="nav-card">
                <a href="transaction_history.php" class="nav-card-content">
                    <div class="nav-card-icon history">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="nav-card-details">
                        <h3 class="nav-card-title">Rekod Transaksi</h3>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="nav-card">
                <a href="monthly_statements.php" class="nav-card-content">
                    <div class="nav-card-icon monthly">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="nav-card-details">
                        <h3 class="nav-card-title">Penyata Bulanan</h3>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="nav-card">
                <a href="financial_statement.php" class="nav-card-content">
                    <div class="nav-card-icon yearly">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div class="nav-card-details">
                        <h3 class="nav-card-title">Penyata Kewangan</h3>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Summary Cards -->
    <div class="row mb-4">
        <!-- Total Savings Card -->
        <div class="col-md-6 mb-3">
            <div class="summary-card">
                <div class="summary-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="summary-details">
                    <h3>Jumlah Simpanan</h3>
                    <h2 class="amount">RM <?php echo number_format($totalSavings, 2); ?></h2>
                    <div class="additional-info">
                        <span class="info-item">
                            <i class="fas fa-university me-1"></i>
                            No. Akaun: <?php echo $accountNo; ?>
                        </span>
                        <span class="info-item">
                            <i class="fas fa-clock me-1"></i>
                            Kemas kini: <?php echo $lastUpdate; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Loans Card -->
        <div class="col-md-6 mb-3">
            <div class="summary-card">
                <div class="summary-icon loans">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div class="summary-details">
                    <h3>Jumlah Pinjaman</h3>
                    <h2 class="amount">RM <?php echo number_format($totalAllLoans, 2); ?></h2>
                    <div class="additional-info">
                        <span class="info-item">
                            <i class="fas fa-tag me-1"></i>
                            <?php echo !empty($loanTypes) ? implode(' • ', $loanTypes) : 'Tiada pinjaman aktif'; ?>
                        </span>
                        <span class="info-item">
                            <i class="fas fa-clock me-1"></i>
                            Kemas kini: <?php echo $lastUpdate; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Section -->
    <div class="row">
        <!-- Savings Details -->
        <div class="col-md-6 mb-4">
            <div class="details-card">
                <h3 class="details-title">
                    <i class="fas fa-piggy-bank me-2"></i>
                    Butiran Simpanan
                </h3>
                <?php foreach ($savings as $type => $amount): ?>
                <div class="detail-item">
                    <span class="detail-label"><?php echo ucwords(str_replace('_', ' ', $type)); ?></span>
                    <span class="detail-amount">RM <?php echo number_format($amount ?? 0, 2); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Loans Details -->
        <div class="col-md-6 mb-4">
            <div class="details-card">
                <h3 class="details-title">
                    <i class="fas fa-file-invoice-dollar me-2"></i>
                    Butiran Pinjaman
                </h3>
                <?php 
                mysqli_data_seek($loans_result, 0);
                while ($loan = mysqli_fetch_assoc($loans_result)): 
                    $remaining_amount = $loan['amountRequested'] - $loan['total_repaid'];
                ?>
                <div class="detail-item">
                    <span class="detail-label"><?php echo $loan['loanType']; ?></span>
                    <span class="detail-amount">
                        RM <?php echo number_format($remaining_amount, 2); ?> / 
                        RM <?php echo number_format($loan['amountRequested'], 2); ?>
                    </span>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<style>
.summary-card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: flex-start;
    transition: all 0.3s ease;
    height: 100%;
}

.summary-icon {
    background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
}

.summary-icon.loans {
    background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
}

.summary-icon i {
    font-size: 24px;
    color: white;
}

.details-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
    height: 100%;
}

.details-title {
    font-size: 18px;
    color: #2c3e50;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}

.detail-label {
    color: #707070;
    font-weight: 500;
}

.detail-amount {
    font-weight: 600;
    color: #2c3e50;
}

.additional-info {
    margin-top: 8px;
    font-size: 12px;
    color: #666;
}

.info-item {
    display: block;
    margin-top: 4px;
}

.nav-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    height: 100%;
}

.nav-card-content {
    display: flex;
    align-items: center;
    padding: 20px;
    text-decoration: none;
    color: #2c3e50;
}

.nav-card-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
}

.nav-card-icon.history {
    background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
}

.nav-card-icon.monthly {
    background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
}

.nav-card-icon.yearly {
    background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);
}

.nav-card-icon i {
    font-size: 24px;
    color: white;
}

.nav-card-title {
    font-size: 16px;
    font-weight: 500;
    margin: 0;
}

.nav-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.btn-secondary {
    background-color: #6c757d;
    border: none;
    padding: 8px 20px;
    border-radius: 5px;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background-color: #5a6268;
    transform: translateY(-1px);
}
</style>