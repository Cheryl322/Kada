<?php
session_start();
include "dbconnect.php";
include "headermember.php";

$employeeID = $_SESSION['employeeID'];

// Fetch all monthly statements
$sql = "SELECT * FROM tb_monthly_reports 
        WHERE employeeID = ? 
        ORDER BY reportMonth DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$employeeID]);
$statements = $stmt->fetchAll(PDO::FETCH_ASSOC);
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