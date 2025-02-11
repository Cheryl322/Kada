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

include "headermember.php";
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
                    <h5 class="mb-0">Pilihan Status</h5>
                </div>
                <div class="card-body">
                    <div class="row justify-content-center">
                        <div class="col-md-6 mb-4">
                            <div class="status-card">
                                <a href="statuspermohonanloan.php" class="text-decoration-none">
                                    <div class="card h-100 status-option">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-invoice-dollar fa-4x mb-3" style="color: #5CBA9B;"></i>
                                            <h4 class="card-title">Status Pembiayaan</h4>
                                            <p class="card-text">Lihat status permohonan pembiayaan anda</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="status-card">
                                <a href="statusanggota.php" class="text-decoration-none">
                                    <div class="card h-100 status-option">
                                        <div class="card-body text-center">
                                            <i class="fas fa-user-check fa-4x mb-3" style="color: #5CBA9B;"></i>
                                            <h4 class="card-title">Status Anggota</h4>
                                            <p class="card-text">Lihat status keahlian dan maklumat anggota anda</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="status-card">
                                <a href="status_permohonanberhenti.php" class="text-decoration-none">
                                    <div class="card h-100 status-option">
                                        <div class="card-body text-center">
                                            <i class="fas fa-user-check fa-4x mb-3" style="color: #5CBA9B;"></i>
                                            <h4 class="card-title">Status Permohonan Berhenti</h4>
                                            <p class="card-text">Lihat status permohonan berhenti anda</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.status-option {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: none;
    border-radius: 15px;
    background: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.status-option:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
}

.status-card a {
    color: inherit;
}

.status-card .card {
    height: 100%;
    padding: 2rem 1rem;
}

.status-card .card-body {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.status-card .card-title {
    color: #2c3e50;
    margin: 1rem 0;
    font-weight: 600;
}

.status-card .card-text {
    color: #666;
    text-align: center;
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

/* 卡片样式优化 */
.card {
    border: none;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    margin-bottom: 1.5rem;
    border-radius: 15px;
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 6px 30px rgba(0, 0, 0, 0.1);
}

/* 左侧栏样式优化 */
.profile-sidebar .card {
    background: linear-gradient(to bottom, #ffffff, #f8f9fa);
}

.profile-image img {
    border: 4px solid #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.profile-image img:hover {
    transform: scale(1.02);
}

.profile-nav .btn {
    background: linear-gradient(45deg, #75B798, #5a8f76);
    border: none;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.profile-nav .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(117, 183, 152, 0.2);
    background: linear-gradient(45deg, #5a8f76, #4d7a64);
}

/* 徽章样式 */
.badge {
    padding: 0.5em 1em;
    border-radius: 30px;
    font-weight: 500;
    letter-spacing: 0.5px;
}

/* 响应式优化 */
@media (max-width: 768px) {
    .profile-image img {
        width: 150px;
        height: 150px;
    }
    
    .profile-nav .btn {
        padding: 0.5rem 1rem;
    }
}
</style>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<?php include "footer.php"; ?> 