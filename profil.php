<?php
session_start();
include "dbconnect.php";

// 检查用户状态
$employeeID = $_SESSION['employeeID'];

// 检查是否为会员
$checkMember = "SELECT * FROM tb_member WHERE employeeID = ?";
$stmt = mysqli_prepare($conn, $checkMember);
mysqli_stmt_bind_param($stmt, 's', $employeeID);
mysqli_stmt_execute($stmt);
$memberResult = mysqli_stmt_get_result($stmt);
$isMember = mysqli_num_rows($memberResult) > 0;

// 检查是否有已批准的贷款
$hasApprovedLoan = false;
if ($isMember) {
    $checkLoan = "SELECT * FROM tb_loanapplication WHERE employeeID = ? AND loanStatus = 'Approved'";
    $stmt = mysqli_prepare($conn, $checkLoan);
    mysqli_stmt_bind_param($stmt, 's', $employeeID);
    mysqli_stmt_execute($stmt);
    $loanResult = mysqli_stmt_get_result($stmt);
    $hasApprovedLoan = mysqli_num_rows($loanResult) > 0;
}

// 获取基本员工信息
$sql = "SELECT employeeID, email FROM tb_employee WHERE employeeID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $employeeID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$userData = mysqli_fetch_assoc($result);

// 获取家庭地址
$homeAddressQuery = "SELECT * FROM tb_member_homeAddress WHERE employeeID = ?";
$stmt = mysqli_prepare($conn, $homeAddressQuery);
mysqli_stmt_bind_param($stmt, "s", $employeeID);
mysqli_stmt_execute($stmt);
$homeAddressResult = mysqli_stmt_get_result($stmt);
$homeAddressData = mysqli_fetch_assoc($homeAddressResult);

// 获取办公地址
$officeAddressQuery = "SELECT * FROM tb_member_officeAddress WHERE employeeID = ?";
$stmt = mysqli_prepare($conn, $officeAddressQuery);
mysqli_stmt_bind_param($stmt, "s", $employeeID);
mysqli_stmt_execute($stmt);
$officeAddressResult = mysqli_stmt_get_result($stmt);
$officeAddressData = mysqli_fetch_assoc($officeAddressResult);

// 获取会员状态
$memberStatus = '-';
if ($isMember) {
    $statusQuery = "SELECT status FROM tb_member_status WHERE employeeID = ?";
    $stmt = mysqli_prepare($conn, $statusQuery);
    mysqli_stmt_bind_param($stmt, "s", $employeeID);
    mysqli_stmt_execute($stmt);
    $statusResult = mysqli_stmt_get_result($stmt);
    if ($statusRow = mysqli_fetch_assoc($statusResult)) {
        $memberStatus = $statusRow['status'];
    }
}

include "headermember.php";

// 设置显示数据
if ($isMember) {
    $memberData = mysqli_fetch_assoc($memberResult);
    // 添加会员状态
    $memberData['memberStatus'] = $memberStatus;
    // 合并地址信息
    $memberData['homeAddress'] = $homeAddressData['homeAddress'] ?? '-';
    $memberData['homePostcode'] = $homeAddressData['homePostcode'] ?? '-';
    $memberData['homeState'] = $homeAddressData['homeState'] ?? '-';
    $memberData['officeAddress'] = $officeAddressData['officeAddress'] ?? '-';
    $memberData['officePostcode'] = $officeAddressData['officePostcode'] ?? '-';
    $memberData['officeState'] = $officeAddressData['officeState'] ?? '-';
} else {
    $memberData = [
        'memberName' => '-',
        'ic' => '-',
        'sex' => '-',
        'phoneNumber' => '-',
        'religion' => '-',
        'nation' => '-',
        'maritalStatus' => '-',
        'memberStatus' => '-',
        'homeAddress' => '-',
        'homePostcode' => '-',
        'homeState' => '-',
        'officeAddress' => '-',
        'officePostcode' => '-',
        'officeState' => '-',
        'employeeID' => $userData['employeeID'] ?? '-',
        'email' => $userData['email'] ?? '-'
    ];
}

// 从IC提取生日和计算年龄
if (!function_exists('getBirthDateFromIC')) {
    function getBirthDateFromIC($ic) {
        $year = substr($ic, 0, 2);
        $month = substr($ic, 2, 2);
        $day = substr($ic, 4, 2);
        
        // 确定世纪
        $year = (int)$year;
        if ($year >= 00 && $year <= 30) {
            $year += 2000;
        } else {
            $year += 1900;
        }
        
        // 验证日期的有效性
        if (!checkdate((int)$month, (int)$day, $year)) {
            return null;
        }
        
        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }
}

