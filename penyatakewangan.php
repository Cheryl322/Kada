<?php
session_start();

if (!isset($_SESSION['employeeID'])) {
    header("Location: login.php");
    exit();
}

include "dbconnect.php";
include "headermember.php";
$employeeID = $_SESSION['employeeID'];

// Fetch financial data
$sql = "SELECT * FROM tb_memberregistration_feesandcontribution 
        WHERE employeeID = ?";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $employeeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $financialData = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

// 获取会员的存款总额
$sqlSavings = "SELECT 
    m.employeeID,
    m.memberName,
    b.accountNo,
    COALESCE(SUM(CASE 
        WHEN t.transType = 'deposit' THEN t.transAmt 
        WHEN t.transType = 'withdrawal' THEN -t.transAmt 
        ELSE 0 
    END), 0) as total_savings,
    MAX(t.transDate) as last_update
FROM tb_member m
LEFT JOIN tb_bank b ON m.employeeID = b.employeeID
LEFT JOIN tb_transaction t ON m.employeeID = t.employeeID
WHERE m.employeeID = ?
GROUP BY m.employeeID, m.memberName, b.accountNo";

$stmt = mysqli_prepare($conn, $sqlSavings);
mysqli_stmt_bind_param($stmt, "s", $employeeID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $totalSavings = $row['total_savings'];
    $accountNo = $row['accountNo'];
    $memberName = $row['memberName'];
    $lastUpdate = $row['last_update'] ? date('d M Y, h:i A', strtotime($row['last_update'])) : date('d M Y, h:i A');
} else {
    $totalSavings = 0;
    $accountNo = '-';
    $memberName = '-';
    $lastUpdate = date('d M Y, h:i A');
}

// 获取贷款信息并添加调试
$sql_loan = "SELECT la.*, l.balance, l.loanID, l.loanType 
             FROM tb_loanapplication la
             LEFT JOIN tb_loan l ON l.loanApplicationID = la.loanApplicationID
             WHERE la.employeeID = ? AND la.loanStatus = 'Diluluskan'
             ORDER BY la.loanApplicationID DESC LIMIT 1";

$stmt_loan = mysqli_prepare($conn, $sql_loan);
mysqli_stmt_bind_param($stmt_loan, 'i', $employeeID);
mysqli_stmt_execute($stmt_loan);
$loanData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_loan));

// 添加调试信息
error_log("Loan Data for employee " . $employeeID . ": " . print_r($loanData, true));

// 获取贷款信息
$sql_loan_details = "SELECT * FROM tb_loan 
                    WHERE employeeID = ? 
                    ORDER BY loanID DESC LIMIT 1";

$stmt_loan_details = mysqli_prepare($conn, $sql_loan_details);
$loan_details = null;
if ($stmt_loan_details) {
    mysqli_stmt_bind_param($stmt_loan_details, 'i', $employeeID);
    mysqli_stmt_execute($stmt_loan_details);
    $result_loan_details = mysqli_stmt_get_result($stmt_loan_details);
    if ($result_loan_details) {
        $loan_details = mysqli_fetch_assoc($result_loan_details);
    }
    mysqli_stmt_close($stmt_loan_details);
}

// 获取当前的 financialstatus
$sql = "SELECT f.* 
        FROM tb_financialstatus f
        WHERE f.employeeID = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $employeeID);
