<!-- for member who havent apply loan and user which havent apply member -->
<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['employeeID'])) {
    header('Location: login.php');
    exit();
}

include "headermember.php";
include "dbconnect.php";
// include "footer";

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

<div class="wrapper">
    <div class="container mt-5">
        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-md-3">
                <div class="profile-sidebar text-center">
                    <div class="profile-image mb-4">
                        <img src="img/profile.jpeg" class="rounded-circle img-fluid" alt="Profile Picture" style="width: 200px; height: 200px; object-fit: cover;">
                        <h3 class="mt-3"><?php echo isset($userData['memberName']) ? $userData['memberName'] : (isset($userData['name']) ? $userData['name'] : 'User'); ?></h3>
                    </div>

                    <div class="profile-nav d-flex flex-column gap-1">
                        <a href="profil2.php" class="btn w-75 mx-auto" style="background-color: #8CD9B5; color: white;">
                            Profil
                        </a>
                        <?php if ($isMember): ?>
                            <a href="statuspermohonanloan.php" class="btn w-75 mx-auto" style="background-color: #75B798; color: white;">
                                Status Permohonan
                            </a>
                            <a href="penyatakewangan.php" class="btn w-75 mx-auto" style="background-color: #75B798; color: white;">
                                Penyata Kewangan
                            </a>
                        <?php else: ?>
                            <a href="daftar_ahli.php" class="btn w-75 mx-auto" style="background-color: #75B798; color: white;">
                                Mohon Keahlian
                            </a>
                        <?php endif; ?>
                        <a href="logout.php" class="btn w-75 mx-auto" style="background-color: #75B798; color: white;">
                            Log Keluar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Content -->
            <div class="col-md-9">
                <div class="card">
                    <form id="profileForm" method="POST" action="update_profil.php">
                        <input type="hidden" name="source_page" value="profil2.php">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">MAKLUMAT PERIBADI</h4>
                        </div>
                        <div class="card-body">
                            <!-- 基本信息部分 -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Penuh:</label>
                                    <input type="text" class="form-control" name="memberName" 
                                           value="<?php echo isset($userData['memberName']) ? htmlspecialchars($userData['memberName']) : 
                                                 (isset($userData['name']) ? htmlspecialchars($userData['name']) : '-'); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No. MyKad:</label>
                                    <input type="text" class="form-control" name="ic" 
                                           value="<?php echo isset($userData['ic']) ? htmlspecialchars($userData['ic']) : '-'; ?>" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Alamat Email:</label>
                                    <input type="text" class="form-control" name="email" 
                                           value="<?php echo isset($userData['email']) ? htmlspecialchars($userData['email']) : '-'; ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No. Telefon:</label>
                                    <input type="text" class="form-control" name="phoneNumber" 
                                           value="<?php echo isset($userData['phoneNumber']) ? htmlspecialchars($userData['phoneNumber']) : '-'; ?>" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Jantina:</label>
                                    <input type="text" class="form-control" name="sex" 
                                           value="<?php echo isset($userData['sex']) ? htmlspecialchars($userData['sex']) : '-'; ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status Perkahwinan:</label>
                                    <input type="text" class="form-control" name="maritalStatus" 
                                           value="<?php echo isset($userData['maritalStatus']) ? htmlspecialchars($userData['maritalStatus']) : '-'; ?>" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Agama:</label>
                                    <input type="text" class="form-control" name="religion" 
                                           value="<?php echo isset($userData['religion']) ? htmlspecialchars($userData['religion']) : '-'; ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bangsa:</label>
                                    <input type="text" class="form-control" name="nation" 
                                           value="<?php echo isset($userData['nation']) ? htmlspecialchars($userData['nation']) : '-'; ?>" readonly>
                                </div>
                            </div>

                            <?php if ($isMember): ?>
                            <!-- 会员地址信息 -->
                            <div class="card mt-4">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">Alamat Rumah</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Alamat:</strong>
                                        <input type="text" class="form-control" name="homeAddress" 
                                               value="<?php echo isset($userData['homeAddress']) ? htmlspecialchars($userData['homeAddress']) : '-'; ?>" readonly>
                                    </p>
                                    <p><strong>Poskod:</strong>
                                        <input type="text" class="form-control" name="homePostcode" 
                                               value="<?php echo isset($userData['homePostcode']) ? htmlspecialchars($userData['homePostcode']) : '-'; ?>" readonly>
                                    </p>
                                    <p><strong>Negeri:</strong>
                                        <input type="text" class="form-control" name="homeState" 
                                               value="<?php echo isset($userData['homeState']) ? htmlspecialchars($userData['homeState']) : '-'; ?>" readonly>
                                    </p>
                                </div>
                            </div>

                            <div class="card mt-4">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">Alamat Pejabat</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Alamat:</strong>
                                        <input type="text" class="form-control" name="officeAddress" 
                                               value="<?php echo isset($userData['officeAddress']) ? htmlspecialchars($userData['officeAddress']) : '-'; ?>" readonly>
                                    </p>
                                    <p><strong>Poskod:</strong>
                                        <input type="text" class="form-control" name="officePostcode" 
                                               value="<?php echo isset($userData['officePostcode']) ? htmlspecialchars($userData['officePostcode']) : '-'; ?>" readonly>
                                    </p>
                                    <p><strong>Negeri:</strong>
                                        <input type="text" class="form-control" name="officeState" 
                                               value="<?php echo isset($userData['officeState']) ? htmlspecialchars($userData['officeState']) : '-'; ?>" readonly>
                                    </p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!$isMember): ?>
                            <!-- 非会员提示信息 -->
                            <!-- <div class="alert alert-info mt-4">
                                <i class="fas fa-info-circle"></i>
                                Untuk mengakses lebih banyak fungsi, sila mohon keahlian KADA.
                                <a href="daftar_ahli.php" class="alert-link" >Mohon Sekarang</a>
                            </div> -->
                            <div class="alert alert-info mt-4">
                                <i class="fas fa-info-circle"></i>
                                Untuk mengakses lebih banyak fungsi, sila mohon keahlian KADA.
                                <a href="daftar_ahli.php" class="btn btn-danger ms-2" style="font-weight: bold;">
                                    Mohon Sekarang <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- 修改 card-footer 部分 -->
                        <div class="card-footer text-end">
                            <?php if ($isMember): ?>
                                <button type="button" class="btn btn-primary" id="editButton" onclick="editProfile()">
                                    <i class="fas fa-edit"></i> Kemaskini
                                </button>
                                <button type="submit" class="btn btn-success" id="updateButton" style="display: none;">
                                    <i class="fas fa-save"></i> Simpan
                                </button>
                                <button type="button" class="btn btn-secondary" id="cancelButton" onclick="cancelEdit()" style="display: none;">
                                    <i class="fas fa-times"></i> Batal
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editProfile() {
    // 移除所有输入框的 readonly 属性
    const inputs = document.querySelectorAll('#profileForm input[type="text"]');
    inputs.forEach(input => {
        if (input.name !== 'employeeID' && 
            input.name !== 'ic' && 
            input.name !== 'memberName') {
            input.removeAttribute('readonly');
            input.style.backgroundColor = '#ffffff';
        }
    });

    // 显示/隐藏按钮
    document.getElementById('editButton').style.display = 'none';
    document.getElementById('updateButton').style.display = 'inline-block';
    document.getElementById('cancelButton').style.display = 'inline-block';
}

