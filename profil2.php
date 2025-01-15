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
                            <a class="btn btn-primary w-75" href="profil2.php">Profil</a>
                        </li>
                        <?php if ($isMember): ?>
                        <!-- 会员菜单选项 -->
                        <li class="nav-item w-100">
                            <a class="btn btn-info w-75" href="statuspermohonanloan.php">Status Permohonan</a>
                        </li>
                        <?php else: ?>
                        <!-- 非会员菜单选项 -->
                        <li class="nav-item w-100">
                            <a class="btn btn-info w-75" href="daftar_ahli.php">Mohon Keahlian</a>
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
</style>