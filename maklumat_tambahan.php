<?php
session_start();
include "headermember.php";
include "dbconnect.php";

// Check if form was submitted from daftar_ahli.php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['personal_info'] = $_POST;
}
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Add jQuery before Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    
    <!-- Then add Bootstrap and other resources -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<style>
.card-header {
    background-color: #F8B4B4 !important; 
    color: black !important;
}

.progress-bar {
    background-color: #95D5B2 !important;  
}

.btn-success {
    background-color: #4CAF50;
    border-color: #4CAF50;
}

.btn-success:hover {
    background-color: #45a049;
    border-color: #45a049;
}

.delete-row {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Section header styling */
.section-header {
    background-color: #95D5B2 !important; /* Changed to theme green color */
    padding: 10px 15px;
    border-radius: 5px;
    margin: 20px 0;
    font-weight: 500;
    color: black !important;
    font-size: 18px !important;
    text-transform: uppercase;
}

/* Header styling */
.navbar {
    background-color: #95D5B2 !important;
}

/* Logo and navigation items */
.navbar-brand,
.nav-link {
    color: white !important;
}

/* Profile icon */
.profile-icon {
    color: white !important;
}

/* Active/hover states */
.nav-link:hover,
.nav-link.active {
    color: #e9ecef !important;
}

/* Keep the existing footer style */
.footer {
    background-color: #95D5B2;
}

/* Progress Bar */
.progress {
    height: 30px;
}

.progress-bar {
    background-color: #8BCEB3 !important; /* Adjusted to match the image */
    width: 100%;
}

/* Text inside progress bar */
.progress-bar {
    color: white;
    font-weight: 500;
}
</style>

<div class="container mt-4">
    <!-- Progress Bar -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-8">
            <div class="progress" style="height: 30px;">
                <div class="progress-bar" role="progressbar" style="width: 100%">
                    Langkah 2/2: Maklumat Tambahan
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="process_registration.php">
        <!-- Family Information Table -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">MAKLUMAT KELUARGA DAN PEWARIS</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="familyTable">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 5%">BIL</th>
                            <th style="width: 20%">HUBUNGAN</th>
                            <th style="width: 45%">NAMA</th>
                            <th style="width: 25%">NO. K/P@ NO. SRT BERANAK</th>
                            <th style="width: 5%">TINDAKAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">1</td>
                            <td>
                                <select name="hubungan[]" class="form-select" required>
                                    <option value="">Pilih Hubungan</option>
                                    <option value="Isteri">Isteri</option>
                                    <option value="Suami">Suami</option>
                                    <option value="Anak">Anak</option>
                                    <option value="Ibu">Ibu</option>
                                    <option value="Bapa">Bapa</option>
                                    <option value="Adik-beradik">Adik-beradik</option>
                                </select>
                            </td>
                            <td><input type="text" name="nama_waris[]" class="form-control" required></td>
                            <td><input type="text" name="no_kp_waris[]" class="form-control" placeholder="______-__-____"></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm delete-row">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="text-end mb-3">
                    <button type="button" class="btn btn-success" id="addRow">
                        <i class="fas fa-plus"></i> Tambah Ahli Keluarga
                    </button>
                </div>
                <div class="text-muted mt-2">
                    <small>* Sila isikan maklumat keluarga terdekat sebagai pewaris</small>
                </div>
            </div>
        </div>

        <!-- Fees Table -->
        <div class="card mt-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">YURAN DAN SUMBANGAN</h5>
            </div>
            <div class="card-body">
                <p>Jika diterima sebagai anggota, saya bersetuju membayar yuran dan sumbangan bulanan seperti di bawah:</p>
                <table class="table table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 10%">BIL</th>
                            <th style="width: 70%">PERKARA</th>
                            <th style="width: 20%">RM</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>FEE MASUK</td>
                            <td><input type="number" name="fee_masuk" class="form-control" min="0" step="1"></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>MODAL SYER *</td>
                            <td><input type="number" name="modal_syer" class="form-control" min="300" step="1"></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>MODAL YURAN</td>
                            <td><input type="number" name="modal_yuran" class="form-control" min="0" step="1"></td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>WANG DEPOSIT ANGGOTA</td>
                            <td><input type="number" name="wang_deposit" class="form-control" min="0" step="1"></td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>SUMBANGAN TABUNG KEBAJIKAN (AL-ABRAR)</td>
                            <td><input type="number" name="sumbangan_tabung" class="form-control" min="0" step="1"></td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>SIMPANAN TETAP</td>
                            <td><input type="number" name="simpanan_tetap" class="form-control" min="0" step="1"></td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td>LAIN-LAIN</td>
                            <td><input type="number" name="lain_lain" class="form-control" min="0" step="1"></td>
                        </tr>
                    </tbody>
                </table>
                <div class="text-muted mt-2">
                    <small>*Minima Modal Syer adalah sebanyak RM300.00 dan tidak melebihi 1/5 daripada Modal Syer Koperasi dan hendaklah dijelaskan dalam tempoh 6 bulan dari tarikh kelulusan menjadi anggota.</small>
                </div>
            </div>
        </div>

        <!-- Submit Section -->
        <div class="card mt-4 mb-5">
            <div class="card-body">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="agree" name="agree" required>
                    <label class="form-check-label" for="agree">
                        Saya mengaku bahawa segala maklumat yang diberikan adalah benar dan tepat.
                    </label>
                </div>
                <div class="text-end">
                    <a href="daftar_ahli.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        Hantar Permohonan <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize mask for existing inputs
    $('input[name="no_kp_waris[]"]').mask('000000-00-0000', {
        placeholder: "______-__-____"
    });

    // Add new row
    $('#addRow').on('click', function() {
        var rowCount = $('#familyTable tbody tr').length + 1;
        var newRow = `
            <tr>
                <td class="text-center">${rowCount}</td>
                <td>
                    <select name="hubungan[]" class="form-select" required>
                        <option value="">Pilih Hubungan</option>
                        <option value="Isteri">Isteri</option>
                        <option value="Suami">Suami</option>
                        <option value="Anak">Anak</option>
                        <option value="Ibu">Ibu</option>
                        <option value="Bapa">Bapa</option>
                        <option value="Adik-beradik">Adik-beradik</option>
                    </select>
                </td>
                <td><input type="text" name="nama_waris[]" class="form-control" required></td>
                <td><input type="text" name="no_kp_waris[]" class="form-control" placeholder="______-__-____"></td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm delete-row">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#familyTable tbody').append(newRow);
        
        // Apply mask to new input
        $('input[name="no_kp_waris[]"]:last').mask('000000-00-0000', {
            placeholder: "______-__-____"
        });
    });

    // Delete row
    $(document).on('click', '.delete-row', function() {
        if ($('#familyTable tbody tr').length > 1) {
            $(this).closest('tr').remove();
            // Reorder numbers
            $('#familyTable tbody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }
    });
});
</script>

</body>
</html>
<?php include "footer.php"; ?> 