<?php
session_start();
<<<<<<< HEAD

// Check if user is logged in
if (!isset($_SESSION['employeeID'])) {
    header('Location: login.php');
    exit();
=======
include"headermember.php";
include "footer.php";
// Assuming you have a database connection and user session management

?>

<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script> -->

<div class="container mt-5">

<?php
// Simple function to get user data
function getUserData($user_id) {
    // Replace these database credentials with your own
    $host = 'localhost';
    $dbname = 'your_database';
    $username = 'your_username';
    $password = 'your_password';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        // For development, you might want to see the error
        // echo "Error: " . $e->getMessage();
        return false;
    }
>>>>>>> b1d39d84883b0d8de7217623c3479b95afbb6c0f
}

include "headermember.php";
include "dbconnect.php";

// Get member data and loan information
$employeeId = $_SESSION['employeeID'];
$sql = "SELECT m.*, 
               l.amountRequested as loan_amount, 
               l.status as loan_status
        FROM tb_member m
        LEFT JOIN tb_loan l ON m.employeeID = l.employeeID
        WHERE m.employeeID = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $employeeId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$memberData = mysqli_fetch_assoc($result);

// Get transaction history
$transactionSql = "SELECT * FROM tb_transaction 
                   WHERE employeeID = ? 
                   ORDER BY transDate DESC";
$transStmt = mysqli_prepare($conn, $transactionSql);
mysqli_stmt_bind_param($transStmt, "i", $employeeId);
mysqli_stmt_execute($transStmt);
$transactions = mysqli_stmt_get_result($transStmt);

// Calculate total savings from transactions
$savingsSql = "SELECT SUM(transAmt) as total_savings 
               FROM tb_transaction 
               WHERE employeeID = ? 
               AND transType = 'savings'";
$savingsStmt = mysqli_prepare($conn, $savingsSql);
mysqli_stmt_bind_param($savingsStmt, "i", $employeeId);
mysqli_stmt_execute($savingsStmt);
$savingsResult = mysqli_stmt_get_result($savingsStmt);
$savingsData = mysqli_fetch_assoc($savingsResult);
$totalSavings = $savingsData['total_savings'] ?? 0;
?>

<div class="container">
   <div class="row">
       <!-- Left Sidebar -->
       <div class="col-md-3">
           <div class="profile-sidebar">
               <div class="profile-image">
                   <img src="img/profile.jpeg" class="rounded-circle" alt="Profile Picture">
                   <h3 class="text-left mt-3"><?php echo isset($memberData['memberName']) ? $memberData['memberName'] : 'User'; ?></h3>
               </div>

                <!-- Navigation Menu -->
                <div class="profile-nav">
                    <ul class="nav flex-column gap-2">
                        <li class="nav-item w-100">
                            <a class="btn btn-info w-75" href="profil.php">Profil</a>
                        </li>
                        <li class="nav-item w-100">
                            <a class="btn btn-primary w-75" href="statuskewangan.php">Pinjaman</a>
                        </li>
                        <li class="nav-item w-100">
                            <a class="btn btn-info w-75" href="statuspermohonanloan.php">Permohonan</a>
                        </li>
                        <li class="nav-item w-100">
                            <a class="btn btn-info w-75" href="logout.php">Daftar Keluar</a>
                        </li>
                    </ul>
                </div>
           </div>
       </div>

       <div class="col-md-9">
            <!-- Financial Summary Cards -->
            <div class="container mt-2">
                <div class="row">
                    <div class="col p-3 bg-primary text-white rounded me-2">
                        <h3>Jumlah Pinjaman</h3>
                        <p class="h4">RM <?php echo number_format(isset($memberData['loan_amount']) ? $memberData['loan_amount'] : 0, 2); ?></p>
                    </div>
                    <div class="col p-3 bg-primary text-white rounded ms-2">
                        <h3>Jumlah Simpanan</h3>
                        <p class="h4">RM <?php echo number_format($totalSavings, 2); ?></p>
                    </div>
                </div>
            </div>

            <!-- Transaction History Table -->
            <div class="container mt-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h4 class="mb-0">Sejarah Transaksi</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>No. Invois</th>
                                    <th>Tarikh</th>
                                    <th>Penerangan</th>
                                    <th>Jumlah (RM)</th>
                                    <th>Status</th>        
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($transactions)): ?>
                                <tr>
                                    <td><?php echo $row['transID']; ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['transDate'])); ?></td>
                                    <td><?php echo $row['transType']; ?></td>
                                    <td><?php echo number_format($row['transAmt'], 2); ?></td>
                                    <td>
                                        <div class="d-grid">
                                            <?php if($row['transType'] == 'savings'): ?>
                                                <a class="btn btn-success btn-sm" href="penyatakewangan.php?id=<?php echo $row['employeeID']; ?>">
                                                    Diluluskan
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-info btn-sm" disabled>
                                                    <?php echo $row['transType']; ?>
                                                </button>
                                            <?php endif; ?>
                                        </div>
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
</div>

