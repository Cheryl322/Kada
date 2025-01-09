<?php

include "headermember.php";
include "dbconnect.php";
include "footer.php";
// Assuming you have a database connection and user session management
session_start();
?>

<div class="container mt-5">

<?php
// Simple function to get user data
function getUserData($user_id) {
    // Replace these database credentials with your own
    $servername = "localhost";
    $dbname = "db_kada";
    $username = "root";
    $password = "";

    /*try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        // For development, you might want to see the error
        // echo "Error: " . $e->getMessage();
        return false;
    }*/
}

// Get user data if user is logged in
$userData = isset($_SESSION['user_id']) ? getUserData($_SESSION['user_id']) : null;
$sql="SELECT * FROM tb_profile";
$result=mysqli_query($con, $sql);
while($row=mysqli_fetch_array($result)){
    $userData['p_name']=$row['p_name'];
    $userData['p_email']=$row['p_email'];
    $userData['p_ic']=$row['p_ic'];
    $userData['p_marital']=$row['p_marital'];
    $userData['p_address']=$row['p_address'];
    $userData['p_poskod']=$row['p_poskod'];
    $userData['p_country']=$row['p_country'];   
    $userData['p_sex']=$row['p_sex'];
    $userData['p_agama']=$row['p_agama'];
    $userData['p_bangsa']=$row['p_bangsa'];
    $userData['p_nostaff']=$row['p_nostaff'];
    $userData['p_nopf']=$row['p_nopf'];
    $userData['p_jawatan']=$row['p_jawatan'];
    $userData['p_addpejabat']=$row['p_addpejabat'];
    $userData['p_notel']=$row['p_notel'];
    $userData['p_notelhome']=$row['p_notelhome'];
}
// If no user data, you might want to set default values
/*if (!$userData) {
    $userData = [
        'nama' => 'Yuna Liew Mei Mei',
        'email' => 'yuna@example.com',
        'ic_passport' => '000000-00-0000',
        'marital_status' => 'Kahwin',
        'address' => '33, Jalan Bunga Tulip',
        'postcode' => '00000',
        'state' => 'Johor',
        'gender' => 'Perempuan',
        'religion' => 'Buddhist',
        'bangsa' => 'Buddhist',
        'noAnggota' => 'Buddhist',
        'noPF' => 'Buddhist',
        'jawatanGred'=>'Buddhist',
        'alamatPejabat'=>'Buddhist',
        'noTelBimbit'=>'Buddhist',
        'noTelRumah'=>'Buddhist',
        'gajiBulanan'=>'Buddhist'
    ];
}*/

?>
<div class="container">
    <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }
    ?>
   <div class="row">
       <!-- Left Sidebar -->
       <div class="col-md-3">
           <div class="profile-sidebar">
               <div class="profile-image">
                   <img src="img/profile.jpeg" class="rounded-circle" alt="Profile Picture">
                   <h3 class="text-left mt-3">Yuna Liew Mei Mei</h3>
               </div>

                <!-- Navigation Menu-->
                <div class="profile-nav">
                    <ul class="nav flex-column gap-2">
                        <li class="nav-item w-100">
                            <a class="btn btn-primary w-75" href="profil.php">Profil</a>
                        </li>
                        <li class="nav-item w-100">
                            <a class="btn btn-info w-75" href="statuskewangan.php">Pinjaman</a>
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
        <!-- Main Content -->
       <div class="col-md-9">
           <div class="profile-content">
               <form method="POST" action="update_profil.php">
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Nama</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="nama" value="<?php echo htmlspecialchars($userData['p_name']); ?>">
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Alamat Emel</label>
                       <div class="col-sm-9">
                           <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($userData['p_email']); ?>">
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">MyKad/No. Passport</label>
                       <div class="col-sm-9">
                            <input type="text" class="form-control" name="ic_passport" value="<?php echo htmlspecialchars($userData['p_ic']); ?>">
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Taraf perkahwinan</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="marital_status" value="<?php echo htmlspecialchars($userData['p_marital']); ?>">
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Alamat Rumah</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($userData['p_address']); ?>">
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Poskod</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="postcode" value="<?php echo htmlspecialchars($userData['p_poskod']); ?>">
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Negeri</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="state" value="<?php echo htmlspecialchars($userData['p_country']); ?>">
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Jantina</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="gender" value="<?php echo htmlspecialchars($userData['p_sex']); ?>">
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Agama</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="religion" value="<?php echo htmlspecialchars($userData['p_agama']); ?>">
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Bangsa</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="bangsa" value="<?php echo htmlspecialchars($userData['p_bangsa']); ?>">
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">No. Anggota</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="noAnggota" value="<?php echo htmlspecialchars($userData['p_nostaff']); ?>">
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">No. PF</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="noPF" value="<?php echo htmlspecialchars($userData['p_nopf']); ?>">
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Jawatan & Gred</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="jawatanGred" value="<?php echo htmlspecialchars($userData['p_jawatan']); ?>">
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Alamat Pejabat</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="alamatPejabat" value="<?php echo htmlspecialchars($userData['p_addpejabat']); ?>">
                       </div>
                   </div>           
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">No. Tel Bimbit</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="noTelBimbit" value="<?php echo htmlspecialchars($userData['p_notel']); ?>">
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">No. Tel Rumah</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="noTelRumah" value="<?php echo htmlspecialchars($userData['p_notelhome']); ?>">
                       </div>
                   </div>   
                    <div class="form-group row mb-5">
                       <div class="col-sm-9 offset-sm-3">
                           <button type="submit" class="btn btn-primary">Kemaskini</button>
                       </div>
                   </div>
               </form>
           </div>
       </div>
   </div>
</div>
<!-- Add this CSS to your stylesheet
style>
profile-sidebar {
   padding: 20px;
   background: #f8f9fa;
   border-radius: 10px;

.profile-image img {
   width: 150px;
   height: 150px;
   object-fit: cover;
   margin: 0 auto;
   display: block;

.profile-nav .nav-link {
   padding: 10px 15px;
   color: #333;
   border-left: 3px solid transparent;

.profile-nav .nav-link.active {
   background: #e9ecef;
   border-left: 3px solid #007bff;

.profile-content {
   padding: 20px;
   background: #fff;
   border-radius: 10px;
   box-shadow: 0 0 10px rgba(0,0,0,0.1);

.form-control {
   border-radius: 5px; -->

</style>
</div>