mysqli_stmt_execute($stmt);
$financialStatus = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// 如果没有记录，创建一个新记录，所有金额初始化为0
if (!$financialStatus) {
    $sql_insert = "INSERT INTO tb_financialstatus 
                   (employeeID, modalShare, feeCapital, contribution, fixedDeposit, dateUpdated)
                   VALUES (?, 0, 0, 0, 0, CURRENT_TIMESTAMP)";
    $stmt_insert = mysqli_prepare($conn, $sql_insert);
    mysqli_stmt_bind_param($stmt_insert, 'i', $employeeID);
    mysqli_stmt_execute($stmt_insert);
    
    // 重新获取刚创建的记录
    mysqli_stmt_execute($stmt);
    $financialStatus = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// 当管理员确认更新时（假设通过POST请求）
if (isset($_POST['confirm_update']) && $_POST['confirm_update'] == 1) {
    // 使用初始注册金额更新 financialstatus
    $sql_update = "UPDATE tb_financialstatus 
                   SET modalShare = ?,
                       feeCapital = ?,
                       contribution = ?,
                       fixedDeposit = ?,
                       dateUpdated = CURRENT_TIMESTAMP
                   WHERE employeeID = ?";
    
    $stmt_update = mysqli_prepare($conn, $sql_update);
    mysqli_stmt_bind_param($stmt_update, 'ddddi', 
        $initialAmount['modalShare'],
        $initialAmount['feeCapital'],
        $initialAmount['contribution'],
        $initialAmount['fixedDeposit'],
        $employeeID
    );
    mysqli_stmt_execute($stmt_update);
}

// 在文件最后关闭数据库连接
mysqli_close($conn);

function formatNumber($number) {
    return str_pad($number, 4, '0', STR_PAD_LEFT);
}
?>

<div class="mt-4 mb-4 ms-3">
        <a href="javascript:history.back()" class="btn btn-kembali">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
</div>

<div class="container mt-4">
    <h2 class="mb-4">Penyata Kewangan</h2>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <!-- Total Savings Card -->
        <div class="col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-piggy-bank me-2"></i>
                        <h5 class="card-title mb-0">Jumlah Simpanan</h5>
                    </div>
                    <h2 class="card-text mb-2">RM <?php echo number_format(
                        ($financialStatus['modalShare'] ?? 0) + 
                        ($financialStatus['feeCapital'] ?? 0) + 
                        ($financialStatus['fixedDeposit'] ?? 0) + 
                        ($financialStatus['contribution'] ?? 0), 
                        2); 
                    ?></h2>
                    <div class="small mt-auto">
                        No. Akaun: <?php echo $accountNo; ?><br>
                        Kemas kini terakhir: <?php echo date('d M Y, h:i A', strtotime($financialData['dateUpdated'] ?? 'now')); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Loan Card -->
        <div class="col-md-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-hand-holding-dollar me-2"></i>
                        <h5 class="card-title mb-0">Jumlah Pinjaman</h5>
                    </div>
                    <h2 class="card-text mb-2">RM <?php 
                        echo number_format($loanData['balance'] ?? 0, 2); 
                    ?></h2>
                    <div class="small mt-auto">
                        <?php if ($loanData): ?>
                            Jenis: <?php echo $loanData['loanType']; ?><br>
                            Tempoh: <?php echo $loanData['financingPeriod']; ?> bulan<br>
                            Bayaran Bulanan: RM <?php echo number_format($loanData['monthlyInstallments'], 2); ?>
                        <?php else: ?>
                            Tiada pinjaman aktif
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <a href="transaction_history.php" class="card text-decoration-none">
                <div class="card-body text-center">
                    <i class="fas fa-history fa-2x mb-2 text-primary"></i>
                    <h5 class="card-title">Rekod Transaksi</h5>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="monthly_statements.php" class="card text-decoration-none">
                <div class="card-body text-center">
                    <i class="fas fa-file-alt fa-2x mb-2 text-success"></i>
                    <h5 class="card-title">Penyata Bulanan</h5>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="financial_statement.php" class="card text-decoration-none">
                <div class="card-body text-center">
                    <i class="fas fa-file-invoice fa-2x mb-2 text-info"></i>
                    <h5 class="card-title">Penyata Kewangan</h5>
                </div>
            </a>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="row">
        <!-- Savings & Shares Section -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Saham & Simpanan</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>Modal Saham</h6>
                                <h4>RM <?php echo number_format($financialStatus['modalShare'] ?? 0, 2); ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>Modal Yuran</h6>
                                <h4>RM <?php echo number_format($financialStatus['feeCapital'] ?? 0, 2); ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>Simpanan Tetap</h6>
                                <h4>RM <?php echo number_format($financialStatus['fixedDeposit'] ?? 0, 2); ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>Tabung Anggota</h6>
                                <h4>RM <?php echo number_format($financialStatus['contribution'] ?? 0, 2); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loans Section -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Maklumat Pinjaman</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>Al-Bai</h6>
                                <h4 class="text-success">RM <?php 
                                    echo number_format(
                                        (isset($loanData['loanType']) && 
                                         strtoupper($loanData['loanType']) == 'AL-BAI' && 
                                         isset($loanData['balance'])) ? $loanData['balance'] : 0, 
                                        2
                                    ); 
                                ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>Al-Innah</h6>
                                <?php 
                                $alInnahAmount = (isset($loanData['loanType']) && 
                                                strtoupper($loanData['loanType']) == 'AL-INNAH' && 
                                                isset($loanData['balance'])) ? $loanData['balance'] : 0;
                                error_log("Al-Innah amount: " . $alInnahAmount);
                                ?>
                                <h4 class="text-success">RM <?php echo number_format($alInnahAmount, 2); ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>B/Pulih Kenderaan</h6>
                                <h4 class="text-success">RM <?php 
                                    echo number_format(
                                        (isset($loanData['loanType']) && 
                                         strtoupper($loanData['loanType']) == 'B/PULIH KENDERAAN' && 
                                         isset($loanData['balance'])) ? $loanData['balance'] : 0, 
                                        2
                                    ); 
                                ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>Road Tax & Insuran</h6>
                                <h4 class="text-success">RM <?php 
                                    echo number_format(
                                        (isset($loanData['loanType']) && 
                                         strtoupper($loanData['loanType']) == 'ROAD TAX & INSURAN' && 
                                         isset($loanData['balance'])) ? $loanData['balance'] : 0, 
                                        2
                                    ); 
                                ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
.card {
    transition: transform 0.2s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card:hover {
    transform: translateY(-5px);
}

.border.rounded {
    transition: all 0.3s;
}

.border.rounded:hover {
    background-color: #f8f9fa;
}

h4 {
    margin-bottom: 0;
}

h6 {
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.savings-card {
    background: linear-gradient(135deg,rgb(105, 212, 164) 0%,rgb(129, 195, 180) 100%);
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.savings-card .btn-light {
    background: rgba(255, 255, 255, 0.9);
    border: none;
    border-radius: 8px;
    font-weight: 500;
    padding: 8px 16px;
    transition: all 0.3s ease;
}

.savings-card .btn-light:hover {
    background: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.savings-card .card-title {
    font-size: 2.5rem;
    font-weight: 600;
}

.savings-card small {
    opacity: 0.8;
}

.btn-kembali {
    background-color: #FF9999;
    color: white;
    padding: 8px 20px;
    /* border-radius: 20px; */
    border: none;
    font-size: 14px;
    transition: all 0.3s ease;
}

.btn-kembali:hover {
    background-color: #FF8080;
    color: white;
}
</style>

<!-- <div class="card savings-card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h5 class="card-subtitle mb-2 text-white">
                    <i class="fas fa-piggy-bank me-2"></i>Jumlah Simpanan
                </h5>
                <h2 class="card-title text-white mb-3">RM <?php echo number_format($totalSavings, 2); ?></h2>
                <p class="card-text text-white mb-1">No. Akaun: <?php echo $accountNo; ?></p>
                <small class="text-white">Kemas kini terakhir: <?php echo $lastUpdate; ?></small>
            </div>
            <div class="d-flex flex-column gap-2">
                <a href="buat_deposit.php" class="btn btn-light">
                    <i class="fas fa-plus me-1"></i> Buat Deposit
                </a>
                <a href="mohon_pengeluaran.php" class="btn btn-light">
                    <i class="fas fa-money-bill-wave me-1"></i> Mohon Pengeluaran
                </a>
            </div>
        </div>
    </div>
</div> -->

<!-- 显示当前余额
$sql_display = "SELECT f.* 
                FROM tb_financialstatus f
                WHERE f.employeeID = ?";
$stmt_display = mysqli_prepare($conn, $sql_display);
mysqli_stmt_bind_param($stmt_display, 'i', $employeeID);
mysqli_stmt_execute($stmt_display);
$financialStatus = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_display)); -->




