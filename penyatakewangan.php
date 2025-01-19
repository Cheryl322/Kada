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

// 获取实际交易总额
$sql_trans = "SELECT transType, transAmt 
              FROM tb_transaction 
              WHERE employeeID = ?";

$stmt_trans = mysqli_prepare($conn, $sql_trans);
mysqli_stmt_bind_param($stmt_trans, 'i', $employeeID);
mysqli_stmt_execute($stmt_trans);
$result_trans = mysqli_stmt_get_result($stmt_trans);

// 添加调试信息
echo "<!-- Transaction Records:";
while ($row = mysqli_fetch_assoc($result_trans)) {
    echo "\nType: " . $row['transType'] . ", Amount: " . $row['transAmt'];
}
echo " -->";

// 重置结果集指针
mysqli_data_seek($result_trans, 0);

// 计算总额
$totalSavings = 0;
while ($row = mysqli_fetch_assoc($result_trans)) {
    switch($row['transType']) {
        case 'Simpanan-M':
        case 'Simpanan-S':
        case 'Simpanan-T':
        case 'Simpanan-Y':
        case 'BAYARAN':
        case 'DEPOSIT':
            $totalSavings += $row['transAmt'];
            break;
    }
}

// 获取费用分配信息
$sql_fees = "SELECT 
    entryFee,
    deposit,
    modalShare,
    feeCapital,
    contribution,
    fixedDeposit
FROM tb_memberregistration_feesandcontribution
WHERE employeeID = ?";

$stmt_fees = mysqli_prepare($conn, $sql_fees);
mysqli_stmt_bind_param($stmt_fees, 'i', $employeeID);
mysqli_stmt_execute($stmt_fees);
$fees_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_fees));

// 获取所有交易
$sql_trans = "SELECT transAmt, transDate 
              FROM tb_transaction 
              WHERE employeeID = ?
              ORDER BY transDate ASC";

$stmt_trans = mysqli_prepare($conn, $sql_trans);
mysqli_stmt_bind_param($stmt_trans, 'i', $employeeID);
mysqli_stmt_execute($stmt_trans);
$result = mysqli_stmt_get_result($stmt_trans);

// 初始化累计金额
$modalSaham = 0;
$modalYuran = 0;
$simpananTetap = 0;
$tabungAnggota = 0;

// 处理每笔交易
while ($trans = mysqli_fetch_assoc($result)) {
    $amount = $trans['transAmt'];
    
    // 先扣除 entry fee 和 deposit（如果还未扣除）
    if ($fees_data['entryFee'] > 0) {
        $deduction = min($amount, $fees_data['entryFee']);
        $amount -= $deduction;
        $fees_data['entryFee'] -= $deduction;
    }
    
    if ($amount > 0 && $fees_data['deposit'] > 0) {
        $deduction = min($amount, $fees_data['deposit']);
        $amount -= $deduction;
        $fees_data['deposit'] -= $deduction;
    }
    
    // 如果还有剩余金额，按比例分配
    if ($amount > 0) {
        // Modal Saham
        if ($modalSaham < $fees_data['modalShare']) {
            $allocation = min($amount, $fees_data['modalShare'] - $modalSaham);
            $modalSaham += $allocation;
            $amount -= $allocation;
        }
        
        // 如果还有剩余金额，平均分配给其他三项
        if ($amount > 0) {
            $remaining = $amount / 3;
            
            // Modal Yuran
            if ($modalYuran < $fees_data['feeCapital']) {
                $allocation = min($remaining, $fees_data['feeCapital'] - $modalYuran);
                $modalYuran += $allocation;
            }
            
            // Simpanan Tetap
            if ($simpananTetap < $fees_data['fixedDeposit']) {
                $allocation = min($remaining, $fees_data['fixedDeposit'] - $simpananTetap);
                $simpananTetap += $allocation;
            }
            
            // Tabung Anggota
            if ($tabungAnggota < $fees_data['contribution']) {
                $allocation = min($remaining, $fees_data['contribution'] - $tabungAnggota);
                $tabungAnggota += $allocation;
            }
        }
    }
}

// 添加调试信息
echo "<!-- ALLOCATION DEBUG
Original Targets:
Modal Share: {$fees_data['modalShare']}
Fee Capital: {$fees_data['feeCapital']}
Fixed Deposit: {$fees_data['fixedDeposit']}
Contribution: {$fees_data['contribution']}

