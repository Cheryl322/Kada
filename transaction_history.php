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

$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

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
                   WHEN dt.DeducType_ID = 6 THEN CONCAT('Loan Payment (', l.loanType, ')')
                   ELSE dt.typeName 
               END as displayType,
               l.loanType
        FROM tb_deduction d
        JOIN tb_deduction_type dt ON d.DeducType_ID = dt.DeducType_ID
        LEFT JOIN tb_loan l ON d.loanApplicationID = l.loanApplicationID
        WHERE d.employeeID = ? 
        AND MONTH(d.Deduct_date) = ? 
        AND YEAR(d.Deduct_date) = ?
        ORDER BY d.Deduct_date DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'sii', $employeeID, $month, $year);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$view = isset($_GET['view']) ? $_GET['view'] : 'monthly';
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? $_GET['month'] : date('m');

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

// 检查是否有可用日期
if (empty($available_dates)) {
    // 如果没有任何记录，设置默认值
    $year = date('Y');
    $month = date('m');
} else {
    // 如果没有选择年月，使用最新的记录日期
    if (!isset($_GET['year']) || !isset($_GET['month'])) {
        reset($available_dates);
        $year = key($available_dates);
        $month = !empty($available_dates[$year]) ? $available_dates[$year][0] : date('m');
    } else {
        $year = $_GET['year'];
        $month = $_GET['month'];
    }
}

$months_in_malay = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Mac',
    4 => 'April', 5 => 'Mei', 6 => 'Jun',
    7 => 'Julai', 8 => 'Ogos', 9 => 'September',
    10 => 'Oktober', 11 => 'November', 12 => 'Disember'
];
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Rekod Transaksi</h2>
        <a href="penyatakewangan.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <!-- Filter Card -->
    <div class="filter-card mb-4">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <label class="filter-label">Tahun</label>
                <select name="year" class="form-select custom-select" id="yearSelect">
                    <?php foreach ($available_dates as $y => $months): ?>
                        <option value="<?php echo $y; ?>" <?php echo ($y == $year) ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label class="filter-label">Bulan</label>
                <select name="month" class="form-select custom-select" id="monthSelect">
                    <?php 
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

    <!-- Transactions Card -->
    <div class="transaction-card">
        <div class="transaction-header">
            <h3>Transaksi <?php 
                if (mysqli_num_rows($result) > 0 && isset($months_in_malay[$month])) {
                    echo $months_in_malay[$month] . ' ' . $year;
                }
            ?></h3>
        </div>
        <div class="transaction-body">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php 
                $totalAmount = 0;
                while ($row = mysqli_fetch_assoc($result)): 
                    $totalAmount += $row['transAmt'];
                ?>
                    <div class="transaction-item">
                        <div class="transaction-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="transaction-details">
                            <div class="transaction-type">
                                <?php 
                                if (strpos($row['displayType'], 'Loan Payment') !== false) {
                                    echo $row['displayType'];
                                } else {
                                    echo $row['displayType'];
                                }
                                ?>
                            </div>
                            <div class="transaction-date"><?php echo date('d/m/Y', strtotime($row['transDate'])); ?></div>
                        </div>
                        <div class="transaction-amount">
                            RM <?php echo number_format($row['transAmt'], 2); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
                
                <div class="total-amount mt-3 p-3 border-top">
                    <h4 class="text-end">
                        Jumlah: <span class="<?php echo ($totalAmount > 0) ? 'text-success' : ''; ?>">
                            RM <?php echo number_format($totalAmount, 2); ?>
                        </span>
                    </h4>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="text-muted mb-0">Tiada rekod transaksi</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.filter-card {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
}

.filter-label {
    font-weight: 500;
    color: #2c3e50;
    margin-bottom: 8px;
}

.custom-select {
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 10px;
}

.transaction-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.transaction-header {
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.transaction-header h3 {
    margin: 0;
    font-size: 18px;
    color: #2c3e50;
}

.total-amount {
    background-color: #f8f9fa;
}

.total-amount h4 {
    margin: 0;
    font-size: 1.2rem;
}

.text-success {
    color: #28a745 !important;
}

.transaction-body {
    padding: 10px;
}

.transaction-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #eee;
    transition: background-color 0.3s;
}

.transaction-item:hover {
    background-color: #f8f9fa;
}

.transaction-icon {
    width: 40px;
    height: 40px;
    background: #e3f2fd;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: #2196F3;
}

.transaction-details {
    flex: 1;
}

.transaction-type {
    font-weight: 500;
    color: #2c3e50;
    margin-bottom: 4px;
}

.transaction-date {
    font-size: 13px;
    color: #666;
}

.transaction-amount {
    font-weight: 600;
    color: #4CAF50;
}

.no-transactions {
    text-align: center;
    padding: 40px;
    color: #666;
}

.btn-outline-secondary {
    border-radius: 10px;
    padding: 8px 20px;
}

.btn-primary {
    border-radius: 10px;
    padding: 10px;
    background: #4CAF50;
    border: none;
}

.btn-primary:hover {
    background: #45a049;
}
</style>

<script>
document.getElementById('yearSelect').addEventListener('change', function() {
    const year = this.value;
    const monthSelect = document.getElementById('monthSelect');
    const availableDates = <?php echo json_encode($available_dates); ?>;
    const monthsInMalay = <?php echo json_encode($months_in_malay); ?>;
    
    monthSelect.innerHTML = '';
    
    if (availableDates[year]) {
        availableDates[year].forEach(month => {
            const option = new Option(monthsInMalay[month], month);
            monthSelect.add(option);
        });
    }
});
</script>