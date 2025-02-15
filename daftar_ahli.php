[00:02, 16/02/2025] Yeewen: <?php
ob_start();
session_start();

require_once 'email_helper.php';

include "headermember.php";
include "dbconnect.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug function
function debug_to_console($data) {
    echo "<script>console.log('Debug: " . json_encode($data) . "');</script>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        mysqli_begin_transaction($conn);

        // Get employeeID from session
        $employeeID = $_SESSION['employeeID'];
        
        // 1. Save Fees and Contribution
        $insertFees = "INSERT INTO tb_memberregistration_feesandcontribution 
                      (employeeID, entryFee, modalShare, feeCapital, deposit, contribution, fixedDeposit) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE 
                      entryFee = VALUES(entryFee),
                      modalShare = VALUES(modalShare),
                      feeCapital = VALUES(feeCapital),
                      deposit = VALUES(deposit),
                      contribution = VALUES(contribution),
                      fixedDeposit = VALUES(fixedDeposit)";

        $stmt = mysqli_prepare($conn, $insertFees);
        
        // Convert form values to integers and handle empty values
        $entryFee = empty($_POST['fee_masuk']) ? 0 : (int)$_POST['fee_masuk'];
        $modalShare = empty($_POST['modal_syer']) ? 0 : (int)$_POST['modal_syer'];
        $feeCapital = empty($_POST['modal_yuran']) ? 0 : (int)$_POST['modal_yuran'];
        $deposit = empty($_POST['wang_deposit']) ? 0 : (int)$_POST['wang_deposit'];
        $contribution = empty($_POST['sumbangan_tabung']) ? 0 : (int)$_POST['sumbangan_tabung'];
        $fixedDeposit = empty($_POST['simpanan_tetap']) ? 0 : (int)$_POST['simpanan_tetap'];

        mysqli_stmt_bind_param($stmt, "iiiiiii", 
            $employeeID,
            $entryFee,
            $modalShare,
            $feeCapital,
            $deposit,
            $contribution,
            $fixedDeposit
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error saving fees");
        }

        // 2. Save Family Member Information
        // First delete existing records for this employee
        $deleteFamily = "DELETE FROM tb_memberregistration_familymemberinfo WHERE employeeID = ?";
        $stmt = mysqli_prepare($conn, $deleteFamily);
        mysqli_stmt_bind_param($stmt, "i", $employeeID);
        mysqli_stmt_execute($stmt);

        // Then insert new family members
        if (isset($_POST['hubungan']) && is_array($_POST['hubungan'])) {
            $insertFamily = "INSERT INTO tb_memberregistration_familymemberinfo 
                           (employeeID, relationship, name, icFamilyMember) 
                           VALUES (?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $insertFamily);

            foreach ($_POST['hubungan'] as $i => $hubungan) {
                if (empty($hubungan) || empty($_POST['nama_waris'][$i]) || empty($_POST['no_kp_waris'][$i])) {
                    continue;
                }

                mysqli_stmt_bind_param($stmt, "isss",
                    $employeeID,
                    $_POST['hubungan'][$i],
                    $_POST['nama_waris'][$i],
                    $_POST['no_kp_waris'][$i]
                );

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Error saving family member");
                }
            }
        }

        // 3. Get user's email from database
        $emailQuery = "SELECT email FROM tb_member WHERE employeeID = ?";
        $stmt = mysqli_prepare($conn, $emailQuery);
        mysqli_stmt_bind_param($stmt, "i", $employeeID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $userEmail = mysqli_fetch_assoc($result)['email'];

        // 4. Try to send email
        try {
            $emailHelper = new EmailHelper();
            $emailData = [
                'fee_masuk' => $entryFee,
                'modal_syer' => $modalShare,
                'modal_yuran' => $feeCapital,
                'wang_deposit' => $deposit,
                'sumbangan_tabung' => $contribution,
                'simpanan_tetap' => $fixedDeposit
            ];
            
            $emailHelper->sendRegistrationEmail($userEmail, $emailData);
            $_SESSION['success_message'] = 'Pendaftaran anda telah berjaya disimpan dan email pengesahan telah dihantar.';
            $_SESSION['email_sent'] = true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            $_SESSION['success_message'] = 'Pendaftaran anda telah berjaya disimpan tetapi email pengesahan tidak dapat dihantar.';
        }
        
        mysqli_commit($conn);
        ob_end_clean();
        header('Location: success.php');
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error_message'] = $e->getMessage();
        ob_end_clean();
        header('Location: success.php');
        exit();
    }
}

