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
$sqlMember = "SELECT * FROM tb_member WHERE employeeID = ?";
$stmtMember = mysqli_prepare($conn, $sqlMember);
mysqli_stmt_bind_param($stmtMember, 's', $employeeID);
mysqli_stmt_execute($stmtMember);
$resultMember = mysqli_stmt_get_result($stmtMember);
$memberData = mysqli_fetch_assoc($resultMember);

// Fetch financial data
$sql = "SELECT f.* FROM tb_financialstatus f
        WHERE f.accountID IN (
            SELECT accountID 
            FROM tb_member_financialstatus 
            WHERE employeeID = ?
        )
        ORDER BY f.dateUpdated DESC LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 's', $employeeID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$financialData = mysqli_fetch_assoc($result);
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
                    <td>RM <?php echo number_format($financialData['memberSaving'] ?? 0, 2); ?></td>
                </tr>
                <tr>
                    <td>Modal Yuran</td>
                    <td>RM <?php echo number_format($financialData['feeCapital'] ?? 0, 2); ?></td>
                </tr>
                <tr>
                    <td>Simpanan Tetap</td>
                    <td>RM <?php echo number_format($financialData['fixedDeposit'] ?? 0, 2); ?></td>
                </tr>
                <tr>
                    <td>Tabung Anggota</td>
                    <td>RM <?php echo number_format($financialData['contribution'] ?? 0, 2); ?></td>
                </tr>
            </table>
        </div>

        <!-- Loans Section -->
        <div class="col-12">
            <h6><u>MAKLUMAT PINJAMAN AHLI:</u></h6>
            <table class="table table-bordered">
                <tr>
                    <td width="50%">Al-Bai</td>
                    <td>RM <?php echo number_format($financialData['alBai'] ?? 0, 2); ?></td>
                </tr>
                <tr>
                    <td>Al-Innah</td>
                    <td>RM <?php echo number_format($financialData['alnnah'] ?? 0, 2); ?></td>
                </tr>
                <tr>
                    <td>B/Pulih Kenderaan</td>
                    <td>RM <?php echo number_format($financialData['bPulihKenderaan'] ?? 0, 2); ?></td>
                </tr>
                <tr>
                    <td>Road Tax & Insuran</td>
                    <td>RM <?php echo number_format($financialData['roadTaxInsurance'] ?? 0, 2); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Confirmation Section -->
    <div class="mt-5">
        <p><b>PENGESAHAN BAGI PENYATA KEWANGAN</b></p>
        <p>Saya <b><?php echo htmlspecialchars($memberData['memberName']); ?></b> No. Ahli: <b><?php echo htmlspecialchars($memberData['employeeID']); ?></b> mengesahkan bahawa Penyata Kewangan Koperasi Kakitangan KADA Kelantan Berhad adalah benar:</p>
        
        <form method="post" action="submit_confirmation.php" class="mt-3">
            <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="confirmation" id="agree" value="agree" required>
                <label class="form-check-label" for="agree">Setuju</label>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="radio" name="confirmation" id="disagree" value="disagree">
                <label class="form-check-label" for="disagree">Tidak Setuju</label>
            </div>
            <!-- <button type="submit" class="btn btn-primary">Hantar Pengesahan</button> -->
        </form>
    </div>

    <!-- Print Button -->
    <div class="text-end mt-4">
        <button onclick="window.print()" class="btn btn-secondary">
            <i class="fas fa-print"></i> Cetak
        </button>
    </div>
</div>

<style>
@media print {
    .btn, .form-check {
        display: none;
    }
    body {
        background-color: white;
    }
    .container {
        box-shadow: none !important;
    }
}

.table {
    margin-bottom: 0;
}

.table td {
    padding: 8px 15px;
}
</style>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</div> 