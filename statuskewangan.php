<?php
session_start();
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
}

// Get user data if user is logged in
$userData = isset($_SESSION['user_id']) ? getUserData($_SESSION['user_id']) : null;

// If no user data, you might want to set default values
if (!$userData) {
    $userData = [
        'nama' => 'Yuna Liew Mei Mei',
        'email' => 'yuna@example.com',
        'ic_passport' => '000000-00-0000',
        'marital_status' => 'Kahwin',
        'address' => '33, Jalan Bunga Tulip',
        'postcode' => '00000',
        'state' => 'Johor',
        'gender' => 'Perempuan',
        'religion' => 'Buddhist'
    ];
}
?>

<style>
	.button {
		color: primary;
		margin: 4px 2px;
	}

	.button1
</style>

<div class="container">
   <div class="row">
       <!-- Left Sidebar -->
       <div class="col-md-3">
           <div class="profile-sidebar">
               <div class="profile-image">
                   <img src="img/profile.jpeg" class="rounded-circle" alt="Profile Picture">
                   <h3 class="text-left mt-3">Yuna Liew</h3>
               </div>

                <!-- Navigation Menu-->
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

			<div class="container mt-2">
			  <div class="row">
			    <div class="col p-3 bg-primary text-white">
			    	<h3>Jumlah Pinjaman</h3>
			  		<p>RM 5000.00</p>
			    </div>
			    <div class="col p-3 bg-primary text-white">
			    	<h3>Jumlah Simpanan</h3>
			  		<p>RM 495.00</p>
			    </div>
			  </div>
			</div>


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