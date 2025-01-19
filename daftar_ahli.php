<?php
session_start();
ob_start();

// Check if user is logged in
if (!isset($_SESSION['employeeID'])) {
    $_SESSION['error'] = "Sila log masuk terlebih dahulu";
    header("Location: login.php");
    exit();
}

include "headermember.php";

// Check if employee is already registered
require_once "dbconnect.php";
$employeeID = $_SESSION['employeeID'];
$checkSql = "SELECT employeeID FROM tb_member WHERE employeeID = ?";
$stmt = mysqli_prepare($conn, $checkSql);
mysqli_stmt_bind_param($stmt, 'i', $employeeID);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    // Employee already registered
    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Perhatian!</strong> Anda telah mendaftar sebagai ahli.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    // You might want to redirect them somewhere else
    // header("Location: dashboard.php");
    // exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debug: Print form data
    error_log("Form Data: " . print_r($_POST, true));
    
    try {
        mysqli_begin_transaction($conn);
        
        // Get employeeID from the form
        $employeeID = $_POST['no_anggota'];
        
        // Debug: Print employeeID
        error_log("EmployeeID: " . $employeeID);

        // First insert member data
        $memberQuery = "INSERT INTO tb_member (
            employeeID, 
            memberName, 
            email, 
            ic, 
            maritalStatus, 
            sex, 
            religion, 
            nation, 
            no_pf, 
            position, 
            phoneNumber,
            phoneHome,
            monthlySalary
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $memberStmt = mysqli_prepare($conn, $memberQuery);
        mysqli_stmt_bind_param($memberStmt, "isssssssssssd", 
            $_POST['no_anggota'],      // employeeID
            $_POST['nama_penuh'],      // memberName
            $_POST['alamat_emel'],     // email
            $_POST['ic'],              // ic
            $_POST['maritalStatus'],   // maritalStatus
            $_POST['sex'],             // sex
            $_POST['religion'],        // religion
            $_POST['nation'],          // nation
            $_POST['no_pf'],          // no_pf
            $_POST['position'],        // position
            $_POST['phoneNumber'],     // phoneNumber
            $_POST['phoneHome'],       // phoneHome
            $_POST['monthlySalary']    // monthlySalary
        );
        
        if (mysqli_stmt_execute($memberStmt)) {
            // Debug: Member inserted successfully
            error_log("Member inserted successfully");
            
            // Insert home address
            $homeQuery = "INSERT INTO tb_member_homeaddress (employeeID, homeAddress, homePostcode, homeState) 
                         VALUES (?, ?, ?, ?)";
            $homeStmt = mysqli_prepare($conn, $homeQuery);
            
            // Debug: Print home address data
            error_log("Home Address Data: " . print_r([
                'employeeID' => $employeeID,
                'homeAddress' => $_POST['homeAddress'],
                'homePostcode' => $_POST['homePostcode'],
                'homeState' => $_POST['homeState']
            ], true));
            
            mysqli_stmt_bind_param($homeStmt, "isss", 
                $employeeID,
                $_POST['homeAddress'],
                $_POST['homePostcode'],
                $_POST['homeState']
            );
            
            if (!mysqli_stmt_execute($homeStmt)) {
                throw new Exception("Error saving home address: " . mysqli_error($conn));
            }
            
            // Debug: Home address inserted successfully
            error_log("Home address inserted successfully");

            // Insert office address
            $officeQuery = "INSERT INTO tb_member_officeaddress (employeeID, officeAddress, officePostcode, officeState) 
                           VALUES (?, ?, ?, ?)";
            $officeStmt = mysqli_prepare($conn, $officeQuery);
            
            // Debug: Print office address data
            error_log("Office Address Data: " . print_r([
                'employeeID' => $employeeID,
                'officeAddress' => $_POST['officeAddress'],
                'officePostcode' => $_POST['officePostcode'],
                'officeState' => $_POST['officeState']
            ], true));
            
            mysqli_stmt_bind_param($officeStmt, "isss", 
                $employeeID,
                $_POST['officeAddress'],
                $_POST['officePostcode'],
                $_POST['officeState']
            );
            
            if (!mysqli_stmt_execute($officeStmt)) {
                throw new Exception("Error saving office address: " . mysqli_error($conn));
            }
            
            // Debug: Office address inserted successfully
            error_log("Office address inserted successfully");

            // Insert initial registration status
            $statusQuery = "INSERT INTO tb_memberregistration_memberapplicationdetails 
                           (memberRegistrationID, regisDate, regisStatus) 
                           VALUES (?, NOW(), 'Belum Selesai')";
            
            $statusStmt = mysqli_prepare($conn, $statusQuery);
            mysqli_stmt_bind_param($statusStmt, "i", $employeeID);
            
            if (!mysqli_stmt_execute($statusStmt)) {
                throw new Exception("Error setting initial registration status: " . mysqli_error($conn));
            }

            mysqli_commit($conn);
            $_SESSION['employeeID'] = $employeeID;
            
            // Debug: Transaction committed
            error_log("Transaction committed successfully");
            
            header("Location: maklumat_tambahan.php");
            exit();
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error occurred: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header("Location: daftar_ahli.php");
        exit();
    }
}

