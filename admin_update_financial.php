<?php
session_start();
if (!isset($_SESSION['employeeID']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include "dbconnect.php";
include "headeradmin.php";

// 获取会员列表
$sql = "SELECT m.employeeID, m.memberName, f.* 
        FROM tb_member m 
        LEFT JOIN tb_member_financialstatus mf ON m.employeeID = mf.employeeID
        LEFT JOIN tb_financialstatus f ON mf.accountID = f.accountID";
$result = mysqli_query($conn, $sql);
?>

<div class="container mt-4">
    <h2 class="mb-4">Kemaskini Status Kewangan Ahli</h2>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID Pekerja</th>
                    <th>Nama</th>
                    <th>Modal Saham</th>
                    <th>Modal Yuran</th>
                    <th>Simpanan Tetap</th>
                    <th>Tabung Anggota</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['employeeID']; ?></td>
                    <td><?php echo $row['memberName']; ?></td>
                    <td>RM <?php echo number_format($row['memberSaving'] ?? 0, 2); ?></td>
                    <td>RM <?php echo number_format($row['feeCapital'] ?? 0, 2); ?></td>
                    <td>RM <?php echo number_format($row['fixedDeposit'] ?? 0, 2); ?></td>
                    <td>RM <?php echo number_format($row['contribution'] ?? 0, 2); ?></td>
                    <td>
                        <button class="btn btn-primary btn-sm" 
                                onclick="openUpdateModal('<?php echo $row['employeeID']; ?>', 
                                                       '<?php echo $row['memberName']; ?>', 
                                                       <?php echo $row['memberSaving'] ?? 0; ?>,
                                                       <?php echo $row['feeCapital'] ?? 0; ?>,
                                                       <?php echo $row['fixedDeposit'] ?? 0; ?>,
                                                       <?php echo $row['contribution'] ?? 0; ?>)">
                            Kemaskini
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for updating financial status -->
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kemaskini Status Kewangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateForm" action="process_financial_update.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="employeeID" name="employeeID">
                    <div class="mb-3">
                        <label class="form-label">Nama Ahli: <span id="memberName"></span></label>
                    </div>
                    <div class="mb-3">
                        <label for="memberSaving" class="form-label">Modal Saham</label>
                        <input type="number" step="0.01" class="form-control" id="memberSaving" name="memberSaving" required>
                    </div>
                    <div class="mb-3">
                        <label for="feeCapital" class="form-label">Modal Yuran</label>
                        <input type="number" step="0.01" class="form-control" id="feeCapital" name="feeCapital" required>
                    </div>
                    <div class="mb-3">
                        <label for="fixedDeposit" class="form-label">Simpanan Tetap</label>
                        <input type="number" step="0.01" class="form-control" id="fixedDeposit" name="fixedDeposit" required>
                    </div>
                    <div class="mb-3">
                        <label for="contribution" class="form-label">Tabung Anggota</label>
                        <input type="number" step="0.01" class="form-control" id="contribution" name="contribution" required>
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
function openUpdateModal(employeeID, memberName, memberSaving, feeCapital, fixedDeposit, contribution) {
    document.getElementById('employeeID').value = employeeID;
    document.getElementById('memberName').textContent = memberName;
    document.getElementById('memberSaving').value = memberSaving;
    document.getElementById('feeCapital').value = feeCapital;
    document.getElementById('fixedDeposit').value = fixedDeposit;
    document.getElementById('contribution').value = contribution;
    
    new bootstrap.Modal(document.getElementById('updateModal')).show();
}
</script>

<?php
mysqli_close($conn);
include "footer.php";
?> 