if (!function_exists('calculateAge')) {
    function calculateAge($birthDate) {
        if (!$birthDate) {
            return 0;
        }
        
        try {
            $birth = new DateTime($birthDate);
            $today = new DateTime();
            $age = $today->diff($birth);
            return $age->y;
        } catch (Exception $e) {
            return 0;
        }
    }
}

// 在获取会员数据后添加这些计算
$birthDate = getBirthDateFromIC($memberData['ic']);
$age = calculateAge($birthDate);
$formattedBirthDate = $birthDate ? date('d/m/Y', strtotime($birthDate)) : '-';

// 状态标签函数
function getStatusLabel($status) {
    if ($status === null) {
        return 'AKTIF'; // 默认状态
    }
    return strtoupper($status); // 直接返回数据库中的状态
}

function getStatusClass($status) {
    if ($status === null) {
        $status = 'Aktif';
    }
    
    switch ($status) {
        case 'Aktif':
            return 'bg-success text-white';
        case 'Berhenti':
            return 'bg-danger text-white';
        case 'Pencen':
            return 'bg-info text-white';
        case 'Aktif/Pencen':
            return 'bg-secondary text-white';
        default:
            return 'bg-light text-dark';
    }
}
?>

<!-- 在 head 部分添加 Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- 添加 SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="wrapper">
    <div class="container mt-5">
        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-md-3">
                <div class="profile-sidebar">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="profile-image mb-4">
                                <img src="img/profile.jpeg" class="rounded-circle img-fluid" alt="Profile Picture" style="width: 200px; height: 200px; object-fit: cover;">
                                <h3 class="mt-3"><?php echo $memberData['memberName'] !== '-' ? $memberData['memberName'] : 'User'; ?></h3>
                                <?php if ($isMember): ?>
                                    <div class="mt-2">
                                        <p class="mb-1"><strong>No. Anggota:</strong> <?php echo $memberData['employeeID']; ?></p>
                                        <div class="mt-2">
                                            <span class="badge <?php echo getStatusClass($memberStatus); ?> rounded-pill px-3 py-2">
                                                <?php echo $memberStatus; ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="profile-nav d-flex flex-column gap-1">
                                <a href="profil.php" class="btn w-75 mx-auto" style="background-color: #8CD9B5; color: white;">
                                    Profil
                                </a>
                                <?php if ($isMember): ?>
                                    <a href="status.php" class="btn w-75 mx-auto" style="background-color: #75B798; color: white;">
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
                </div>
            </div>

            <!-- Right Content -->
            <div class="col-md-9">
                <div class="card">
                    <?php if (!$isMember): ?>
                        <div class="alert alert-info m-3" role="alert">
                            <i class="fas fa-info-circle"></i> Tiada rekod maklumat. Sila mohon keahlian KADA.
                            <a href="daftar_ahli.php" class="btn btn-primary ms-2">Mohon Sekarang</a>
                        </div>
                    <?php endif; ?>

                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                                Maklumat Peribadi
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="family-tab" data-bs-toggle="tab" data-bs-target="#family" type="button" role="tab">
                                Maklumat Keluarga
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                                Tukar Kata Laluan
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="myTabContent">
                        <!-- Personal Info Tab -->
                        <div class="tab-pane fade show active" id="profile" role="tabpanel">
                            <div class="card-body">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-primary text-white">
                                        <h4 class="mb-0">MAKLUMAT PERIBADI</h4>
                                    </div>
                                    <form id="profileForm" action="update_profil.php" method="POST">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <table class="table table-borderless">
                                                        <tr>
                                                            <td width="35%"><strong>Nama:</strong></td>
                                                            <td><input type="text" class="form-control-plaintext" name="memberName" value="<?php echo $memberData['memberName']; ?>" readonly></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>No. MyKad:</strong></td>
                                                            <td><input type="text" class="form-control-plaintext" name="ic" value="<?php echo $memberData['ic']; ?>" readonly></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>No. PF:</strong></td>
                                                            <td><input type="text" class="form-control-plaintext" name="employeeID" value="<?php echo $memberData['employeeID']; ?>" readonly></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Email:</strong></td>
                                                            <td><input type="email" class="form-control-plaintext" name="email" value="<?php echo $memberData['email']; ?>" readonly></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>No. Telefon:</strong></td>
                                                            <td><input type="text" class="form-control-plaintext" name="phoneNumber" value="<?php echo $memberData['phoneNumber']; ?>" readonly></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <table class="table table-borderless">
                                                        <tr>
                                                            <td width="35%"><strong>Agama:</strong></td>
                                                            <td><input type="text" class="form-control-plaintext" name="religion" value="<?php echo $memberData['religion']; ?>" readonly></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Bangsa:</strong></td>
                                                            <td><input type="text" class="form-control-plaintext" name="nation" value="<?php echo $memberData['nation']; ?>" readonly></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Status:</strong></td>
                                                            <td><input type="text" class="form-control-plaintext" name="maritalStatus" value="<?php echo $memberData['maritalStatus']; ?>" readonly></td>
                                                        </tr>
                                                    </table>
                                                </div>

                                                <!-- Address Section -->
                                                <div class="col-12 mt-4">
                                                    <h5 class="border-bottom pb-2 mb-3">Alamat</h5>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="card shadow-sm">
                                                                <div class="card-header bg-light">
                                                                    <h6 class="mb-0">Alamat Rumah</h6>
                                                                </div>
                                                                <div class="card-body">
                                                                    <table class="table table-borderless mb-0">
                                                                        <tr>
                                                                            <td colspan="2"><strong>Alamat Rumah:</strong></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td colspan="2">
                                                                                <input type="text" class="form-control-plaintext" name="homeAddress" value="<?php echo $memberData['homeAddress']; ?>" readonly>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td width="35%"><strong>Poskod:</strong></td>
                                                                            <td><input type="text" class="form-control-plaintext" name="homePostcode" value="<?php echo $memberData['homePostcode']; ?>" readonly></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Negeri:</strong></td>
                                                                            <td><input type="text" class="form-control-plaintext" name="homeState" value="<?php echo $memberData['homeState']; ?>" readonly></td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="card shadow-sm">
                                                                <div class="card-header bg-light">
                                                                    <h6 class="mb-0">Alamat Pejabat</h6>
                                                                </div>
                                                                <div class="card-body">
                                                                    <table class="table table-borderless mb-0">
                                                                        <tr>
                                                                            <td colspan="2"><strong>Alamat Pejabat:</strong></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td colspan="2">
                                                                                <input type="text" class="form-control-plaintext" name="officeAddress" value="<?php echo $memberData['officeAddress']; ?>" readonly>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td width="35%"><strong>Poskod:</strong></td>
                                                                            <td><input type="text" class="form-control-plaintext" name="officePostcode" value="<?php echo $memberData['officePostcode']; ?>" readonly></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Negeri:</strong></td>
                                                                            <td><input type="text" class="form-control-plaintext" name="officeState" value="<?php echo $memberData['officeState']; ?>" readonly></td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if ($isMember): ?>
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
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Family Info Tab -->
                        <div class="tab-pane fade" id="family" role="tabpanel">
                            <div class="card-body">
                                <div class="card">
                                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                        <h4 class="mb-0">MAKLUMAT KELUARGA</h4>
                                        <?php if ($isMember): ?>
                                            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addFamilyModal">
                                                <i class="fas fa-plus"></i> Tambah
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                    // 获取家庭成员信息
                                    $familyQuery = "SELECT employeeID, relationship, name, icFamilyMember FROM tb_memberregistration_familymemberinfo WHERE employeeID = ?";
                                    $stmt = mysqli_prepare($conn, $familyQuery);
                                    mysqli_stmt_bind_param($stmt, "s", $employeeID);
                                    mysqli_stmt_execute($stmt);
                                    $familyResult = mysqli_stmt_get_result($stmt);
                                    
                                    if (mysqli_num_rows($familyResult) > 0) {
                                        ?>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th class="text-center" style="width: 5%">No.</th>
                                                            <th style="width: 30%">Nama</th>
                                                            <th style="width: 25%">No. MyKad</th>
                                                            <th style="width: 25%">Hubungan</th>
                                                            <th style="width: 15%" class="text-center">Tindakan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php 
                                                        $i = 1;
                                                        while ($familyMember = mysqli_fetch_assoc($familyResult)) { 
                                                        ?>
                                                            <tr>
                                                                <td class="text-center"><?php echo $i++; ?></td>
                                                                <td><?php echo $familyMember['name']; ?></td>
                                                                <td><?php echo $familyMember['icFamilyMember']; ?></td>
                                                                <td><?php echo $familyMember['relationship']; ?></td>
                                                                <td class="text-center">
                                                                    <button type="button" class="btn btn-danger btn-sm" 
                                                                            onclick="deleteFamilyMember('<?php echo $familyMember['employeeID']; ?>', '<?php echo $familyMember['icFamilyMember']; ?>')">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <div class="card-body">
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-info-circle"></i> Tiada rekod maklumat keluarga.
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <!-- 密码更改标签页 -->
                        <div class="tab-pane fade" id="password" role="tabpanel">
                            <div class="card-body">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h4 class="mb-0">TUKAR KATA LALUAN</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- 左侧表单 -->
                                            <div class="col-md-7 border-end">
                                                <form id="changePasswordForm" onsubmit="return updatePassword(event)">
                                                    <div class="mb-3">
                                                        <label for="currentPassword">Kata Laluan Semasa</label>
                                                        <div class="input-group">
                                                            <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                                                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('currentPassword')">
                                                                <i class="far fa-eye-slash"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="newPassword">Kata Laluan Baru</label>
                                                        <div class="input-group">
                                                            <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                                                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('newPassword')">
                                                                <i class="far fa-eye-slash"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="confirmPassword">Sahkan Kata Laluan</label>
                                                        <div class="input-group">
                                                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                                                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirmPassword')">
                                                                <i class="far fa-eye-slash"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-save"></i> SIMPAN
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- 右侧密码要求 -->
                                            <div class="col-md-5">
                                                <div class="password-requirements ps-md-4">
                                                    <h5 class="text-muted mb-3">Kata laluan mestilah:</h5>
                                                    <ul class="list-unstyled">
                                                        <li id="length" class="requirement">• Minimum 8 aksara</li>
                                                        <li id="uppercase" class="requirement">• Sekurang-kurangnya 1 huruf besar</li>
                                                        <li id="lowercase" class="requirement">• Sekurang-kurangnya 1 huruf kecil</li>
                                                        <li id="number" class="requirement">• Sekurang-kurangnya 1 nombor</li>
                                                        <li id="special" class="requirement">• Sekurang-kurangnya 1 simbol (@$!%*?&)</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 添加家庭成员的Modal -->
<div class="modal fade" id="addFamilyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Maklumat Keluarga</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="add_family_member.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. MyKad</label>
                        <input type="text" class="form-control" name="icFamilyMember" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hubungan</label>
                        <select class="form-select" name="relationship" required>
                            <option value="">Pilih Hubungan</option>
                            <option value="Isteri">Isteri</option>
                            <option value="Suami">Suami</option>
                            <option value="Anak">Anak</option>
                            <option value="Ibu">Ibu</option>
                            <option value="Bapa">Bapa</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteFamilyMember(employeeID, icFamilyMember) {
    if (confirm('Adakah anda pasti untuk memadam maklumat ini?')) {
        window.location.href = 'delete_family_member.php?employeeID=' + employeeID + '&icFamilyMember=' + icFamilyMember;
    }
}

function editProfile() {
    const inputs = document.querySelectorAll('#profileForm input[type="text"], #profileForm input[type="email"]');
    inputs.forEach(input => {
        if (input.name !== 'employeeID' && 
            input.name !== 'ic' && 
            input.name !== 'memberName' &&
            input.name !== 'membershipNo') {
            input.removeAttribute('readonly');
            input.classList.remove('form-control-plaintext');
            input.classList.add('form-control');
            input.style.backgroundColor = '#ffffff';
        }
    });

    document.getElementById('editButton').style.display = 'none';
    document.getElementById('updateButton').style.display = 'inline-block';
    document.getElementById('cancelButton').style.display = 'inline-block';
}

function cancelEdit() {
    if(confirm('Adakah anda pasti untuk membatalkan?')) {
        location.reload();
    }
}

document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if(confirm('Adakah anda pasti untuk menyimpan perubahan ini?')) {
        const form = this;
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.href = 'profil.php'; // 更新成功后重定向
            } else {
                alert('Ralat: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ralat semasa mengemaskini profil');
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    const requirements = {
        length: /.{8,}/,
        uppercase: /[A-Z]/,
        lowercase: /[a-z]/,
        number: /[0-9]/,
        special: /[@$!%*?&]/
    };

    // 实时检查密码要求
    newPassword.addEventListener('input', function() {
        const password = this.value;
        let allValid = true;
        
        for (let req in requirements) {
            const element = document.getElementById(req);
            if (requirements[req].test(password)) {
                element.classList.add('text-success');
                element.classList.remove('text-danger');
            } else {
                element.classList.add('text-danger');
                element.classList.remove('text-success');
                allValid = false;
            }
        }
    });

    // 实时检查密码匹配
    confirmPassword.addEventListener('input', function() {
        if (this.value !== newPassword.value) {
            this.setCustomValidity('Kata laluan tidak sepadan');
        } else {
            this.setCustomValidity('');
        }
    });
});

function updatePassword(e) {
    e.preventDefault();
    
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    // 验证所有字段都已填写
    if (!currentPassword || !newPassword || !confirmPassword) {
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: 'Sila isi semua medan yang diperlukan'
        });
        return false;
    }

    // 验证新密码和确认密码是否匹配
    if (newPassword !== confirmPassword) {
        Swal.fire({
            icon: 'error',
            title: 'Ralat',
            text: 'Kata laluan baru dan pengesahan tidak sepadan'
        });
        return false;
    }

    // 验证密码要求
    if (!validatePassword(newPassword)) {
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: 'Sila pastikan kata laluan memenuhi semua keperluan'
        });
        return false;
    }

    // 显示确认对话框
    Swal.fire({
        title: 'Pengesahan',
        text: 'Adakah anda pasti untuk menukar kata laluan?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Tidak'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = e.target;
            fetch('update_password.php', {
                method: 'POST',
                body: new FormData(form)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berjaya',
                        text: 'Kata laluan berjaya dikemaskini',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        form.reset();
                        $('#profile-tab').tab('show');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ralat',
                        text: data.message
                    });
                }
            });
        }
    });
    
    return false;
}

