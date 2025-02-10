<!-- for member that already apply loan -->
<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['employeeID'])) {
    header('Location: login.php');
    exit();
}

include "headermember.php";
include "dbconnect.php";
// include "footer.php";

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


    <div class="container mt-5">
        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-md-3">
                <div class="profile-sidebar text-center">
                    <div class="profile-image mb-4">
                        <img src="img/profile.jpeg" class="rounded-circle img-fluid" alt="Profile Picture" style="width: 200px; height: 200px; object-fit: cover;">
                        <h3 class=" mt-3"><?php echo isset($memberData['memberName']) ? $memberData['memberName'] : 'User'; ?></h3>
                    </div>

                    <!-- Navigation Menu -->
                    <div class="profile-nav d-flex flex-column gap-3">
                        <a href="profil.php" class="btn w-75 mx-auto" style="background-color: #8CD9B5; color: white;">
                            Profil
                        </a>
                        <a href="status.php" class="btn w-75 mx-auto" style="background-color: #75B798; color: white;">
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

            <!-- Right Content -->
            <div class="col-md-9">
                <div class="card">
                <form id="profileForm" method="POST" action="update_profil.php">
                    <input type="hidden" name="source_page" value="profil.php">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">MAKLUMAT PERIBADI</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Penuh:</label>
                                <p><input type="text" class="form-control" name="memberName" value="<?php echo htmlspecialchars($memberData['memberName']); ?>"readonly></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. MyKad:</label>
                                <p><input type="text" class="form-control" name="ic" value="<?php echo htmlspecialchars($memberData['ic']); ?>"readonly></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Alamat Email:</label>
                                <p><input type="text" class="form-control" name="email" value="<?php echo htmlspecialchars($memberData['email']); ?>"readonly></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. Telefon:</label>
                                <p><input type="text" class="form-control" name="phoneNumber" value="<?php echo htmlspecialchars($memberData['phoneNumber']); ?>"readonly></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Jantina:</label>
                                <p><input type="text" class="form-control" name="sex" value="<?php echo htmlspecialchars($memberData['sex']); ?>"readonly></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status Perkahwinan:</label>
                                <p><input type="text" class="form-control" name="maritalStatus" value="<?php echo htmlspecialchars($memberData['maritalStatus']); ?>"readonly></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Agama:</label>
                                <p><input type="text" class="form-control" name="religion" value="<?php echo htmlspecialchars($memberData['religion']); ?>"readonly></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bangsa:</label>
                                <p><input type="text" class="form-control" name="nation" value="<?php echo htmlspecialchars($memberData['nation']); ?>"readonly></p>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">Alamat Rumah</h5>
                            </div>
                            <div class="card-body">
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
        location.href = 'profil.php';
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
</style>