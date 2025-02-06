<?php
session_start();

if (!isset($_SESSION['employeeID'])) {
    header('Location: login.php');
    exit();
}

include "headermember.php";
include "dbconnect.php";

$employeeID = $_SESSION['employeeID'];

// Get member data
$sql = "SELECT memberName FROM tb_member WHERE employeeID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $employeeID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$memberData = mysqli_fetch_assoc($result);
?>

<div class="container mt-5">
    <div class="row">
        <!-- Left Sidebar -->
        <div class="col-md-3">
            <div class="profile-sidebar text-center">
                <div class="profile-image mb-4">
                    <img src="img/profile.jpeg" class="rounded-circle img-fluid" alt="Profile Picture" style="width: 200px; height: 200px; object-fit: cover;">
                    <h3 class="mt-3"><?php echo isset($memberData['memberName']) ? htmlspecialchars($memberData['memberName']) : 'Member'; ?></h3>
                </div>

                <div class="profile-nav d-flex flex-column gap-3">
                    <a href="profil.php" class="btn w-75 mx-auto" style="background-color: #75B798; color: white;">
                        Profil
                    </a>
                    <a href="status.php" class="btn w-75 mx-auto" style="background-color: #8CD9B5; color: white;">
                        Status Permohonan
                    </a>
                    <a href="penyatakewangan.php" class="btn w-75 mx-auto" style="background-color: #75B798; color: white;">
                        Penyata Kewangan
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
</style>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<?php include "footer.php"; ?> 