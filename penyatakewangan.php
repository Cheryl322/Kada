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

// 获取实际交易总额 - 修改查询使用 tb_deduction
$sql_trans = "SELECT DeducType_ID, Deduct_Amt 
              FROM tb_deduction 
              WHERE employeeID = ?";

$stmt_trans = mysqli_prepare($conn, $sql_trans);
mysqli_stmt_bind_param($stmt_trans, 'i', $employeeID);
mysqli_stmt_execute($stmt_trans);
$result_trans = mysqli_stmt_get_result($stmt_trans);

// 添加调试信息
echo "<!-- Transaction Records:";
while ($row = mysqli_fetch_assoc($result_trans)) {
    echo "\nType: " . $row['DeducType_ID'] . ", Amount: " . $row['Deduct_Amt'];
}
echo " -->";

// 重置结果集指针
mysqli_data_seek($result_trans, 0);

// 计算总额 - 修改为包含 deposit
$sql_total = "SELECT 
    SUM(CASE 
        WHEN DeducType_ID IN (1,2,3,4,5) THEN Deduct_Amt 
        ELSE 0 
    END) as total_amount
FROM tb_deduction 
WHERE employeeID = ?";

$stmt_total = mysqli_prepare($conn, $sql_total);
mysqli_stmt_bind_param($stmt_total, 'i', $employeeID);
mysqli_stmt_execute($stmt_total);
$total_result = mysqli_stmt_get_result($stmt_total);
$total_row = mysqli_fetch_assoc($total_result);
$totalSavings = $total_row['total_amount'] ?? 0;

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
$row = mysqli_fetch_assoc($result);
$accountNo = $row ? $row['accountNo'] : '-';
$lastUpdate = date('d M Y, h:i A');

// 首先更新贷款余额
$sql_update_balance = "UPDATE tb_loan l
JOIN tb_loanapplication la ON l.employeeID = la.employeeID 
SET l.balance = CASE 
    WHEN l.balance IS NULL THEN la.amountRequested 
    ELSE l.balance 
END
WHERE l.employeeID = ?
AND la.loanStatus = 'Diluluskan'";

$stmt_update = mysqli_prepare($conn, $sql_update_balance);
mysqli_stmt_bind_param($stmt_update, 's', $employeeID);
mysqli_stmt_execute($stmt_update);

// 获取已支付的贷款金额 - 修改查询
$sql_payments = "SELECT SUM(Deduct_Amt) as total_paid
                FROM tb_deduction 
                WHERE employeeID = ? 
                AND DeducType_ID = 6";  // 6 是贷款还款类型

$stmt_payments = mysqli_prepare($conn, $sql_payments);
mysqli_stmt_bind_param($stmt_payments, 's', $employeeID);
mysqli_stmt_execute($stmt_payments);
$payments = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_payments));
$total_paid = $payments['total_paid'] ?? 0;

// 然后获取贷款信息
$sql_loan = "SELECT 
    l.loanType,
    l.balance,
    la.amountRequested,
    la.monthlyInstallments
FROM tb_loan l
JOIN tb_loanapplication la ON l.employeeID = la.employeeID 
WHERE l.employeeID = ?
AND la.loanStatus = 'Diluluskan'";

$stmt_loan = mysqli_prepare($conn, $sql_loan);
mysqli_stmt_bind_param($stmt_loan, 's', $employeeID);
mysqli_stmt_execute($stmt_loan);
$result = mysqli_stmt_get_result($stmt_loan);

// 初始化变量
$totalLoanAmount = 0;
$totalBalance = 0;
$loanInfo = '';

if ($loan = mysqli_fetch_assoc($result)) {
    $totalLoanAmount = $loan['amountRequested'];
    $totalBalance = $totalLoanAmount - $total_paid;  // 计算实际余额
    $loanInfo = "Jenis Pinjaman: {$loan['loanType']}\nBayaran Bulanan: RM " . number_format($loan['monthlyInstallments'], 2);
    
    // 更新 tb_loan 的 balance
    $sql_update = "UPDATE tb_loan 
                   SET balance = ? 
                   WHERE employeeID = ? 
                   AND loanType = ?";
    $stmt_update = mysqli_prepare($conn, $sql_update);
    mysqli_stmt_bind_param($stmt_update, 'dss', $totalBalance, $employeeID, $loan['loanType']);
    mysqli_stmt_execute($stmt_update);
} 

// 获取 tb_financialstatus 中的最新总额
$sql_totals = "SELECT 
    modalShare,   
    feeCapital,    
    fixedDeposit,  
    contribution   
