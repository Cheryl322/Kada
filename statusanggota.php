<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['employeeID'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

include "headermember.php";
include "footer.php";
include "dbconnect.php";

// Get the current user's employeeID from session
$employeeID = $_SESSION['employeeID']; // Make sure this matches your session variable name

// Get the registration status
$sql = "SELECT 
            CASE 
                WHEN mr.memberRegistrationID IS NOT NULL AND mr.regisStatus = 'Diluluskan' THEN 'Diluluskan'
                WHEN mr.memberRegistrationID IS NOT NULL AND mr.regisStatus = 'Ditolak' THEN 'Ditolak'
                WHEN mr.memberRegistrationID IS NOT NULL THEN 'Belum Selesai'
                ELSE 'Tiada Rekod'
            END as regisStatus,
            CASE 
                WHEN mr.regisDate IS NOT NULL THEN DATE(mr.regisDate)
                WHEN m.created_at IS NOT NULL THEN DATE(m.created_at)
                ELSE CURRENT_DATE()
            END as regisDate
        FROM tb_member m 
        LEFT JOIN tb_memberregistration_memberapplicationdetails mr 
        ON m.employeeID = mr.memberRegistrationID 
        WHERE m.employeeID = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $employeeID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Set default values
$status = 'Tiada Rekod';
$regisDate = date('d/m/Y'); // Default to today's date

// Only update values if we have results
if ($result && $row = mysqli_fetch_assoc($result)) {
    $status = $row['regisStatus'];
    $regisDate = !empty($row['regisDate']) 
        ? date('d/m/Y', strtotime($row['regisDate'])) 
        : date('d/m/Y');
}

// Make sure the date is available for JavaScript
echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        const currentStatus = document.getElementById('currentStatus');
        if (currentStatus) {
            currentStatus.setAttribute('data-date', '" . $regisDate . "');
        }
    });
</script>";
?>

<div class="container mt-3">
    <h3>Status Permohonan Anggota</h3>

    <!-- <?php if ($status == 'Diluluskan'): ?>
        <div class="success-status">
            <div class="status-circle">
                <i class="fas fa-check"></i>
            </div>
            <div class="status-text">Permohonan Diluluskan</div>
        </div>
    <?php elseif ($status == 'Ditolak'): ?>
        <div class="failed-status">
            <div class="status-circle">
                <i class="fas fa-times"></i>
            </div>
            <div class="status-text">Permohonan Ditolak</div>
        </div>
    <?php elseif ($status == 'Belum Selesai'): ?>
        <div class="pending-status">
            <div class="status-circle">
                <i class="fas fa-clock"></i>
            </div>
            <div class="status-text">Permohonan Belum Selesai</div>
            <div class="status-date">Tarikh: <?php echo $regisDate; ?></div>
        </div>
    <?php else: // 'Tiada Rekod' ?>
        <div class="no-record-status">
            <div class="status-circle">
                <i class="fas fa-exclamation"></i>
            </div>
            <div class="status-text">Tiada Rekod Permohonan</div>
        </div>
    <?php endif; ?> -->

    <!-- Add this button container -->
    <div class="button-container text-center mt-4">
        <a href="status.php" class="btn btn-kembali">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<input type="hidden" id="currentEmployeeID" value="<?php echo $employeeID; ?>">
<input type="hidden" id="currentStatus" value="<?php echo $status; ?>">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function updateProgressSteps(status) {
    const container = document.querySelector('.container');
    // Get the current date if no date is set
    const currentDate = new Date().toLocaleDateString('en-GB'); // Format as dd/mm/yyyy
    const regisDate = document.getElementById('currentStatus').getAttribute('data-date') || currentDate;
    let html = '';
    
    if (status === 'Diluluskan') {
        html = `
            <div class="success-status">
                <div class="status-circle">
                    <i class="fas fa-check"></i>
                </div>
                <div class="status-text">Permohonan Diluluskan</div>
            </div>
        `;
    } else if (status === 'Ditolak') {
        html = `
            <div class="failed-status">
                <div class="status-circle">
                    <i class="fas fa-times"></i>
                </div>
                <div class="status-text">Permohonan Ditolak</div>
            </div>
        `;
    } else if (status === 'Belum Selesai') {
        html = `
            <div class="pending-status">
                <div class="status-circle">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="status-text">Permohonan Belum Disahkan</div>
            </div>
        `;
    } else { // 'Tiada Rekod'
        html = `
            <div class="no-record-status">
                <div class="status-circle">
                    <i class="fas fa-exclamation"></i>
                </div>
                <div class="status-text">Tiada Rekod Permohonan</div>
            </div>
        `;
    }
    
    // Remove any existing status elements
    const existingElements = container.querySelectorAll('.success-status, .failed-status, .progress-steps');
    existingElements.forEach(element => element.remove());
    
    // Add the new status element after h3
    const h3 = container.querySelector('h3');
    h3.insertAdjacentHTML('afterend', html);
}

