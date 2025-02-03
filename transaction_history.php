<?php
session_start();
include "dbconnect.php";
include "headermember.php";

if (!isset($_SESSION['employeeID'])) {
    header("Location: login.php");
    exit();
}

$employeeID = $_SESSION['employeeID'];
// 移除前导零
$employeeID = ltrim($employeeID, '0');

// 获取筛选参数
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// 获取会员的第一次付款日期
$sql_first_payment = "SELECT MIN(transDate) as first_payment 
                     FROM tb_transaction 
                     WHERE employeeID = ?";
$stmt_first = mysqli_prepare($conn, $sql_first_payment);
mysqli_stmt_bind_param($stmt_first, 's', $employeeID);
mysqli_stmt_execute($stmt_first);
$first_payment = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_first))['first_payment'];

// 检查当前选择的月份是否是第一次付款的月份
$is_first_month = false;
if ($first_payment) {
    $first_payment_month = date('m', strtotime($first_payment));
    $first_payment_year = date('Y', strtotime($first_payment));
    $is_first_month = ($month == $first_payment_month && $year == $first_payment_year);
}

// 修改主查询
$sql = "SELECT t.transDate, t.transType, t.transAmt 
        FROM tb_transaction t
        WHERE t.employeeID = ? 
        AND MONTH(t.transDate) = ? 
        AND YEAR(t.transDate) = ?";

// 如果不是第一个月，排除 entry fee 和 deposit
if (!$is_first_month) {
    $sql .= " AND t.transType NOT IN ('Entry Fee', 'Deposit')";
}

$sql .= " ORDER BY t.transDate DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'sii', $employeeID, $month, $year);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$totalAmount = 0;

function formatNumber($number) {
    return str_pad($number, 4, '0', STR_PAD_LEFT);
}
?>

<div class="container mt-4">
    <div class="mb-4">
        <a href="penyatakewangan.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <h2 class="mb-4">Rekod Pembayaran</h2>
    
    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Bulan</label>
                    <select name="month" class="form-select">
                        <?php
                        $months = [
                            '1' => 'Januari',
                            '2' => 'Februari',
                            '3' => 'Mac',
                            '4' => 'April',
                            '5' => 'Mei',
                            '6' => 'Jun',
                            '7' => 'Julai',
                            '8' => 'Ogos',
                            '9' => 'September',
                            '10' => 'Oktober',
                            '11' => 'November',
                            '12' => 'Disember'
                        ];
                        foreach ($months as $key => $value) {
                            $selected = ($key == $month) ? 'selected' : '';
                            echo "<option value='$key' $selected>$value</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-select">
                        <?php
                        $currentYear = date('Y');
                        for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                            $selected = ($y == $year) ? 'selected' : '';
                            echo "<option value='$y' $selected>$y</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Tapis</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Transaction Table -->
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tarikh</th>
                        <th>Jenis Pembayaran</th>
                        <th>Jumlah (RM)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php 
                        while ($row = mysqli_fetch_assoc($result)): 
                            $totalAmount += $row['transAmt'];
                        ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($row['transDate'])); ?></td>
                                <td><?php echo $row['transType']; ?></td>
                                <td><?php echo number_format($row['transAmt'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        <tr class="table-info">
                            <td colspan="2" class="text-end"><strong>Jumlah Keseluruhan:</strong></td>
                            <td><strong>RM <?php echo number_format($totalAmount, 2); ?></strong></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">Tiada rekod pembayaran</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div> 