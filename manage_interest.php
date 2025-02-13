<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['employeeID'])) {
    header('Location: login.php');
    exit();
}

include "headeradmin.php";
include "dbconnect.php";

// Add SweetAlert2 CDN if not already in headeradmin.php
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pengurusan Kadar Faedah</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="main-content">
    <div class="container">
        <br><br>
           <!-- Back button -->
           <div style="margin: 20px 0;">
            <a href="adminmainpage.php" class="btn-kembali">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <!-- Card content starts here -->
        <div class="card">
        
         
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Pengurusan Kadar Faedah Pinjaman</h5>
            </div>
            <div class="card-body">
                <!-- 在显示之前先获取当前利率 -->
                <?php
                $currentRateQuery = "SELECT rate, updated_at, updated_by FROM tb_interestrate ORDER BY updated_at DESC LIMIT 1";
                $currentRateResult = mysqli_query($conn, $currentRateQuery);
                $currentRate = mysqli_fetch_assoc($currentRateResult);

                // 如果没有找到任何记录，设置默认值
                if (!$currentRate) {
                    $currentRate = array(
                        'rate' => 0.00,
                        'updated_at' => date('Y-m-d H:i:s'),
                        'updated_by' => 'System'
                    );
                }
                ?>
                <div class="current-rate mb-4">
                    <h6>Kadar Faedah Semasa</h6>
                    <p class="h3 text-primary"><?php echo number_format($currentRate['rate'], 2); ?>%</p>
                    <small class="text-muted">
                        Dikemaskini pada: <?php echo date('d/m/Y H:i', strtotime($currentRate['updated_at'])); ?>
                        oleh <?php echo $currentRate['updated_by']; ?>
                    </small>
                </div>

                <form method="POST" class="mt-4">
                    <div class="mb-3">
                        <label for="new_rate" class="form-label">Kadar Faedah Baru (%)</label>
                        <input type="number" 
                               class="form-control" 
                               id="new_rate" 
                               name="new_rate" 
                               step="0.01" 
                               min="0" 
                               max="100"
                               required>
                    </div>
                    <button type="submit" class="btn btn-primary">Kemaskini Kadar</button>
                </form>

                <!-- Rate History Table -->
                <div class="mt-5">
                    <h6>Sejarah Perubahan Kadar</h6>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Kadar (%)</th>
                                <th>Tarikh Kemaskini</th>
                                <th>Dikemaskini Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $historySql = "SELECT rate, updated_at, updated_by FROM tb_interestrate ORDER BY updated_at DESC LIMIT 5";
                            $historyResult = mysqli_query($conn, $historySql);
                            while ($row = mysqli_fetch_assoc($historyResult)) {
                                echo "<tr>";
                                echo "<td>" . number_format($row['rate'], 2) . "%</td>";
                                echo "<td>" . date('d/m/Y H:i', strtotime($row['updated_at'])) . "</td>";
                                echo "<td>" . $row['updated_by'] . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
body {
    margin: 0;
    padding: 0;
    min-height: 100vh;
    background: linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)), url('img/padi.jpg') no-repeat center center fixed;
    background-size: cover;
}

.main-content {
    margin-top: 20px;
}

.back-button-container {
    margin-bottom: 10px;
    margin-top: 5px;
}

.page-header {
    background-color: #00796b;
    color: white;
    padding: 12px 20px;
    margin-bottom: 15px;
    width: 100%;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.page-header h2 {
    margin: 0;
    font-size: 24px;
    font-weight: normal;
}

.card {
    background: rgba(255, 255, 255, 0.95);
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    border-radius: 10px;
    border: none;
    margin-bottom: 40px;
}

.card-header {
    background-color: #20B2AA !important;
    color: white;
    border-radius: 10px 10px 0 0 !important;
    border: none;
}

.current-rate {
    background: rgba(248, 249, 250, 0.9);
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 30px;
    border: 1px solid rgba(0,0,0,0.1);
}

.table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

.table thead th {
    background-color: #20B2AA;
    color: white;
    font-weight: 500;
    border: none;
}

.btn-primary {
    background-color: #00796b;
    border: none;
    padding: 10px 20px;
}

.btn-primary:hover {
    background-color: #00695c;
}

.btn-secondary {
    background-color: #6c757d;
    border: none;
    padding: 8px 20px;
    border-radius: 5px;
    color: white;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
}

.btn-secondary:hover {
    background-color: #5a6268;
    color: white;
    text-decoration: none;
}

.form-control:focus {
    border-color: #20B2AA;
    box-shadow: 0 0 0 0.2rem rgba(32, 178, 170, 0.25);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .container {
        padding: 10px;
    }
    
    .card-body {
        padding: 15px;
    }
}

.btn-kembali {
    display: inline-flex;
    align-items: center;
    padding: 8px 20px;
    background-color: #6c757d;
    color: white;
    border-radius: 5px;
    text-decoration: none;
    font-size: 16px;
    margin-bottom: 15px;
    position: relative;
    z-index: 1000;
}

.btn-kembali:hover {
    background-color: #5a6268;
    color: white;
    text-decoration: none;
}

.btn-kembali i {
    margin-right: 8px;
}

.page-header {
    margin-top: 10px;
}
</style>

<?php include "footer.php"; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // 阻止表单直接提交
        
        const newRate = document.getElementById('new_rate').value;
        
        Swal.fire({
            title: 'Pengesahan',
            text: `Adakah anda pasti untuk mengemaskini kadar faedah kepada ${newRate}%?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00796b',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Ya, Kemaskini',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // 如果用户确认，提交表单
                form.submit();
            }
        });
    });
});
</script>

<?php
// Add this at the beginning of your PHP processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_rate = $_POST['new_rate'];
    $employee_id = $_SESSION['employeeID'];
    
    $sql = "INSERT INTO tb_interestrate (rate, updated_by, updated_at) VALUES (?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ds", $new_rate, $employee_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // 使用 JavaScript 重定向而不是 reload
        echo "
        <script>
        Swal.fire({
            title: 'Berjaya!',
            text: 'Kadar faedah telah dikemaskini.',
            icon: 'success',
            confirmButtonColor: '#00796b'
        }).then(() => {
            window.location.href = 'manage_interest.php'; // 使用完整的URL重定向
        });
        </script>";
        exit(); // 确保脚本在这里停止执行
    } else {
        echo "
        <script>
        Swal.fire({
            title: 'Ralat!',
            text: 'Gagal mengemaskini kadar faedah.',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
        </script>";
        exit();
    }
}
?>

</body>
</html> 