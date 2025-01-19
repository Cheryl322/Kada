<?php
session_start();
include "dbconnect.php";
include "headermember.php";

if (!isset($_SESSION['employeeID'])) {
    header("Location: login.php");
    exit();
}

$employeeID = $_SESSION['employeeID'];

// Fetch member details
$sqlMember = "SELECT m.*, f.* 
              FROM tb_member m
              LEFT JOIN tb_memberregistration_feesandcontribution f ON m.employeeID = f.employeeID 
              WHERE m.employeeID = ?";
$stmtMember = mysqli_prepare($conn, $sqlMember);
mysqli_stmt_bind_param($stmtMember, 's', $employeeID);
mysqli_stmt_execute($stmtMember);
$memberData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtMember));

// Fetch loan data
$sqlLoan = "SELECT 
    SUM(CASE WHEN l.loanType = 'AL-BAI' THEN l.amountRequested ELSE 0 END) as alBai,
    SUM(CASE WHEN l.loanType = 'AL-INAH' THEN l.amountRequested ELSE 0 END) as alnnah,
    SUM(CASE WHEN l.loanType = 'B/PULIH KENDERAAN' THEN l.amountRequested ELSE 0 END) as bPulihKenderaan,
    SUM(CASE WHEN l.loanType = 'ROAD TAX & INSURAN' THEN l.amountRequested ELSE 0 END) as roadTaxInsurance
    FROM tb_loan l
    JOIN tb_loanapplication la ON l.loanApplicationID = la.loanApplicationID
    WHERE l.employeeID = ? AND la.loanStatus = 'Diluluskan'";
$stmtLoan = mysqli_prepare($conn, $sqlLoan);
mysqli_stmt_bind_param($stmtLoan, 's', $employeeID);
mysqli_stmt_execute($stmtLoan);
$loanData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtLoan));

function formatNumber($number) {
    return str_pad($number, 4, '0', STR_PAD_LEFT);
}
?>

