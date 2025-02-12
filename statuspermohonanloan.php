<?php
session_start();
include "dbconnect.php";

if (!isset($_SESSION['employeeID'])) {
    header("Location: login.php");
    exit();
}

$employeeID = $_SESSION['employeeID'];

// 获取会员数据
$sql_member = "SELECT * FROM tb_member WHERE employeeID = ?";   
$stmt_member = mysqli_prepare($conn, $sql_member);
mysqli_stmt_bind_param($stmt_member, 's', $employeeID);
mysqli_stmt_execute($stmt_member);
$result_member = mysqli_stmt_get_result($stmt_member);
$userData = mysqli_fetch_assoc($result_member);

// 如果没有找到会员数据，设置默认值
if (!$userData) {
    $userData = [
        'memberName' => 'User',
        'membershipNo' => '-',
        // 其他需要的默认值
    ];
}

// 获取贷款申请数据
$sql = "SELECT 
    la.loanApplicationID,
    la.loanStatus,
    la.loanApplicationDate,
    la.amountRequested,
    la.financingPeriod,
    la.monthlyInstallments
FROM tb_loanapplication la
WHERE la.employeeID = ? 
ORDER BY la.loanApplicationDate DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $employeeID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

include "headermember.php";
include "footer.php";
?>

<div class="container mt-5">
    <div class="row">
        <!-- Left Sidebar -->
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="profile-sidebar">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="profile-image mb-4">
                                    <img src="img/profile.jpeg" class="rounded-circle img-fluid" alt="Profile Picture" style="width: 200px; height: 200px; object-fit: cover;">
                                    <h3 class="mt-3"><?php echo $userData['memberName'] !== '-' ? $userData['memberName'] : 'User'; ?></h3>
                                </div>

                                <div class="profile-nav d-flex flex-column gap-1">
                                    <a href="profil.php" class="btn w-75 mx-auto" style="background-color: #75B798; color: white;">
                                        Profil
                                    </a>
                                    <a href="status.php" class="btn w-75 mx-auto" style="background-color: #75B798; color: white;">
                                        Status Permohonan
                                    </a>
                                    <a href="penyatakewangan.php" class="btn w-75 mx-auto" style="background-color: #75B798; color: white;">
                                        Penyata Kewangan
                                    </a>
                                    <a href="logout.php" class="btn w-75 mx-auto" style="background-color: #75B798; color: white;">
                                        Log Keluar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i> Sejarah Permohonan Pembiayaan</h5>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No.</th>
                                        <th>Tarikh Permohonan</th>
                                        <th>Jumlah (RM)</th>
                                        <th>Tempoh (Bulan)</th>
                                        <th>Ansuran (RM)</th>
                                        <th>Status</th>
                                        <th>Butiran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $i = 1;
                                    while ($row = mysqli_fetch_assoc($result)): 
                                    ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row['loanApplicationDate'])); ?></td>
                                            <td><?php echo number_format($row['amountRequested'], 2); ?></td>
                                            <td><?php echo $row['financingPeriod']; ?></td>
                                            <td><?php echo number_format($row['monthlyInstallments'], 2); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = '';
                                                $statusIcon = '';
                                                switch ($row['loanStatus']) {
                                                    case 'Diluluskan':
                                                        $statusClass = 'success';
                                                        $statusIcon = 'check-circle';
                                                        break;
                                                    case 'Ditolak':
                                                        $statusClass = 'danger';
                                                        $statusIcon = 'times-circle';
                                                        break;
                                                    case 'Dalam Proses':
                                                        $statusClass = 'warning';
                                                        $statusIcon = 'clock';
                                                        break;
                                                    default:
                                                        $statusClass = 'secondary';
                                                        $statusIcon = 'hourglass-half';
                                                }
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?>">
                                                    <i class="fas fa-<?php echo $statusIcon; ?> me-1"></i>
                                                    <?php echo $row['loanStatus']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="lihatStatus.php?id=<?php echo $row['loanApplicationID']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> Lihat
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tiada permohonan pembiayaan</h5>
                            <a href="permohonanloan.php" class="btn btn-primary mt-3">
                                <i class="fas fa-plus me-2"></i>Buat Permohonan Baru
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add required CSS and JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
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

.list-group-item {
    border: none;
    padding: 12px 20px;
    transition: all 0.3s ease;
}

.list-group-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

.list-group-item.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.table {
    vertical-align: middle;
}

.badge {
    padding: 8px 12px;
    font-weight: 500;
}

.btn-sm {
    padding: 5px 10px;
}

.card {
    border: none;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    border-radius: 10px;
}

.card-header {
    background-color: #5CBA9B !important;
    border-radius: 10px 10px 0 0 !important;
    padding: 15px 20px;
}

.table-responsive {
    border-radius: 10px;
}

.table th {
    font-weight: 600;
    color: #495057;
}
</style>

<script>
// Remove or comment out this function as it's no longer needed
/*
function viewDetails(loanID) {
    alert('View details for loan ID: ' + loanID);
}
*/

function checkLoanStatus() {
    const employeeID = <?php echo $employeeID; ?>;
    
    $.ajax({
        url: 'check_loan_status.php',
        method: 'POST',
        data: { employeeID: employeeID },
        success: function(response) {
            try {
                const data = JSON.parse(response);
                if (data.status !== currentStatus) {
                    location.reload();
                }
            } catch (e) {
                console.error('Error parsing response:', e);
            }
        }
    });
}

// Check for updates every 30 seconds
setInterval(checkLoanStatus, 30000);
</script>