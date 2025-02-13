<?php
// Start session and check login before any output
session_start();

// Include files after login check
include "dbconnect.php";
include "headeradmin.php";


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// 在处理POST请求之后，添加以下代码来获取所有申请记录
$sql = "SELECT b.*, m.memberName, m.email 
        FROM tb_berhenti b
        LEFT JOIN tb_member m ON b.employeeID = m.employeeID
        ORDER BY b.applyDate DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("<div class='alert alert-danger'>Query failed: " . mysqli_error($conn) . "</div>");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resignID'])) {
    $resignID = $_POST['resignID'];
    $status = $_POST['status'];
    $rejectReason = isset($_POST['rejectionReason']) ? $_POST['rejectionReason'] : '';

    // 获取会员的邮箱和姓名
    $member_query = "SELECT m.memberName, m.email, m.employeeID, b.applyDate, b.approveDate 
                     FROM tb_berhenti b
                     JOIN tb_member m ON b.employeeID = m.employeeID
                     WHERE b.berhentiID = ?";
    $stmt_member = mysqli_prepare($conn, $member_query);
    mysqli_stmt_bind_param($stmt_member, 'i', $resignID);
    mysqli_stmt_execute($stmt_member);
    $result = mysqli_stmt_get_result($stmt_member);
    $member_data = mysqli_fetch_assoc($result);
    
    $sql = "UPDATE tb_berhenti 
            SET approvalStatus = ?, 
                approveDate = CURRENT_TIMESTAMP, 
                rejectReason = ? 
            WHERE berhentiID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssi', $status, $rejectReason, $resignID);
    
    if (mysqli_stmt_execute($stmt)) {
        if ($status == 'Lulus') {
            $update_member = "UPDATE tb_member_status ms
                            JOIN tb_berhenti b ON ms.employeeID = b.employeeID
                            SET ms.status = 'Berhenti'
                            WHERE b.berhentiID = ?";
            $stmt2 = mysqli_prepare($conn, $update_member);
            mysqli_stmt_bind_param($stmt2, 'i', $resignID);
            mysqli_stmt_execute($stmt2);
            
            // 发送批准邮件
            $to = $member_data['email'];
            $subject = "Permohonan Berhenti Ahli Koperasi KADA - DILULUSKAN";
            $message = "Salam sejahtera " . $member_data['memberName'] . ",\n\n"
                    . "Merujuk kepada permohonan berhenti ahli koperasi yang telah dikemukakan oleh pihak tuan/puan pada tarikh " . date('d/m/Y', strtotime($member_data['applyDate'])) . ".\n\n"
                    . "Sukacita dimaklumkan bahawa permohonan tersebut telah DILULUSKAN oleh pihak pentadbiran Koperasi KADA.\n\n"
                    . "Berikut adalah maklumat berkaitan:\n"
                    . "Nama: " . $member_data['memberName'] . "\n"
                    . "No. Pekerja: " . $member_data['employeeID'] . "\n"
                    . "Tarikh Kelulusan: " . date('d/m/Y', strtotime($member_data['approveDate'])) . "\n\n"
                    . "Pihak kami ingin mengucapkan ribuan terima kasih atas segala sumbangan dan perkhidmatan yang telah diberikan sepanjang menjadi ahli Koperasi KADA.\n\n"
                    . "Sekiranya terdapat sebarang pertanyaan, sila hubungi pihak pentadbiran Koperasi KADA.\n\n"
                    . "Sekian, terima kasih.\n\n"
                    . "Yang benar,\n\n"
                    . "Pentadbiran\n"
                    . "Koperasi KADA\n"
                    . "Tel: 09-7447088\n"
                    . "Email: koperasikada@kada.gov.my";
        } else {
            // 发送拒绝邮件
            $to = $member_data['email'];
            $subject = "Permohonan Berhenti Ahli Koperasi KADA - TIDAK DILULUSKAN";
            $message = "Salam sejahtera " . $member_data['memberName'] . ",\n\n"
                    . "Merujuk kepada permohonan berhenti ahli koperasi yang telah dikemukakan oleh pihak tuan/puan pada tarikh " . date('d/m/Y', strtotime($member_data['applyDate'])) . ".\n\n"
                    . "Dukacita dimaklumkan bahawa permohonan tersebut TIDAK DILULUSKAN oleh pihak pentadbiran Koperasi KADA.\n\n"
                    . "Berikut adalah maklumat berkaitan:\n"
                    . "Nama: " . $member_data['memberName'] . "\n"
                    . "No. Pekerja: " . $member_data['employeeID'] . "\n"
                    . "Tarikh Keputusan: " . date('d/m/Y', strtotime($member_data['approveDate'])) . "\n"
                    . "Sebab Penolakan: " . $rejectReason . "\n\n"
                    . "Sekiranya tuan/puan ingin membuat rayuan atau mendapatkan penjelasan lanjut, sila hubungi pihak pentadbiran Koperasi KADA dalam tempoh 14 hari dari tarikh surat ini.\n\n"
                    . "Tuan/Puan juga boleh mengemukakan permohonan baru dengan memastikan segala keperluan dan syarat telah dipenuhi.\n\n"
                    . "Sekian, terima kasih.\n\n"
                    . "Yang benar,\n\n"
                    . "Pentadbiran\n"
                    . "Koperasi KADA\n"
                    . "Tel: 09-7447088\n"
                    . "Email: koperasikada@kada.gov.my";
        }
        

        function sendEmail($to, $subject, $message) {
            $mail = new PHPMailer(true);

            try {
                // 服务器设置
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; // 使用Gmail SMTP或其他SMTP服务器
                $mail->SMTPAuth   = true;
                $mail->Username   = 'koperasikada.site@gmail.com'; // SMTP 用户名
                $mail->Password   = 'rtmh vdnc mozb lion'; // SMTP 密码
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // 收发件人
                $mail->setFrom('koperasikada.site@gmail.com', 'Koperasi Kada');
                $mail->addAddress($to);
                
                // 内容
                $mail->isHTML(false);
                $mail->Subject = $subject;
                $mail->Body    = $message;

                $mail->send();
                return true;
            } catch (Exception $e) {
                error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                return false;
            }
        }

        // 然后在需要发送邮件的地方使用
        if (sendEmail($to, $subject, $message)) {
            $_SESSION['success_message'] = "Status telah dikemaskini dan email telah dihantar.";
        } else {
            $_SESSION['error_message'] = "Status telah dikemaskini tetapi email tidak dapat dihantar.";
        }
        
        echo "<script>window.location.href = '" . $_SERVER['PHP_SELF'] . "';</script>";
        exit;
    }
}
?>