<div class="container" style="max-width: 800px; background-color: white; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <div class="mb-4">
        <a href="penyatakewangan.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    <!-- Header with Logo and Member Info -->
    <div class="row align-items-center mb-4">
        <div class="col-3">
            <img src="img/kadalogo.jpg" alt="Logo" style="width: 100px;">
        </div>
        <div class="col-9">
            <div class="border p-3 rounded">
                <div class="row">
                    <div class="col-8">
                        <label><b>NAMA: </b><?php echo htmlspecialchars($memberData['memberName']); ?></label>
                    </div>
                    <div class="col-4">
                        <label><b>NO. AHLI: </b><?php echo htmlspecialchars($memberData['employeeID']); ?></label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-8">
                        <label><b>NO. K/P: </b><?php echo htmlspecialchars($memberData['ic']); ?></label>
                    </div>
                    <div class="col-4">
                        <label><b>NO. PF: </b><?php echo htmlspecialchars($memberData['no_pf']); ?></label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Official Letter Content -->
    <div class="mb-4">
        <p>Tuan/Puan,</p>
        <p><u>PENGESAHAN PENYATA KEWANGAN AHLI KOPERASI KAKITANGAN KADA KELANTAN BERHAD BAGI TAHUN BERAKHIR <?php echo date('j M Y'); ?></u></p>
        <p>Untuk penentuan Juruaudit, kami dengan ini menyatakan bagi akaun tuan/puan adalah sebagaimana berikut:</p>
    </div>

    <!-- Financial Details -->
    <div class="row mb-4">
        <!-- Shares & Savings Section -->
        <div class="col-12 mb-4">
            <h6><u>MAKLUMAT SAHAM AHLI:</u></h6>
            <table class="table table-bordered">
                <tr>
                    <td width="50%">Modal Saham</td>
                    <td>RM <?php echo number_format($memberData['modalShare'] ?? 0, 2); ?></td>
                </tr>
                <tr>
                    <td>Modal Yuran</td>
                    <td>RM <?php echo number_format($memberData['feeCapital'] ?? 0, 2); ?></td>
                </tr>
                <tr>
                    <td>Simpanan Tetap</td>
                    <td>RM <?php echo number_format($memberData['fixedDeposit'] ?? 0, 2); ?></td>
                </tr>
                <tr>
                    <td>Tabung Anggota</td>
                    <td>RM <?php echo number_format($memberData['contribution'] ?? 0, 2); ?></td>
                </tr>
            </table>
        </div>

        <!-- Loans Section -->
        <div class="col-12">
            <h6><u>MAKLUMAT PINJAMAN AHLI:</u></h6>
            <table class="table table-bordered">
                <tr>
                    <td width="50%">Al-Bai</td>
                    <td>RM <?php echo number_format($loanData['alBai'] ?? 0, 2); ?></td>
                </tr>
                <tr>
                    <td>Al-Innah</td>
                    <td>RM <?php echo number_format($loanData['alnnah'] ?? 0, 2); ?></td>
                </tr>
                <tr>
                    <td>B/Pulih Kenderaan</td>
                    <td>RM <?php echo number_format($loanData['bPulihKenderaan'] ?? 0, 2); ?></td>
                </tr>
                <tr>
                    <td>Road Tax & Insuran</td>
                    <td>RM <?php echo number_format($loanData['roadTaxInsurance'] ?? 0, 2); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Confirmation Section -->
    <div class="confirmation-section mt-4">
        <div class="border-top pt-4">
            <p><strong>PENGESAHAN BAGI PENYATA KEWANGAN</strong></p>
            <p>Saya <strong><?php echo htmlspecialchars($memberData['memberName']); ?></strong> 
               No. Ahli: <strong><?php echo formatNumber($memberData['employeeID']); ?></strong> 
               mengesahkan bahawa Penyata Kewangan Koperasi Kakitangan KADA Kelantan Berhad adalah benar.</p>
            
            <?php if (isset($_POST['confirmation']) && $_POST['confirmation'] == 'agree'): ?>
                <p><strong>Status: Setuju</strong></p>
            <?php elseif (isset($_POST['confirmation']) && $_POST['confirmation'] == 'disagree'): ?>
                <p><strong>Status: Tidak Setuju</strong></p>
            <?php else: ?>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="confirmation" id="agree" value="agree" required>
                    <label class="form-check-label" for="agree">Setuju</label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="confirmation" id="disagree" value="disagree">
                    <label class="form-check-label" for="disagree">Tidak Setuju</label>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Print Button -->
    <div class="text-end mt-4 no-print">
        <button onclick="window.print()" class="btn btn-secondary">
            <i class="fas fa-print"></i> Cetak
        </button>
    </div>
</div>

<style>
@media print {
    /* 移除所有边框和背景 */
    body, html {
        margin: 0 !important;
        padding: 0 !important;
        background: none !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* 内容区域样式 */
    .container {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 20px 40px !important; /* 调整左右内边距 */
        box-shadow: none !important;
        background: none !important;
    }

    /* 表格样式 */
    .table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin-bottom: 20px !important;
    }

    .table td {
        border: 1px solid black !important;
        padding: 8px !important;
    }

    /* 隐藏所有不需要的元素 */
    .no-print,
    nav,
    header,
    footer,
    .btn,
    .content-wrapper::before,
    .content-wrapper::after {
        display: none !important;
    }

    /* 移除所有边框和阴影 */
    * {
        box-shadow: none !important;
        border-radius: 0 !important;
    }

    /* 确保文本清晰可见 */
    * {
        color: black !important;
        text-shadow: none !important;
    }

    /* 移除所有背景图片和颜色 */
    body::before,
    body::after,
    .container::before,
    .container::after {
        display: none !important;
    }
}

/* 正常显示时的样式保持不变 */
</style>

<!-- 添加内容包装器 -->
<div class="content-wrapper">
    <!-- 现有的内容 -->
</div>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</div> 
</div> 