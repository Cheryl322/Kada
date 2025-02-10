<?php
session_start();
include "dbconnect.php";
include "headermember.php";

if (!isset($_SESSION['employeeID'])) {
    header("Location: login.php");
    exit();
}

$employeeID = $_SESSION['employeeID'];

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
    <div class="card main-card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title mb-0">
                <i class="fas fa-file-alt me-2"></i>
                Borang Permohonan Berhenti
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <!-- Personal Information Section -->
                <div class="section-card mb-4">
                    <div class="section-header">
                        <i class="fas fa-user-circle"></i>
                        <h5>Maklumat Peribadi</h5>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['memberName']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Kad Pengenalan</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['ic']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jantina</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-venus-mars"></i></span>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['sex']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Agama</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-pray"></i></span>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['religion']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bangsa</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-users"></i></span>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['nation']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat Rumah</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-home"></i></span>
                                <textarea class="form-control" rows="2" readonly><?php echo htmlspecialchars($member['homeAddress']); ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Poskod (Rumah)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-mail-bulk"></i></span>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['homePostcode']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Negeri (Rumah)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['homeState']); ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employment Information Section -->
                <div class="section-card mb-4">
                    <div class="section-header">
                        <i class="fas fa-briefcase"></i>
                        <h5>Maklumat Pekerjaan</h5>
                    </div>
                    <div class="row g-3">
                    <div class="col-md-6">
                            <label class="form-label">No. Anggota</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['employeeID']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. PF</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['no_pf']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jawatan</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['position']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat Pejabat</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                                <textarea class="form-control" rows="2" readonly><?php echo htmlspecialchars($member['officeAddress']); ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Poskod (Pejabat)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-mail-bulk"></i></span>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['officePostcode']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Negeri (Pejabat)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['officeState']); ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reason Section -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-clipboard-list"></i>
                        <h5>Sebab Berhenti Menjadi Anggota</h5>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Sebab-sebab</label>
                            <textarea name="reasonDetail" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Hantar Permohonan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.main-card {
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border-radius: 8px;
}

.card-header {
    background: #fff;
    border-bottom: 1px solid #eaeaea;
    padding: 1.5rem;
}

.card-header h3 {
    color: #2c3e50;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.section-card {
    background: #fff;
    padding: 1.75rem;
    margin-bottom: 1.5rem;
    border: 1px solid #eaeaea;
    border-radius: 8px;
}

.section-header {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    gap: 0.75rem;
}

.section-header i {
    font-size: 1.25rem;
    color: #2c3e50;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 6px;
}

.section-header h5 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.1rem;
    font-weight: 600;
}

.form-control[readonly] {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    color: #495057;
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.15);
}

.input-group-text {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    color: #6c757d;
}

.form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.5rem;
}

textarea {
    resize: none;
}

.btn {
    padding: 0.6rem 1.5rem;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #4361ee;
    border: none;
}

.btn-primary:hover {
    background: #3250e2;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #fff;
    color: #4361ee;
    border: 1px solid #4361ee;
}

.btn-secondary:hover {
    background: #f8f9fa;
    color: #3250e2;
    border-color: #3250e2;
}

@media (max-width: 768px) {
    .section-card {
        padding: 1.25rem;
    }
    
    .card-header {
        padding: 1.25rem;
    }
}
</style>