function updateStatus(memberId, newStatus) {
    $.ajax({
        url: 'update_status.php',
        method: 'POST',
        data: {
            memberId: memberId,
            status: newStatus
        },
        success: function(response) {
            try {
                const data = JSON.parse(response);
                if (data.success) {
                    // Update the status immediately
                    document.getElementById('currentStatus').value = data.status;
                    document.getElementById('currentStatus').setAttribute('data-date', data.date);
                    updateProgressSteps(data.status);
                    
                    // Optional: Show success message
                    alert('Status updated successfully');
                    
                    // Reload the page to refresh all data
                    window.location.reload();
                } else {
                    alert('Failed to update status');
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                alert('Error updating status');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error updating status:', error);
            alert('Error updating status');
        }
    });
}

// Set initial date
document.getElementById('currentStatus').setAttribute('data-date', '<?php echo $regisDate; ?>');

// Update status initially
updateProgressSteps(document.getElementById('currentStatus').value);

// Check for updates every 5 seconds
setInterval(checkStatus, 5000);
</script>

<style>
/* Steps Navigation */
.progress-steps {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2.5rem 4rem;
    background: #fff;
    margin-bottom: 30px;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
}

.step-item {
    flex: 1;
    text-align: center;
    position: relative;
    padding: 0 15px;
}

.step-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #f0f0f0;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    font-weight: 600;
    font-size: 1.2rem;
    position: relative;
    z-index: 2;
    transition: all 0.3s ease;
}

.step-line {
    position: absolute;
    top: 25px;
    left: 50%;
    width: 100%;
    height: 3px;
    background: #e0e0e0;
    transform: translateY(-50%);
    z-index: 1;
}

.step-item:last-child .step-line {
    display: none;
}

.step-label {
    color: #666;
    font-size: 1rem;
    font-weight: 500;
    margin-top: 0.8rem;
    transition: all 0.3s ease;
}

/* Active State */
.step-item.active .step-circle {
    background: #5CBA9B;
    color: white;
    box-shadow: 0 0 0 4px rgba(92,186,155,0.2);
    transform: scale(1.1);
}

/* Completed State */
.step-item.completed .step-circle {
    background: #5CBA9B;
    color: white;
}

.step-item.completed .step-line {
    background: #5CBA9B;
}

/* Responsive Design */
@media (max-width: 768px) {
    .progress-steps {
        padding: 2rem 1rem;
    }

    .step-circle {
        width: 45px;
        height: 45px;
        font-size: 1.1rem;
    }

    .step-label {
        font-size: 0.8rem;
    }

    .loan-header h1 {
        font-size: 1.5rem;
    }
}

/* Animation */
.step-item {
    transition: all 0.3s ease;
}

.step-item:hover .step-circle {
    transform: scale(1.1);
    box-shadow: 0 0 0 3px rgba(92,186,155,0.1);
}

/* New styles for success/failed status */
.success-status, .failed-status {
    text-align: center;
    padding: 2rem;
    margin: 2rem auto;
    max-width: 300px;
}

.status-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    transition: all 0.3s ease;
}

.success-status .status-circle {
    background: #5CBA9B;
    box-shadow: 0 0 0 15px rgba(92,186,155,0.2);
}

.failed-status .status-circle {
    background: #dc3545;
    box-shadow: 0 0 0 15px rgba(220,53,69,0.2);
}

.status-circle i {
    font-size: 50px;
    color: white;
}

.status-text {
    font-size: 1.5rem;
    font-weight: 600;
    margin-top: 1rem;
}

.success-status .status-text {
    color: #5CBA9B;
}

.failed-status .status-text {
    color: #dc3545;
}

/* Animation */
@keyframes scaleIn {
    from {
        transform: scale(0);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.status-circle {
    animation: scaleIn 0.5s ease-out;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .status-circle {
        width: 100px;
        height: 100px;
    }

    .status-circle i {
        font-size: 40px;
    }

    .status-text {
        font-size: 1.2rem;
    }
}

.status-date, .step-date {
    font-size: 0.9rem;
    color: #666;
    margin-top: 0.5rem;
}

.success-status .status-date {
    color: #5CBA9B;
}

.failed-status .status-date {
    color: #dc3545;
}

.no-record-status {
    text-align: center;
    padding: 2rem;
    margin: 2rem auto;
    max-width: 300px;
}

.no-record-status .status-circle {
    background: #6c757d;
    box-shadow: 0 0 0 15px rgba(108, 117, 125, 0.2);
}

.no-record-status .status-text {
    color: #6c757d;
}

/* Add pending status styles */
.pending-status {
    text-align: center;
    padding: 2rem;
    margin: 2rem auto;
    max-width: 300px;
}

.pending-status .status-circle {
    background: #ffc107;
    box-shadow: 0 0 0 15px rgba(255, 193, 7, 0.2);
}

.pending-status .status-text {
    color: #ffc107;
}

.pending-status .status-date {
    color: #ffc107;
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

/* Kembali button styles */
.button-container {
    margin-top: 2rem;
}

.btn-kembali {
    background-color: #5CBA9B;
    color: white;
    padding: 10px 30px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 2px solid #5CBA9B;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-kembali:hover {
    background-color: white;
    color: #5CBA9B;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(92, 186, 155, 0.2);
}

.btn-kembali i {
    font-size: 0.9em;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .btn-kembali {
        padding: 8px 20px;
        font-size: 0.9rem;
    }
}
</style>