function validatePassword(password) {
    return password.length >= 8 && 
           /[A-Z]/.test(password) && 
           /[a-z]/.test(password) && 
           /[0-9]/.test(password) && 
           /[@$!%*?&]/.test(password);
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = event.target.closest('button').querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    }
}
</script>

<!-- 在密码表单上方添加错误消息显示区域 -->
<div id="password-error-message" class="d-none mb-3"></div>

<style>
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

/* 标题栏样式 */
.card-header {
    padding: 1.25rem;
    background: linear-gradient(45deg, #2196F3, #1976D2);
    border-radius: 15px 15px 0 0 !important;
    border: none;
}

.card-header h4 {
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

/* 导航标签样式 */
.nav-tabs {
    padding: 1rem 1rem 0;
    border: none;
    gap: 0.5rem;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
    background-color: rgba(13, 110, 253, 0.05);
    color: #0d6efd;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    background: rgba(13, 110, 253, 0.1);
    border: none;
    position: relative;
}

/* 表格样式 */
.table-borderless td {
    padding: 1rem 0;
    vertical-align: middle;
}

/* 输入框样式 */
.form-control-plaintext {
    padding: 0.5rem 0;
    font-size: 1rem;
    color: #495057;
    transition: all 0.3s ease;
}

.form-control {
    border-radius: 8px;
    padding: 0.5rem 1rem;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
}

/* 按钮样式 */
.btn {
    padding: 0.6rem 1.5rem;
    font-weight: 500;
    border-radius: 10px;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.9rem;
}

.btn i {
    margin-right: 0.5rem;
    font-size: 1rem;
}

.btn-primary {
    background: linear-gradient(45deg, #2196F3, #1976D2);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(45deg, #1976D2, #1565C0);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
}

.btn-success {
    background: linear-gradient(45deg, #4CAF50, #388E3C);
    border: none;
}

.btn-success:hover {
    background: linear-gradient(45deg, #388E3C, #2E7D32);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.2);
}

/* 地址卡片样式 */
.card-body .card {
    border-radius: 12px;
    background: #fff;
}

.card-body .card-header {
    background: #f8f9fa;
    color: #495057;
    font-weight: 600;
    padding: 1rem 1.25rem;
    border-radius: 12px 12px 0 0;
}

/* 分隔线样式 */
.border-bottom {
    border-bottom: 2px solid #e9ecef !important;
}

/* 标题样式 */
h5 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 1.5rem;
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
    .card-body {
        padding: 1.25rem;
    }
    
    .nav-tabs .nav-link {
        padding: 0.5rem 1rem;
    }
    
    .btn {
        padding: 0.5rem 1rem;
    }
}

/* 动画效果 */
.tab-pane {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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

.requirement {
    margin-bottom: 0.8rem;
    padding-left: 1.5rem;
    position: relative;
    color: #6c757d;
}

.requirement.text-success {
    color: #198754 !important;
}

.requirement.text-danger {
    color: #dc3545 !important;
}

.requirement.text-success:before {
    content: '✓';
    position: absolute;
    left: 0;
}

.requirement.text-danger:before {
    content: '•';
    position: absolute;
    left: 0;
}

@media (max-width: 767.98px) {
    .border-end {
        border-right: none !important;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 2rem;
        margin-bottom: 2rem;
    }
    
    .password-requirements {
        padding-left: 0 !important;
    }
}
</style>