// Show success/error messages if they exist
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            ' . $_SESSION['success'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            ' . $_SESSION['error'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['error']);
}
?>

<style>
.container {
    width: 90%;
    max-width: 1200px !important;
    margin: 0 auto;
    padding: 0 15px;
}

.progress-container {
    width: 90%;
    margin: 20px auto;
}

.progress {
    height: 30px !important;
}

.progress-bar {
    background-color: #95D5B2 !important;
    font-weight: 500;
}

.section-header {
    background-color: #F8B4B4;
    padding: 15px;
    border-radius: 5px;
    margin: 20px 0;
    font-weight: 500;
    color: black !important;
    font-size: 18px;
    text-transform: uppercase;
}

.section-content {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-label {
    font-weight: 500;
    color: black;
    margin-bottom: 8px;
}

.form-control, .form-select {
    padding: 10px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus, .form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.input-group-text {
    background-color: #e9ecef;
    border-right: none;
}

.btn-primary {
    background-color: #95D5B2;
    border-color: #95D5B2;
    padding: 10px 20px;
    font-weight: 500;
}

.btn-primary:hover {
    background-color: #74c69d;
    border-color: #74c69d;
}

.text-danger {
    color: #dc3545 !important;
}

.text-muted {
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .col-md-6 {
        margin-bottom: 1rem;
    }
}

.alert {
    margin: 20px;
    padding: 15px;
    border-radius: 4px;
    position: relative;
}

.alert-dismissible .btn-close {
    position: absolute;
    top: 0;
    right: 0;
    padding: 1.25rem;
}
</style>

<div class="container mt-4">
    <!-- Progress Bar -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-8">
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: 50%">
                    Langkah 1/2: Permohonan Menjadi Anggota
                </div>
            </div>
        </div>
    </div>

    <!-- Form Content -->
    <form method="POST" action="" class="needs-validation" novalidate>
        <!-- Personal Information Section -->
        <div class="section-header">
            MAKLUMAT PERIBADI
        </div>
        
        <div class="section-content">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Penuh <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_penuh" required>
                    <small class="text-muted">Sila pastikan NAMA PENUH seperti dalam kad pengenalan.</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Alamat Emel <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="alamat_emel" required>
                    <small class="text-muted">Sila pastikan ALAMAT EMEL adalah sah dan masih aktif.</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">MyKad/No. Passport <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="ic" required 
                           pattern="[0-9]{12}" maxlength="12"
                           placeholder="Contoh: 890126012345">
                    <small class="text-muted">Masukkan 12 digit nombor kad pengenalan tanpa (-)</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Taraf Perkahwinan <span class="text-danger">*</span></label>
                    <select class="form-select" name="maritalStatus" required>
                        <option value="">Sila Pilih</option>
                        <option value="Bujang">Bujang</option>
                        <option value="Berkahwin">Berkahwin</option>
                        <option value="Duda/Janda">Duda/Janda</option>
                    </select>
                </div>
            </div>

            <!-- Home Address Section -->
            <div class="mb-3">
                <label class="form-label">Alamat Rumah <span class="text-danger">*</span></label>
                <textarea class="form-control" name="homeAddress" rows="3" required></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Poskod <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="homePostcode" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Negeri <span class="text-danger">*</span></label>
                    <select class="form-select" name="homeState" required>
                        <option value="">Sila Pilih</option>
                        <option value="Johor">Johor</option>
                        <option value="Kedah">Kedah</option>
                        <option value="Kelantan">Kelantan</option>
                        <option value="Melaka">Melaka</option>
                        <option value="Negeri Sembilan">Negeri Sembilan</option>
                        <option value="Pahang">Pahang</option>
                        <option value="Perak">Perak</option>
                        <option value="Perlis">Perlis</option>
                        <option value="Pulau Pinang">Pulau Pinang</option>
                        <option value="Sabah">Sabah</option>
                        <option value="Sarawak">Sarawak</option>
                        <option value="Selangor">Selangor</option>
                        <option value="Terengganu">Terengganu</option>
                        <option value="WP Kuala Lumpur">WP Kuala Lumpur</option>
                        <option value="WP Labuan">WP Labuan</option>
                        <option value="WP Putrajaya">WP Putrajaya</option>
                    </select>
                </div>
            </div>

            <!-- Other Personal Details -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jantina <span class="text-danger">*</span></label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="sex" value="Lelaki" required>
                            <label class="form-check-label">Lelaki</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="sex" value="Perempuan" required>
                            <label class="form-check-label">Perempuan</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Agama <span class="text-danger">*</span></label>
                    <select class="form-select" name="religion" required>
                        <option value="">Sila Pilih</option>
                        <option value="Islam">Islam</option>
                        <option value="Buddha">Buddha</option>
                        <option value="Hindu">Hindu</option>
                        <option value="Kristian">Kristian</option>
                        <option value="Lain-lain">Lain-lain</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bangsa <span class="text-danger">*</span></label>
                    <select class="form-select" name="nation" required>
                        <option value="">Sila Pilih</option>
                        <option value="Melayu">Melayu</option>
                        <option value="Cina">Cina</option>
                        <option value="India">India</option>
                        <option value="Lain-lain">Lain-lain</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Employment Information Section -->
        <div class="section-header">
            MAKLUMAT PEKERJAAN
        </div>

        <div class="section-content">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. Anggota <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="no_anggota" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">No. PF <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="no_pf" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Jawatan & Gred <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="position" required>
            </div>

            <!-- Office Address Section -->
            <div class="mb-3">
                <label class="form-label">Alamat Pejabat <span class="text-danger">*</span></label>
                <textarea class="form-control" name="officeAddress" rows="3" required></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Poskod <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="officePostcode" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Negeri <span class="text-danger">*</span></label>
                    <select class="form-select" name="officeState" required>
                        <option value="">Sila Pilih</option>
                        <option value="Johor">Johor</option>
                        <option value="Kedah">Kedah</option>
                        <option value="Kelantan">Kelantan</option>
                        <option value="Melaka">Melaka</option>
                        <option value="Negeri Sembilan">Negeri Sembilan</option>
                        <option value="Pahang">Pahang</option>
                        <option value="Perak">Perak</option>
                        <option value="Perlis">Perlis</option>
                        <option value="Pulau Pinang">Pulau Pinang</option>
                        <option value="Sabah">Sabah</option>
                        <option value="Sarawak">Sarawak</option>
                        <option value="Selangor">Selangor</option>
                        <option value="Terengganu">Terengganu</option>
                        <option value="WP Kuala Lumpur">WP Kuala Lumpur</option>
                        <option value="WP Labuan">WP Labuan</option>
                        <option value="WP Putrajaya">WP Putrajaya</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. Tel Bimbit <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" name="phoneNumber" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">No. Tel Rumah <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" name="phoneHome" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Gaji Bulanan (RM) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="monthlySalary" required step="0.01">
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary btn-lg">
                Seterusnya <i class="fas fa-arrow-right"></i>
            </button>
            <br><br><br>
        </div>
    </form>
</div>

<script>
// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    // Validate IC number
    const icInput = document.querySelector('input[name="ic"]');
    icInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12);
    });

    // Validate postcodes
    const postcodeInputs = document.querySelectorAll('input[pattern="[0-9]{5}"]');
    postcodeInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 5);
        });
    });

    // Validate phone numbers
    const phoneInputs = document.querySelectorAll('input[pattern="[0-9]{9,11}"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });

    // Form submission validation
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>