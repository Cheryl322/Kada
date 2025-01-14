<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['employeeID'])) {
    header('Location: login.php');
    exit();
}

include "headermember.php";
include "dbconnect.php";

// Get member data including addresses
$employeeId = $_SESSION['employeeID'];
$sql = "SELECT m.*, 
               h.homeAddress, h.homePostcode, h.homeState,
               o.officeAddress, o.officePostcode, o.officeState
        FROM tb_member m
        LEFT JOIN tb_member_homeaddress h ON m.employeeID = h.employeeID
        LEFT JOIN tb_member_officeaddress o ON m.employeeID = o.employeeID
        WHERE m.employeeID = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $employeeId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$memberData = mysqli_fetch_assoc($result);
?>

<div class="container">
    <div class="row">
        <!-- Left Sidebar -->
        <div class="col-md-3">
            <div class="profile-sidebar">
                <div class="profile-image">
                    <img src="img/profile.jpeg" class="rounded-circle" alt="Profile Picture">
                    <h3 class="text-left mt-3"><?php echo isset($memberData['memberName']) ? $memberData['memberName'] : 'User'; ?></h3>
                </div>

                <!-- Navigation Menu -->
                <div class="profile-nav">
                    <ul class="nav flex-column gap-2">
                        <li class="nav-item w-100">
                            <a class="btn btn-primary w-75" href="profil.php">Profil</a>
                        </li>
                        <li class="nav-item w-100">
                            <a class="btn btn-info w-75" href="statuskewangan.php">Pinjaman</a>
                        </li>
                        <li class="nav-item w-100">
                            <a class="btn btn-info w-75" href="statuspermohonanloan.php">Permohonan</a>
                        </li>
                        <li class="nav-item w-100">
                            <a class="btn btn-info w-75" href="logout.php">Daftar Keluar</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Right Content -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">MAKLUMAT PERIBADI</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Penuh:</label>
                            <p class="form-control"><?php echo isset($memberData['memberName']) ? $memberData['memberName'] : '-'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. MyKad:</label>
                            <p class="form-control"><?php echo isset($memberData['ic']) ? $memberData['ic'] : '-'; ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Alamat Email:</label>
                            <p class="form-control"><?php echo isset($memberData['email']) ? $memberData['email'] : '-'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Telefon:</label>
                            <p class="form-control"><?php echo isset($memberData['phoneNumber']) ? $memberData['phoneNumber'] : '-'; ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Jantina:</label>
                            <p class="form-control"><?php echo isset($memberData['sex']) ? $memberData['sex'] : '-'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Perkahwinan:</label>
                            <p class="form-control"><?php echo isset($memberData['maritalStatus']) ? $memberData['maritalStatus'] : '-'; ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Agama:</label>
                            <p class="form-control"><?php echo isset($memberData['religion']) ? $memberData['religion'] : '-'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bangsa:</label>
                            <p class="form-control"><?php echo isset($memberData['nation']) ? $memberData['nation'] : '-'; ?></p>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Alamat Rumah</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Alamat:</strong> <?php echo isset($memberData['homeAddress']) ? $memberData['homeAddress'] : '-'; ?></p>
                            <p><strong>Poskod:</strong> <?php echo isset($memberData['homePostcode']) ? $memberData['homePostcode'] : '-'; ?></p>
                            <p><strong>Negeri:</strong> <?php echo isset($memberData['homeState']) ? $memberData['homeState'] : '-'; ?></p>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Alamat Pejabat</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Alamat:</strong> <?php echo isset($memberData['officeAddress']) ? $memberData['officeAddress'] : '-'; ?></p>
                            <p><strong>Poskod:</strong> <?php echo isset($memberData['officePostcode']) ? $memberData['officePostcode'] : '-'; ?></p>
                            <p><strong>Negeri:</strong> <?php echo isset($memberData['officeState']) ? $memberData['officeState'] : '-'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>