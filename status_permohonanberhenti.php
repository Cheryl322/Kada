<?php
session_start();
include "dbconnect.php";
include "headermember.php";

if (!isset($_SESSION['employeeID'])) {
    header("Location: login.php");
    exit();
}

$employeeID = $_SESSION['employeeID'];

$sql_member = "SELECT * FROM tb_member WHERE employeeID = ?";   
$stmt_member = mysqli_prepare($conn, $sql_member);
mysqli_stmt_bind_param($stmt_member, 's', $employeeID);
mysqli_stmt_execute($stmt_member);
$userData =mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_member)) ;

// Fix the SQL query
$sql = "SELECT b.*, 
        DATE_FORMAT(b.applyDate, '%d/%m/%Y') as formatApplyDate,
        DATE_FORMAT(b.approveDate, '%d/%m/%Y') as formatApproveDate
        FROM tb_berhenti b
        WHERE b.employeeID = ? 
        ORDER BY b.applyDate DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 's', $employeeID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

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
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h4 class="card-title mb-4">Status Permohonan Berhenti</h4>
                    
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php 
                            echo $_SESSION['success_message'];
                            unset($_SESSION['success_message']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <div class="application-card mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">No. Permohonan: <?php echo $row['berhentiID']; ?></span>
                                    <span class="badge bg-<?php 
                                        echo match($row['approvalStatus']) {
                                            'Pending' => 'warning',
                                            'Lulus' => 'success',
                                            'Tolak' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>"><?php echo $row['approvalStatus']; ?></span>
                                </div>

                                <div class="timeline">
                                    <!-- Application Submitted -->
                                    <div class="timeline-item">
                                        <div class="timeline-point bg-success"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-0">Permohonan Dihantar</h6>
                                            <small class="text-muted"><?php echo $row['formatApplyDate']; ?></small>
                                        </div>
                                    </div>

                                    <!-- Application Status -->
                                    <div class="timeline-item">
                                        <div class="timeline-point bg-<?php 
                                            echo $row['approvalStatus'] != 'Pending' ? 
                                                ($row['approvalStatus'] == 'Lulus' ? 'success' : 'danger') : 'warning'; 
                                        ?>"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-0">Status Permohonan</h6>
                                            <small class="text-muted">
                                                <?php 
                                                if ($row['approvalStatus'] == 'Pending') {
                                                    echo 'Dalam proses...';
                                                } else {
                                                    echo $row['approvalStatus'] . ' pada ' . $row['formatApproveDate'];
                                                }
                                                ?>
                                            </small>
                                            <?php if ($row['approvalStatus'] == 'Tolak' && !empty($row['rejectReason'])): ?>
                                                <div class="mt-2 text-danger">
                                                    <small>Sebab: <?php echo htmlspecialchars($row['rejectReason']); ?></small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tiada rekod permohonan berhenti.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.application-card {
    background: #fff;
    border: 1px solid #eaeaea;
    border-radius: 8px;
    padding: 1.5rem;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-point {
    position: absolute;
    left: -30px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    top: 5px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -24px;
    width: 1px;
    background: #e9ecef;
    height: 100%;
    top: 10px;
}

.badge {
    padding: 0.5em 1em;
    font-weight: 500;
}

.card {
    border-radius: 10px;
}

.card-title {
    color: #2c3e50;
    font-weight: 600;
}

.text-muted {
    color: #6c757d !important;
}

.profile-nav .btn {
    transition: all 0.3s ease;
}

.profile-nav .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }
    
    /* .profile-sidebar {
        margin-bottom: 2rem;
    } */
    
    .profile-image img {
        width: 150px;
        height: 150px;
    }
}
</style>