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
                    . "Sukacita dimaklumkan bahawa permohonan tersebut telah *DILULUSKAN* oleh pihak pentadbiran Koperasi KADA.\n\n"
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
                    . "Dukacita dimaklumkan bahawa permohonan tersebut *TIDAK DILULUSKAN* oleh pihak pentadbiran Koperasi KADA.\n\n"
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
                $mail->setFrom('koperasikada.site@gmail.com', 'Koperasi KADA');
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

<div class="container mt-5 pt-5" style="margin-left: 250px; width: calc(100% - 280px); transition: all 0.3s ease-in-out;">
    <?php if(isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
            ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
            ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

<!-- Add this new div for spacing -->
<div class="header-spacing mb-4"></div>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title">Senarai Permohonan Berhenti</h3>
        </div>
        <div class="card-body">
            <!-- 搜索和显示条数控件 - 确保只有一组 -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <label class="me-2">Papar</label>
                    <select class="form-select me-2" style="width: auto" id="recordsPerPage">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>rekod</span>
                </div>
                <div class="d-flex align-items-center">
                    <label class="me-2">Carian:</label>
                    <input type="search" class="form-control" style="width: 200px" id="searchInput">
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

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
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
    // 初始化 DataTable，但不使用其默认的搜索和显示条数控件
    var table = $('#berhentiTable').DataTable({
        dom: 't<"bottom"p>', // 只显示表格和分页
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Malay.json"
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
</script>

<style>
/* Add this to ensure content appears below fixed header */
.header-spacing {
    height: 60px; /* Adjust this value based on your header height */
}

/* Optional: Add smooth scroll behavior */
html {
    scroll-behavior: smooth;
}

/* 调整容器宽度 */
.container {
    max-width: 1400px; /* 增加最大宽度 */
    width: 95%; /* 使用百分比宽度 */
    margin: 0 auto;
    padding: 20px;
    margin-top: 1rem !important;
}

/* 移除之前的左边距设置 */
.container.mt-5 {
    margin-left: auto !important;
    width: auto !important;
}

/* Add these styles to your existing CSS */
.modal-content {
    border: none;
    border-radius: 8px;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.modal-title {
    color: #2c3e50;
    font-weight: 600;
}

.form-select:focus,
.form-control:focus {
    border-color: #4361ee;
    box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
}

/* 卡片样式 */
.card {
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.08);
    border-radius: 12px;
    margin: 0 auto;
    width: 100%;
}

.card-header {
    background: white;
    padding: 12px 20px; /* 减少上下内边距 */
    border-bottom: 1px solid #edf2f7;
}

.card-title {
    color: #2d3748;
    font-weight: 600;
    font-size: 1.5rem; /* 稍微减小标题大小 */
    margin: 0;
}

/* 表格样式 */
.table {
    margin: 0;
    width: 100%;
}

.table thead th {
    background-color: #f8fafc;
    color: #4a5568;
    font-weight: 600;
    border-bottom: 2px solid #edf2f7;
    padding: 15px;
}

.table tbody td {
    padding: 15px;
    vertical-align: middle;
    color: #2d3748;
    border-bottom: 1px solid #edf2f7;
}

/* 状态标签样式 */
.badge {
    padding: 8px 12px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.85rem;
}

.bg-warning {
    background-color: #fef3c7 !important;
    color: #92400e;
}

.bg-success {
    background-color: #dcfce7 !important;
    color: #166534;
}

.bg-danger {
    background-color: #fee2e2 !important;
    color: #991b1b;
}

/* 按钮样式 */
.btn-tindakan {
    background-color: #3b82f6;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    transition: all 0.2s;
}

.btn-tindakan:hover {
    background-color: #2563eb;
    transform: translateY(-1px);
}

/* DataTables 自定义样式 */
.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 6px 12px;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    border-radius: 6px;
    padding: 6px 12px;
    margin: 0 2px;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #3b82f6;
    border: none;
    color: white !important;
}

/* 搜索框样式 */
.dataTables_filter input {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 8px 12px;
    width: 250px !important;
}

.dataTables_filter input:focus {
    border-color: #3b82f6;
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* 分页样式 */
.dataTables_paginate {
    margin-top: 20px !important;
}

/* 设置各列的最小宽度 */
.table th:nth-child(1), /* ID列 */
.table td:nth-child(1) {
    width: 5%;
    min-width: 60px;
}

.table th:nth-child(2), /* Nama列 */
.table td:nth-child(2) {
    width: 15%;
    min-width: 150px;
}

.table th:nth-child(3), /* Sebab列 */
.table td:nth-child(3) {
    width: 30%;
    min-width: 200px;
}

.table th:nth-child(4), /* Tarikh Mohon列 */
.table td:nth-child(4) {
    width: 15%;
    min-width: 120px;
}

.table th:nth-child(5), /* Status列 */
.table td:nth-child(5) {
    width: 15%;
    min-width: 100px;
}

.table th:nth-child(6), /* Tindakan列 */
.table td:nth-child(6) {
    width: 15%;
    min-width: 120px;
}

/* 确保表格响应式布局正常 */
.table-responsive {
    overflow-x: auto;
}

/* 调整搜索框宽度 */
.form-control[type="search"] {
    width: 250px !important; /* 增加搜索框宽度 */
}

/* 响应式调整 */
@media (max-width: 768px) {
    .container {
        padding: 10px;
    }
    
    .table thead {
        display: none;
    }
    
    .table tbody td {
        display: block;
        text-align: right;
        padding: 10px;
    }
    
    .table tbody td:before {
        content: attr(data-label);
        float: left;
        font-weight: 600;
        color: #4a5568;
    }
}

/* 调整搜索和显示条数控件的样式 */
.form-select {
    min-width: 70px;
    padding: 0.375rem 1.75rem 0.375rem 0.75rem;
}

.form-control {
    height: calc(1.5em + 0.75rem + 2px);
}

/* 确保在小屏幕上也保持水平布局 */
@media (max-width: 576px) {
    .d-flex {
        flex-wrap: nowrap;
    }
    
    .form-control {
        width: 150px !important;
    }
}

/* 减少顶部间距 */
.container.mt-4 {
    margin-top: 1rem !important; /* 从2rem减少到1rem */
}

/* 减少搜索区域的下边距 */
.d-flex.justify-content-between.align-items-center.mb-3 {
    margin-bottom: 0.75rem !important; /* 减少搜索区域的下边距 */
}

/* 调整卡片内容区域的内边距 */
.card-body {
    padding-top: 15px; /* 减少顶部内边距 */
}
</style>