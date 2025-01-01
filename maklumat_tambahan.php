<?php
session_start();
include "headermember.php";
include "dbconnect.php";

// Store personal info from previous page
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['personal_info'] = $_POST;
}
?>

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
            <div class="card-header bg-secondary text-white">
                MAKLUMAT KELUARGA DAN PEWARIS
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 5%">BIL</th>
                            <th style="width: 20%">HUBUNGAN</th>
                            <th style="width: 45%">NAMA</th>
                            <th style="width: 30%">NO. K/P@ NO. SRT BERANAK</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for($i = 1; $i <= 5; $i++): ?>
                        <tr>
                            <td class="text-center"><?php echo $i; ?></td>
                            <td>
                                <select class="form-select" name="hubungan[]">
                                    <option value="">Pilih Hubungan</option>
                                    <option value="Isteri">Isteri</option>
                                    <option value="Suami">Suami</option>
                                    <option value="Anak">Anak</option>
                                    <option value="Ibu">Ibu</option>
                                    <option value="Bapa">Bapa</option>
                                    <option value="Adik-beradik">Adik-beradik</option>
                                </select>
                            </td>
                            <td><input type="text" class="form-control" name="nama_waris[]"></td>
                            <td><input type="text" class="form-control" name="no_kp_waris[]"></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Fees Table -->
        <div class="card mt-4">
            <div class="card-header bg-secondary text-white">
                YURAN DAN SAMBUNGAN
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
                            <td><input type="number" class="form-control" name="fee_masuk" min="0" step="1"></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>MODAL SYER *</td>
                            <td><input type="number" class="form-control" name="modal_syer" min="300" step="1"></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>MODAL YURAN</td>
                            <td><input type="number" class="form-control" name="modal_yuran" min="0" step="1"></td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>WANG DEPOSIT ANGGOTA</td>
                            <td><input type="number" class="form-control" name="wang_deposit" min="0" step="1"></td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>SUMBANGAN TABUNG KEBAJIKAN (AL-ABRAR)</td>
                            <td><input type="number" class="form-control" name="sumbangan_tabung" min="0" step="1"></td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>SIMPANAN TETAP</td>
                            <td><input type="number" class="form-control" name="simpanan_tetap" min="0" step="1"></td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td>LAIN-LAIN</td>
                            <td><input type="number" class="form-control" name="lain_lain" min="0" step="1"></td>
                        </tr>
                    </tbody>
                </table>
                <small class="text-muted">*Minima Modal Syer adalah sebanyak RM300.00 dan tidak melebihi 1/5 daripada Modal Syer Koperasi dan hendaklah dijelaskan dalam tempoh 6 bulan dari tarikh kelulusan menjadi anggota.</small>
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

    <!-- Add spacing before footer -->
    <div class="mb-5"></div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // MyKad formatting
    $('input[name="no_kp_waris[]"]').mask('000000-00-0000');
});
</script>

<?php include "footer.php"; ?> 