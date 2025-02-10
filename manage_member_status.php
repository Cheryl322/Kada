<?php
session_start();
include "../dbconnect.php";
include "headeradmin.php";

if (!isset($_SESSION['adminID'])) {
    header("Location: ../login.php");
    exit();
}

// 处理状态更新
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employeeID = $_POST['employeeID'];
    $newStatus = $_POST['status'];
    $adminID = $_SESSION['adminID'];
    
    $sql = "UPDATE tb_member_status 
            SET status = ?, 
                dateUpdated = CURRENT_TIMESTAMP,
                updatedBy = ?
            WHERE employeeID = ?";
            
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sss', $newStatus, $adminID, $employeeID);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<div class='alert alert-success'>Status ahli telah dikemaskini.</div>";
    } else {
        echo "<div class='alert alert-danger'>Ralat: " . mysqli_error($conn) . "</div>";
    }
}

// 获取所有会员的状态
$sql = "SELECT ms.*, e.name, e.staffNo
        FROM tb_member_status ms
        JOIN tb_employees e ON ms.employeeID = e.employeeID
        ORDER BY e.name ASC";
$result = mysqli_query($conn, $sql);
?>

<div class="container mt-4">
    <h2>Pengurusan Status Ahli</h2>
    
    <div class="card mt-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="memberTable">
                    <thead>
                        <tr>
                            <th>No. Staf</th>
                            <th>Nama</th>
                            <th>Status</th>
                            <th>Tarikh Kemaskini</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['staffNo']; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $row['status'] == 'Aktif' ? 'success' : 
                                        ($row['status'] == 'Pencen' ? 'warning' : 'danger'); 
                                ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($row['dateUpdated'])); ?></td>
                            <td>
                                <?php if ($row['status'] != 'Berhenti'): ?>
                                <button class="btn btn-sm btn-primary" 
                                        onclick="showStatusModal('<?php echo $row['employeeID']; ?>', '<?php echo $row['status']; ?>')">
                                    Kemaskini
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

<!-- 状态更新模态框 -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Kemaskini Status Ahli</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="employeeID" id="employeeID">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Aktif">Aktif</option>
                            <option value="Pencen">Pencen</option>
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

<script>
function showStatusModal(employeeID, currentStatus) {
    document.getElementById('employeeID').value = employeeID;
    document.querySelector('select[name="status"]').value = currentStatus;
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}

// 初始化 DataTables
$(document).ready(function() {
    $('#memberTable').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Malay.json"
        }
    });
});
</script>