<!-- Add this right after opening body tag -->
<div class="page-background"></div>

<div class="main-content">
    <br><br><br>
    <!-- Back button -->
    <div class="back-section">
        <a href="adminmainpage.php" class="btn-kembali">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Main container -->
    <div class="container">
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Senarai Permohonan Berhenti</h3>
            </div>
            <div class="card-body">
                <!-- 搜索和显示条数控件 - 确保只有一组 -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <span class="me-2">Papar</span>
                        <select class="form-select me-2" style="width: 70px" id="recordsPerPage">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="me-2">rekod</span>
                    </div>
                    <div class="search-container">
                        <div class="input-group">
                            <input type="search" class="form-control" id="searchInput" placeholder="Cari..." style="width: 200px;">
                            <button class="btn btn-outline-secondary search-btn" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table" id="berhentiTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Sebab</th>
                                <th>Tarikh Mohon</th>
                                <th>Status</th>
                                <th>Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td data-label="ID"><?php echo htmlspecialchars($row['berhentiID']); ?></td>
                                    <td data-label="Nama"><?php echo htmlspecialchars($row['memberName']); ?></td>
                                    <td data-label="Sebab"><?php echo htmlspecialchars($row['reason']); ?></td>
                                    <td data-label="Tarikh Mohon"><?php echo date('d/m/Y', strtotime($row['applyDate'])); ?></td>
                                    <td data-label="Status">
                                        <span class="badge bg-<?php echo getStatusClass($row['approvalStatus']); ?>">
                                            <?php echo htmlspecialchars($row['approvalStatus']); ?>
                                        </span>
                                    </td>
                                    <td data-label="Tindakan">
                                        <?php if ($row['approvalStatus'] == 'Pending'): ?>
                                            <button type="button" class="btn btn-tindakan text-white" data-id="<?php echo $row['berhentiID']; ?>">
                                                <i class="fas fa-check-circle me-1"></i>Tindakan
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" id="approvalForm">
                <div class="modal-header">
                    <h5 class="modal-title">Tindakan Permohonan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="resignID" id="resignID">
                    <div class="mb-3">
                        <label class="form-label">Keputusan</label>
                        <select name="status" class="form-select" required id="statusSelect">
                            <option value="">Sila Pilih</option>
                            <option value="Lulus">Lulus</option>
                            <option value="Tolak">Tolak</option>
                        </select>
                    </div>
                    <div class="mb-3 rejection-reason" style="display: none;">
                        <label class="form-label">Sebab Penolakan</label>
                        <select name="rejectionReason" class="form-select" id="rejectionReason" required>
                            <option value="">Sila Pilih</option>
                            <option value="Sebab tidak mencukupi">Sebab tidak mencukupi</option>
                            <option value="Dokumen tidak lengkap">Dokumen tidak lengkap</option>
                            <option value="Masih ada pinjaman aktif">Masih ada pinjaman aktif</option>
                            <option value="Sebab lain">Sebab lain</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Helper function to get status badge class
function getStatusClass($status) {
    return match($status) {
        'Pending' => 'warning',
        'Lulus' => 'success',
        'Tolak' => 'danger',
        default => 'secondary'
    };
}

