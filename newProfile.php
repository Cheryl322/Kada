<?php

include "headermember.php";
include "dbconnect.php";
include "footer.php";
session_start();

// Simple function to get user data
function getUserData($user_id, $con) {
    $sql = "SELECT * FROM tb_member WHERE id = employeeId";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Get user data if user is logged in
$userData = isset($_SESSION['user_id']) ? getUserData($_SESSION['user_id'], $con) : null;

?>
<!-- <div class="container mt-5">
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
       Left Sidebar 
       <div class="col-md-3">
           <div class="profile-sidebar">
               <div class="profile-image">
                   <img src="img/profile.jpeg" class="rounded-circle" alt="Profile Picture">
                   <h3 class="text-left mt-3"><?php echo htmlspecialchars($userData['memberName']); ?></h3>
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
                           <input type="text" class="form-control" name="nama" value="<?php echo htmlspecialchars($userData['memberName']); ?>" readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Alamat Emel</label>
                       <div class="col-sm-9">
                           <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">MyKad/No. Passport</label>
                       <div class="col-sm-9">
                            <input type="text" class="form-control" name="ic_passport" value="<?php echo htmlspecialchars($userData['ic']); ?>" readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Taraf perkahwinan</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="marital_status" value="<?php echo htmlspecialchars($userData['maritalStatus']); ?>" readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Alamat Rumah</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($userData['address']); ?>" readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Poskod</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="postcode" value="<?php echo htmlspecialchars($userData['poscode']); ?>" readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Negeri</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="state" value="<?php echo htmlspecialchars($userData['state']); ?>" readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Jantina</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="gender" value="<?php echo htmlspecialchars($userData['sex']); ?>" readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Agama</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="religion" value="<?php echo htmlspecialchars($userData['religion']); ?>" readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Bangsa</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="bangsa" value="<?php echo htmlspecialchars($userData['nation']); ?>" readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">No. Anggota</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="noAnggota" value="<?php echo htmlspecialchars($userData['employeeId']); ?>" readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">No. PF</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="noPF" value="<?php echo htmlspecialchars($userData['no_pf']); ?>" readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Jawatan & Gred</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="jawatanGred" value="<?php echo htmlspecialchars($userData['position']); ?>" readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Alamat Pejabat</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="alamatPejabat" value="<?php echo htmlspecialchars($userData['officeAddress']); ?>" readonly>
                       </div>
                   </div>  
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Poskod</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="poskod2" value="<?php echo htmlspecialchars($userData['officePostcode']); ?>" readonly>
                       </div>
                   </div>   
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">State</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="state2" value="<?php echo htmlspecialchars($userData['officeState']); ?>" readonly>
                       </div>
                   </div>           
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">No. Tel Bimbit</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="noTelBimbit" value="<?php echo htmlspecialchars($userData['phoneNumber']); ?>" readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">No. Tel Rumah</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="noTelRumah" value="<?php echo htmlspecialchars($userData['phoneHome']); ?>" readonly>
                       </div>
                   </div>   
                   <div class="form-group row mb-5">
                       <div class="col-sm-9 offset-sm-3">
                           <button type="button" class="btn btn-primary" id="editButton" onclick="editProfile()">Edit</button>
                           <button type="submit" class="btn btn-success" id="updateButton" style="display: none;">Simpan</button>
                           <button type="button" class="btn btn-secondary" id="cancelButton" onclick="cancelEdit()" style="display: none;">Batal</button>
                       </div>
                   </div>
               </form>
           </div>
       </div>
   </div>
</div>-->

<!-- <script>
function editProfile() {
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.removeAttribute('readonly');
    });
    document.getElementById('editButton').style.display = 'none';
    document.getElementById('updateButton').style.display = 'inline-block';
    document.getElementById('cancelButton').style.display = 'inline-block';
}

function cancelEdit() {
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.setAttribute('readonly', true);
    });
    document.getElementById('editButton').style.display = 'inline-block';
    document.getElementById('updateButton').style.display = 'none';
    document.getElementById('cancelButton').style.display = 'none';
    location.reload(); // Reload the page to reset the form
}
</script>  -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
</head>
<body>
    <h1>Profile</h1>
    <?php if ($userData): ?>
        <p>Nama: <?php echo htmlspecialchars($userData['memberName']); ?></p>
        <p>Email: <?php echo htmlspecialchars($userData['email']); ?></p>
        <p>IC/Passport: <?php echo htmlspecialchars($userData['ic']); ?></p>
        <p>Marital Status: <?php echo htmlspecialchars($userData['maritalStatus']); ?></p>
        <p>Address: <?php echo htmlspecialchars($userData['address']); ?></p>
        <p>Poscode: <?php echo htmlspecialchars($userData['poscode']); ?></p>
        <p>State: <?php echo htmlspecialchars($userData['state']); ?></p>
        <p>Sex: <?php echo htmlspecialchars($userData['sex']); ?></p>
        <p>Religion: <?php echo htmlspecialchars($userData['religion']); ?></p>
        <p>Nation: <?php echo htmlspecialchars($userData['nation']); ?></p>
        <p>Employee ID: <?php echo htmlspecialchars($userData['employeeId']); ?></p>
        <p>No PF: <?php echo htmlspecialchars($userData['no_pf']); ?></p>
        <p>Position: <?php echo htmlspecialchars($userData['position']); ?></p>
        <p>Office Address: <?php echo htmlspecialchars($userData['officeAddress']); ?></p>
        <p>Phone Number: <?php echo htmlspecialchars($userData['phoneNumber']); ?></p>
        <p>Phone Home: <?php echo htmlspecialchars($userData['phoneHome']); ?></p>
    <?php else: ?>
        <p>No user data found.</p>
    <?php endif; ?>
</body>
</html>