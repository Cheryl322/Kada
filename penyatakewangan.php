<?php
session_start();
include "dbconnect.php";
include "headermember.php";

if (!isset($_SESSION['employeeID'])) {
    header("Location: login.php");
    exit();
}

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
</style>




