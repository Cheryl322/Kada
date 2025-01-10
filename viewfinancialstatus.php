<?php
session_start();
include "dbconnect.php";

// Check if user is logged in
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }

// Fetch financial status from database
//$employeeID = $_SESSION['employeeID'];

$sql = "SELECT f.*, u.memberName 
        FROM tb_financialStatus f
        JOIN tb_member u ON f.accountID = u.employeeID 
        WHERE f.accountID = ?";

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
        
        <!-- <?php if ($financial_data): ?> -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Maklumat Kewangan untuk 
                     <?php echo htmlspecialchars($financial_data['memberName']); ?>
                </h5>
                
                <table class="table table-bordered mt-3">
                <tr>
                        <th>Account ID</th>
                        <td><?php echo htmlspecialchars($financial_data['accountID']); ?></td>
                    </tr>
                    <tr>
                        <th>Member Saving</th>
                        <td><?php echo htmlspecialchars($financial_data['memberSaving']); ?></td>
                    </tr>
                    <tr>
                        <th>Al Bai</th>
                        <td><?php echo htmlspecialchars($financial_data['alBai']); ?></td>
                    </tr>
                    <tr>
                        <th>Al Nnah</th>
                        <td><?php echo htmlspecialchars($financial_data['alnnah']); ?></td>
                    </tr>
                    <tr>
                        <th>B Pulih Kenderaan</th>
                        <td><?php echo htmlspecialchars($financial_data['bPulihKenderaan']); ?></td>
                    </tr>
                    <tr>
                        <th>Road Tax Insurance</th>
                        <td><?php echo htmlspecialchars($financial_data['roadTaxInsurance']); ?></td>
                    </tr>
                    <tr>
                        <th>Special Scheme</th>
                        <td><?php echo htmlspecialchars($financial_data['specialScheme']); ?></td>
                    </tr>
                    <tr>
                        <th>Al Qadrul Hassan</th>
                        <td><?php echo htmlspecialchars($financial_data['alQadrulHassan']); ?></td>
                    </tr>
                    <tr>
                        <th>Date Updated</th>
                        <td><?php echo htmlspecialchars($financial_data['dateUpdated']); ?></td>
                    </tr>
                </table>

                <div class="mt-3">
                    <!-- <a href="edit_financial_status.php" class="btn btn-primary">Kemaskini</a> -->
                    <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">No financial data found for this user.</div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>