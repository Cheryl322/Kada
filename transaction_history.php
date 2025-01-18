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

// SQL 查询
$sql = "SELECT transDate, transType, transAmt 
        FROM tb_transaction 
        WHERE employeeID = '$employeeID' 
        AND MONTH(transDate) = $month 
        AND YEAR(transDate) = $year
        ORDER BY transDate DESC";

$result = mysqli_query($conn, $sql);

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