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
$sql = "SELECT f.* FROM tb_financialstatus f
        WHERE f.accountID IN (
            SELECT accountID 
            FROM tb_member_financialstatus 
            WHERE employeeID = ?
        )
        ORDER BY f.dateUpdated DESC LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 's', $employeeID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$financialData = mysqli_fetch_assoc($result);

// 获取会员的存款总额
$sqlSavings = "SELECT 
    m.employeeID,
    m.memberName,
    b.no_akaun,    // 从 tb_bank 表获取 no_akaun
    COALESCE(SUM(CASE 
        WHEN t.transType = 'deposit' THEN t.transAmt 
        WHEN t.transType = 'withdrawal' THEN -t.transAmt 
        ELSE 0 
    END), 0) as total_savings,
    MAX(t.transDate) as last_update
FROM tb_member m
LEFT JOIN tb_bank b ON m.employeeID = b.employeeID    // 添加与 tb_bank 的关联
LEFT JOIN tb_transaction t ON m.employeeID = t.employeeID
WHERE m.employeeID = ?
GROUP BY m.employeeID, m.memberName, b.no_akaun";

$stmt = mysqli_prepare($conn, $sqlSavings);
mysqli_stmt_bind_param($stmt, "s", $employeeID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $totalSavings = $row['total_savings'];
    $accountNo = $row['no_akaun'];
    $memberName = $row['memberName'];
    $lastUpdate = $row['last_update'] ? date('d M Y, h:i A', strtotime($row['last_update'])) : date('d M Y, h:i A');
} else {
    $totalSavings = 0;
    $accountNo = '-';
    $memberName = '-';
    $lastUpdate = date('d M Y, h:i A');
}
?>

<div class="container mt-4">
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
                                <h4 class="text-primary">RM <?php echo number_format($financialData['memberSaving'] ?? 0, 2); ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>Modal Yuran</h6>
                                <h4 class="text-primary">RM <?php echo number_format($financialData['feeCapital'] ?? 0, 2); ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>Simpanan Tetap</h6>
                                <h4 class="text-primary">RM <?php echo number_format($financialData['fixedDeposit'] ?? 0, 2); ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>Tabung Anggota</h6>
                                <h4 class="text-primary">RM <?php echo number_format($financialData['contribution'] ?? 0, 2); ?></h4>
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
                                <h4 class="text-success">RM <?php echo number_format($financialData['alBai'] ?? 0, 2); ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>Al-Innah</h6>
                                <h4 class="text-success">RM <?php echo number_format($financialData['alnnah'] ?? 0, 2); ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>B/Pulih Kenderaan</h6>
                                <h4 class="text-success">RM <?php echo number_format($financialData['bPulihKenderaan'] ?? 0, 2); ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>Road Tax & Insuran</h6>
                                <h4 class="text-success">RM <?php echo number_format($financialData['roadTaxInsurance'] ?? 0, 2); ?></h4>
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
    background: linear-gradient(135deg, #ff9a9e 0%, #ff6a88 100%);
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
</style>

<div class="card savings-card mb-4">
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
</div>




