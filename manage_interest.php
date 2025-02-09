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
    <!-- Add these if they're not in headeradmin.php -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_rate'])) {
    $newRate = floatval($_POST['new_rate']);
    $adminID = $_SESSION['employeeID'];
    

    $updateSql = "INSERT INTO tb_interestrate (rate, updated_by) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $updateSql);
    mysqli_stmt_bind_param($stmt, "ds", $newRate, $adminID);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>
            Swal.fire({
                title: 'Berjaya!',
                text: 'Kadar faedah telah dikemaskini.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                title: 'Ralat!',
                text: 'Gagal mengemaskini kadar faedah.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
    }
}

// Get current rate
$sql = "SELECT rate, updated_at, updated_by FROM tb_interestrate ORDER BY updated_at DESC LIMIT 1";
$result = mysqli_query($conn, $sql);
$currentRate = mysqli_fetch_assoc($result);
?>

<div class="container mb-5">
<div class="row justify-content-center align-items-center min-vh-100">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Pengurusan Kadar Faedah Pinjaman</h5>
                </div>
                <div class="card-body">
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
</div>

<style>
/* Add these styles to remove extra spacing */
body {
    padding-top: 0 !important;
    margin-top: 0 !important;
}

.container {
    margin-top: 20px; /* Add a small margin for spacing */
}

<style>
.card {
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    border-radius: 10px;
}

.card-header {
    border-radius: 10px 10px 0 0;
}



.current-rate {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}
</style>

<?php include "footer.php"; ?>

</body>
</html> 