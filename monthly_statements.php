<?php
session_start();
include "dbconnect.php";
include "headermember.php";

if (!isset($_SESSION['employeeID'])) {
    header("Location: login.php");
    exit();
}

$employeeID = $_SESSION['employeeID'];

// 检查连接
if (!isset($conn)) {
    die("Database connection not established");
}

// 获取所有可用的月份
$sql = "SELECT DISTINCT YEAR(Deduct_date) as year, MONTH(Deduct_date) as month 
        FROM tb_deduction 
        WHERE employeeID = ? 
        ORDER BY year DESC, month DESC";

try {
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt === false) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 's', $employeeID);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error executing statement: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    $available_months = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
} finally {
    if (isset($stmt)) {
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="container mt-5">
    <div class="mb-4">
        <a href="penyatakewangan.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    <h2>Penyata Bulanan</h2>

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th>Tahun</th>
                        <th>Status</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($available_months as $month): 
                        $monthName = date('F', mktime(0, 0, 0, $month['month'], 1));
                        $year = $month['year'];
                    ?>
                    <tr>
                        <td><?php echo $monthName; ?></td>
                        <td><?php echo $year; ?></td>
                        <td>
                            <span class="badge bg-success">Tersedia</span>
                        </td>
                        <td>
                            <a href="view_statement.php?month=<?php echo $month['month']; ?>&year=<?php echo $year; ?>" 
                               class="btn btn-sm btn-primary">
                                Lihat Penyata
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>