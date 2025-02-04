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
$accountNo = ($row = mysqli_fetch_assoc($result)) ? $row['accountNo'] : '-';
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
    SUM(CASE WHEN DeducType_ID = 1 THEN Deduct_Amt ELSE 0 END) as modal_saham,
    SUM(CASE WHEN DeducType_ID = 2 THEN Deduct_Amt ELSE 0 END) as modal_yuran,
    SUM(CASE WHEN DeducType_ID = 3 THEN Deduct_Amt ELSE 0 END) as simpanan_tetap,
    SUM(CASE WHEN DeducType_ID = 4 THEN Deduct_Amt ELSE 0 END) as tabung_anggota,
    SUM(CASE WHEN DeducType_ID = 5 THEN Deduct_Amt ELSE 0 END) as wang_deposit
FROM tb_deduction 
WHERE employeeID = ?";

$stmt_savings = mysqli_prepare($conn, $sql_savings);
mysqli_stmt_bind_param($stmt_savings, 's', $employeeID);
mysqli_stmt_execute($stmt_savings);
$savings = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_savings));

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
                    <h2 class="card-text mb-2">RM <?php echo number_format($totalAllLoans, 2); ?></h2>
                    <div class="small">
                        <?php 
                        $loanTypes = array();
                        if ($albai_total > 0) $loanTypes[] = "AL-BAI";
                        if ($alinnah_total > 0) $loanTypes[] = "AL-INNAH";
                        if ($bpulih_total > 0) $loanTypes[] = "B/PULIH KENDERAAN";
                        if ($roadtax_total > 0) $loanTypes[] = "ROAD TAX & INSURAN";
                        if ($skimkhas_total > 0) $loanTypes[] = "SKIM KHAS";
                        if ($karnival_total > 0) $loanTypes[] = "KARNIVAL MUSIM ISTIMEWA";
                        
                        if (!empty($loanTypes)) {
                            echo implode(", ", $loanTypes);
                        } else {
                            echo "Tiada pinjaman aktif";
                        }
                        ?>
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
                <div class="card-body py-4">
                    <div class="row g-4">
                        <!-- Modal Saham -->
                        <div class="col-12">  <!-- 改为全宽 -->
                            <div class="border rounded p-3 savings-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0">Modal Syer</h6>
                                    <h4 class="text-primary mb-0">RM <?php echo number_format($savings['modal_saham'] ?? 0, 2); ?></h4>
                                </div>
                            </div>
                        </div>
                        <!-- Modal Yuran -->
                        <div class="col-12">
                            <div class="border rounded p-3 savings-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0">Modal Yuran</h6>
                                    <h4 class="text-primary mb-0">RM <?php echo number_format($savings['modal_yuran'] ?? 0, 2); ?></h4>
                                </div>
                            </div>
                        </div>
                        <!-- Simpanan Tetap -->
                        <div class="col-12">
                            <div class="border rounded p-3 savings-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0">Simpanan Tetap</h6>
                                    <h4 class="text-primary mb-0">RM <?php echo number_format($savings['simpanan_tetap'] ?? 0, 2); ?></h4>
                                </div>
                            </div>
                        </div>
                        <!-- Tabung Anggota -->
                        <div class="col-12">
                            <div class="border rounded p-3 savings-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0">Sumbangan Tabung Kebajikan (AL-ABRAR)</h6>
                                    <h4 class="text-primary mb-0">RM <?php echo number_format($savings['tabung_anggota'] ?? 0, 2); ?></h4>
                                </div>
                            </div>
                        </div>
                        <!-- Wang Deposit -->
                        <div class="col-12">
                            <div class="border rounded p-3 savings-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0">Wang Deposit Anggota</h6>
                                    <h4 class="text-primary mb-0">RM <?php echo number_format($savings['wang_deposit'] ?? 0, 2); ?></h4>
                                </div>
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
                <div class="card-body py-4">
                    <!-- Summary Row -->
                    <div class="loan-summary mb-4">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="summary-item border rounded p-3">
                                    <h6 class="text-muted mb-1">Jumlah Pinjaman</h6>
                                    <h4 class="text-success">RM <?php 
                                        $totalLoanSum = $albai_amount + $alinnah_amount + $bpulih_amount + 
                                                      $roadtax_amount + $skimkhas_amount + $karnival_amount;
                                        echo number_format($totalLoanSum, 2); 
                                    ?></h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="summary-item border rounded p-3">
                                    <h6 class="text-muted mb-1">Bilangan Pinjaman</h6>
                                    <h4 class="text-success"><?php 
                                        $activeLoanCount = 0;
                                        if ($albai_amount > 0) $activeLoanCount++;
                                        if ($alinnah_amount > 0) $activeLoanCount++;
                                        if ($bpulih_amount > 0) $activeLoanCount++;
                                        if ($roadtax_amount > 0) $activeLoanCount++;
                                        if ($skimkhas_amount > 0) $activeLoanCount++;
                                        if ($karnival_amount > 0) $activeLoanCount++;
                                        echo $activeLoanCount;
                                    ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Divider -->
                    <hr class="my-4">

                    <!-- Loan Details -->
                    <div class="loan-details">
                        <h6 class="text-muted mb-3">Butiran Pinjaman</h6>
                        <div class="row g-4">
                            <!-- Al-Bai -->
                            <?php if ($albai_amount > 0): ?>
                            <div class="col-12">
                                <div class="border rounded p-3 savings-item loan-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0">Al-Bai</h6>
                                        <h4 class="text-success mb-0">
                                            RM <?php echo number_format($albai_amount, 2); ?> / 
                                            RM <?php echo number_format($albai_total, 2); ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- 其他贷款项目使用相同的模式 -->
                            <?php if ($alinnah_amount > 0): ?>
                            <div class="col-12">
                                <div class="border rounded p-3 savings-item loan-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0">Al-Innah</h6>
                                        <h4 class="text-success mb-0">
                                            RM <?php echo number_format($alinnah_amount, 2); ?> / 
                                            RM <?php echo number_format($alinnah_total, 2); ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($bpulih_amount > 0): ?>
                            <div class="col-12">
                                <div class="border rounded p-3 savings-item loan-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0">B/Pulih Kenderaan</h6>
                                        <h4 class="text-success mb-0">
                                            RM <?php echo number_format($bpulih_amount, 2); ?> / 
                                            RM <?php echo number_format($bpulih_total, 2); ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($roadtax_amount > 0): ?>
                            <div class="col-12">
                                <div class="border rounded p-3 savings-item loan-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0">Road Tax & Insuran</h6>
                                        <h4 class="text-success mb-0">
                                            RM <?php echo number_format($roadtax_amount, 2); ?> / 
                                            RM <?php echo number_format($roadtax_total, 2); ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($skimkhas_amount > 0): ?>
                            <div class="col-12">
                                <div class="border rounded p-3 savings-item loan-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0">Skim Khas</h6>
                                        <h4 class="text-success mb-0">
                                            RM <?php echo number_format($skimkhas_amount, 2); ?> / 
                                            RM <?php echo number_format($skimkhas_total, 2); ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($karnival_amount > 0): ?>
                            <div class="col-12">
                                <div class="border rounded p-3 savings-item loan-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0">Karnival Musim Istimewa</h6>
                                        <h4 class="text-success mb-0">
                                            RM <?php echo number_format($karnival_amount, 2); ?> / 
                                            RM <?php echo number_format($karnival_total, 2); ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- <style>
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

