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
                                <a class="btn btn-info w-75" href="penyatakewangan.php">Penyata Kewangan</a>
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
                <form id="profileForm" method="POST" action="update_profil.php">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">MAKLUMAT PERIBADI</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Penuh:</label>
                                <!-- <p class="form-control"><?php echo isset($memberData['memberName']) ? $memberData['memberName'] : '-'; ?></p> -->
                                <p><input type="text" class="form-control" name="memberName" value="<?php echo htmlspecialchars($memberData['memberName']); ?>"readonly></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. MyKad:</label>
                                <!-- <p class="form-control"><?php echo isset($memberData['ic']) ? $memberData['ic'] : '-'; ?></p> -->
                                <p><input type="text" class="form-control" name="ic" value="<?php echo htmlspecialchars($memberData['ic']); ?>"readonly></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Alamat Email:</label>
                                <!-- <p class="form-control"><?php echo isset($memberData['email']) ? $memberData['email'] : '-'; ?></p> -->
                                <p><input type="text" class="form-control" name="email" value="<?php echo htmlspecialchars($memberData['email']); ?>"readonly></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. Telefon:</label>
                                <!-- <p class="form-control"><?php echo isset($memberData['phoneNumber']) ? $memberData['phoneNumber'] : '-'; ?></p> -->
                                <p><input type="text" class="form-control" name="phoneNumber" value="<?php echo htmlspecialchars($memberData['phoneNumber']); ?>"readonly></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Jantina:</label>
                                <!-- <p class="form-control"><?php echo isset($memberData['sex']) ? $memberData['sex'] : '-'; ?></p> -->
                                <p><input type="text" class="form-control" name="sex" value="<?php echo htmlspecialchars($memberData['sex']); ?>"readonly></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status Perkahwinan:</label>
                                <!-- <p class="form-control"><?php echo isset($memberData['maritalStatus']) ? $memberData['maritalStatus'] : '-'; ?></p> -->
                                <p><input type="text" class="form-control" name="maritalStatus" value="<?php echo htmlspecialchars($memberData['maritalStatus']); ?>"readonly></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Agama:</label>
                                <!-- <p class="form-control"><?php echo isset($memberData['religion']) ? $memberData['religion'] : '-'; ?></p> -->
                                <p><input type="text" class="form-control" name="religion" value="<?php echo htmlspecialchars($memberData['religion']); ?>"readonly></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bangsa:</label>
                                <!-- <p class="form-control"><?php echo isset($memberData['nation']) ? $memberData['nation'] : '-'; ?></p> -->
                                <p><input type="text" class="form-control" name="nation" value="<?php echo htmlspecialchars($memberData['nation']); ?>"readonly></p>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">Alamat Rumah</h5>
                            </div>
                            <div class="card-body">
                                <!-- <p><strong>Alamat:</strong> <?php echo isset($memberData['homeAddress']) ? $memberData['homeAddress'] : '-'; ?></p>
                                <p><strong>Poskod:</strong> <?php echo isset($memberData['homePostcode']) ? $memberData['homePostcode'] : '-'; ?></p>
                                <p><strong>Negeri:</strong> <?php echo isset($memberData['homeState']) ? $memberData['homeState'] : '-'; ?></p> -->
                                <p><strong>Alamat:</strong><input type="text" class="form-control" name="homeAddress" value="<?php echo htmlspecialchars($memberData['homeAddress']); ?>"readonly>
                                <p><strong>Poskod:</strong><input type="text" class="form-control" name="homePostcode" value="<?php echo htmlspecialchars($memberData['homePostcode']); ?>"readonly>
                                <p><strong>Negeri:</strong><input type="text" class="form-control" name="homeState" value="<?php echo htmlspecialchars($memberData['homeState']); ?>"readonly>
                            </div>
                        </div>
                       
                        <div class="card mt-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">Alamat Pejabat</h5>
                            </div>
                            <div class="card-body">
                                <!-- <p><strong>Alamat:</strong> <?php echo isset($memberData['officeAddress']) ? $memberData['officeAddress'] : '-'; ?></p>
                                <p><strong>Poskod:</strong> <?php echo isset($memberData['officePostcode']) ? $memberData['officePostcode'] : '-'; ?></p>
                                <p><strong>Negeri:</strong> <?php echo isset($memberData['officeState']) ? $memberData['officeState'] : '-'; ?></p>  -->
                                <p><strong>Alamat:</strong><input type="text" class="form-control" name="officeAddress" value="<?php echo htmlspecialchars($memberData['officeAddress']); ?>"readonly>
                                <p><strong>Poskod:</strong><input type="text" class="form-control" name="officePostcode" value="<?php echo htmlspecialchars($memberData['officePostcode']); ?>"readonly>
                                <p><strong>Negeri:</strong><input type="text" class="form-control" name="officeState" value="<?php echo htmlspecialchars($memberData['officeState']); ?>"readonly>
                            </div>
                        </div>
                    </div>
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
// 修改 JavaScript 代码
function editProfile() {
    // 移除所有输入框的 readonly 属性
    const inputs = document.querySelectorAll('#profileForm input[type="text"]');
    inputs.forEach(input => {
        input.removeAttribute('readonly');
        input.style.backgroundColor = '#ffffff';
    });

    // 显示/隐藏按钮
    document.getElementById('editButton').style.display = 'none';
    document.getElementById('updateButton').style.display = 'inline-block';
    document.getElementById('cancelButton').style.display = 'inline-block';
}

function cancelEdit() {
    if(confirm('Adakah anda pasti untuk membatalkan?')) {
        document.location = 'profil.php';
    }
}

// 移除 fetch，使用传统表单提交
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if(confirm('Adakah anda pasti untuk menyimpan perubahan ini?')) {
        this.submit(); // 直接提交表单
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
</style>