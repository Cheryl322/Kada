<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['employeeID'])) {
    header('Location: login.php');
    exit();
}

include "headermember.php";
include "dbconnect.php";

// Get member data and loan information
$employeeId = $_SESSION['employeeID'];
$sql = "SELECT m.*, 
               l.amountRequested as loan_amount, 
               l.status as loan_status
        FROM tb_member m
        LEFT JOIN tb_loan l ON m.employeeID = l.employeeID
        WHERE m.employeeID = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $employeeId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$memberData = mysqli_fetch_assoc($result);

// Get transaction history
$transactionSql = "SELECT * FROM tb_transaction 
                   WHERE employeeID = ? 
                   ORDER BY transDate DESC";
$transStmt = mysqli_prepare($conn, $transactionSql);
mysqli_stmt_bind_param($transStmt, "i", $employeeId);
mysqli_stmt_execute($transStmt);
$transactions = mysqli_stmt_get_result($transStmt);

// Calculate total savings from transactions
$savingsSql = "SELECT SUM(transAmt) as total_savings 
               FROM tb_transaction 
               WHERE employeeID = ? 
               AND transType = 'savings'";
$savingsStmt = mysqli_prepare($conn, $savingsSql);
mysqli_stmt_bind_param($savingsStmt, "i", $employeeId);
mysqli_stmt_execute($savingsStmt);
$savingsResult = mysqli_stmt_get_result($savingsStmt);
$savingsData = mysqli_fetch_assoc($savingsResult);
$totalSavings = $savingsData['total_savings'] ?? 0;
?>

<div class="container">
   <div class="row">
       <!-- Left Sidebar -->
       <div class="col-md-3">
           <div class="profile-sidebar">
               <div class="profile-image">
                   <img src="img/profile.jpeg" class="rounded-circle" alt="Profile Picture">
                   <h3 class="text-left mt-3"><?php echo isset($memberData['memberName']) ? $memberData['memberName'] : 'User'; ?></h3>
               </div>

                <!-- Navigation Menu -->
                <div class="profile-nav">
                    <ul class="nav flex-column gap-2">
                        <li class="nav-item w-100">
                            <a class="btn btn-info w-75" href="profil.php">Profil</a>
                        </li>
                        <li class="nav-item w-100">
                            <a class="btn btn-primary w-75" href="statuskewangan.php">Pinjaman</a>
                        </li>
                        <li class="nav-item w-100">
                            <a class="btn btn-info w-75" href="statuspermohonanloan.php">Permohonan</a>
                        </li>
                        <li class="nav-item w-100">
                            <a class="btn btn-info w-75" href="logout.php">Daftar Keluar</a>
                        </li>
                    </ul>
                </div>
           </div>
       </div>

       <div class="col-md-9">
            <!-- Financial Summary Cards -->
            <div class="container mt-2">
                <div class="row">
                    <div class="col p-3 bg-primary text-white rounded me-2">
                        <h3>Jumlah Pinjaman</h3>
                        <p class="h4">RM <?php echo number_format(isset($memberData['loan_amount']) ? $memberData['loan_amount'] : 0, 2); ?></p>
                    </div>
                    <div class="col p-3 bg-primary text-white rounded ms-2">
                        <h3>Jumlah Simpanan</h3>
                        <p class="h4">RM <?php echo number_format($totalSavings, 2); ?></p>
                    </div>
                </div>
            </div>

            <!-- Transaction History Table -->
            <div class="container mt-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h4 class="mb-0">Sejarah Transaksi</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>No. Invois</th>
                                    <th>Tarikh</th>
                                    <th>Penerangan</th>
                                    <th>Jumlah (RM)</th>
                                    <th>Status</th>        
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($transactions)): ?>
                                <tr>
                                    <td><?php echo $row['transID']; ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['transDate'])); ?></td>
                                    <td><?php echo $row['transType']; ?></td>
                                    <td><?php echo number_format($row['transAmt'], 2); ?></td>
                                    <td>
                                        <div class="d-grid">
                                            <?php if($row['transType'] == 'savings'): ?>
                                                <a class="btn btn-success btn-sm" href="penyatakewangan.php?id=<?php echo $row['employeeID']; ?>">
                                                    Diluluskan
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-info btn-sm" disabled>
                                                    <?php echo $row['transType']; ?>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-sidebar {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.profile-image {
    text-align: center;
    margin-bottom: 20px;
}

.profile-image img {
    width: 150px;
    height: 150px;
    object-fit: cover;
    margin-bottom: 10px;
}

.profile-nav {
    margin-top: 20px;
}

.profile-nav .nav-item {
    margin-bottom: 10px;
}

.btn-info {
    background-color: #36b9cc;
    border-color: #36b9cc;
    color: white;
}

.btn-info:hover {
    background-color: #2a9aaa;
    border-color: #2a9aaa;
    color: white;
}

.rounded {
    border-radius: 8px !important;
}
</style>

<?php include "footer.php"; ?>