<style>
.profile-sidebar {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

<<<<<<< HEAD
.profile-image {
    text-align: center;
    margin-bottom: 20px;
}

.profile-image img {
    width: 150px;
    height: 150px;
    object-fit: cover;
    margin-bottom: 10px;
}

.profile-nav {
    margin-top: 20px;
}

.profile-nav .nav-item {
    margin-bottom: 10px;
}

.btn-info {
    background-color: #36b9cc;
    border-color: #36b9cc;
    color: white;
}

.btn-info:hover {
    background-color: #2a9aaa;
    border-color: #2a9aaa;
    color: white;
}

.rounded {
    border-radius: 8px !important;
}
</style>

<?php include "footer.php"; ?>
=======
			<div class="container mt-3">
			  <table class="table">
			    <thead class="table-dark">
			      <tr>
			        <th>No. Invois</th>
			        <th>Tarikh</th>
			        <th>Penerangan</th>
			        <th>Jumlah</th>
			        <th>Status</th>        
			      </tr>
			    </thead>
			    <tbody>
			      <tr>
			        <td>#001</td>
			        <td>2 jan 2024</td>
			        <td>Yuran Koperasi</td>
			        <td>$ 55.00</td>
					<td><div class="d-grid">
                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#statusModal">Ditolak</button>
                   </div></td>
			      </tr>
			      <tr>
			        <td>#002</td>
			        <td>2 jan 2024</td>
			        <td>Yuran Koperasi</td>
			        <td>$ 55.00</td>
					<td><div class="d-grid">
                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#statusModal">Diluluskan</button>
                   </div></td>
			      </tr>
			      <tr>
			        <td>#003</td>
			        <td>2 jan 2024</td>
			        <td>Yuran Koperasi</td>
			        <td>$ 55.00</td>
					<td><div class="d-grid">
                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#statusModal">Ditolak</button>
                   </div></td>
			      </tr>
			    </tbody>
			  </table>
			</div>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Status Permohonan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Status permohonan pembiayaan</h6>
                
                <!-- Status Steps -->
                <div class="status-container mt-4">
                    <div class="status-steps">
                        <div class="step">
                            <div class="circle">1</div>
                            <div class="label">Permohonan serahkan</div>
                        </div>
                        <div class="step">
                            <div class="circle">2</div>
                            <div class="label">Permohonan diteliti oleh pengurusan lembaga</div>
                        </div>
                        <div class="step">
                            <div class="circle">3</div>
                            <div class="label">Permohonan lulus / gagal</div>
                        </div>
                        <div class="step">
                            <div class="circle">4</div>
                            <div class="label">Keputusan pembentangan</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
.status-container {
    padding: 20px;
}

.status-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin-bottom: 40px;
}

.status-steps::before {
    content: '';
    position: absolute;
    top: 25px;
    left: 0;
    right: 0;
    height: 2px;
    background: #4CAF50;
    z-index: 1;
}

.step {
    text-align: center;
    position: relative;
    z-index: 2;
    width: 120px;
}

.circle {
    width: 50px;
    height: 50px;
    background-color: #4CAF50;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: white;
    font-weight: bold;
    font-size: 18px;
}

.label {
    margin-top: 10px;
    font-size: 12px;
    color: #333;
}

/* For Ditolak status */
.step.failed .circle {
    background-color: #ff0000;
}

.step.pending .circle {
    background-color: #808080;
}
</style>

<script>
function updateStatus(status) {
    const steps = document.querySelectorAll('.step');
    const statusLine = document.querySelector('.status-steps::before');
    
    if (status === 'Diluluskan') {
        steps.forEach(step => {
            step.querySelector('.circle').style.backgroundColor = '#4CAF50';
        });
        document.querySelector('.status-steps').style.setProperty('--line-color', '#4CAF50');
    } else if (status === 'Ditolak') {
        steps.forEach((step, index) => {
            const circle = step.querySelector('.circle');
            if (index < 2) {
                circle.style.backgroundColor = '#4CAF50';
            } else if (index === 2) {
                circle.style.backgroundColor = '#ff0000';
            } else {
                circle.style.backgroundColor = '#808080';
            }
        });
        
        // Update line gradient
        document.querySelector('.status-steps').style.setProperty(
            '--line-gradient',
            'linear-gradient(to right, #4CAF50 50%, #ff0000 50%, #808080 75%)'
        );
    }
}

// Update when modal opens
document.getElementById('statusModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const status = button.textContent.trim();
    updateStatus(status);
});
</script>
>>>>>>> b1d39d84883b0d8de7217623c3479b95afbb6c0f
