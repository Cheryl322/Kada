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
$sqlMember = "SELECT 
    m.*,
    COALESCE((
        SELECT SUM(CASE 
            WHEN d.DeducType_ID = 1 THEN d.Deduct_Amt -- Modal Saham
            ELSE 0 
        END)
        FROM tb_deduction d 
        WHERE d.employeeID = m.employeeID
    ), 0) as modalShare,
    COALESCE((
        SELECT SUM(CASE 
            WHEN d.DeducType_ID = 2 THEN d.Deduct_Amt -- Modal Yuran
            ELSE 0 
        END)
        FROM tb_deduction d 
        WHERE d.employeeID = m.employeeID
    ), 0) as feeCapital,
    COALESCE((
        SELECT SUM(CASE 
            WHEN d.DeducType_ID = 3 THEN d.Deduct_Amt -- Simpanan Tetap
            ELSE 0 
        END)
        FROM tb_deduction d 
        WHERE d.employeeID = m.employeeID
    ), 0) as fixedDeposit,
    COALESCE((
        SELECT SUM(CASE 
            WHEN d.DeducType_ID = 4 THEN d.Deduct_Amt -- Tabung Anggota
            ELSE 0 
        END)
        FROM tb_deduction d 
        WHERE d.employeeID = m.employeeID
    ), 0) as contribution,
    COALESCE((
        SELECT SUM(CASE 
            WHEN d.DeducType_ID = 5 THEN d.Deduct_Amt -- Simpanan Anggota
            ELSE 0 
        END)
        FROM tb_deduction d 
        WHERE d.employeeID = m.employeeID
    ), 0) as memberSavings
FROM tb_member m
WHERE m.employeeID = ?";
$stmtMember = mysqli_prepare($conn, $sqlMember);
mysqli_stmt_bind_param($stmtMember, 's', $employeeID);
mysqli_stmt_execute($stmtMember);
$memberData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtMember));

// 添加调试输出
echo "<!-- Starting loan query for employeeID: $employeeID -->";

// 修改贷款查询
$sqlLoan = "SELECT 
    l.loanApplicationID,
    l.loanType,
    l.amountRequested,
    l.balance,
    la.loanStatus,
    COALESCE((
        SELECT SUM(d.Deduct_Amt)
        FROM tb_deduction d
        WHERE d.employeeID = l.employeeID 
        AND d.DeducType_ID = 6
        AND d.loanApplicationID = l.loanApplicationID
    ), 0) as total_repaid
FROM tb_loan l
JOIN tb_loanapplication la ON l.loanApplicationID = la.loanApplicationID
WHERE l.employeeID = ? 
AND la.loanStatus = 'Diluluskan'";

$stmtLoan = mysqli_prepare($conn, $sqlLoan);
mysqli_stmt_bind_param($stmtLoan, 's', $employeeID);
mysqli_stmt_execute($stmtLoan);
$loanResult = mysqli_stmt_get_result($stmtLoan);

// 详细的调试输出
echo "<!-- Debug: Found " . mysqli_num_rows($loanResult) . " loans -->";
$loanData = [
    'alBai' => [],          // 改为数组以存储多个同类型贷款
    'alInnah' => [],
    'bPulihKenderaan' => [],
    'roadTaxInsurance' => [],
    'khas' => [],
    'alQadrulHassan' => []
];