// Add this at the bottom to show any errors
if (isset($conn->error) && $conn->error) {
    echo "<div class='alert alert-danger'>Database Error: " . $conn->error . "</div>";
}

// Add this at the bottom of your form to show current session data
?>

<!DOCTYPE html>
<html>
<head>
    <!-- Add jQuery before Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    
    <!-- Then add Bootstrap and other resources -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<style>
.card-header {
    background-color: #F8B4B4 !important; 
    color: black !important;
}

.progress-bar {
    background-color: #95D5B2 !important;  
}

.btn-success {
    background-color: #4CAF50;
    border-color: #4CAF50;
}

.btn-success:hover {
    background-color: #45a049;
    border-color: #45a049;
}

.delete-row {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Section header styling */
.section-header {
    background-color: #95D5B2 !important; /* Changed to theme green color */
    padding: 10px 15px;
    border-radius: 5px;
    margin: 20px 0;
    font-weight: 500;
    color: black !important;
    font-size: 18px !important;
    text-transform: uppercase;
}

/* Header styling */
.navbar {
    background-color: #95D5B2 !important;
}

/* Logo and navigation items */
.navbar-brand,
.nav-link {
    color: white !important;
}

/* Profile icon */
.profile-icon {
    color: white !important;
}

/* Active/hover states */
.nav-link:hover,
.nav-link.active {
    color: #e9ecef !important;
}

/* Keep the existing footer style */
.footer {
    background-color: #95D5B2;
}

/* Progress Bar */
.progress {
    height: 30px;
}

.progress-bar {
    background-color: #8BCEB3 !important; /* Adjusted to match the image */
    width: 100%;
}

/* Text inside progress bar */
.progress-bar {
    color: white;
    font-weight: 500;
}

.is-invalid {
    border-color: #dc3545 !important;
}

.invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 80%;
}
</style>

