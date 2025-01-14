<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['employeeID'])) {
    header('Location: login.php');
    exit();
}

include "headermember.php";
include "dbconnect.php";

$employeeId = $_SESSION['employeeID'];

// 检查用户是否为会员
$checkMember = "SELECT * FROM tb_member WHERE employeeID = ?";
$stmt = mysqli_prepare($conn, $checkMember);
mysqli_stmt_bind_param($stmt, 's', $employeeId);
mysqli_stmt_execute($stmt);
$memberResult = mysqli_stmt_get_result($stmt);
$isMember = mysqli_num_rows($memberResult) > 0;

// 根据会员状态获取不同的数据
if ($isMember) {
    $sql = "SELECT m.*, 
                   h.homeAddress, h.homePostcode, h.homeState,
                   o.officeAddress, o.officePostcode, o.officeState
            FROM tb_member m
            LEFT JOIN tb_member_homeaddress h ON m.employeeID = h.employeeID
            LEFT JOIN tb_member_officeaddress o ON m.employeeID = o.employeeID
            WHERE m.employeeID = ?";
} else {
    $sql = "SELECT e.*
            FROM tb_employee e
            WHERE e.employeeID = ?";
}

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $employeeId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$userData = mysqli_fetch_assoc($result);
?>

<div class="container">
    <div class="row">
        <!-- Left Sidebar -->
        <div class="col-md-3">
            <div class="profile-sidebar">
                <div class="profile-image">
                    <img src="img/profile.jpeg" class="rounded-circle" alt="Profile Picture">
                    <h3 class="text-left mt-3"><?php echo isset($userData['memberName']) ? $userData['memberName'] : (isset($userData['name']) ? $userData['name'] : 'User'); ?></h3>
                </div>

                <!-- Navigation Menu -->
                <div class="profile-nav">
                    <ul class="nav flex-column gap-2">
                        <li class="nav-item w-100">
                            <a class="btn btn-primary w-75" href="profil.php">Profil</a>
                        </li>
                        <?php if ($isMember): ?>
                        <!-- 会员菜单选项 -->
                        <li class="nav-item w-100">
                            <a class="btn btn-info w-75" href="statuspermohonanloan.php">Status Permohonan</a>
                        </li>
                        <?php else: ?>
                        <!-- 非会员菜单选项 -->
                        <li class="nav-item w-100">
                            <a class="btn btn-info w-75" href="apply_member.php">Mohon Keahlian</a>
                        </li>
                        <?php endif; ?>
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
                <form id="profileForm" method="POST" action="update_profil.php">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">MAKLUMAT PERIBADI</h4>
                    </div>
                    <div class="card-body">
                        <!-- 基本信息部分 -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Penuh:</label>
                                <input type="text" class="form-control" name="memberName" 
                                       value="<?php echo htmlspecialchars($isMember ? $userData['memberName'] : $userData['name']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. MyKad:</label>
                                <input type="text" class="form-control" name="ic" 
                                       value="<?php echo htmlspecialchars($userData['ic']); ?>" readonly>
                            </div>
                        </div>

                        <?php if ($isMember): ?>
                        <!-- 会员专属字段 -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Alamat Email:</label>
                                <input type="text" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($userData['email']); ?>" readonly>
                            </div>
                            <!-- ... 其他会员字段 ... -->
                        </div>
                        <?php endif; ?>

                        <?php if (!$isMember): ?>
                        <!-- 非会员提示信息 -->
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle"></i>
                            Untuk mengakses lebih banyak fungsi, sila mohon keahlian KADA.
                            <a href="apply_member.php" class="alert-link">Mohon Sekarang</a>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($isMember): ?>
                    <!-- 会员地址信息 -->
                    <div class="card mt-4">
                        <!-- ... 地址信息部分 ... -->
                    </div>
                    <?php endif; ?>

                    <div class="card-footer text-end">
                        <button type="button" class="btn btn-primary" id="editButton" onclick="editProfile()">
                            <i class="fas fa-edit"></i> Kemaskini
                        </button>
                        <button type="submit" class="btn btn-success" id="updateButton" style="display: none;">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <button type="button" class="btn btn-secondary" id="cancelButton" onclick="cancelEdit()" style="display: none;">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript 代码保持不变
</script>

<style>
// CSS 样式保持不变
</style>