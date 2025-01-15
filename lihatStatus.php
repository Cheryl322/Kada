<?php
session_start();

if (!isset($_SESSION['employeeID'])) {
    header('Location: login.php');
    exit();
}

include "headermember.php";
include "dbconnect.php";

$employeeID = $_SESSION['employeeID'];
$loanApplicationID = isset($_GET['id']) ? $_GET['id'] : null;

if (!$loanApplicationID) {
    header('Location: statuspermohonanloan.php');
    exit();
}

// Get loan application details
$sql = "SELECT 
            la.*,
            l.*,
            b.bankName,
            b.accountNo
        FROM tb_loanapplication la
        LEFT JOIN tb_loan l ON la.loanApplicationID = l.loanApplicationID
        LEFT JOIN tb_bank b ON la.loanApplicationID = b.loanApplicationID
        WHERE la.loanApplicationID = ? AND la.employeeID = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $loanApplicationID, $employeeID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$loanData = mysqli_fetch_assoc($result);

// Get guarantor details
$sql_guarantor = "SELECT * FROM tb_guarantor WHERE loanApplicationID = ?";
$stmt_guarantor = mysqli_prepare($conn, $sql_guarantor);
mysqli_stmt_bind_param($stmt_guarantor, "i", $loanApplicationID);
mysqli_stmt_execute($stmt_guarantor);
$result_guarantor = mysqli_stmt_get_result($stmt_guarantor);
$guarantors = mysqli_fetch_all($result_guarantor, MYSQLI_ASSOC);

// Add this query to get member name
$sqlMember = "SELECT memberName FROM tb_member WHERE employeeID = ?";
$stmtMember = mysqli_prepare($conn, $sqlMember);
mysqli_stmt_bind_param($stmtMember, "i", $employeeID);
mysqli_stmt_execute($stmtMember);
$resultMember = mysqli_stmt_get_result($stmtMember);
$memberData = mysqli_fetch_assoc($resultMember);
?>

<div class="wrapper">
    <div class="container mt-5">
        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-md-3">
                <div class="profile-sidebar text-center">
                    <div class="profile-image mb-4">
                        <img src="img/profile.jpeg" class="rounded-circle img-fluid" alt="Profile Picture" style="width: 200px; height: 200px; object-fit: cover;">
                        <h3 class="mt-3"><?php echo htmlspecialchars($memberData['memberName']); ?></h3>
                    </div>

                    <div class="profile-nav d-flex flex-column gap-3">
                        <a href="profil2.php" class="btn w-75 mx-auto" style="background-color: #75B798; color: white;">
                            Profil
                        </a>
                    
                        <a href="statuspermohonanloan.php" class="btn w-75 mx-auto" style="background-color: #8CD9B5; color: white;">
                            Permohonan
                        </a>
                        <a href="logout.php" class="btn w-75 mx-auto" style="background-color: #75B798; color: white;">
                            Daftar Keluar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Butiran Permohonan Pembiayaan</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($loanData): ?>
                            <!-- Loan Details Section -->
                            <div class="section mb-4">
                                <h6 class="section-title">Maklumat Pembiayaan</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Tarikh Permohonan:</strong> <?php echo date('d/m/Y', strtotime($loanData['loanApplicationDate'])); ?></p>
                                        <p><strong>Jumlah Dipohon:</strong> RM <?php echo number_format($loanData['amountRequested'], 2); ?></p>
                                        <p><strong>Tempoh Pembayaran:</strong> <?php echo $loanData['financingPeriod']; ?> bulan</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Ansuran Bulanan:</strong> RM <?php echo number_format($loanData['monthlyInstallments'], 2); ?></p>
                                        <p><strong>Status:</strong> 
                                            <span class="badge status-badge bg-<?php 
                                                echo $loanData['loanStatus'] == 'Diluluskan' ? 'success' : 
                                                    ($loanData['loanStatus'] == 'Ditolak' ? 'danger' : 'warning'); 
                                            ?>">
                                                <i class="fas fa-<?php 
                                                    echo $loanData['loanStatus'] == 'Diluluskan' ? 'check-circle' : 
                                                        ($loanData['loanStatus'] == 'Ditolak' ? 'times-circle' : 'clock'); 
                                                ?> me-2"></i>
                                                <?php echo $loanData['loanStatus']; ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Bank Details Section -->
                            <div class="section mb-4">
                                <h6 class="section-title">Maklumat Bank</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Nama Bank:</strong> <?php echo $loanData['bankName']; ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>No. Akaun:</strong> <?php echo $loanData['accountNo']; ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Employer Details Section -->
                            <div class="section mb-4">
                                <h6 class="section-title">Maklumat Majikan</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Nama Majikan:</strong> <?php echo $loanData['employerName']; ?></p>
                                        <p><strong>No. KP Majikan:</strong> <?php echo $loanData['employerIC']; ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Gaji Pokok:</strong> RM <?php echo number_format($loanData['basicSalary'], 2); ?></p>
                                        <p><strong>Gaji Bersih:</strong> RM <?php echo number_format($loanData['netSalary'], 2); ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Guarantor Details Section -->
                            <div class="section">
                                <h6 class="section-title">Maklumat Penjamin</h6>
                                <?php foreach ($guarantors as $index => $guarantor): ?>
                                    <div class="guarantor-details mb-3">
                                        <h6>Penjamin <?php echo $index + 1; ?></h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Nama:</strong> <?php echo $guarantor['guarantorName']; ?></p>
                                                <p><strong>No. KP:</strong> <?php echo $guarantor['guarantorIC']; ?></p>
                                                <p><strong>No. Telefon:</strong> <?php echo $guarantor['guarantorPhone']; ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>No. PF:</strong> <?php echo $guarantor['guarantorPFNo']; ?></p>
                                                <p><strong>No. Anggota:</strong> <?php echo $guarantor['guarantorMemberNo']; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mt-4">
                                <a href="statuspermohonanloan.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                Maklumat permohonan tidak dijumpai.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.wrapper {
    min-height: calc(100vh - 60px);
    position: relative;
}

.card {
    border: none;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    border-radius: 10px;
    margin-bottom: 20px;
}

.container {
    position: relative;
    z-index: 1;
}

.profile-sidebar {
    background: transparent;
    box-shadow: none;
}

.profile-nav .btn {
    border: none;
    padding: 10px;
    font-size: 18px;
    transition: all 0.3s ease;
}

.profile-nav .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    opacity: 0.9;
}

.profile-image img {
    border: 3px solid #fff;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}

.profile-image h3 {
    color: #333;
    font-weight: 500;
}

.section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.section-title {
    color: #5CBA9B;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #5CBA9B;
}

.guarantor-details {
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.guarantor-details h6 {
    color: #5CBA9B;
    margin-bottom: 15px;
}

.badge {
    padding: 8px 12px;
    font-weight: 500;
}

.status-badge {
    font-size: 1rem;
    padding: 8px 16px;
    border-radius: 6px;
    font-weight: 500;
    letter-spacing: 0.5px;
}

.status-badge i {
    font-size: 1.1rem;
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
