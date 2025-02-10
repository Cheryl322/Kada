<?php
session_start();
include "dbconnect.php";
include "headermember.php";

if (!isset($_SESSION['employeeID'])) {
    header("Location: login.php");
    exit();
}

$employeeID = $_SESSION['employeeID'];
$view = isset($_GET['view']) ? $_GET['view'] : 'monthly';
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? $_GET['month'] : date('m');

// 检查连接
if (!isset($conn)) {
    die("Database connection not established");
}

// 使用原有的 SQL 查询
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

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Penyata Bulanan</h2>
        <a href="penyatakewangan.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <!-- Filter Card -->
    <div class="filter-card mb-4">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="filter-label">Jenis Paparan</label>
                <select name="view" class="form-select custom-select" onchange="this.form.submit()">
                    <option value="monthly" <?php echo $view == 'monthly' ? 'selected' : ''; ?>>Bulanan</option>
                    <option value="yearly" <?php echo $view == 'yearly' ? 'selected' : ''; ?>>Tahunan</option>
                </select>
            </div>
            <?php if ($view == 'monthly'): ?>
            <div class="col-md-4">
                <label class="filter-label">Tahun</label>
                <select name="year" class="form-select custom-select" onchange="this.form.submit()">
                    <?php
                    $years_sql = "SELECT DISTINCT YEAR(Deduct_date) as year FROM tb_deduction ORDER BY year DESC";
                    $years_result = mysqli_query($conn, $years_sql);
                    while ($year_row = mysqli_fetch_assoc($years_result)) {
                        $selected = ($year_row['year'] == $year) ? 'selected' : '';
                        echo "<option value='{$year_row['year']}' $selected>{$year_row['year']}</option>";
                    }
                    ?>
                </select>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Statements List -->
    <div class="statement-card">
        <div class="statement-body">
            <table class="table">
                <thead>
                    <tr>
                        <?php if ($view == 'monthly'): ?>
                            <th>Bulan</th>
                            <th>Tahun</th>
                        <?php else: ?>
                            <th>Tahun</th>
                        <?php endif; ?>
                        <th>Status</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($available_months as $row): ?>
                        <tr>
                            <?php if ($view == 'monthly'): ?>
                                <td><?php echo date('F', mktime(0, 0, 0, $row['month'], 1)); ?></td>
                                <td><?php echo $row['year']; ?></td>
                                <td><span class="badge bg-success">Tersedia</span></td>
                                <td>
                                    <a href="view_statement.php?month=<?php echo $row['month']; ?>&year=<?php echo $row['year']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-file-alt me-1"></i> Lihat Penyata
                                    </a>
                                </td>
                            <?php else: ?>
                                <td><?php echo $row['year']; ?></td>
                                <td><span class="badge bg-success">Tersedia</span></td>
                                <td>
                                    <a href="view_yearly_statement.php?year=<?php echo $row['year']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-file-alt me-1"></i> Lihat Penyata
                                    </a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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

.statement-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.statement-body {
    padding: 20px;
}

.table {
    margin: 0;
}

.table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
    border-bottom: 2px solid #dee2e6;
}

.table td {
    vertical-align: middle;
    color: #2c3e50;
}

.btn-primary {
    background: #4CAF50;
    border: none;
}

.btn-primary:hover {
    background: #45a049;
}

.badge {
    padding: 6px 12px;
    border-radius: 6px;
}
</style>