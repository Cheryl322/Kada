<?php
session_start();
include "dbconnect.php";

// Check if user is logged in
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }

// Fetch financial status from database
// $employeeID = $_SESSION['employeeID'];
// $sql = "SELECT f.*, u.nama 
//         FROM tb_financialStatus
//         JOIN tb_member u ON f. = u.id 
//         WHERE f.user_id = ?";

// $stmt = mysqli_prepare($con, $sql);
// mysqli_stmt_bind_param($stmt, "i", $user_id);
// mysqli_stmt_execute($stmt);
// $result = mysqli_stmt_get_result($stmt);
// $financial_data = mysqli_fetch_assoc($result);
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
        
        <!-- <?php if ($financial_data): ?> -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Maklumat Kewangan untuk 
                    <!-- <?php echo htmlspecialchars($financial_data['nama']); ?> -->
                </h5>
                
                <table class="table table-bordered mt-3">
                    <tr>
                        <th>Pendapatan Bulanan</th>
                        <td>RM 
                            <!-- <?php if ($financial_data): ?> -->
                        </td>
                    </tr>
                    <tr>
                        <th>Perbelanjaan Bulanan</th>
                        <td>RM 
                            <!-- <?php echo number_format($financial_data['monthly_expenses'], 2); ?> -->
                        </td>
                    </tr>
                    <tr>
                        <th>Hutang</th>
                        <td>RM 
                            <!-- <?php echo number_format($financial_data['debt'], 2); ?>-->
                        </td> 
                    </tr>
                    <tr>
                        <th>Simpanan</th>
                        <td>RM 
                            <!-- <?php echo number_format($financial_data['savings'], 2); ?> -->
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <?php 
                            $status = $financial_data['status'];
                            $badge_class = ($status == 'Baik') ? 'badge-success' : 
                                         (($status == 'Sederhana') ? 'badge-warning' : 'badge-danger');
                            ?>
                            <span class="badge <?php echo $badge_class; ?>">
                                <!-- <?php echo htmlspecialchars($status); ?> -->
                            </span>
                        </td>
                    </tr>
                </table>

                <div class="mt-3">
                    <!-- <a href="edit_financial_status.php" class="btn btn-primary">Kemaskini</a> -->
                    <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
        <!-- <?php if ($financial_data): ?> -->
        <div class="alert alert-info">
            Tiada maklumat kewangan dijumpai. 
            <a href="add_financial_status.php" class="btn btn-primary btn-sm ml-2">Tambah Maklumat Kewangan</a>
        </div>
        <!-- <?php endif; ?> -->
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>