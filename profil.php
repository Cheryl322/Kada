<?php

include "headermember.php";
include "dbconnect.php";
include "footer.php";
// Assuming you have a database connection and user session management
session_start();

// 检查用户是否登录
if (!isset($_SESSION['employeeID'])) {
    echo "<script>alert('请先登录！');
          window.location.href='login.php';</script>";
    exit();
}

// 确保数据库连接正确
if (!isset($pdo)) {
    die("数据库连接失败");
}

function getMemberData($employeeId) {
    global $pdo;
    try {
        $sql = "SELECT m.*, h.*, o.*
                FROM tb_member m
                LEFT JOIN tb_member_homeaddress h ON m.employeeId = h.employeeID
                LEFT JOIN tb_member_officeaddress o ON m.employeeId = o.employeeID
                WHERE m.employeeId = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$employeeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        return false;
    }
}

// 获取会员数据
$memberData = getMemberData($_SESSION['employeeID']);
if (!$memberData) {
    echo "<script>alert('无法获取会员数据！');
          window.location.href='login.php';</script>";
    exit();
}

?>
<!-- $result=mysqli_query($con, $sql);
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
    $userData['employeeId']=$row['employeeId'];
    $userData['no_pf']=$row['no_pf'];
    $userData['position']=$row['position'];
    $userData['officeAddress']=$row['officeAddress'];
    $userData['phoneNumber']=$row['phoneNumber'];
    $userData['phoneHome']=$row['phoneHome'];
} -->


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
                   <h3 class="text-left mt-3">Yuna Liew Mei Ling</h3>
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
               <form method="POST" action="update_profil.php" id="profileForm">
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Nama</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="nama" value="<?php echo htmlspecialchars($memberData['memberName']); ?>"readonly>
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Alamat Emel</label>
                       <div class="col-sm-9">
                           <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($memberData['email']); ?>"readonly>
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">MyKad/No. Passport</label>
                       <div class="col-sm-9">
                            <input type="text" class="form-control" name="ic_passport" value="<?php echo htmlspecialchars($memberData['ic']); ?>"readonly>
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Taraf perkahwinan</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="marital_status" value="<?php echo htmlspecialchars($memberData['maritalStatus']); ?>"readonly>
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Alamat Rumah</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="homeAddress" value="<?php echo htmlspecialchars($memberData['homeAddress']); ?>"readonly>
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Poskod</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="homePostcode" value="<?php echo htmlspecialchars($memberData['homePostcode']); ?>"readonly>
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Negeri</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="homeState" value="<?php echo htmlspecialchars($memberData['homeState']); ?>"readonly>
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Jantina</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="gender" value="<?php echo htmlspecialchars($memberData['sex']); ?>"readonly>
                       </div>
                   </div>
                    <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Agama</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="religion" value="<?php echo htmlspecialchars($memberData['religion']); ?>"readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Bangsa</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="bangsa" value="<?php echo htmlspecialchars($memberData['nation']); ?>"readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">No. Anggota</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="noAnggota" value="<?php echo htmlspecialchars($memberData['employeeId']); ?>"readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">No. PF</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="noPF" value="<?php echo htmlspecialchars($memberData['no_pf']); ?>"readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Jawatan & Gred</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="jawatanGred" value="<?php echo htmlspecialchars($memberData['position']); ?>"readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Alamat Pejabat</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="alamatPejabat" value="<?php echo htmlspecialchars($memberData['officeAddress']); ?>"readonly>
                       </div>
                   </div>  
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Poskod Pejabat</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="officePostcode" value="<?php echo htmlspecialchars($memberData['officePostcode']); ?>"readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">Negeri Pejabat</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="officeState" value="<?php echo htmlspecialchars($memberData['officeState']); ?>"readonly>
                       </div>
                   </div>           
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">No. Tel Bimbit</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="noTelBimbit" value="<?php echo htmlspecialchars($memberData['phoneNumber']); ?>"readonly>
                       </div>
                   </div>
                   <div class="form-group row mb-3">
                       <label class="col-sm-3 col-form-label">No. Tel Rumah</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" name="noTelRumah" value="<?php echo htmlspecialchars($memberData['phoneHome']); ?>"readonly>
                       </div>
                   </div>
               </form>
           </div>
       </div>
   </div>
</div>


<div class="form-group row mb-5">
    <div class="col-sm-9 offset-sm-3">
        <button type="button" class="btn btn-primary" id="editButton" onclick="editProfile()">Edit</button>
        <button type="submit" class="btn btn-success" id="updateButton" style="display: none;">Simpan</button>
        <button type="button" class="btn btn-secondary" id="cancelButton" onclick="cancelEdit()" style="display: none;">Batal</button>
    </div>
</div>

<script>
function editProfile() {
    const inputs = document.querySelectorAll('#profileForm .form-control');
    inputs.forEach(input => {
        input.removeAttribute('readonly');
    });
    document.getElementById('editButton').style.display = 'none';
    document.getElementById('updateButton').style.display = 'inline-block';
    document.getElementById('cancelButton').style.display = 'inline-block';
}

function cancelEdit() {
    location.reload();
}
</script>