<div class="container mt-4">
    <!-- Progress Bar -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-8">
            <div class="progress" style="height: 30px;">
                <div class="progress-bar" role="progressbar" style="width: 100%">
                    Langkah 2/2: Maklumat Tambahan
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="registrationForm" onsubmit="return validateForm()">
        <!-- Family Information Table -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">MAKLUMAT KELUARGA DAN PEWARIS</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="familyTable">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 5%">BIL</th>
                            <th style="width: 20%">HUBUNGAN</th>
                            <th style="width: 45%">NAMA</th>
                            <th style="width: 25%">NO. K/P@ NO. SRT BERANAK</th>
                            <th style="width: 5%">TINDAKAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">1</td>
                            <td>
                                <select name="hubungan[]" class="form-select" required>
                                    <option value="">Pilih Hubungan</option>
                                    <option value="Isteri">Isteri</option>
                                    <option value="Suami">Suami</option>
                                    <option value="Anak">Anak</option>
                                    <option value="Ibu">Ibu</option>
                                    <option value="Bapa">Bapa</option>
                                    <option value="Adik-beradik">Adik-beradik</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="nama_waris[]" class="form-control" required>
                                <small class="text-muted">Sila pastikan NAMA PENUH seperti dalam kad pengenalan</small>
                            </td>
                            <td><input type="text" name="no_kp_waris[]" class="form-control" placeholder="__-_-_"></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm delete-row">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="text-end mb-3">
                    <button type="button" class="btn btn-success" id="addRow">
                        <i class="fas fa-plus"></i> Tambah Ahli Keluarga
                    </button>
                </div>
                <div class="text-muted mt-2">
                    <small>* Sila isikan maklumat keluarga terdekat sebagai pewaris</small>
                </div>
            </div>
        </div>

        <!-- Fees Table -->
        <div class="card mt-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">YURAN DAN SUMBANGAN</h5>
            </div>
            <div class="card-body">
                <p>Jika diterima sebagai anggota, saya bersetuju membayar yuran dan sumbangan bulanan seperti di bawah:</p>
                <table class="table table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 10%">BIL</th>
                            <th style="width: 70%">PERKARA</th>
                            <th style="width: 20%">RM</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>FEE MASUK</td>
                            <td><input type="number" name="fee_masuk" class="form-control" value="50" readonly></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>
                                MODAL SYER *
                                <small class="text-muted d-block">*Minimum RM300.00</small>
                            </td>
                            <td><input type="number" name="modal_syer" class="form-control" min="300" value="300" step="1"></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>
                                MODAL YURAN
                                <small class="text-muted d-block">*Minimum RM35.00</small>
                            </td>
                            <td><input type="number" name="modal_yuran" class="form-control" min="35" value="35" step="1"></td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>
                                WANG DEPOSIT ANGGOTA
                                <small class="text-muted d-block">*Minimum RM20.00</small>
                            </td>
                            <td><input type="number" name="wang_deposit" class="form-control" min="20" value="20" step="1"></td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>
                                SUMBANGAN TABUNG KEBAJIKAN (AL-ABRAR)
                                <small class="text-muted d-block">*Minimum RM5.00</small>
                            </td>
                            <td><input type="number" name="sumbangan_tabung" class="form-control" min="5" value="5" step="1"></td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>
                                SIMPANAN TETAP
                                <small class="text-muted d-block">*Minimum RM5.00</small>
                            </td>
                            <td><input type="number" name="simpanan_tetap" class="form-control" min="5" value="5" step="1"></td>
                        </tr>
                    </tbody>
                </table>
                <div class="text-muted mt-2">
                    <small>*Minima Modal Syer adalah sebanyak RM300.00 dan tidak melebihi 1/5 daripada Modal Syer Koperasi dan hendaklah dijelaskan dalam tempoh 6 bulan dari tarikh kelulusan menjadi anggota.</small>
                </div>
            </div>
        </div>

        <!-- Submit Section -->
        <div class="card mt-4 mb-5">
            <div class="card-body">
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="agree" name="agree" required>
                    <label class="form-check-label" for="agree">Saya mengesahkan semua maklumat adalah benar</label>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="daftar_ahli.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" name="submit" class="btn btn-primary" id="submitBtn" onclick="return confirmSubmit()">
                        Hantar Pendaftaran <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Add this hidden field -->
        <input type="hidden" name="debug" value="1">
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize mask for existing inputs
    $('input[name="no_kp_waris[]"]').mask('000000-00-0000', {
        placeholder: "__-_-_"
    });

    // Add new row
    $('#addRow').on('click', function() {
        var rowCount = $('#familyTable tbody tr').length + 1;
        var newRow = `
            <tr>
                <td class="text-center">${rowCount}</td>
                <td>
                    <select name="hubungan[]" class="form-select" required>
                        <option value="">Pilih Hubungan</option>
                        <option value="Isteri">Isteri</option>
                        <option value="Suami">Suami</option>
                        <option value="Anak">Anak</option>
                        <option value="Ibu">Ibu</option>
                        <option value="Bapa">Bapa</option>
                        <option value="Adik-beradik">Adik-beradik</option>
                    </select>
                </td>
                <td><input type="text" name="nama_waris[]" class="form-control" required></td>
                <td><input type="text" name="no_kp_waris[]" class="form-control" placeholder="__-_-_"></td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm delete-row">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#familyTable tbody').append(newRow);
        
        // Apply mask to new input
        $('input[name="no_kp_waris[]"]:last').mask('000000-00-0000', {
            placeholder: "__-_-_"
        });
    });

    // Delete row
    $(document).on('click', '.delete-row', function() {
        if ($('#familyTable tbody tr').length > 1) {
            $(this).closest('tr').remove();
            // Reorder numbers
            $('#familyTable tbody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }
    });

    const form = document.querySelector('form');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
        // Form will submit normally to the same page
    });
});

function validateForm() {
    // Debug: Log form data
    const formData = new FormData(document.getElementById('registrationForm'));
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }

    // Get all form values
    const fees = {
        fee_masuk: document.querySelector('input[name="fee_masuk"]').value,
        modal_syer: document.querySelector('input[name="modal_syer"]').value,
        modal_yuran: document.querySelector('input[name="modal_yuran"]').value,
        wang_deposit: document.querySelector('input[name="wang_deposit"]').value,
        sumbangan_tabung: document.querySelector('input[name="sumbangan_tabung"]').value,
        simpanan_tetap: document.querySelector('input[name="simpanan_tetap"]').value
    };

    // Debug: Log fees data
    console.log('Fees data:', fees);

    // Get family members data
    const familyMembers = [];
    const rows = document.querySelectorAll('#familyTable tbody tr');
    rows.forEach((row, index) => {
        if (index === 0) return; // Skip header row
        const member = {
            hubungan: row.querySelector('select[name="hubungan[]"]').value,
            nama: row.querySelector('input[name="nama_waris[]"]').value,
            ic: row.querySelector('input[name="no_kp_waris[]"]').value
        };
        familyMembers.push(member);
    });

    // Debug: Log family members data
    console.log('Family members:', familyMembers);

    // Validate fee masuk is 50
    if (parseInt(fees.fee_masuk) !== 50) {
        alert('Fee masuk mestilah RM50');
        return false;
    }

    // Validate modal yuran is at least 35
    if (parseInt(fees.modal_yuran) < 35) {
        alert('Modal yuran mestilah minimum RM35');
        return false;
    }

    return true;
}

function confirmSubmit() {
    if (validateForm()) {
        return confirm('Adakah anda pasti untuk menghantar pendaftaran ini?');
    }
    return false;
}
</script>

</body>
</html>
<?php include "footer.php"; ?>
[00:02, 16/02/2025] Yeewen: <?php
session_start();
ob_start();

// Check if user is logged in
if (!isset($_SESSION['employeeID'])) {
    $_SESSION['error'] = "Sila log masuk terlebih dahulu";
    header("Location: login.php");
    exit();
}

include "headermember.php";

// Check if employee is already registered
require_once "dbconnect.php";
$employeeID = $_SESSION['employeeID'];
$check_sql = "SELECT status FROM tb_member_status WHERE employeeID = ?";
$stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($stmt, "s", $employeeID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$existing_member = mysqli_fetch_assoc($result);

if ($existing_member) {
    if ($existing_member['status'] === 'Berhenti') {
        // 如果状态是 'Berhenti'，提示用户联系管理员
        ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                title: 'Perhatian!',
                text: 'Status keahlian anda telah berhenti. Sila hubungi admin untuk mengaktifkan semula akaun anda.',
                icon: 'warning',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'mainpage.php';
                }
            });
        </script>
        <?php
        exit();
    } else if ($existing_member['status'] === 'Pencen') {
        // 如果状态是 'Pencen'，显示不能申请的消息
        ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                title: 'Akses Ditolak!',
                text: 'Maaf, anda tidak boleh mendaftar sebagai ahli kerana status anda adalah pencen.',
                icon: 'error',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'mainpage.php';
                }
            });
        </script>
        <?php
        exit();
    } else {
        // 其他状态显示已注册消息
        ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                title: 'Perhatian!',
                text: 'Anda telah mendaftar sebagai ahli.',
                icon: 'warning',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'mainpage.php';
                }
            });
        </script>
        <?php
        exit();
    }
} else {

}

// 检查会员状态
$sql_check_status = "SELECT status FROM tb_member_status WHERE employeeID = ?";
$stmt_status = mysqli_prepare($conn, $sql_check_status);
mysqli_stmt_bind_param($stmt_status, 's', $employeeID);
mysqli_stmt_execute($stmt_status);
$result_status = mysqli_stmt_get_result($stmt_status);
$member_status = mysqli_fetch_assoc($result_status);

// 检查是否有待处理的会员申请
$sql_check_pending = "SELECT regisStatus
                     FROM tb_memberregistration_memberapplicationdetails 
                     WHERE memberRegistrationID  = ? 
                     AND regisStatus = 'Belum Selesai'";
$stmt_pending = mysqli_prepare($conn, $sql_check_pending);
mysqli_stmt_bind_param($stmt_pending, 's', $employeeID);
mysqli_stmt_execute($stmt_pending);
$result_pending = mysqli_stmt_get_result($stmt_pending);

// 检查 $member_status 是否存在且不为 null
if (isset($member_status) && $member_status !== null && isset($member_status['status'])) {
    // 只有当状态是 "Aktif" 时才显示已注册消息
    if ($member_status['status'] == 'Aktif' ) {
        ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                title: 'Perhatian!',
                text: 'Anda telah mendaftar sebagai ahli.',
                icon: 'warning',
                confirmButtonText: 'OK'
            }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'mainpage.php';
            }
        });
        </script>
        <?php
    }
}

// 如果有待处理申请，不允许再次申请
if (mysqli_num_rows($result_pending) > 0) {
    ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            title: 'Akses Ditolak!',
            text: 'Maaf, anda masih mempunyai permohonan keahlian yang belum selesai.',
            icon: 'error',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'mainpage.php';
            }
        });
    </script>
    <?php
    exit();
}

// 检查连接
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// 检查是否是 POST 请求
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 初始化变量
    $no_kp = null;
    
    // 安全地获取 POST 数据
    if (isset($_POST['no_kp']) && !empty($_POST['no_kp'])) {
        $no_kp = trim($_POST['no_kp']);
        
        // 准备 SQL 语句
            $check_sql = "SELECT status FROM tb_member_status WHERE employeeID = ?";
            
        // 准备和执行查询
        if ($stmt = mysqli_prepare($conn, $check_sql)) {
            mysqli_stmt_bind_param($stmt, "s", $employeeID);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    mysqli_stmt_bind_result($stmt, $status);
                    mysqli_stmt_fetch($stmt);
                    
                    // 只有当状态是 'Berhenti' 时显示警告
                    if ($status && $status !== 'Berhenti')  {
                        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <strong>Perhatian!</strong> Anda telah mendaftar sebagai ahli.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                              </div>';
                        mysqli_stmt_close($stmt);
                        exit();
                    }
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// 继续处理注册逻辑
// ... rest of your code ...

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debug: Print form data
    error_log("Form Data: " . print_r($_POST, true));
    
    try {
        mysqli_begin_transaction($conn);
        
        // Get employeeID from the form
        $employeeID = $_POST['no_anggota'];
        
        // Debug: Print employeeID
        error_log("EmployeeID: " . $employeeID);

        // First insert member data
        $memberQuery = "INSERT INTO tb_member (
            employeeID, 
            memberName, 
            email, 
            ic, 
            maritalStatus, 
            sex, 
            religion, 
            nation, 
            no_pf, 
            position, 
            phoneNumber,
            phoneHome,
            monthlySalary
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $memberStmt = mysqli_prepare($conn, $memberQuery);
        mysqli_stmt_bind_param($memberStmt, "isssssssssssd", 
            $_POST['no_anggota'],      // employeeID
            $_POST['nama_penuh'],      // memberName
            $_POST['alamat_emel'],     // email
            $_POST['ic'],              // ic
            $_POST['maritalStatus'],   // maritalStatus
            $_POST['sex'],             // sex
            $_POST['religion'],        // religion
            $_POST['nation'],          // nation
            $_POST['no_pf'],          // no_pf
            $_POST['position'],        // position
            $_POST['phoneNumber'],     // phoneNumber
            $_POST['phoneHome'],       // phoneHome
            $_POST['monthlySalary']    // monthlySalary
        );
        
        if (mysqli_stmt_execute($memberStmt)) {
            // Debug: Member inserted successfully
            error_log("Member inserted successfully");
            
            // Insert home address
            $homeQuery = "INSERT INTO tb_member_homeaddress (employeeID, homeAddress, homePostcode, homeState) 
                         VALUES (?, ?, ?, ?)";
            $homeStmt = mysqli_prepare($conn, $homeQuery);
            
            // Debug: Print home address data
            error_log("Home Address Data: " . print_r([
                'employeeID' => $employeeID,
                'homeAddress' => $_POST['homeAddress'],
                'homePostcode' => $_POST['homePostcode'],
                'homeState' => $_POST['homeState']
            ], true));
            
            mysqli_stmt_bind_param($homeStmt, "isss", 
                $employeeID,
                $_POST['homeAddress'],
                $_POST['homePostcode'],
                $_POST['homeState']
            );
            
            if (!mysqli_stmt_execute($homeStmt)) {
                throw new Exception("Error saving home address: " . mysqli_error($conn));
            }
            
            // Debug: Home address inserted successfully
            error_log("Home address inserted successfully");

            // Insert office address
            $officeQuery = "INSERT INTO tb_member_officeaddress (employeeID, officeAddress, officePostcode, officeState) 
                           VALUES (?, ?, ?, ?)";
            $officeStmt = mysqli_prepare($conn, $officeQuery);
            
            // Debug: Print office address data
            error_log("Office Address Data: " . print_r([
                'employeeID' => $employeeID,
                'officeAddress' => $_POST['officeAddress'],
                'officePostcode' => $_POST['officePostcode'],
                'officeState' => $_POST['officeState']
            ], true));
            
            mysqli_stmt_bind_param($officeStmt, "isss", 
                $employeeID,
                $_POST['officeAddress'],
                $_POST['officePostcode'],
                $_POST['officeState']
            );
            
            if (!mysqli_stmt_execute($officeStmt)) {
                throw new Exception("Error saving office address: " . mysqli_error($conn));
            }
            
            // Debug: Office address inserted successfully
            error_log("Office address inserted successfully");

            // Insert initial registration status
            $statusQuery = "INSERT INTO tb_memberregistration_memberapplicationdetails 
                           (memberRegistrationID, regisDate, regisStatus) 
                           VALUES (?, NOW(), 'Belum Selesai')";
            
            $statusStmt = mysqli_prepare($conn, $statusQuery);
            mysqli_stmt_bind_param($statusStmt, "i", $employeeID);
            
            if (!mysqli_stmt_execute($statusStmt)) {
                throw new Exception("Error setting initial registration status: " . mysqli_error($conn));
            }

            mysqli_commit($conn);
            $_SESSION['employeeID'] = $employeeID;
            
            // Debug: Transaction committed
            error_log("Transaction committed successfully");
            
            header("Location: maklumat_tambahan.php");
            exit();
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error occurred: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header("Location: daftar_ahli.php");
        exit();
    }
}

// Show success/error messages if they exist
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            ' . $_SESSION['success'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            ' . $_SESSION['error'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['error']);
}
?>

<style>
.container {
    width: 90%;
    max-width: 1200px !important;
    margin: 0 auto;
    padding: 0 15px;
}

.progress-container {
    width: 90%;
    margin: 20px auto;
}

.progress {
    height: 30px !important;
}

.progress-bar {
    background-color: #95D5B2 !important;
    font-weight: 500;
}

.section-header {
    background-color: #F8B4B4;
    padding: 15px;
    border-radius: 5px;
    margin: 20px 0;
    font-weight: 500;
    color: black !important;
    font-size: 18px;
    text-transform: uppercase;
}

.section-content {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-label {
    font-weight: 500;
    color: black;
    margin-bottom: 8px;
}

.form-control, .form-select {
    padding: 10px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus, .form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.input-group-text {
    background-color: #e9ecef;
    border-right: none;
}

.btn-primary {
    background-color: #95D5B2;
    border-color: #95D5B2;
    padding: 10px 20px;
    font-weight: 500;
}

.btn-primary:hover {
    background-color: #74c69d;
    border-color: #74c69d;
}

.text-danger {
    color: #dc3545 !important;
}

.text-muted {
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .col-md-6 {
        margin-bottom: 1rem;
    }
}

.alert {
    margin: 20px;
    padding: 15px;
    border-radius: 4px;
    position: relative;
}

.alert-dismissible .btn-close {
    position: absolute;
    top: 0;
    right: 0;
    padding: 1.25rem;
}

.invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 0.875em;
    margin-top: 0.25rem;
}

.is-invalid {
    border-color: #dc3545 !important;
}

.is-valid {
    border-color: #198754 !important;
}
</style>

<div class="container mt-4">
    <!-- Progress Bar -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-8">
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: 50%">
                    Langkah 1/2: Permohonan Menjadi Anggota
                </div>
            </div>
        </div>
    </div>

    <!-- Form Content -->
    <form method="POST" action="" class="needs-validation" novalidate>
        <!-- Personal Information Section -->
        <div class="section-header">
            MAKLUMAT PERIBADI
        </div>
        
        <div class="section-content">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Penuh <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_penuh" required>
                    <small class="text-muted">Sila pastikan NAMA PENUH seperti dalam kad pengenalan.</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Alamat Emel <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="alamat_emel" required>
                    <small class="text-muted">Sila pastikan ALAMAT EMEL adalah sah dan masih aktif.</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">MyKad/No. Passport <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="ic" required 
                           pattern="[0-9]{12}" maxlength="12"
                           placeholder="Contoh: 890126012345">
                    <small class="text-muted">Masukkan 12 digit nombor kad pengenalan tanpa (-)</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Taraf Perkahwinan <span class="text-danger">*</span></label>
                    <select class="form-select" name="maritalStatus" required>
                        <option value="">Sila Pilih</option>
                        <option value="Bujang">Bujang</option>
                        <option value="Berkahwin">Berkahwin</option>
                        <option value="Duda/Janda">Duda/Janda</option>
                    </select>
                </div>
            </div>

            <!-- Home Address Section -->
            <div class="mb-3">
                <label class="form-label">Alamat Rumah <span class="text-danger">*</span></label>
                <textarea class="form-control" name="homeAddress" rows="3" required></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Poskod <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="homePostcode" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Negeri <span class="text-danger">*</span></label>
                    <select class="form-select" name="homeState" required>
                        <option value="">Sila Pilih</option>
                        <option value="Johor">Johor</option>
                        <option value="Kedah">Kedah</option>
                        <option value="Kelantan">Kelantan</option>
                        <option value="Melaka">Melaka</option>
                        <option value="Negeri Sembilan">Negeri Sembilan</option>
                        <option value="Pahang">Pahang</option>
                        <option value="Perak">Perak</option>
                        <option value="Perlis">Perlis</option>
                        <option value="Pulau Pinang">Pulau Pinang</option>
                        <option value="Sabah">Sabah</option>
                        <option value="Sarawak">Sarawak</option>
                        <option value="Selangor">Selangor</option>
                        <option value="Terengganu">Terengganu</option>
                        <option value="WP Kuala Lumpur">WP Kuala Lumpur</option>
                        <option value="WP Labuan">WP Labuan</option>
                        <option value="WP Putrajaya">WP Putrajaya</option>
                    </select>
                </div>
            </div>

            <!-- Other Personal Details -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jantina <span class="text-danger">*</span></label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="sex" value="Lelaki" required>
                            <label class="form-check-label">Lelaki</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="sex" value="Perempuan" required>
                            <label class="form-check-label">Perempuan</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Agama <span class="text-danger">*</span></label>
                    <select class="form-select" name="religion" required>
                        <option value="">Sila Pilih</option>
                        <option value="Islam">Islam</option>
                        <option value="Buddha">Buddha</option>
                        <option value="Hindu">Hindu</option>
                        <option value="Kristian">Kristian</option>
                    
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bangsa <span class="text-danger">*</span></label>
                    <select class="form-select" name="nation" required>
                        <option value="">Sila Pilih</option>
                        <option value="Melayu">Melayu</option>
                        <option value="Cina">Cina</option>
                        <option value="India">India</option>
                        
                    </select>
                </div>
            </div>
        </div>

        <!-- Employment Information Section -->
        <div class="section-header">
            MAKLUMAT PEKERJAAN
        </div>

        <div class="section-content">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. Anggota <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="no_anggota" value="<?php echo $_SESSION['employeeID']; ?>" readonly>
                    <small class="text-muted">No. Anggota anda akan diisi secara automatik.</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">No. PF <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="no_pf" required maxlength="12" 
                           pattern="\d{1,12}" title="Sila masukkan nombor PF yang sah">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Jawatan & Gred <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="position" required>
            </div>

            <!-- Office Address Section -->
            <div class="mb-3">
                <label class="form-label">Alamat Pejabat <span class="text-danger">*</span></label>
                <textarea class="form-control" name="officeAddress" rows="3" required></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Poskod <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="officePostcode" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Negeri <span class="text-danger">*</span></label>
                    <select class="form-select" name="officeState" required>
                        <option value="">Sila Pilih</option>
                        <option value="Johor">Johor</option>
                        <option value="Kedah">Kedah</option>
                        <option value="Kelantan">Kelantan</option>
                        <option value="Melaka">Melaka</option>
                        <option value="Negeri Sembilan">Negeri Sembilan</option>
                        <option value="Pahang">Pahang</option>
                        <option value="Perak">Perak</option>
                        <option value="Perlis">Perlis</option>
                        <option value="Pulau Pinang">Pulau Pinang</option>
                        <option value="Sabah">Sabah</option>
                        <option value="Sarawak">Sarawak</option>
                        <option value="Selangor">Selangor</option>
                        <option value="Terengganu">Terengganu</option>
                        <option value="WP Kuala Lumpur">WP Kuala Lumpur</option>
                        <option value="WP Labuan">WP Labuan</option>
                        <option value="WP Putrajaya">WP Putrajaya</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. Tel Bimbit <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" name="phoneNumber" required>
                    <small class="text-muted">Contoh: 0123456789 (10-11 digit)</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">No. Tel Rumah <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" name="phoneHome" required>
                    <small class="text-muted">Contoh: 097447088 (9-11 digit)</small>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Gaji Bulanan (RM) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="monthlySalary" required step="0.01">
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary btn-lg">
                Seterusnya <i class="fas fa-arrow-right"></i>
            </button>
            <br><br><br>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('form');
    
    // Validate IC number (12 digits)
    const icInput = document.querySelector('input[name="ic"]');
    icInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12);
        validateInput(this, /^\d{12}$/);
    });

    // Validate postcodes (5 digits)
    const postcodeInputs = document.querySelectorAll('input[name="homePostcode"], input[name="officePostcode"]');
    postcodeInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 5);
            validateInput(this, /^\d{5}$/, 'Sila masukkan 5 digit poskod');
        });
    });

    // Validate phone numbers
    const phoneInputs = document.querySelectorAll('input[name="phoneNumber"], input[name="phoneHome"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            
            if (input.name === 'phoneNumber') {
                // Mobile phone: must be 10-11 digits
                validateInput(this, /^\d{10,11}$/, 'Sila masukkan nombor telefon bimbit yang sah (10-11 digit)');
            } else if (input.name === 'phoneHome') {
                // Home phone: can be 9-11 digits
                validateInput(this, /^\d{9,11}$/, 'Sila masukkan nombor telefon rumah yang sah (9-11 digit)');
            }
        });
    });

    // Validate salary (numbers and decimal point only)
    const salaryInput = document.querySelector('input[name="monthlySalary"]');
    salaryInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9.]/g, '');
        validateInput(this, /^\d+(\.\d{0,2})?$/, 'Sila masukkan nilai gaji yang sah');
    });

    // Validate name (letters, spaces, and special characters)
    const nameInput = document.querySelector('input[name="nama_penuh"]');
    nameInput.addEventListener('input', function() {
        validateInput(this, /^[A-Za-z\s@\-\/'\.]+$/, 'Sila masukkan nama yang sah');
    });

    // Validate email
    const emailInput = document.querySelector('input[name="alamat_emel"]');
    emailInput.addEventListener('input', function() {
        validateInput(this, /^[^\s@]+@[^\s@]+\.[^\s@]+$/);
    });

    // Helper function to validate input and show error message
    function validateInput(input, regex, errorMessage) {
        const isValid = regex.test(input.value);
        const errorDiv = input.nextElementSibling?.classList.contains('invalid-feedback') 
            ? input.nextElementSibling 
            : createErrorDiv();
        
        if (!isValid && input.value !== '') {
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            errorDiv.textContent = errorMessage;
            if (!input.nextElementSibling?.classList.contains('invalid-feedback')) {
                input.parentNode.insertBefore(errorDiv, input.nextSibling);
            }
        } else if (input.value !== '') {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            errorDiv.remove();
        } else {
            input.classList.remove('is-invalid', 'is-valid');
            errorDiv.remove();
        }
        return isValid;
    }

    // Helper function to create error message div
    function createErrorDiv() {
        const div = document.createElement('div');
        div.className = 'invalid-feedback';
        return div;
    }

    // Form submission handler
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validate all required fields
        const requiredInputs = form.querySelectorAll('[required]');
        requiredInputs.forEach(input => {
            if (!input.value) {
                isValid = false;
                input.classList.add('is-invalid');
            }
        });

        // Validate specific fields
        if (!validateInput(icInput, /^\d{12}$/, 'Sila masukkan 12 digit nombor kad pengenalan')) isValid = false;
        postcodeInputs.forEach(input => {
            if (!validateInput(input, /^\d{5}$/, 'Sila masukkan 5 digit poskod')) isValid = false;
        });
        phoneInputs.forEach(input => {
            if (!validateInput(input, /^\d{10,11}$/, 'Sila masukkan nombor telefon yang sah')) isValid = false;
        });
        if (!validateInput(salaryInput, /^\d+(\.\d{0,2})?$/, 'Sila masukkan nilai gaji yang sah')) isValid = false;
        if (!validateInput(nameInput, /^[A-Za-z\s@\-\/'\.]+$/, 'Sila masukkan nama yang sah')) isValid = false;
        if (!validateInput(emailInput, /^[^\s@]+@[^\s@]+\.[^\s@]+$/, 'Sila masukkan alamat emel yang sah')) isValid = false;

        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                title: 'Ralat!',
                text: 'Sila semak semula borang anda.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
});
</script>