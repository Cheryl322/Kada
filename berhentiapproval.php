<?php
// Start session and check login before any output
session_start();

// Include files after login check
include "dbconnect.php";
include "headeradmin.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resignID'])) {
    $resignID = $_POST['resignID'];
    $status = $_POST['status'];
    
    // 获取会员的邮箱和姓名
    $member_query = "SELECT m.memberName, m.email, b.reason
                     FROM tb_berhenti b
                     JOIN tb_member m ON b.employeeID = m.employeeID
                     WHERE b.berhentiID = ?";
    $stmt_member = mysqli_prepare($conn, $member_query);
    mysqli_stmt_bind_param($stmt_member, 'i', $resignID);
    mysqli_stmt_execute($stmt_member);
    $result = mysqli_stmt_get_result($stmt_member);
    $member_data = mysqli_fetch_assoc($result);
    
    $sql = "UPDATE tb_berhenti SET approvalStatus = ?, approveDate = CURRENT_TIMESTAMP WHERE berhentiID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $status, $resignID);
    
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
            $subject = "Permohonan Berhenti Diluluskan";
            $message = "Salam sejahtera " . $member_data['memberName'] . ",\n\n"
                    . "Permohonan berhenti anda telah diluluskan.\n"
                    . "Sebab: " . $member_data['reason'] . "\n\n"
                    . "Terima kasih atas perkhidmatan anda bersama kami.\n\n"
                    . "Hormat,\n"
                    . "Koperasi KADA";
        } else {
            // 发送拒绝邮件
            $to = $member_data['email'];
            $subject = "Permohonan Berhenti Ditolak";
            $message = "Salam sejahtera " . $member_data['memberName'] . ",\n\n"
                    . "Permohonan berhenti anda telah ditolak.\n"
                    . "Sebab: " . $member_data['reason'] . "\n\n"
                    . "Sila hubungi pihak pentadbiran untuk maklumat lanjut. Anda boleh membuat permohonan baru dengan mengklik link berikut: https://kada.com.my/permohonanberhenti.php\n\n"
                    . "Hormat,\n"
                    . "Koperasi KADA";
        }
        
        // 设置邮件头部
        $headers = "From: kada@example.com\r\n";
        $headers .= "Reply-To: kada@example.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        // 发送邮件
        if(mail($to, $subject, $message, $headers)) {
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

    <div class="card">
        <div class="card-header">
            <h3>Senarai Permohonan Berhenti</h3>
        </div>
        <div class="card-body">
            <?php
            $sql = "SELECT b.*, m.memberName 
                    FROM tb_berhenti b
                    LEFT JOIN tb_member m ON b.employeeID = m.employeeID
                    ORDER BY b.applyDate DESC";
            $result = mysqli_query($conn, $sql);
            ?>

            <table class="table">
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
                            <td><?php echo $row['berhentiID']; ?></td>
                            <td><?php echo $row['memberName']; ?></td>
                            <td><?php echo $row['reason']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['applyDate'])); ?></td>
                            <td><?php echo $row['approvalStatus']; ?></td>
                            <td>
                                <?php if ($row['approvalStatus'] == 'Pending'): ?>
                                    <button onclick="showModal(<?php echo $row['berhentiID']; ?>)" 
                                            class="btn btn-primary btn-sm">
                                        Tindakan
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

<!-- Simple Modal -->
<div class="modal" id="approvalModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5>Tindakan Permohonan</h5>
                    <button type="button" class="close" onclick="closeModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="resignID" id="resignID">
                    <select name="status" required>
                        <option value="">Sila Pilih</option>
                        <option value="Lulus">Lulus</option>
                        <option value="Tolak">Tolak</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showModal(id) {
    document.getElementById('resignID').value = id;
    document.getElementById('approvalModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('approvalModal').style.display = 'none';
}
</script>

<style>
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-dialog {
    margin: 10% auto;
    width: 300px;
    background-color: white;
}

.modal-content {
    padding: 20px;
}

select {
    width: 100%;
    padding: 5px;
    margin: 10px 0;
}
</style>