FROM tb_financialstatus 
WHERE employeeID = ?";

$stmt_totals = mysqli_prepare($conn, $sql_totals);
mysqli_stmt_bind_param($stmt_totals, 's', $employeeID);
mysqli_stmt_execute($stmt_totals);
$totals = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_totals));

// 更新 tb_financialstatus 表
$sql_update = "INSERT INTO tb_financialstatus 
               (employeeID, modalShare, feeCapital, contribution, fixedDeposit, dateUpdated)
               VALUES (?, ?, ?, ?, ?, UNIX_TIMESTAMP())
               ON DUPLICATE KEY UPDATE 
               modalShare = VALUES(modalShare),
               feeCapital = VALUES(feeCapital),
               contribution = VALUES(contribution),
               fixedDeposit = VALUES(fixedDeposit),
               dateUpdated = UNIX_TIMESTAMP()";

$stmt_update = mysqli_prepare($conn, $sql_update);
mysqli_stmt_bind_param($stmt_update, 'sdddd', 
    $employeeID,
    $totals['modalShare'],  // Simpanan-M
    $totals['feeCapital'],  // Simpanan-Y
    $totals['fixedDeposit'],  // Simpanan-S
    $totals['contribution']   // Simpanan-T
);
mysqli_stmt_execute($stmt_update);

// 添加调试信息
echo "<!-- Financial Status Update:
Modal Share (Simpanan-M): {$totals['modalShare']}
Fee Capital (Simpanan-Y): {$totals['feeCapital']}
Fixed Deposit (Simpanan-S): {$totals['fixedDeposit']}
Contribution (Simpanan-T): {$totals['contribution']}
-->";

// 获取各类型贷款的金额和总额
$sql_loans = "SELECT 
    l.loanType,
    l.balance,
    la.amountRequested
FROM tb_loan l
JOIN tb_loanapplication la ON l.employeeID = la.employeeID 
WHERE l.employeeID = ?
AND la.loanStatus = 'Diluluskan'";

$stmt_loans = mysqli_prepare($conn, $sql_loans);
mysqli_stmt_bind_param($stmt_loans, 's', $employeeID);
mysqli_stmt_execute($stmt_loans);
$loans_result = mysqli_stmt_get_result($stmt_loans);

// 初始化贷款金额变量，添加总额变量
$albai_amount = 0;
$albai_total = 0;
$alinnah_amount = 0;
$alinnah_total = 0;
$bpulih_amount = 0;
$bpulih_total = 0;
$roadtax_amount = 0;
$roadtax_total = 0;
$skimkhas_amount = 0;
$skimkhas_total = 0;
$karnival_amount = 0;
$karnival_total = 0;

// 设置实际贷款金额
while ($loan = mysqli_fetch_assoc($loans_result)) {
    switch($loan['loanType']) {
        case 'AL-BAI':
            $albai_amount = $loan['balance'];
            $albai_total = $loan['amountRequested'];
            break;
        case 'AL-INNAH':
            $alinnah_amount = $loan['balance'];
            $alinnah_total = $loan['amountRequested'];
            break;
        case 'B/PULIH KENDERAAN':
            $bpulih_amount = $loan['balance'];
            $bpulih_total = $loan['amountRequested'];
            break;
        case 'ROAD TAX & INSURAN':
            $roadtax_amount = $loan['balance'];
            $roadtax_total = $loan['amountRequested'];
            break;
        case 'SKIM KHAS':
            $skimkhas_amount = $loan['balance'];
            $skimkhas_total = $loan['amountRequested'];
            break;
        case 'KARNIVAL MUSIM ISTIMEWA':
            $karnival_amount = $loan['balance'];
            $karnival_total = $loan['amountRequested'];
            break;
    }
}

// 计算所有贷款的总额
$totalAllLoans = $albai_total + $alinnah_total + $bpulih_total + 
                 $roadtax_total + $skimkhas_total + $karnival_total;

// 获取各类型储蓄的最新总额 - 修改查询，添加 deposit
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

