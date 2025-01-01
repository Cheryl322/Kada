<?php
include"headermember.php";
include "footer.php";
// Assuming you have a database connection and user session management
session_start();
?>

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
                            <a class="btn btn-info w-75" href="statuskewangan.php">Pinjaman</a>
                        </li>
                        <li class="nav-item w-100">
                            <a class="btn btn-primary w-75" href="statuspermohonanloan.php">Permohonan</a>
                        </li>
                        <li class="nav-item w-100">
                            <a class="btn btn-info w-75" href="logout.php">Daftar Keluar</a>
                        </li>
                    </ul>
                </div>

           </div>
       </div>

       <div class="col-md-9">
            <div class="container mt-3">
              <table class="table">
                <h3>Status Permohonan Pembiayaan </h3>
                <thead class="table-dark">
                  <tr>
                    <th>No. Permohonan</th>
                    <th>Nama</th>
                    <th>IC</th>
                    <th>Tarikh Penyerahan</th>
                    <th>Status</th>        
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>#001</td>
                    <td>Yuna Liew</td>
                    <td>000000-00-0000</td>
                    <td>6 Jun 2024</td>
                    <td>Ditolak</td>
                  </tr>
                  <tr>
                    <td>#002</td>
                    <td>Yuna Liew</td>
                    <td>000000-00-0000</td>
                    <td>20 Dec 2024</td>
                    <td>Dilulus</td>
                  </tr>
                </tbody>
              </table>
            </div>
        </div>
    </div>
</div>