<?php
include "headermember.php";
include "dbconnect.php";
include "footer.php";
// Assuming you have a database connection and user session management
session_start();
?>

<div class="container mt-5">

<?php
// Get user data if user is logged in
$userData = isset($_SESSION['user_id']) ? getUserData($_SESSION['user_id']) : null;
$sql="SELECT * FROM tb_member";
$result=mysqli_query($con, $sql);
while($row=mysqli_fetch_array($result)){
    $userData['memberName']=$row['memberName'];
    $userData['email']=$row['email'];
    $userData['ic']=$row['ic'];
    $userData['maritalStatus']=$row['maritalStatus'];
    $userData['address']=$row['address'];
    $userData['poscode']=$row['poscode'];
    $userData['state']=$row['state'];   
    $userData['sex']=$row['sex'];
    $userData['religion']=$row['religion'];
    $userData['nation']=$row['nation'];
    $userData['no_anggota']=$row['no_anggota'];
    $userData['no_pf']=$row['no_pf'];
    $userData['position']=$row['position'];
    $userData['officeAddress']=$row['officeAddress'];
    $userData['phoneHome']=$row['phoneHome'];
    $userData['phoneNumber']=$row['phoneNumber'];
}

?>
<div class="container">
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
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-primary" onclick="editProfile()">Edit</button>
                    </div>
               <form method="POST" action="update_profile.php">
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Nama</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="nama" value="<?php echo htmlspecialchars($userData['memberName']); ?>"readonly>
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Alamat Emel</label>
                       <div class="col-sm-9">
                           <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>"readonly>
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">MyKad/No. Passport</label>
                       <div class="col-sm-9">
                            <input type="text" class="form-control" name="ic_passport" value="<?php echo htmlspecialchars($userData['ic']); ?>"readonly>
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Taraf perkahwinan</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="marital_status" value="<?php echo htmlspecialchars($userData['maritalStatus']); ?>"readonly>
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Alamat Rumah</label>
                       <div class="col-sm-9">
                            <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($userData['address']); ?>"readonly>
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Poskod</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="postcode" value="<?php echo htmlspecialchars($userData['poscode']); ?>"readonly>
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Negeri</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="state" value="<?php echo htmlspecialchars($userData['state']); ?>"readonly>
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Jantina</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="gender" value="<?php echo htmlspecialchars($userData['sex']); ?>"readonly>
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Agama</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="religion" value="<?php echo htmlspecialchars($userData['religion']); ?>"readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Bangsa</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="bangsa" value="<?php echo htmlspecialchars($userData['nation']); ?>"readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">No. Anggota</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="noAnggota" value="<?php echo htmlspecialchars($userData['no_anggota']); ?>"readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">No. PF</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="noPF" value="<?php echo htmlspecialchars($userData['no_pf']); ?>"readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Jawatan & Gred</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="jawatanGred" value="<?php echo htmlspecialchars($userData['position']); ?>"readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Alamat Pejabat</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="alamatPejabat" value="<?php echo htmlspecialchars($userData['officeAddress']); ?>"readonly>
                       </div>
                   </div>           
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">No. Tel Bimbit</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="noTelBimbit" value="<?php echo htmlspecialchars($userData['phoneNumber']); ?>"readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">No. Tel Rumah</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="noTelRumah" value="<?php echo htmlspecialchars($userData['phoneHome']); ?>"readonly>
                       </div>
                   </div>   
                    <div class="form-group row mb-5">
                       <div class="col-sm-9 offset-sm-3">
                           <button type="submit" class="btn btn-primary" style="display:none;" id="updateButton">Kemaskini</button>
                       </div>
                   </div>
               </form>
           </div>
       </div>
   </div>
</div>


<script>
function editProfile(){
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.removeAttribute('readonly');
    });
    document.getElementById('updateButton').style.display='block';
}

</script>   


