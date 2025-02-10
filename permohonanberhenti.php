<?php
session_start();
include "dbconnect.php";
include "headermember.php";

if (!isset($_SESSION['employeeID'])) {
    header("Location: login.php");
    exit();
}

$employeeID = $_SESSION['employeeID'];

// 从IC提取生日和计算年龄
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
        // 如果日期无效，返回空值或默认日期
        return null;
    }
    
    return sprintf('%04d-%02d-%02d', $year, $month, $day);
}

function calculateAge($birthDate) {
    if (!$birthDate) {
        return 0; // 如果生日无效，返回0或其他默认值
    }
    
    try {
        $birth = new DateTime($birthDate);
        $today = new DateTime();
        $age = $today->diff($birth);
        return $age->y;
    } catch (Exception $e) {
        return 0; // 如果出现异常，返回0或其他默认值
    }
}

// 获取会员信息
$sql_member = "SELECT m.*, 
               mh.homeAddress, mh.homePostcode, mh.homeState,
               mo.officeAddress, mo.officePostcode, mo.officeState
               FROM tb_member m
               LEFT JOIN tb_member_homeaddress mh ON m.employeeID = mh.employeeID
               LEFT JOIN tb_member_officeaddress mo ON m.employeeID = mo.employeeID
               WHERE m.employeeID = ?";

$stmt_member = mysqli_prepare($conn, $sql_member);
mysqli_stmt_bind_param($stmt_member, 's', $employeeID);
mysqli_stmt_execute($stmt_member);
$member = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_member));

// 计算生日和年龄
$birthDate = getBirthDateFromIC($member['ic']);
$age = calculateAge($birthDate);

// 格式化生日显示
$formattedBirthDate = $birthDate ? date('d/m/Y', strtotime($birthDate)) : 'Invalid Date';

// 检查是否已经提交过申请
$check_sql = "SELECT * FROM tb_berhenti 
              WHERE employeeID = ? 
              AND approvalStatus = 'Pending'";
$stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($stmt, 's', $employeeID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    echo "<div class='alert alert-warning'>Anda telah menghantar permohonan berhenti. Sila tunggu kelulusan.</div>";
    exit();
}

// Update form submission for tb_berhenti
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reason = $_POST['reasonDetail'];
    
    $sql = "INSERT INTO tb_berhenti (employeeID, reason, applyDate) 
            VALUES (?, ?, CURRENT_DATE)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'is', $employeeID, $reason);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Permohonan anda telah dihantar.";
        header("Location: status_permohonanberhenti.php");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Ralat: " . mysqli_error($conn) . "</div>";
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Permohonan Berhenti</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="application-card">
        <div class="card-header">
            <div class="header-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <h3>Borang Permohonan Berhenti</h3>
        </div>

        <form method="POST" action="">
            <!-- Personal Information Section -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h4>Maklumat Peribadi</h4>
                </div>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nama</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['memberName']); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>No. Kad Pengenalan</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['ic']); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tarikh Lahir</label>
                            <input type="text" class="form-control" value="<?php echo $formattedBirthDate; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Umur</label>
                            <input type="text" class="form-control" value="<?php echo $age; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Jantina</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['sex']); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Agama</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['religion']); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Bangsa</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['nation']); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>No. Telefon</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['phoneHome']); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>No. Telefon Bimbit</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['phoneNumber']); ?>" readonly>
                        </div>
                    </div>
                    
                    <!-- Home Address within Personal Information -->
                    <div class="col-12 mt-4">
                        <div class="form-group">
                            <label>Alamat Rumah</label>
                            <textarea class="form-control" rows="2" readonly><?php echo htmlspecialchars($member['homeAddress']); ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Poskod</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['homePostcode']); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Negeri</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['homeState']); ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employment Information Section -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h4>Maklumat Pekerjaan</h4>
                </div>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>No. Anggota</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['employeeID']); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>No. PF</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['no_pf']); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Jawatan</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['position']); ?>" readonly>
                        </div>
                    </div>
                    
                    <!-- Office Address within Employment Information -->
                    <div class="col-12 mt-4">
                        <div class="form-group">
                            <label>Alamat Pejabat</label>
                            <textarea class="form-control" rows="2" readonly><?php echo htmlspecialchars($member['officeAddress']); ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Poskod</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['officePostcode']); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Negeri</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['officeState']); ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reason Section -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-comment"></i>
                    </div>
                    <h4>Sebab Berhenti</h4>
                </div>
                <div class="form-group">
                    <label>Sila nyatakan sebab-sebab berhenti</label>
                    <textarea name="reasonDetail" class="form-control" rows="4" required></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-2"></i>Hantar Permohonan
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.application-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
}

.card-header {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.header-icon {
    width: 45px;
    height: 45px;
    background: #4CAF50;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.header-icon i {
    font-size: 20px;
    color: white;
}

.card-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.5rem;
}

.form-section {
    padding: 25px;
    border-bottom: 1px solid #eee;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.section-icon {
    width: 35px;
    height: 35px;
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.section-icon i {
    font-size: 16px;
    color: #4CAF50;
}

.section-header h4 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.2rem;
}

.form-group {
    margin-bottom: 0;
}

.form-group label {
    font-weight: 500;
    color: #2c3e50;
    margin-bottom: 8px;
}

.form-control {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 10px 15px;
}

.form-control:read-only {
    background-color: #f8f9fa;
    color: #495057;
}

.form-control:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
}

textarea.form-control {
    resize: none;
}

.form-actions {
    padding: 25px;
    display: flex;
    justify-content: flex-end;
}

.btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
}

.btn-primary {
    background: #4CAF50;
    border: none;
}

.btn-primary:hover {
    background: #45a049;
}

.btn-outline-secondary {
    color: #2c3e50;
    border-color: #2c3e50;
}

.btn-outline-secondary:hover {
    background: #2c3e50;
    color: #fff;
}

@media (max-width: 768px) {
    .form-section {
        padding: 20px;
    }
    
    .card-header {
        padding: 15px;
    }
    
    .header-icon {
        width: 40px;
        height: 40px;
    }
}
</style>
</style>