function cancelEdit() {
    if(confirm('Adakah anda pasti untuk membatalkan?')) {
        location.href = 'profil2.php';
    }
}

// 添加表单提交处理
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if(confirm('Adakah anda pasti untuk menyimpan perubahan ini?')) {
        this.submit();
    }
});
</script>

<style>
/* 添加按钮样式 */
.card-footer {
    background-color: transparent;
    border-top: 1px solid #dee2e6;
    padding: 1rem;
}

.btn {
    padding: 0.5rem 1rem;
    margin-left: 0.5rem;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

/* 确保表单控件样式正确 */
.form-control {
    display: block;
    width: 100%;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.alert-info .btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
    transition: all 0.3s ease;
}

.alert-info .btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
    transform: scale(1.05);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.wrapper {
    min-height: calc(100vh - 60px);
    position: relative;
}

.container {
    position: relative;
    z-index: 1;
    padding-bottom: 60px;
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

.card {
    border: none;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    border-radius: 10px;
    margin-bottom: 20px;
}

.card-header {
    background-color: #5CBA9B !important;
    border-radius: 10px 10px 0 0 !important;
    padding: 15px 20px;
}

/* Button colors */
.btn-profile {
    background-color: #8CD9B5;
    color: white;
}

.btn-application {
    background-color: #75B798;
    color: white;
}

.btn-logout {
    background-color: #75B798;
    color: white;
}

/* Additional styles for consistent spacing */
.profile-nav {
    margin-top: 20px;
}

.profile-nav .btn {
    margin-bottom: 10px;
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">