.savings-item {
    background-color: #fff;
    transition: all 0.3s ease;
    border: 1px solid #e0e0e0 !important;
}

.savings-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.savings-item h6 {
    color: #2c3e50;
    font-size: 0.95rem;
}

.savings-item h4 {
    font-weight: 600;
}

/* 修复背景色继承问题 */
.savings-bg .card-body, 
.loan-bg .card-body {
    background-color: inherit !important; /* 修正语法错误，删除中文注释 */
    background: inherit !重要;
}

/* 移除可能造成冲突的样式 */
.card-body {
    background-color: transparent;
}

/* 确保渐变色正确显示 */
.savings-bg {
    background: linear-gradient(135deg, var(--mint-light) 0%, var(--mint-lighter) 100%) !important;
}

.loan-bg {
    background: linear-gradient(135deg, var(--mint-lighter) 0%, var(--mint-light) 100%) !important;
}

.loan-item:hover {
    transform: translateX(5px);
    background-color: #f0fff0;
}

.loan-item h4 {
    font-weight: 600;
}

.summary-item {
    background: #fff;
    transition: all 0.3s ease;
}

.summary-item:hover {
    background-color: #f0fff0;
    transform: translateY(-3px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.loan-details {
    opacity: 0.9;
}

.loan-details:hover {
    opacity: 1;
}

hr {
    border-color: #e0e0e0;
    opacity: 0.5;
}

.summary-card {
    border: none;
}

/* 定义新的薄荷绿色变量 - 稍微加深的色调 */
:root {
    --mint-light: #7CCDB5;     /* 原来是 #98D8C6，调深一点 */
    --mint-lighter: #96D6C4;   /* 原来是 #B5E4D7，调深一点 */
}

.savings-bg {
    background: linear-gradient(135deg, var(--mint-light) 0%, var(--mint-lighter) 100%) !重要;
    color: white !重要;
}

.loan-bg {
    background: linear-gradient(135deg, var(--mint-lighter) 0%, var(--mint-light) 100%) !重要;
    color: white !重要;
} 

.bg-primary {
    background-color: var(--mint-light) !重要;
}

.bg-success {
    background-color: var(--mint-lighter) !重要;
}

.savings-bg .card-body, .loan-bg .card-body {
    background-color: inherit !重要; /* 继承父元素的背景色 */
}

.summary-card .card-title, 
.summary-card .card-text, 
.summary-card .small {
    color: white !重要;
}
</style> -->

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


