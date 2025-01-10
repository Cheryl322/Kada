<?php
session_start();
include "headermember.php";
include "footer.php";
include "dbconnect.php";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Store form data in session
    $_SESSION['personal_info'] = [
        'nama_penuh' => $_POST['nama_penuh'] ?? '',
        'alamat_emel' => $_POST['alamat_emel'] ?? '',
        'ic' => $_POST['ic'] ?? '',
        'maritalStatus' => $_POST['maritalStatus'] ?? '',
        'address' => $_POST['address'] ?? '',
        'poscode' => $_POST['poscode'] ?? '',
        'state' => $_POST['state'] ?? '',
        'sex' => $_POST['sex'] ?? '',
        'religion' => $_POST['religion'] ?? '',
        'nation' => $_POST['nation'] ?? '',
        'no_anggota' => $_POST['no_anggota'] ?? '',
        'no_pf' => $_POST['no_pf'] ?? '',
        'position' => $_POST['position'] ?? '',
        'officeAddress' => $_POST['officeAddress'] ?? '',
        'phoneNumber' => $_POST['phoneNumber'] ?? '',
        'phoneHome' => $_POST['phoneHome'] ?? '',
        'monthlySalary' => $_POST['monthlySalary'] ?? ''
    ];

    // Redirect to maklumat_tambahan.php
    header("Location: maklumat_tambahan.php");
    exit();
}

// Restore form data if coming back from maklumat_tambahan.php
$formData = isset($_SESSION['personal_info']) ? $_SESSION['personal_info'] : [];
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
            <form method="POST" action="" onsubmit="return saveFormData();">
                <div class="mb-3">
                    <label class="form-label">Nama Penuh <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_penuh" required 
                           value="<?php echo isset($formData['nama_penuh']) ? htmlspecialchars($formData['nama_penuh']) : ''; ?>">
                    <small class="text-muted">Sila pastikan NAMA PENUH seperti dalam kad pengenalan.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat Emel <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="alamat_emel" required
                           value="<?php echo isset($formData['alamat_emel']) ? htmlspecialchars($formData['alamat_emel']) : ''; ?>">
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

                <div class="text-end">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Seterusnya <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function saveFormData() {
    // Store all form data in session storage before submitting
    const formData = {
        nama_penuh: $('input[name="nama_penuh"]').val(),
        alamat_emel: $('input[name="alamat_emel"]').val(),
        ic: $('input[name="ic"]').val(),
        maritalStatus: $('select[name="maritalStatus"]').val(),
        address: $('textarea[name="address"]').val(),
        poscode: $('input[name="poscode"]').val(),
        state: $('select[name="state"]').val(),
        sex: $('input[name="sex"]:checked').val(),
        religion: $('select[name="religion"]').val(),
        nation: $('select[name="nation"]').val(),
        no_anggota: $('input[name="no_anggota"]').val(),
        no_pf: $('input[name="no_pf"]').val(),
        position: $('input[name="position"]').val(),
        officeAddress: $('textarea[name="officeAddress"]').val(),
        phoneNumber: $('input[name="phoneNumber"]').val(),
        phoneHome: $('input[name="phoneHome"]').val(),
        monthlySalary: $('input[name="monthlySalary"]').val()
    };

    // Store in session storage
    sessionStorage.setItem('formData', JSON.stringify(formData));
    return true;
}

// When page loads, check for saved data
$(document).ready(function() {
    const savedData = sessionStorage.getItem('formData');
    if (savedData) {
        const formData = JSON.parse(savedData);
        
        // Fill in all form fields with saved data
        $('input[name="nama_penuh"]').val(formData.nama_penuh);
        $('input[name="alamat_emel"]').val(formData.alamat_emel);
        $('input[name="ic"]').val(formData.ic);
        $('select[name="maritalStatus"]').val(formData.maritalStatus);
        $('textarea[name="address"]').val(formData.address);
        $('input[name="poscode"]').val(formData.poscode);
        $('select[name="state"]').val(formData.state);
        $(`input[name="sex"][value="${formData.sex}"]`).prop('checked', true);
        $('select[name="religion"]').val(formData.religion);
        $('select[name="nation"]').val(formData.nation);
        $('input[name="no_anggota"]').val(formData.no_anggota);
        $('input[name="no_pf"]').val(formData.no_pf);
        $('input[name="position"]').val(formData.position);
        $('textarea[name="officeAddress"]').val(formData.officeAddress);
        $('input[name="phoneNumber"]').val(formData.phoneNumber);
        $('input[name="phoneHome"]').val(formData.phoneHome);
        $('input[name="monthlySalary"]').val(formData.monthlySalary);
    }
});
</script>

header("Location: success.php");
exit();


<?php include "footer.php"; ?>