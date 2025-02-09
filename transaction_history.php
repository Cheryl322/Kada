<?php
session_start();
include "dbconnect.php";
include "headermember.php";

if (!isset($_SESSION['employeeID'])) {
    header("Location: login.php");
    exit();
}

$employeeID = $_SESSION['employeeID'];
$employeeID = ltrim($employeeID, '0');

// 获取筛选参数
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// 修改主查询中的 CASE 语句
$sql = "SELECT d.Deduct_date as transDate, 
               dt.typeName as transType, 
               d.Deduct_Amt as transAmt,
               CASE 
                   WHEN dt.DeducType_ID = 7 THEN 'Fee Masuk'
                   WHEN dt.DeducType_ID = 1 THEN 'Modal Syer'
                   WHEN dt.DeducType_ID = 2 THEN 'Modal Yuran'
                   WHEN dt.DeducType_ID = 3 THEN 'Simpanan Tetap'
                   WHEN dt.DeducType_ID = 4 THEN 'Sumbangan Tabung Kebajikan (AL-ABRAR)'
                   WHEN dt.DeducType_ID = 5 THEN 'Wang Deposit Anggota'
                   ELSE dt.typeName 
               END as displayType
        FROM tb_deduction d
        JOIN tb_deduction_type dt ON d.DeducType_ID = dt.DeducType_ID
        WHERE d.employeeID = ? 
        AND MONTH(d.Deduct_date) = ? 
        AND YEAR(d.Deduct_date) = ?
        ORDER BY d.Deduct_date DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'sii', $employeeID, $month, $year);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// 添加视图类型参数
$view = isset($_GET['view']) ? $_GET['view'] : 'monthly';
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? $_GET['month'] : date('m');

// 修改 SQL 查询以支持按月和按年查看
if ($view == 'yearly') {
    $sql = "SELECT DISTINCT YEAR(Deduct_date) as year 
            FROM tb_deduction
            WHERE employeeID = ? 
            ORDER BY year DESC";
} else {
    $sql = "SELECT DISTINCT YEAR(Deduct_date) as year, MONTH(Deduct_date) as month 
            FROM tb_deduction
            WHERE employeeID = ?
            ORDER BY year DESC, month DESC";
}

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$totalAmount = 0;

function formatNumber($number) {
    return str_pad($number, 4, '0', STR_PAD_LEFT);
}

// 获取所有有交易记录的月份和年份
$sql_dates = "SELECT DISTINCT 
                MONTH(Deduct_date) as month, 
                YEAR(Deduct_date) as year
              FROM tb_deduction 
              WHERE employeeID = ?
              ORDER BY year DESC, month DESC";

$stmt_dates = mysqli_prepare($conn, $sql_dates);
mysqli_stmt_bind_param($stmt_dates, 's', $employeeID);
mysqli_stmt_execute($stmt_dates);
$dates_result = mysqli_stmt_get_result($stmt_dates);

$available_dates = [];
while ($row = mysqli_fetch_assoc($dates_result)) {
    $available_dates[$row['year']][] = $row['month'];
}

// 如果没有选择月份和年份，使用最新的记录
if (!isset($_GET['year']) || !isset($_GET['month'])) {
    reset($available_dates);
    $year = key($available_dates);
    $month = $available_dates[$year][0];
}
?>

<div class="container mt-4">
    <div class="mb-4">
        <a href="penyatakewangan.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <h2 class="mb-4">Rekod Pembayaran</h2>
    
    <!-- 修改筛选表单 -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-select" id="yearSelect">
                        <?php foreach ($available_dates as $y => $months): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($y == $year) ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Bulan</label>
                    <select name="month" class="form-select" id="monthSelect">
                        <?php 
                        $months_in_malay = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Mac',
                            4 => 'April', 5 => 'Mei', 6 => 'Jun',
                            7 => 'Julai', 8 => 'Ogos', 9 => 'September',
                            10 => 'Oktober', 11 => 'November', 12 => 'Disember'
                        ];
                        
                        if (isset($available_dates[$year])) {
                            foreach ($available_dates[$year] as $m) {
                                $selected = ($m == $month) ? 'selected' : '';
                                echo "<option value='{$m}' {$selected}>{$months_in_malay[$m]}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Tapis
                    </button>
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
                                <td><?php echo $row['displayType']; ?></td>
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

<script>
document.getElementById('yearSelect').addEventListener('change', function() {
    const year = this.value;
    const monthSelect = document.getElementById('monthSelect');
    const availableDates = <?php echo json_encode($available_dates); ?>;
    const monthsInMalay = <?php echo json_encode($months_in_malay); ?>;
    
    // Clear current options
    monthSelect.innerHTML = '';
    
    // Add new options based on selected year
    if (availableDates[year]) {
        availableDates[year].forEach(month => {
            const option = new Option(monthsInMalay[month], month);
            monthSelect.add(option);
        });
    }
});
</script>