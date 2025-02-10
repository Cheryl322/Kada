<?php
session_start();
include "dbconnect.php";
include "headermember.php";

if (!isset($_SESSION['employeeID']) || !isset($_GET['year'])) {
    header("Location: monthly_statements.php");
    exit();
}

$employeeID = $_SESSION['employeeID'];
$year = $_GET['year'];


$sql_member = "SELECT m.*, f.* 
               FROM tb_member m
               LEFT JOIN tb_memberregistration_feesandcontribution f ON m.employeeID = f.employeeID
               WHERE m.employeeID = ?";
$stmt_member = mysqli_prepare($conn, $sql_member);
mysqli_stmt_bind_param($stmt_member, 's', $employeeID);
mysqli_stmt_execute($stmt_member);
$member = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_member));


$sql = "SELECT 
    MONTH(d.Deduct_date) as month,
    SUM(CASE WHEN dt.DeducType_ID = 1 THEN d.Deduct_Amt ELSE 0 END) as modal_syer,
    SUM(CASE WHEN dt.DeducType_ID = 2 THEN d.Deduct_Amt ELSE 0 END) as modal_yuran,
    SUM(CASE WHEN dt.DeducType_ID = 3 THEN d.Deduct_Amt ELSE 0 END) as simpanan_tetap,
    SUM(CASE WHEN dt.DeducType_ID = 4 THEN d.Deduct_Amt ELSE 0 END) as tabung_kebajikan,
    SUM(CASE WHEN dt.DeducType_ID = 5 THEN d.Deduct_Amt ELSE 0 END) as wang_deposit
FROM tb_deduction d
JOIN tb_deduction_type dt ON d.DeducType_ID = dt.DeducType_ID
WHERE d.employeeID = ? 
AND YEAR(d.Deduct_date) = ?
AND dt.DeducType_ID IN (1,2,3,4,5)
GROUP BY MONTH(d.Deduct_date)
ORDER BY month";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'si', $employeeID, $year);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);


$sql_loan = "SELECT 
    l.loanType,
    l.balance,
    la.amountRequested,
    la.monthlyInstallments
FROM tb_loan l
JOIN tb_loanapplication la ON l.employeeID = la.employeeID 
WHERE l.employeeID = ?
AND la.loanStatus = 'Diluluskan'";

$stmt_loan = mysqli_prepare($conn, $sql_loan);
mysqli_stmt_bind_param($stmt_loan, 's', $employeeID);
mysqli_stmt_execute($stmt_loan);
$loan_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_loan));


$monthly_data = [];
$type_totals = array_fill(1, 5, 0); 

while ($row = mysqli_fetch_assoc($result)) {
    $month = $row['month'];
    $monthly_data[$month] = [
        'modal_syer' => $row['modal_syer'],
        'modal_yuran' => $row['modal_yuran'],
        'simpanan_tetap' => $row['simpanan_tetap'],
        'tabung_kebajikan' => $row['tabung_kebajikan'],
        'wang_deposit' => $row['wang_deposit'],
        'total' => $row['modal_syer'] + $row['modal_yuran'] + $row['simpanan_tetap'] + 
                  $row['tabung_kebajikan'] + $row['wang_deposit']
    ];
    

    $type_totals[1] += $row['modal_syer'];
    $type_totals[2] += $row['modal_yuran'];
    $type_totals[3] += $row['simpanan_tetap'];
    $type_totals[4] += $row['tabung_kebajikan'];
    $type_totals[5] += $row['wang_deposit'];
}


$year_total = array_sum($type_totals);

function formatNumber($number) {
    return str_pad($number, 4, '0', STR_PAD_LEFT);
}


$malay_months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Mac', 4 => 'April',
    5 => 'Mei', 6 => 'Jun', 7 => 'Julai', 8 => 'Ogos',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Disember'
];

?>