// 在页面中只显示一次贷款信息卡片
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Penyata Kewangan</h2>
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
                            No. Akaun: <?php echo !empty($accountNo) ? $accountNo : '-'; ?>
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
                            <?php 
                            $loanTypes = array_filter([
                                $albai_amount > 0 ? 'Al-Bai' : null,
                                $alinnah_amount > 0 ? 'Al-Innah' : null,
                                $bpulih_amount > 0 ? 'B/Pulih' : null,
                                $roadtax_amount > 0 ? 'Road Tax' : null,
                                $skimkhas_amount > 0 ? 'Skim Khas' : null,
                                $karnival_amount > 0 ? 'Karnival' : null
                            ]);
                            echo implode(' • ', $loanTypes) ?: 'Tiada pinjaman aktif';
                            ?>
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

    <!-- 保留现有的详细信息部分，但使用新的样式 -->
    <div class="row">
        <!-- Savings Section -->
        <div class="col-md-6 mb-4">
            <div class="details-card">
                <h3 class="details-title">
                    <i class="fas fa-piggy-bank me-2"></i>
                    Butiran Simpanan
                </h3>
                <!-- 现有的储蓄详情，使用新样式 -->
                <?php foreach ($savings as $type => $amount): ?>
                <div class="detail-item">
                    <span class="detail-label"><?php echo ucwords(str_replace('_', ' ', $type)); ?></span>
                    <span class="detail-amount">RM <?php echo number_format($amount ?? 0, 2); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Loans Section -->
        <div class="col-md-6 mb-4">
            <div class="details-card">
                <h3 class="details-title">
                    <i class="fas fa-file-invoice-dollar me-2"></i>
                    Butiran Pinjaman
                </h3>
                <!-- Al-Bai -->
                <?php if ($albai_amount > 0): ?>
                <div class="detail-item">
                    <span class="detail-label">Al-Bai</span>
                    <span class="detail-amount">
                        RM <?php echo number_format($albai_amount, 2); ?> / 
                        RM <?php echo number_format($albai_total, 2); ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <!-- Al-Innah -->
                <?php if ($alinnah_amount > 0): ?>
                <div class="detail-item">
                    <span class="detail-label">Al-Innah</span>
                    <span class="detail-amount">
                        RM <?php echo number_format($alinnah_amount, 2); ?> / 
                        RM <?php echo number_format($alinnah_total, 2); ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <!-- B/Pulih -->
                <?php if ($bpulih_amount > 0): ?>
                <div class="detail-item">
                    <span class="detail-label">B/Pulih Kenderaan</span>
                    <span class="detail-amount">
                        RM <?php echo number_format($bpulih_amount, 2); ?> / 
                        RM <?php echo number_format($bpulih_total, 2); ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <!-- Road Tax -->
                <?php if ($roadtax_amount > 0): ?>
                <div class="detail-item">
                    <span class="detail-label">Road Tax & Insuran</span>
                    <span class="detail-amount">
                        RM <?php echo number_format($roadtax_amount, 2); ?> / 
                        RM <?php echo number_format($roadtax_total, 2); ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <!-- Skim Khas -->
                <?php if ($skimkhas_amount > 0): ?>
                <div class="detail-item">
                    <span class="detail-label">Skim Khas</span>
                    <span class="detail-amount">
                        RM <?php echo number_format($skimkhas_amount, 2); ?> / 
                        RM <?php echo number_format($skimkhas_total, 2); ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <!-- Karnival -->
                <?php if ($karnival_amount > 0): ?>
                <div class="detail-item">
                    <span class="detail-label">Karnival Musim Istimewa</span>
                    <span class="detail-amount">
                        RM <?php echo number_format($karnival_amount, 2); ?> / 
                        RM <?php echo number_format($karnival_total, 2); ?>
                    </span>
                </div>
                <?php endif; ?>
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

.summary-icon.account {
    background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);
}

.summary-icon i {
    font-size: 24px;
    color: white;
}

.summary-details h3 {
    font-size: 14px;
    color: #707070;
    margin-bottom: 5px;
}

.summary-details .amount {
    font-size: 24px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
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
    transition: all 0.3s ease;
}

.detail-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

.detail-label {
    color: #707070;
    font-weight: 500;
}

.detail-amount {
    font-weight: 600;
    color: #2c3e50;
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

/* Add these new styles to your existing CSS */
.additional-info {
    margin-top: 8px;
    font-size: 12px;
    color: #666;
}

.info-item {
    display: block;
    margin-top: 4px;
}

.info-item i {
    width: 16px;
    text-align: center;
}

/* Update existing card styles */
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

.summary-details {
    flex: 1;
}

/* Rest of your existing styles remain the same */

/* Add these new styles for navigation cards */
.nav-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    height: 100%;
}

.nav-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.nav-card-content {
    display: flex;
    align-items: center;
    padding: 20px;
    text-decoration: none;
    color: inherit;
}

.nav-card-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    flex-shrink: 0;
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
    font-size: 20px;
    color: white;
}

.nav-card-details {
    flex: 1;
}

.nav-card-title {
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0 0 5px 0;
}

.nav-card-desc {
    font-size: 12px;
    color: #666;
    margin: 0;
}
</style>


