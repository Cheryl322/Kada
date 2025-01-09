<?php
include "headermember.php";
include "footer.php";
include "dbconnect.php";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_penuh = mysqli_real_escape_string($conn, $_POST['nama_penuh']);
    $alamat_emel = mysqli_real_escape_string($conn, $_POST['alamat_emel']);
    $mykad_passport = mysqli_real_escape_string($conn, $_POST['mykad_passport']);
    $taraf_perkahwinan = mysqli_real_escape_string($conn, $_POST['taraf_perkahwinan']);
    $alamat_rumah = mysqli_real_escape_string($conn, $_POST['alamat_rumah']);
    $poskod = mysqli_real_escape_string($conn, $_POST['poskod']);
    $negeri = mysqli_real_escape_string($conn, $_POST['negeri']);
    $jantina = isset($_POST['jantina']) ? mysqli_real_escape_string($conn, $_POST['jantina']) : '';
    $agama = isset($_POST['agama']) ? mysqli_real_escape_string($conn, $_POST['agama']) : '';
    $bangsa = isset($_POST['bangsa']) ? mysqli_real_escape_string($conn, $_POST['bangsa']) : '';
    $no_anggota = isset($_POST['no_anggota']) ? mysqli_real_escape_string($conn, $_POST['no_anggota']) : '';
    $no_pf = isset($_POST['no_pf']) ? mysqli_real_escape_string($conn, $_POST['no_pf']) : '';
    $jawatan_gred = isset($_POST['jawatan_gred']) ? mysqli_real_escape_string($conn, $_POST['jawatan_gred']) : '';
    $alamat_pejabat = isset($_POST['alamat_pejabat']) ? mysqli_real_escape_string($conn, $_POST['alamat_pejabat']) : '';
    $no_tel_bimbit = isset($_POST['no_tel_bimbit']) ? mysqli_real_escape_string($conn, $_POST['no_tel_bimbit']) : '';
    $no_tel_rumah = isset($_POST['no_tel_rumah']) ? mysqli_real_escape_string($conn, $_POST['no_tel_rumah']) : '';
    $gaji_bulanan = isset($_POST['gaji_bulanan']) ? mysqli_real_escape_string($conn, $_POST['gaji_bulanan']) : '';

    // Validation
    $errors = [];
    
    if (empty($nama_penuh)) {
        $errors[] = "Sila masukkan nama penuh";
    }
    
    if (empty($alamat_emel) || !filter_var($alamat_emel, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Alamat emel tidak sah";
    }
    
    if (empty($mykad_passport)) {
        $errors[] = "Sila masukkan MyKad/No. Passport";
    }

    if (empty($errors)) {
        $sql = "INSERT INTO members (nama_penuh, alamat_emel, mykad_passport, taraf_perkahwinan, 
                alamat_rumah, poskod, negeri, jantina, agama, bangsa, no_anggota, no_pf,
                jawatan_gred, alamat_pejabat, no_tel_bimbit, no_tel_rumah, gaji_bulanan) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssssssssssssss", 
            $nama_penuh, $alamat_emel, $mykad_passport, $taraf_perkahwinan, 
            $alamat_rumah, $poskod, $negeri, $jantina, $agama, $bangsa, 
            $no_anggota, $no_pf, $jawatan_gred, $alamat_pejabat, 
            $no_tel_bimbit, $no_tel_rumah, $gaji_bulanan);

        if (mysqli_stmt_execute($stmt)) {
            echo "<div class='alert alert-success'>Pendaftaran berjaya!</div>";
        } else {
            echo "<div class='alert alert-danger'>Ralat: " . mysqli_error($conn) . "</div>";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        foreach ($errors as $error) {
            echo "<div class='alert alert-danger'>$error</div>";
        }
    }
}
?>

<style>
/* Container sizing */
.container {
    width: 90%;
    max-width: 1200px !important; /* Adjusted to match maklumat_tambahan.php */
    margin: 0 auto;
    padding: 0 15px;
}

/* Progress bar */
.progress-container {
    width: 90%;
    margin: 20px auto;
    font-weight: 500;
}

/* Section header */
.section-header {
    background-color: #F8B4B4 !important; 
    padding: 10px 15px;
    border-radius: 5px;
    margin: 20px 0;
    font-weight: 500;
    color: black !important;
    font-size: 18px !important;
    text-transform: uppercase;
}

/* Specific styling for the text */
.section-header, 
.section-header * {
    color: black !important;
}

/* Table styling */
.table {
    width: 100%;
    margin-bottom: 20px;
    background-color: white;
    border: 1px solid #dee2e6;
}

