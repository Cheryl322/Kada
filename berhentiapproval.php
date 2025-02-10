<?php
// Start session and check login before any output
session_start();

// Include files after login check
include "dbconnect.php";
include "headeradmin.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verify database connection
if (mysqli_connect_errno()) {
    die("<div class='alert alert-danger'>Connection failed: " . mysqli_connect_error() . "</div>");
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resignID'])) {
    processApproval($conn);
}

// Get all applications
$sql = "SELECT b.*, m.memberName, m.email 
        FROM tb_berhenti b
        LEFT JOIN tb_member m ON b.employeeID = m.employeeID
        ORDER BY b.applyDate DESC";
        
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("<div class='alert alert-danger'>Query failed: " . mysqli_error($conn) . "</div>");
}
?>

<!-- Add this new div for spacing -->
<div class="header-spacing mb-4"></div>

<div class="container mt-5"> <!-- Changed from mt-4 to mt-5 -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h3 class="card-title mb-0">Senarai Permohonan Berhenti</h3>
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="berhentiTable">
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
                                    <td><?php echo htmlspecialchars($row['berhentiID']); ?></td>
                                    <td><?php echo htmlspecialchars($row['memberName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['reason']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['applyDate'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo getStatusClass($row['approvalStatus']); ?>">
                                            <?php echo htmlspecialchars($row['approvalStatus']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['approvalStatus'] == 'Pending'): ?>
                                            <button type="button" class="btn btn-primary btn-sm btn-tindakan"
                                                    data-id="<?php echo $row['berhentiID']; ?>">
                                                <i class="fas fa-check-circle me-1"></i>Tindakan
        
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Tiada permohonan berhenti yang ditemui.
                </div>
            <?php endif; ?>
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
                        <select name="rejectionReason" class="form-select" id="rejectionReason">
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

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#berhentiTable').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Malay.json"
        },
        order: [[3, 'desc']]
    });

    // Handle status select change
    $('#statusSelect').on('change', function() {
        var selectedValue = $(this).val();
        if (selectedValue === 'Tolak') {
            $('.rejection-reason').slideDown();
        } else {
            $('.rejection-reason').slideUp();
        }
    });

    // Add click handler for Tindakan buttons
    $(document).on('click', '.btn-tindakan', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        showApprovalModal(id);
    });
});

function showApprovalModal(id) {
    // Reset form values
    $('#resignID').val(id);
    $('#statusSelect').val('');
    $('.rejection-reason').hide();
    
    // Show modal
    $('#approvalModal').modal('show');
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

/* Ensure container has proper spacing */
.container {
    padding-top: 20px;
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
</style>