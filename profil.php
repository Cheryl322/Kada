<?php

include "headermember.php";
include "dbconnect.php";
include "footer.php";
// Assuming you have a database connection and user session management
if(!session_id()){
    session_start();
}

if (!isset($_SESSION['employeeID'])) {
    header('Location: login.php');
    exit();
}



// 获取会员数据
$sql = "SELECT m.*, 
               h.homeAddress, h.homePostcode, h.homeState,
               o.officeAddress, o.officePostcode, o.officeState
        FROM tb_member m
        LEFT JOIN tb_member_homeaddress h ON m.employeeId = h.employeeID 
        LEFT JOIN tb_member_officeaddress o ON m.employeeId = o.employeeID
        WHERE m.employeeId = ?";


$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $_SESSION['employeeID']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$memberData = mysqli_fetch_assoc($result);

?>

    <div class="container">

    <div class="my-3"></div>
    <p style="text-align: center; font-size:30px;"><b>Maklumat Peribadi</b></p>
    <div class="my-4"></div>

   <div class="row">
       <!-- Left Sidebar -->
       <div class="col-md-3">
           <div class="profile-sidebar">
               <div class="profile-image">
                   <img src="img/profile.jpeg" class="rounded-circle" alt="Profile Picture">
                   <h3 class="text-left mt-3"><?php echo htmlspecialchars($memberData['memberName']); ?></h3>
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
                            <a class="btn btn-info w-75" href="viewfinancialstatus.php">Penyata Kewangan</a>
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
               <!-- 添加卡片样式 -->
               <div class="card mb-3">
                   <div class="card-header text-white bg-primary d-flex justify-content-between align-items-center">
                       Butir-butir Peribadi Pemohon
                       <button type="button" class="btn btn-info" id="editButton" onclick="editProfile()">
                           Kemaskini
                       </button>
                   </div>
                   <div class="card-body">
                       <form method="POST" action="update_profil.php" id="profileForm">
                           <!-- 保持现有的表单字段，但使用 table 布局 -->
                           <table class="table table-hover">
                               <tbody>
                                   <tr>
                                        <td scope="row">Nama</td>
                                        <td><input type="text" class="form-control" name="nama" value="<?php echo htmlspecialchars($memberData['memberName']); ?>" readonly></td>
                                   </tr>  
                                   <tr>
                                        <td scope="row">Alamat Emel</td>
                                        <td><input type="text" class="form-control" name="email" value="<?php echo htmlspecialchars($memberData['email']); ?>" readonly></td>
                                   </tr>
                                   <tr>
                                        <td scope="row">MyKad/No. Passport</td>
                                        <td><input type="text" class="form-control" name="ic_passport" value="<?php echo htmlspecialchars($memberData['ic']); ?>"readonly></td>
                                   </tr>
                                   <tr>
                                        <td scope="row">Taraf perkahwinan</td>
                                        <td><input type="text" class="form-control" name="marital_status" value="<?php echo htmlspecialchars($memberData['maritalStatus']); ?>"readonly></td>
                                   </tr>
                                   <tr>
                                        <td scope="row">Alamat Rumah</td>
                                        <td><input type="text" class="form-control" name="homeAddress" value="<?php echo htmlspecialchars($memberData['homeAddress']); ?>"readonly></td>
                                   </tr>
                                   <tr>
                                        <td scope="row">Poskod</td>
                                        <td><input type="text" class="form-control" name="homePostcode" value="<?php echo htmlspecialchars($memberData['homePostcode']); ?>"readonly></td>
                                   </tr>
                                   <tr>
                                        <td scope="row">Negeri</td>
                                        <td><input type="text" class="form-control" name="homeState" value="<?php echo htmlspecialchars($memberData['homeState']); ?>"readonly></td>
                                   </tr>
                                   <tr>
                                        <td scope="row">Jantina</td>
                                        <td><input type="text" class="form-control" name="gender" value="<?php echo htmlspecialchars($memberData['sex']); ?>"readonly></td>
                                   </tr>
                                   <tr>
                                        <td scope="row">Agama</td>
                                        <td><input type="text" class="form-control" name="religion" value="<?php echo htmlspecialchars($memberData['religion']); ?>"readonly></td>
                                   </tr>
                                   <tr>
                                        <td scope="row">Bangsa</td>
                                        <td><input type="text" class="form-control" name="nation" value="<?php echo htmlspecialchars($memberData['nation']); ?>"readonly></td>
                                   </tr>
                                   <tr>
                                        <td scope="row">No. Anggota</td>
                                        <td><input type="text" class="form-control" name="noAnggota" value="<?php echo htmlspecialchars($memberData['employeeID']); ?>"readonly></td>
                                   </tr>
                                   <tr>
                                        <td scope="row">No. PF</td>
                                        <td><input type="text" class="form-control" name="noPF" value="<?php echo htmlspecialchars($memberData['no_pf']); ?>"readonly></td>
                                   </tr>
                                   <tr>
                                        <td scope="row">Jawatan & Gred</td>
                                        <td><input type="text" class="form-control" name="jawatanGred" value="<?php echo htmlspecialchars($memberData['position']); ?>"readonly></td>
                                   </tr>
                                   <tr>
                                        <td scope="row">Alamat Pejabat</td>
                                        <td><input type="text" class="form-control" name="alamatPejabat" value="<?php echo htmlspecialchars($memberData['officeAddress']); ?>"readonly></td>
                                   </tr>
                                   <tr>
                                        <td scope="row">Poskod Pejabat</td>
                                        <td><input type="text" class="form-control" name="officePostcode" value="<?php echo htmlspecialchars($memberData['officePostcode']); ?>"readonly></td>
                                   </tr> 
                                   <tr>
                                        <td scope="row">Negeri Pejabat</td>
                                        <td><input type="text" class="form-control" name="officeState" value="<?php echo htmlspecialchars($memberData['officeState']); ?>"readonly></td>
                                   </tr>
                                   <tr>
                                        <td scope="row">No. Tel Bimbit</td>
                                        <td><input type="text" class="form-control" name="noTelBimbit" value="<?php echo htmlspecialchars($memberData['phoneNumber']); ?>"readonly></td>
                                   </tr>
                                   <tr>
                                        <td scope="row">No. Tel Rumah</td>
                                        <td><input type="text" class="form-control" name="noTelRumah" value="<?php echo htmlspecialchars($memberData['phoneHome']); ?>"readonly></td>
                                   </tr>
                               </tbody>
                           </table>
                           <div class="form-group row mb-5">
                                <div class="col-sm-9 offset-sm-3">
                                    <!-- <button type="button" class="btn btn-primary" id="editButton" onclick="editProfile()">Edit</button> -->
                                    <button type="submit" class="btn btn-success" id="updateButton" style="display: none;">Simpan</button>
                                    <button type="button" class="btn btn-secondary" id="cancelButton" onclick="cancelEdit()" style="display: none;">Batal</button>
                                </div>
                           </div>
                       </form>
                   </div>
               </div>
           </div>
       </div>
   </div>
</div>






<script>
function editProfile() {
    console.log('Edit profile clicked');
    const inputs = document.querySelectorAll('#profileForm .form-control');
    inputs.forEach(input => {
        if (input.name !== 'employeeID') {
            input.removeAttribute('readonly');
            console.log('Made editable:', input.name);
        }
    });
    document.getElementById('editButton').style.display = 'none';
    document.getElementById('updateButton').style.display = 'inline-block';
    document.getElementById('cancelButton').style.display = 'inline-block';
}

function cancelEdit() {
    location.reload();
}

// Add console logging to debug form submission
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    console.log('Form submission attempted');
    
    // Log form data
    const formData = new FormData(this);
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    if (confirm('Adakah anda pasti untuk menyimpan perubahan ini?')) {
        console.log('Submission confirmed');
        this.submit();
    } else {
        console.log('Submission cancelled');
    }
});
</script>

<br><br><br><br><br>