Actual Allocations:
Modal Saham: $modalSaham
Modal Yuran: $modalYuran
Simpanan Tetap: $simpananTetap
Tabung Anggota: $tabungAnggota
-->";

// 获取账号信息
$sqlBank = "SELECT accountNo FROM tb_bank WHERE employeeID = ? ORDER BY bankID DESC LIMIT 1";
$stmt = mysqli_prepare($conn, $sqlBank);
mysqli_stmt_bind_param($stmt, "s", $employeeID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$accountNo = ($row = mysqli_fetch_assoc($result)) ? $row['accountNo'] : '-';
$lastUpdate = date('d M Y, h:i A');

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
$result_loan = mysqli_stmt_get_result($stmt_loan);
$loan_data = mysqli_fetch_assoc($result_loan);

// 设置贷款变量
$loanAmount = $loan_data['amountRequested'] ?? 0;
$balance = $loan_data['balance'] ?? $loanAmount;
$monthlyPayment = $loan_data['monthlyInstallments'] ?? 0;
$loanType = $loan_data['loanType'] ?? '';

// 添加调试信息
echo "<!-- LOAN DEBUG
Loan Amount: $loanAmount
Balance from DB: $balance
-->";

// 初始化贷款类型金额
$albai_amount = 0;
$alinnah_amount = 0;
$bpulih_amount = 0;
$roadtax_amount = 0;

// 根据当前贷款类型设置对应金额
if ($loan_data) {
    switch (strtoupper($loanType)) {
        case 'AL-BAI':
            $albai_amount = $balance;
            break;
        case 'AL-INAH':
            $alinnah_amount = $balance;
            break;
        case 'B/PULIH KENDERAAN':
            $bpulih_amount = $balance;
            break;
        case 'ROAD TAX & INSURAN':
            $roadtax_amount = $balance;
            break;
    }
}

// 在页面中只显示一次贷款信息卡片
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
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-piggy-bank me-2"></i>
                        <h5 class="card-title mb-0">Jumlah Simpanan</h5>
                    </div>
                    <h2 class="card-text mb-2">RM <?php echo number_format($totalSavings, 2); ?></h2>
                    <div class="small">
                        No. Akaun: <?php echo $accountNo; ?><br>
                        Kemas kini terakhir: <?php echo $lastUpdate; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loan Card -->
        <div class="col-md-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        <h5 class="card-title mb-0">Jumlah Pinjaman</h5>
                    </div>
                    <?php if ($loan_data): ?>
                        <h2 class="card-text mb-2">RM <?php echo number_format($balance, 2); ?> / <?php echo number_format($loanAmount, 2); ?></h2>
                        <div class="small">
                            Jenis Pinjaman: <?php echo $loanType; ?><br>
                            Bayaran Bulanan: RM <?php echo number_format($monthlyPayment, 2); ?>
                        </div>
                    <?php else: ?>
                        <h2 class="card-text mb-2">RM 0.00 / 0.00</h2>
                        <div class="small">Tiada pinjaman aktif</div>
                    <?php endif; ?>
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
                                <h4 class="text-primary">RM <?php echo number_format($modalSaham, 2); ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>Modal Yuran</h6>
                                <h4 class="text-primary">RM <?php echo number_format($modalYuran, 2); ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>Simpanan Tetap</h6>
                                <h4 class="text-primary">RM <?php echo number_format($simpananTetap, 2); ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>Tabung Anggota</h6>
                                <h4 class="text-primary">RM <?php echo number_format($tabungAnggota, 2); ?></h4>
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
                                <h4 class="text-success">RM <?php echo number_format($albai_amount, 2); ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>Al-Innah</h6>
                                <h4 class="text-success">RM <?php echo number_format($alinnah_amount, 2); ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>B/Pulih Kenderaan</h6>
                                <h4 class="text-success">RM <?php echo number_format($bpulih_amount, 2); ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>Road Tax & Insuran</h6>
                                <h4 class="text-success">RM <?php echo number_format($roadtax_amount, 2); ?></h4>
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
    
    border: none;
    font-size: 14px;
    transition: all 0.3s ease;
}

.btn-kembali:hover {
    background-color: #FF8080;
    color: white;
}
</style>


