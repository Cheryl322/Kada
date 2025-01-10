<?php
session_start();
include "dbconnect.php";

// Check if user is logged in
if (!isset($_SESSION['employeeId'])) {
    header("Location: login.php");
    exit();
}

//Fetch financial status from database
$employeeID = $_SESSION['employeeID'];
$sql = "SELECT f.transID, f.transType, f.transAmt, f.transDate, u.nama 
        FROM tb_financialStatus f
        JOIN tb_member u ON f.employeeID = u.employeeID
        WHERE f.employeeID = ?";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$financial_data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Status Kewangan</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Status Kewangan</h2>
        
        <?php if ($financial_data): ?> 
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Maklumat Kewangan untuk 
                    <?php echo htmlspecialchars($financial_data['memberName']); ?>
                </h5>
                
                <table class="table table-bordered mt-3">
                <thead>
                        <tr>
                            <th>Trans ID</th>
                            <th>Jenis Transaksi</th>
                            <th>Jumlah Transaksi</th>
                            <th>Tarikh Transaksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($financial_data as $data): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($data['transID']); ?></td>
                            <td><?php echo htmlspecialchars($data['transType']); ?></td>
                            <td>RM <?php echo number_format($data['transAmt'], 2); ?></td>
                            <td><?php echo htmlspecialchars($data['transDate']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="mt-3">
                    <a href="edit_financial_status.php" class="btn btn-primary">Kemaskini</a>
                    <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            Tiada maklumat kewangan dijumpai. 
            <a href="add_financial_status.php" class="btn btn-primary btn-sm ml-2">Tambah Maklumat Kewangan</a>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>