while ($row = mysqli_fetch_assoc($loanResult)) {
    echo "<!-- Debug loan: " . 
         "ID: " . $row['loanApplicationID'] . ", " .
         "Type: " . $row['loanType'] . ", " .
         "Amount: " . $row['amountRequested'] . ", " .
         "Balance: " . $row['balance'] . ", " .
         "Status: " . $row['loanStatus'] . " -->";
    
    $remainingAmount = $row['balance'] ?? ($row['amountRequested'] - $row['total_repaid']);
    
    // 修改 switch 语句，确保正确匹配 SKIM KHAS
    switch ($row['loanType']) {
        case 'AL-BAI':
            $loanData['alBai'][] = $remainingAmount;
            break;
        case 'AL-INAH':
            $loanData['alInnah'][] = $remainingAmount;
            break;
        case 'B/PULIH KENDERAAN':
            $loanData['bPulihKenderaan'][] = $remainingAmount;
            break;
        case 'ROAD TAX & INSURAN':
            $loanData['roadTaxInsurance'][] = $remainingAmount;
            break;
        case 'SKIM KHAS':  // 修改这里以匹配数据库中的确切名称
            $loanData['khas'][] = $remainingAmount;
            break;
        case 'AL-QADRUL HASSAN':
            $loanData['alQadrulHassan'][] = $remainingAmount;
            break;
    }
}

// 添加调试输出
echo "<!-- Debug loan data: " . json_encode($loanData) . " -->";

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
                <tr>
                    <td>Simpanan Anggota</td>
                    <td>RM <?php echo number_format($memberData['memberSavings'] ?? 0, 2); ?></td>
                </tr>
            </table>
        </div>

        <!-- Loans Section -->
        <div class="col-12">
            <h6><u>MAKLUMAT PINJAMAN AHLI:</u></h6>
            <table class="table table-bordered">
                <tr>
                    <td width="50%">Al-Bai</td>
                    <td>RM <?php echo number_format(array_sum($loanData['alBai']), 2); ?></td>
                </tr>
                <tr>
                    <td>Al-Inah</td>
                    <td>RM <?php echo number_format(array_sum($loanData['alInnah']), 2); ?></td>
                </tr>
                <tr>
                    <td>B/Pulih Kenderaan</td>
                    <td>RM <?php echo number_format(array_sum($loanData['bPulihKenderaan']), 2); ?></td>
                </tr>
                <tr>
                    <td>Road Tax & Insuran</td>
                    <td>RM <?php echo number_format(array_sum($loanData['roadTaxInsurance']), 2); ?></td>
                </tr>
                <tr>
                    <td>Skim Khas</td>
                    <td>RM <?php echo number_format(array_sum($loanData['khas']), 2); ?></td>
                </tr>
                <tr>
                    <td>Al-Qadrul Hassan</td>
                    <td>RM <?php echo number_format(array_sum($loanData['alQadrulHassan']), 2); ?></td>
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
            
            <div class="mb-3">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="confirmation" id="agree" value="agree">
                    <label class="form-check-label" for="agree">Setuju</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="confirmation" id="disagree" value="disagree">
                    <label class="form-check-label" for="disagree">Tidak Setuju</label>
                </div>
            </div>

            <div id="statusMessage" class="alert d-none">
                Status: <span id="statusText"></span>
            </div>

            <div class="text-end">
                <button onclick="window.print()" id="printButton" class="btn btn-danger rounded-pill d-none">
                    <i class="fas fa-print"></i> Cetak
                </button>
            </div>
        </div>
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

.btn-danger {
    background-color: #ff6b6b;
    border: none;
}

.btn-danger:hover {
    background-color: #ff5252;
}

@media print {
    .form-check, 
    #printButton {
        display: none !important;
    }
}
</style>

<!-- 添加内容包装器 -->
<div class="content-wrapper">
    <!-- 现有的内容 -->
</div>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<script>
document.querySelectorAll('input[name="confirmation"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const statusMessage = document.getElementById('statusMessage');
        const statusText = document.getElementById('statusText');
        const printButton = document.getElementById('printButton');
        
        statusMessage.classList.remove('d-none');
        printButton.classList.remove('d-none');
        
        if (this.value === 'agree') {
            statusMessage.className = 'alert alert-success';
            statusText.textContent = 'Setuju';
        } else {
            statusMessage.className = 'alert alert-warning';
            statusText.textContent = 'Tidak Setuju';
        }
    });
});
</script>
</div> 
</div> 