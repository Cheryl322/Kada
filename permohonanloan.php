<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'headermember.php';
include "footer.php";

?>

<div class="container mt-3">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Permohonan Pembiayaan KADA</h4>
                </div>
                <div class="card-body">
                    <form action="statuspermohonanloan.php" method="POST">
                        
                        <!-- Maklumat Pembiayaan -->
                        <h5 class="mb-3 mt-5">Maklumat Pembiayaan</h5>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="jumlah_pinjaman" class="form-label">Jumlah Pinjaman</label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" class="form-control" id="jumlah_pinjaman" name="jumlah_pinjaman" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="tempoh_pembayaran" class="form-label">Tempoh Pembayaran (Bulan)</label>
                                <input type="number" class="form-control" id="tempoh_pembayaran" name="tempoh_pembayaran" required>
                            </div>
                            <div class="col-md-6">
                                <label for="ansuran_bulanan" class="form-label">Ansuran Bulanan</label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" class="form-control" id="ansuran_bulanan" name="ansuran_bulanan" required>
                                </div>
                            </div>
                        </div>

                        <!-- Maklumat Peribadi -->
                        <h5 class="mb-3 mt-5">Maklumat Peribadi</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="nama" class="form-label">Nama Penuh (Seperti Dalam K/P)</label>
                                <input type="text" class="form-control" id="nama" name="nama" required>
                            </div>
                            <div class="col-md-6">
                                <label for="ic" class="form-label">No. Kad Pengenalan</label>
                                <input type="text" class="form-control" id="ic" name="ic" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="jantina" class="form-label">Jantina</label>
                                <select class="form-select" id="jantina" name="jantina" required>
                                    <option value="">Pilih Jantina</option>
                                    <option value="Lelaki">Lelaki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="agama" class="form-label">Agama</label>
                                <select class="form-select" id="agama" name="agama" required>
                                    <option value="">Pilih Agama</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Buddha">Buddha</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Lain-lain">Lain-lain</option>
                                </select>
                                <div id="agama_lain_div" style="display: none; margin-top: 10px;">
                                    <input type="text" class="form-control" id="agama_lain" name="agama_lain" placeholder="Sila nyatakan agama anda">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="bangsa" class="form-label">Bangsa</label>
                                <select class="form-select" id="bangsa" name="bangsa" required>
                                    <option value="">Pilih Bangsa</option>
                                    <option value="Melayu">Melayu</option>
                                    <option value="Cina">Cina</option>
                                    <option value="India">India</option>
                                    <option value="Lain-lain">Lain-lain</option>
                                </select>
                                <div id="bangsa_lain_div" style="display: none; margin-top: 10px;">
                                    <input type="text" class="form-control" id="bangsa_lain" name="bangsa_lain" placeholder="Sila nyatakan bangsa anda">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="tarikh_lahir" class="form-label">Tarikh Lahir</label>
                                <input type="date" class="form-control" id="tarikh_lahir" name="tarikh_lahir" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="alamat_rumah" class="form-label">Alamat Rumah</label>
                                <textarea class="form-control" id="alamat_rumah" name="alamat_rumah" rows="2" required></textarea>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="poskod" class="form-label">Poskod</label>
                                <input type="text" class="form-control" id="poskod" name="poskod" required>
                            </div>
                            <div class="col-md-6">
                                <label for="negeri" class="form-label">Negeri</label>
                                <select class="form-select" id="negeri" name="negeri" required>
                                    <option value="">Pilih Negeri</option>
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

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="no_anggota" class="form-label">No. Anggota</label>
                                <input type="text" class="form-control" id="no_anggota" name="no_anggota" required>
                            </div>
                            <div class="col-md-6">
                                <label for="no_pf" class="form-label">No. PF</label>
                                <input type="text" class="form-control" id="no_pf" name="no_pf" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="jawatan" class="form-label">Jawatan</label>
                                <input type="text" class="form-control" id="jawatan" name="jawatan" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="alamat_rumah" class="form-label">Alamat Pejabat</label>
                                <textarea class="form-control" id="alamat_rumah" name="alamat_rumah" rows="2" required></textarea>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="poskod" class="form-label">Poskod</label>
                                <input type="text" class="form-control" id="poskod" name="poskod" required>
                            </div>
                            <div class="col-md-6">
                                <label for="negeri" class="form-label">Negeri</label>
                                <select class="form-select" id="negeri" name="negeri" required>
                                    <option value="">Pilih Negeri</option>
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

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="tel_pejabat" class="form-label">No. Telefon Pejabat</label>
                                <input type="tel" class="form-control" id="tel_pejabat" name="tel_pejabat" required>
                            </div>
                            <div class="col-md-6">
                                <label for="tel_bimbit" class="form-label">No. Telefon Bimbit</label>
                                <div class="input-group">
                                    <span class="input-group-text">+60</span>
                                    <input type="tel" class="form-control" id="tel_bimbit" name="tel_bimbit" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="nama_bank" class="form-label">Nama Bank</label>
                                <select class="form-select" id="nama_bank" name="nama_bank" required>
                                    <option value="">Pilih Bank</option>
                                    <option value="Maybank2u">Maybank2u</option>
                                    <option value="CIMB Clicks">CIMB Clicks</option>
                                    <option value="Public Bank">Public Bank</option>
                                    <option value="RHB Now">RHB Now</option>
                                    <option value="Ambank">Ambank</option>
                                    <option value="MyBSN">MyBSN</option>
                                    <option value="Bank Rakyat">Bank Rakyat</option>
                                    <option value="UOB">UOB</option>
                                    <option value="Affin Bank">Affin Bank</option>
                                    <option value="Bank Islam">Bank Islam</option>
                                    <option value="HSBC Online">HSBC Online</option>
                                    <option value="Standard Chartered Bank">Standard Chartered Bank</option>
                                    <option value="Kuwait Finance House">Kuwait Finance House</option>
                                    <option value="Bank Muamalat">Bank Muamalat</option>
                                    <option value="OCBC Online">OCBC Online</option>
                                    <option value="Alliance Bank">Alliance Bank (Personal)</option>
                                    <option value="Hong Leong Connect">Hong Leong Connect</option>
                                    <option value="Agrobank">Agrobank</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="no_akaun" class="form-label">No. Akaun Bank</label>
                                <input type="text" class="form-control" id="no_akaun" name="no_akaun" required>
                            </div>
                        </div>

                        <!-- Maklumat Penjamin 1 -->
                        <h5 class="mb-3 mt-5">Butir-butir Penjamin 1</h5>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="penjamin1_nama" class="form-label">Nama (Seperti Dalam K/P)</label>
                                <input type="text" class="form-control" id="penjamin1_nama" name="penjamin1_nama" required>
                            </div>
                            <div class="col-md-6">
                                <label for="penjamin1_ic" class="form-label">No. Kad Pengenalan</label>
                                <input type="text" class="form-control" id="penjamin1_ic" name="penjamin1_ic" required>
                            </div>
                            <div class="col-md-6">
                                <label for="penjamin1_telefon" class="form-label">No. Telefon</label>
                                <div class="input-group">
                                    <span class="input-group-text">+60</span>
                                    <input type="tel" class="form-control" id="penjamin2_telefon" name="penjamin2_telefon" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="penjamin1_pf" class="form-label">No. PF</label>
                                <input type="text" class="form-control" id="penjamin1_pf" name="penjamin1_pf" required>
                            </div>
                            <div class="col-md-6">
                                <label for="penjamin1_anggota" class="form-label">No. Anggota</label>
                                <input type="text" class="form-control" id="penjamin1_anggota" name="penjamin1_anggota" required>
                            </div>
                            <div class="col-md-6">
                                <label for="penjamin1_signature" class="form-label">Tandatangan (PDF)</label>
                                <input type="file" class="form-control" id="penjamin1_signature" name="penjamin1_signature" accept=".pdf" required>
                            </div>
                        </div>

                        <!-- Maklumat Penjamin 2 -->
                        <h5 class="mb-3 mt-5">Butir-butir Penjamin 2</h5>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="penjamin2_nama" class="form-label">Nama (Seperti Dalam K/P)</label>
                                <input type="text" class="form-control" id="penjamin2_nama" name="penjamin2_nama" required>
                            </div>
                            <div class="col-md-6">
                                <label for="penjamin2_ic" class="form-label">No. Kad Pengenalan</label>
                                <input type="text" class="form-control" id="penjamin2_ic" name="penjamin2_ic" required>
                            </div>
                            <div class="col-md-6">
                                <label for="penjamin2_telefon" class="form-label">No. Telefon</label>
                                <div class="input-group">
                                    <span class="input-group-text">+60</span>
                                    <input type="tel" class="form-control" id="penjamin2_telefon" name="penjamin2_telefon" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="penjamin2_pf" class="form-label">No. PF</label>
                                <input type="text" class="form-control" id="penjamin2_pf" name="penjamin2_pf" required>
                            </div>
                            <div class="col-md-6">
                                <label for="penjamin2_anggota" class="form-label">No. Anggota</label>
                                <input type="text" class="form-control" id="penjamin2_anggota" name="penjamin2_anggota" required>
                            </div>
                            <div class="col-md-6">
                                <label for="penjamin2_signature" class="form-label">Tandatangan (PDF)</label>
                                <input type="file" class="form-control" id="penjamin2_signature" name="penjamin2_signature" accept=".pdf" required>
                            </div>
                        </div>


                        <!-- Pengesahan Majikan -->
                        <h5 class="mb-3 mt-5">Pengesahan Majikan</h5>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="majikan_nama" class="form-label">Nama Majikan (Seperti Dalam K/P)</label>
                                <input type="text" class="form-control" id="majikan_nama" name="majikan_nama" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="majikan_ic" class="form-label">No. Kad Pengenalan</label>
                                <input type="text" class="form-control" id="majikan_ic" name="majikan_ic" required>
                            </div>

                            <div class="col-md-6">
                                <label for="gaji_pokok" class="form-label">Gaji Pokok Sebulan Kakitangan</label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" class="form-control" id="gaji_pokok" name="gaji_pokok" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="gaji_bersih" class="form-label">Gaji Bersih Sebulan Kakitangan</label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" class="form-control" id="gaji_bersih" name="gaji_bersih" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="gaji_pokok_file" class="form-label">Lampiran Slip Gaji Pokok Kakitangan</label>
                                <input type="file" class="form-control" id="gaji_pokok_file" name="gaji_pokok_file" accept=".pdf,.jpg,.jpeg,.png" required>
                                <div class="form-text">Sila lampirkan slip gaji pokok dalam format PDF</div>
                            </div>

                            <div class="col-md-6">
                                <label for="gaji_bersih_file" class="form-label">Lampiran Slip Gaji Bersih Kakitangan</label>
                                <input type="file" class="form-control" id="gaji_bersih_file" name="gaji_bersih_file" accept=".pdf,.jpg,.jpeg,.png" required>
                                <div class="form-text">Sila lampirkan slip gaji bersih dalam format PDF</div>
                            </div>

                            <div class="col-md-6">
                                <label for="majikan_signature" class="form-label">Tandatangan Sah/Cop (PDF)</label>
                                <input type="file" class="form-control" id="majikan_signature" name="majikan_signature" accept=".pdf" required>
                                <div class="form-text">Sila lampirkan tandatangan dalam format PDF</div>
                            </div>
                        </div>

                        <!-- Agreement Checkboxes -->
                        <div class="mt-5">
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="agreement1" name="agreement1" required>
                                <label class="form-check-label" for="agreement1">
                                    Saya mengesahkan bahawa semua maklumat yang diberikan dalam borang ini adalah tepat dan benar. Saya faham bahawa sebarang maklumat palsu yang diberikan boleh menyebabkan permohonan ditolak dan tindakan undang-undang boleh diambil.
                                </label>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="agreement2" name="agreement2" required>
                                <label class="form-check-label" for="agreement2">
                                Saya bersetuju untuk memberi kuasa kepada KOPERASI KAKITANGAN KADA KELANTAN BHD atau wakilnya yang sah untuk medapat apa-apa maklumat yang diperlukan dan juga medapatkan bayaran balik dari potongan gaji dan emolumen saya sebagaimana amaun yang dipinjamkan.
                                </label>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="agreement3" name="agreement3" required>
                                <label class="form-check-label" for="agreement3">
                                    Saya bersetuju menerima sebarang keputusan dari KOPERASI in untuk menolak permohonan tanpa memberi sebarang alasan.
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2 col-6 mx-auto mt-5 mb-4">
                                <button class="btn btn-primary" type="submit" id="submitBtn">Hantar Permohonan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('agama').addEventListener('change', function() {
    var agamaLainDiv = document.getElementById('agama_lain_div');
    if (this.value === 'Lain-lain') {
        agamaLainDiv.style.display = 'block';
        document.getElementById('agama_lain').required = true;
    } else {
        agamaLainDiv.style.display = 'none';
        document.getElementById('agama_lain').required = false;
    }
});

document.getElementById('bangsa').addEventListener('change', function() {
    var bangsaLainDiv = document.getElementById('bangsa_lain_div');
    if (this.value === 'Lain-lain') {
        bangsaLainDiv.style.display = 'block';
        document.getElementById('bangsa_lain').required = true;
    } else {
        bangsaLainDiv.style.display = 'none';
        document.getElementById('bangsa_lain').required = false;
    }
});
</script>

<?php include 'footer.php'; ?>