<div class="container mt-5">
    <div class="mb-4 no-print">
        <a href="monthly_statements.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Cetak
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            
            <div class="text-center mb-4">
                <img src="img/kadalogo.jpg" alt="KADA Logo" class="mb-3" style="height: 80px;">
                <h2 class="mb-2">Koperasi Kakitangan Kada Kelantan Berhad</h2>
                <h4 class="mb-3">PENYATA KEWANGAN TAHUNAN</h4>
                <h5><?php echo $year; ?></h5>
            </div>

            
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>No. Anggota</strong></td>
                            <td>: <?php echo formatNumber($member['employeeID']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Nama</strong></td>
                            <td>: <?php echo $member['memberName']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>No. K/P</strong></td>
                            <td>: <?php echo $member['ic']; ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-1">Tarikh Cetak: <?php echo date('d/m/Y'); ?></p>
                </div>
            </div>

            
            <div class="mb-4">
                <h5 class="border-bottom pb-2">RINGKASAN TRANSAKSI BULANAN</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Bulan</th>
                                <th>Modal<br>Syer</th>
                                <th>Modal<br>Yuran</th>
                                <th>Simpanan<br>Tetap</th>
                                <th>Sumbangan<br>Tabung<br>Kebajikan</th>
                                <th>Wang<br>Deposit</th>
                                <th>Jumlah<br>(RM)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $months_with_data = array_keys($monthly_data);
                            sort($months_with_data); 
                            
                            foreach ($months_with_data as $month): 
                                $data = $monthly_data[$month];
                            ?>
                                <tr>
                                    <td><?php echo $malay_months[$month]; ?></td>
                                    <td class="text-end"><?php echo number_format($data['modal_syer'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($data['modal_yuran'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($data['simpanan_tetap'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($data['tabung_kebajikan'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($data['wang_deposit'], 2); ?></td>
                                    <td class="text-end"><strong>
                                        RM <?php echo number_format($data['total'], 2); ?>
                                    </strong></td>
                                </tr>
                            <?php endforeach; ?>
                            
                            
                            <tr class="table-light">
                                <td><strong>JUMLAH</strong></td>
                                <?php
                                for ($type = 1; $type <= 5; $type++) {
                                    echo "<td class='text-end'><strong>RM " . 
                                         number_format($type_totals[$type], 2) . 
                                         "</strong></td>";
                                }
                                ?>
                                <td class="text-end"><strong>
                                    RM <?php echo number_format($year_total, 2); ?>
                                </strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            
            <div class="mb-4">
                <h5 class="border-bottom pb-2">MAKLUMAT PINJAMAN</h5>
                <?php if ($loan_data): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Jenis Pinjaman</th>
                            <th>Jumlah Pinjaman</th>
                            <th>Bayaran Bulanan</th>
                            <th>Baki Pinjaman</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $loan_data['loanType']; ?></td>
                            <td class="text-end">RM <?php echo number_format($loan_data['amountRequested'], 2); ?></td>
                            <td class="text-end">RM <?php echo number_format($loan_data['monthlyInstallments'], 2); ?></td>
                            <td class="text-end">RM <?php echo number_format($loan_data['balance'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-center">Tiada pinjaman aktif</p>
                <?php endif; ?>
            </div>

            <div class="mt-5 pt-4 border-top">
                <p class="text-center mb-0">Ini adalah cetakan komputer. Tandatangan tidak diperlukan.</p>
            </div>
        </div>
    </div>
</div>



<style>
    @media print {
        /* 基本设置 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        /* 移除所有边框和背景 */
        .card, 
        .table,
        .container,
        .card-body {
            border: none !important;
            box-shadow: none !important;
            background: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* 页面设置 */
        @page {
            size: A4;
            margin: 2cm;
        }

        /* 容器和内容设置 */
        .container {
            width: 100% !重要;
            max-width: none !important;
        }

        /* 表格设置 */
        .table {
            width: 100% !important;
            border-collapse: collapse !重要;
        }

        /* 只保留必要的边框 */
        .table-bordered,
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #000 !important;
        }

        .table-borderless,
        .table-borderless td,
        .table-borderless th {
            border: none !important;
        }

        /* 文字大小设置 */
        body { font-size: 12pt; }
        h2 { font-size: 16pt; }
        h4 { font-size: 14pt; }
        h5 { font-size: 12pt; }
        td, th, p { font-size: 11pt; }

        /* Logo 大小控制 */
        img {
            height: 60px !重要;
            width: auto !important;
        }

        /* 隐藏不需要的元素 */
        .no-print,
        .navbar,
        .header-section,
        .btn,
        header {
            display: none !important;
        }

        /* 确保打印时显示完整内容 */
        .card {
            position: static !important;
            transform: none !重要;
        }

        /* 文本对齐 */
        .text-end { text-align: right !重要; }
        .text-center { text-align: center !重要; }

        /* 表格间距 */
        .table td,
        .table th {
            padding: 4px 8px !重要;
        }

        /* 移除 Bootstrap 的背景色 */
        .table-light,
        .table-light td,
        .table-light th {
            background-color: transparent !重要;
        }
    }

</style>