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

<!-- <!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Menjadi Anggota</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
</head> -->


<body>
    <!-- <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">KADA</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Pendaftaran</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Senarai Anggota</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav> -->
    <div class="container mt-4">
        <h2 class="mb-4">Permohonan Menjadi Anggota</h2>
        
        <div class="card">
            <div class="card-header bg-secondary text-white">
                MAKLUMAT PERIBADI
            </div>
            <div class="card-body">
                <form method="POST" action="maklumat_tambahan.php">
                    <div class="mb-3">
                        <label class="form-label">Nama Penuh <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_penuh" required>
                        <small class="text-muted">Sila pastikan NAMA PENUH seperti dalam kad pengenalan.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat Emel <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="alamat_emel" required>
                        <small class="text-muted">Sila pastikan ALAMAT EMEL adalah sah dan masih aktif.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">MyKad/No. Passport <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="mykad_passport" 
                               placeholder="eg. 760910015001 (tanpa sengkang - )" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Taraf Perkahwinan <span class="text-danger">*</span></label>
                        <select class="form-select" name="taraf_perkahwinan" required>
                            <option value="">Sila Pilih</option>
                            <option value="Bujang">Bujang</option>
                            <option value="Berkahwin">Berkahwin</option>
                            <option value="Duda/Janda">Duda/Janda</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat Rumah <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="alamat_rumah" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Poskod <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="poskod" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Negeri <span class="text-danger">*</span></label>
                            <select class="form-select" name="negeri" required>
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
                                <input class="form-check-input" type="radio" name="jantina" id="jantina_lelaki" value="Lelaki" required>
                                <label class="form-check-label" for="jantina_lelaki">Lelaki</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="jantina" id="jantina_perempuan" value="Perempuan" required>
                                <label class="form-check-label" for="jantina_perempuan">Perempuan</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Agama <span class="text-danger">*</span></label>
                        <select class="form-select" name="agama" required>
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
                        <select class="form-select" name="bangsa" required>
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
                        <input type="text" class="form-control" name="jawatan_gred" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat Pejabat (Tempat Bertugas) <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="alamat_pejabat" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">No. Tel Bimbit <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="no_tel_bimbit" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">No. Tel Rumah <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="no_tel_rumah" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Gaji Bulanan (RM) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="gaji_bulanan" required>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function(){
        // Phone number formatting
        $('input[name="no_tel_bimbit"]').mask('000-000-0000');
        $('input[name="no_tel_rumah"]').mask('000-000-0000');
        
        // MyKad formatting
        $('input[name="mykad_passport"]').mask('000000-00-0000');
        
        // Currency formatting
        $('input[name="gaji_bulanan"]').mask('000,000,000.00', {reverse: true});
        
        // Form validation
        $('form').on('submit', function(e){
            let isValid = true;
            
            // Reset previous errors
            $('.is-invalid').removeClass('is-invalid');
            
            // Validate email
            const email = $('input[name="alamat_emel"]').val();
            if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                $('input[name="alamat_emel"]').addClass('is-invalid');
                isValid = false;
            }
            
            // Validate MyKad
            const mykad = $('input[name="mykad_passport"]').val();
            if (!mykad.match(/^\d{6}-\d{2}-\d{4}$/)) {
                $('input[name="mykad_passport"]').addClass('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
    </script>
    <footer class="bg-light mt-5 py-3 mb-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Hubungi Kami</h5>
                    <p>
                        Tel: 03-XXXXXXXX<br>
                        Email: info@kada.com.my<br>
                        Alamat: Jalan XXXX, 50000 Kuala Lumpur
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <p>&copy; 2024 KADA. Hak Cipta Terpelihara.</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html> 