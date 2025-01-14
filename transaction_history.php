<?php
session_start();
include "dbconnect.php";
include "headermember.php";

$employeeID = $_SESSION['employeeID'];

// Get month and year filter from URL parameters
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

$sql = "SELECT * FROM tb_transaction 
        WHERE employeeID = ? 
        AND MONTH(transDate) = ? 
        AND YEAR(transDate) = ?
        ORDER BY transDate DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$employeeID, $month, $year]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <h2>Rekod Transaksi</h2>

    <!-- Month/Year Filter -->
    <form class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <select name="month" class="form-control">
                    <?php for($m=1; $m<=12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php echo $m == $month ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0,0,0,$m,1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4">
                <select name="year" class="form-control">
                    <?php 
                    $currentYear = date('Y');
                    for($y=$currentYear; $y>=$currentYear-5; $y--): 
                    ?>
                        <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">Tapis</button>
            </div>
        </div>
    </form>

    <!-- Transactions Table -->
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tarikh</th>
                        <th>Jenis Transaksi</th>
                        <th>Jumlah (RM)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($transactions as $trans): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($trans['transDate'])); ?></td>
                        <td><?php echo $trans['transType']; ?></td>
                        <td><?php echo number_format($trans['transAmt'], 2); ?></td>
                        <td>
                            <span class="badge badge-success">Selesai</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div> 