<?php
session_start();
include "dbconnect.php";
include "headermember.php";

if (!isset($_SESSION['employeeID'])) {
    header("Location: login.php");
    exit();
}

$employeeID = $_SESSION['employeeID'];

// Fetch financial summary
$sqlSummary = "SELECT 
    f.memberSaving,
    f.alBai,
    f.alnnah,
    f.bPulihKenderaan,
    f.roadTaxInsurance,
    f.specialScheme,
    f.alQadrul Hassan,
    m.modalShare,
    fc.deposit,
    fc.fixedDeposit
FROM tb_financialstatus f
JOIN tb_member m ON m.employeeID = ?
JOIN tb_memberregistration_feesandcontribution fc ON fc.employeeID = ?
WHERE f.accountID IN (
    SELECT accountID 
    FROM tb_member_financialstatus 
    WHERE employeeID = ?
)
ORDER BY f.dateUpdated DESC LIMIT 1";

$stmt = $pdo->prepare($sqlSummary);
$stmt->execute([$employeeID, $employeeID, $employeeID]);
$summary = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <h2>Status Kewangan</h2>
    
    <!-- Financial Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    Saham & Simpanan
                </div>
                <div class="card-body">
                    <p><strong>Modal Saham:</strong> RM <?php echo number_format($summary['modalShare'], 2); ?></p>
                    <p><strong>Simpanan:</strong> RM <?php echo number_format($summary['memberSaving'], 2); ?></p>
                    <p><strong>Simpanan Tetap:</strong> RM <?php echo number_format($summary['fixedDeposit'], 2); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    Pinjaman
                </div>
                <div class="card-body">
                    <p><strong>Al-Bai:</strong> RM <?php echo number_format($summary['alBai'], 2); ?></p>
                    <p><strong>Al-Innah:</strong> RM <?php echo number_format($summary['alnnah'], 2); ?></p>
                    <p><strong>B/Pulih Kenderaan:</strong> RM <?php echo number_format($summary['bPulihKenderaan'], 2); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-md-6">
            <a href="transaction_history.php" class="btn btn-primary btn-block">
                Lihat Rekod Transaksi
            </a>
        </div>
        <div class="col-md-6">
            <a href="monthly_statements.php" class="btn btn-success btn-block">
                Lihat Penyata Bulanan
            </a>
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="card">
        <div class="card-header">
            Transaksi Terkini
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tarikh</th>
                        <th>Jenis</th>
                        <th>Jumlah (RM)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sqlTrans = "SELECT transDate, transType, transAmt 
                                FROM tb_transaction 
                                WHERE employeeID = ? 
                                ORDER BY transDate DESC LIMIT 5";
                    $stmtTrans = $pdo->prepare($sqlTrans);
                    $stmtTrans->execute([$employeeID]);
                    while ($trans = $stmtTrans->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($trans['transDate'])); ?></td>
                        <td><?php echo $trans['transType']; ?></td>
                        <td><?php echo number_format($trans['transAmt'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>