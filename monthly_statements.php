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

// Fetch all monthly statements
$sql = "SELECT * FROM tb_transaction
        WHERE employeeID = ? 
        -- ORDER BY reportMonth DESC";

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
    $statements = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
} finally {
    if (isset($stmt)) {
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="container mt-5">
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
                    <?php foreach($statements as $statement): ?>
                    <tr>
                        <td><?php echo date('F', strtotime($statement['reportMonth'])); ?></td>
                        <td><?php echo date('Y', strtotime($statement['reportMonth'])); ?></td>
                        <td>
                            <span class="badge badge-success">Tersedia</span>
                        </td>
                        <td>
                            <a href="<?php echo $statement['filePath']; ?>" 
                               class="btn btn-sm btn-primary" 
                               target="_blank">
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