.table td {
    padding: 15px 20px; /* Adjusted padding */
    border: 1px solid #dee2e6;
    vertical-align: middle;
}

/* Form controls */
.form-control, .form-select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
}

/* Form labels */
.form-label {
    font-weight: 500;
    color: black !important;
    margin-bottom: 8px;
}

.form-text {
    color: black !important;
    font-size: 0.875rem;
    margin-top: 5px;
}

/* Navigation/Header bar styling */
.navbar {
    background-color: #95D5B2 !important; /* Light green color */
}

/* Logo and navigation items */
.navbar-brand,
.nav-link {
    color: white !important;
}

/* Profile icon */
.profile-icon {
    color: white;
}

/* Active/hover states */
.nav-link:hover,
.nav-link.active {
    color: #e9ecef !important;
}
</style>

<div class="container mt-4">
    <!-- Progress Bar -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-8">
            <div class="progress" style="height: 30px;">
                <div class="progress-bar" role="progressbar" style="width: 100%">
                    Langkah 1/2: Permohonan Menjadi Anggota
                </div>
            </div>
        </div>
    </div>

    <!-- Section Header -->
    <div class="section-header" style="color: black !important;">
        MAKLUMAT PERIBADI
    </div>

    <!-- Form Content -->
    <div class="card">
        <div class="card-body">
            <form method="POST" action="maklumat_tambahan.php">
                <div class="mb-3">
                    <label class="form-label">Nama Penuh <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="memberName" required>
                    <small class="text-muted">Sila pastikan NAMA PENUH seperti dalam kad pengenalan.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat Emel <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email" required>
                    <small class="text-muted">Sila pastikan ALAMAT EMEL adalah sah dan masih aktif.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">MyKad/No. Passport <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="ic" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Taraf Perkahwinan <span class="text-danger">*</span></label>
                    <select class="form-select" name="maritalStatus" required>
                        <option value="">Sila Pilih</option>
                        <option value="Bujang">Bujang</option>
                        <option value="Berkahwin">Berkahwin</option>
                        <option value="Duda/Janda">Duda/Janda</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat Rumah <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="address" rows="3" required></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Poskod <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="poscode" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Negeri <span class="text-danger">*</span></label>
                        <select class="form-select" name="state" required>
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

                <div class="mb-3">
                    <label class="form-label">Jantina <span class="text-danger">*</span></label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="sex" id="jantina_lelaki" value="Lelaki" required>
                            <label class="form-check-label" for="jantina_lelaki">Lelaki</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="sex" id="jantina_perempuan" value="Perempuan" required>
                            <label class="form-check-label" for="jantina_perempuan">Perempuan</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
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

                <div class="mb-3">
                    <label class="form-label">Bangsa <span class="text-danger">*</span></label>
                    <select class="form-select" name="nation" required>
                        <option value="">Sila Pilih</option>
                        <option value="Melayu">Melayu</option>
                        <option value="Cina">Cina</option>
                        <option value="India">India</option>
                        <option value="Lain-lain">Lain-lain</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">No. Anggota <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="no_anggota" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">No. PF <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="no_pf" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jawatan & Gred <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="position" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat Pejabat (Tempat Bertugas) <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="officeAddress" rows="3" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">No. Tel Bimbit <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" name="phoneNumber" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">No. Tel Rumah <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" name="phoneHome" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Gaji Bulanan (RM) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="monthlySalary" required>
                </div>

                <div class="text-end mt-3 mb-5">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Seterusnya <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    // Phone number formatting
    $('input[name="phoneNumber"]').mask('000-000-0000');
    $('input[name="phoneHome"]').mask('000-000-0000');
    
    // MyKad formatting
    $('input[name="ic"]').mask('000000-00-0000');
    
    // Currency formatting
    $('input[name="monthlySalary"]').mask('000,000,000.00', {reverse: true});
    
    // Form validation
    $('form').on('submit', function(e){
        let isValid = true;
        
        // Reset previous errors
        $('.is-invalid').removeClass('is-invalid');
        
        // Validate email
        const email = $('input[name="email"]').val();
        if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            $('input[name="email"]').addClass('is-invalid');
            isValid = false;
        }
        
        // Validate MyKad
        const mykad = $('input[name="ic"]').val();
        if (!mykad.match(/^\d{6}-\d{2}-\d{4}$/)) {
            $('input[name="ic"]').addClass('is-invalid');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
});
</script>

header("Location: success.php");
exit();


<?php include "footer.php"; ?>