// Helper function to process approval
function processApproval($conn) {
    $resignID = $_POST['resignID'];
    $status = $_POST['status'];
    $rejectionReason = $_POST['rejectionReason'] ?? '';

    mysqli_begin_transaction($conn);

    try {
        // Update application status
        $sql = "UPDATE tb_berhenti 
                SET approvalStatus = ?, 
                    approveDate = CURRENT_TIMESTAMP,
                    rejectReason = ?
                WHERE berhentiID = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssi', $status, $rejectionReason, $resignID);
        mysqli_stmt_execute($stmt);

        // Update member status if approved
        if ($status == 'Lulus') {
            $update_member = "UPDATE tb_member m
                            JOIN tb_berhenti b ON m.employeeID = b.employeeID
                            SET m.status = 'Berhenti'
                            WHERE b.berhentiID = ?";
            $stmt2 = mysqli_prepare($conn, $update_member);
            mysqli_stmt_bind_param($stmt2, 'i', $resignID);
            mysqli_stmt_execute($stmt2);
        }

        mysqli_commit($conn);
        echo "<div class='alert alert-success'>Keputusan telah dikemaskini.</div>";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<div class='alert alert-danger'>Ralat: " . $e->getMessage() . "</div>";
    }
}
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<!-- <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap5.min.js"></script> -->

<script>
$(document).ready(function() {
    var table = $('#berhentiTable').DataTable({
        dom: 't<"bottom"p>', // only show table and pagination
        language: {
            paginate: {
                previous: "<<",
                next: ">>"
            }
        },
        pageLength: 10
    });

    // 绑定自定义搜索输入框
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });

    // 绑定自定义显示条数选择
    $('#recordsPerPage').on('change', function() {
        table.page.len(this.value).draw();
    });

    // 当状态选择改变时显示/隐藏拒绝原因
    $('#statusSelect').on('change', function() {
        if ($(this).val() === 'Tolak') {
            $('.rejection-reason').slideDown();
            $('#rejectionReason').prop('required', true);
        } else {
            $('.rejection-reason').slideUp();
            $('#rejectionReason').prop('required', false);
        }
    });

    // 修改按钮点击事件绑定
    $('.btn-tindakan').on('click', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        showApprovalModal(id);
    });
});

// 确保modal正确初始化
function showApprovalModal(id) {
    $('#resignID').val(id);
    $('#statusSelect').val('');
    $('.rejection-reason').hide();
    
    // 使用Bootstrap 5的方式显示modal
    var myModal = new bootstrap.Modal(document.getElementById('approvalModal'));
    myModal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    // Get the form
    const form = document.querySelector('#approvalForm');
    
    // Add submit handler to the form
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        
        // Get the selected status
        const status = document.querySelector('#statusSelect').value;
        const action = status === 'Lulus' ? 'meluluskan' : 'menolak';
        
        // Show confirmation dialog
        Swal.fire({
            title: 'Pengesahan',
            text: `Adakah anda pasti untuk ${action} permohonan ini?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5CBA9B',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Teruskan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // If confirmed, submit the form
                form.submit();
            }
        });
    });
});
</script>

<style>
/* Background styles */
.page-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)), 
                url('img/padi.jpg') no-repeat center center fixed;
    background-size: cover;
    z-index: -1;
}

/* Layout */
.main-content {
    padding: 20px;
    margin-top: 20px;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
}

/* Back button */
.back-section {
    margin-bottom: 15px;
}

.btn-kembali {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    background-color: #FF9B9B;
    color: white;
    border-radius: 5px;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.3s ease;
}

.btn-kembali:hover {
    background-color: #ff8282;
    color: white;
    text-decoration: none;
}

.btn-kembali i {
    margin-right: 8px;
}

/* Card styles */
.content-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.card-header {
    background-color: #006E5E;
    color: white;
    padding: 15px 20px;
    border-radius: 10px 10px 0 0;
    border-bottom: none;
}

.card-title {
    margin: 0;
    font-size: 18px;
    font-weight: 500;
}

.card-body {
    padding: 20px;
}

/* Table styles */
.table {
    width: 100%;
    margin-bottom: 0;
}

.table th {
    background-color: #f8f9fa;
    color: #495057;
    font-weight: 500;
    padding: 12px 15px;
    border-bottom: 2px solid #dee2e6;
}

.table td {
    padding: 12px 15px;
    vertical-align: middle;
    border-bottom: 1px solid #dee2e6;
}

/* Status badges */
.badge {
    padding: 6px 12px;
    border-radius: 4px;
    font-weight: 500;
}

/* Action buttons */
.btn-tindakan {
    background-color: #5CBA9B;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.btn-tindakan:hover {
    background-color: #4a9d82;
}

/* Modal styles */
.modal-content {
    border-radius: 10px;
    border: none;
}

.modal-header {
    background-color: #006E5E;
    color: white;
    border-radius: 10px 10px 0 0;
}

.modal-title {
    color: white;
}

/* Responsive design */
@media (max-width: 768px) {
    .main-content {
        padding: 15px;
        margin-top: 15px;
    }
    
    .card-body {
        padding: 15px;
    }
    
    .table-responsive {
        margin: 0 -15px;
    }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>