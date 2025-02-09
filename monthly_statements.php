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
    <h2>Penyata Kewangan</h2>

    <!-- 添加筛选表单 -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Jenis Paparan</label>
                    <select name="view" class="form-select" onchange="this.form.submit()">
                        <option value="monthly" <?php echo $view == 'monthly' ? 'selected' : ''; ?>>Bulanan</option>
                        <option value="yearly" <?php echo $view == 'yearly' ? 'selected' : ''; ?>>Tahunan</option>
                    </select>
                </div>
                <?php if ($view == 'monthly'): ?>
                <div class="col-md-4">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-select" onchange="this.form.submit()">
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
    </div>

    <div class="card">
        <div class="card-body">
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
                                        Lihat Penyata Bulanan
                                    </a>
                                </td>
                            <?php else: ?>
                                <td><?php echo $row['year']; ?></td>
                                <td><span class="badge bg-success">Tersedia</span></td>
                                <td>
                                    <a href="view_yearly_statement.php?year=<?php echo $row['year']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        Lihat Penyata Tahunan
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