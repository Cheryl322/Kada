<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'headermember.php';

?>


<div class="container mt-3">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Permohonan Pembiayaan KADA</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="loanApplicationProcess.php" enctype="multipart/form-data">
                        
                        <!-- Maklumat Pembiayaan -->
                        <h5 class="mb-3 mt-5">Maklumat Pembiayaan</h5>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="t_amount" class="form-label">Jumlah Pinjaman</label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" name="t_amount" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="period" class="form-label">Tempoh Pembayaran (Bulan)</label>
                                <input type="number" name="period" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="mon_installment" class="form-label">Ansuran Bulanan</label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" name="mon_installment" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <!-- Maklumat Peribadi -->
                        <h5 class="mb-3 mt-5">Maklumat Peribadi</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nama Penuh (Seperti Dalam K/P)</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="no_ic" class="form-label">No. Kad Pengenalan</label>
                                <input type="text" name="no_ic" class="form-control" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="sex" class="form-label">Jantina</label>
                                <select name="sex" class="form-select" required>
                                    <option value="">Pilih Jantina</option>
                                    <option value="Lelaki">Lelaki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="religion" class="form-label">Agama</label>
                                <select name="religion" class="form-select" required>
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
                                <label for="nationality" class="form-label">Bangsa</label>
                                <select name="nationality" class="form-select" required>
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
                                <label for="DOB" class="form-label">Tarikh Lahir</label>
                                <input type="date" name="DOB" class="form-control" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="add1" class="form-label">Alamat Rumah</label>
                                <textarea name="add1" class="form-control" required></textarea>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="postcode1" class="form-label">Poskod</label>
                                <input type="text" name="postcode1" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="state1" class="form-label">Negeri</label>
                                <select name="state1" class="form-select" required>
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
                                <label for="memberID" class="form-label">No. Anggota</label>
                                <input type="text" name="memberID" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="PFNo" class="form-label">No. PF</label>
                                <input type="text" name="PFNo" class="form-control" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="position" class="form-label">Jawatan</label>
                                <input type="text" name="position" class="form-control" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="add2" class="form-label">Alamat Pejabat</label>
                                <textarea name="add2" class="form-control" required></textarea>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="postcode2" class="form-label">Poskod</label>
                                <input type="text" name="postcode2" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="state2" class="form-label">Negeri</label>
                                <select name="state2" class="form-select" required>
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
                                <label for="office_pNo" class="form-label">No. Telefon Pejabat</label>
                                <input type="tel" name="office_pNo" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="pNo" class="form-label">No. Telefon Bimbit</label>
                                <div class="input-group">
                                    <span class="input-group-text">+60</span>
                                    <input type="tel" name="pNo" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="bankName" class="form-label">Nama Bank</label>
                                <select name="bankName" class="form-select" required>
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
                                <label for="bankAcc" class="form-label">No. Akaun Bank</label>
                                <input type="text" name="bankAcc" class="form-control" required>
                            </div>
                        </div>

                        <!-- Maklumat Penjamin 1 -->
                        <h5 class="mb-3 mt-5">Butir-butir Penjamin 1</h5>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="guarantor_N" class="form-label">Nama (Seperti Dalam K/P)</label>
                                <input type="text" name="guarantor_N" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="guarantor_ic" class="form-label">No. Kad Pengenalan</label>
                                <input type="text" name="guarantor_ic" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="guarantor_pNo" class="form-label">No. Telefon</label>
                                <div class="input-group">
                                    <span class="input-group-text">+60</span>
                                    <input type="tel" name="guarantor_pNo" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="PFNo1" class="form-label">No. PF</label>
                                <input type="text" name="PFNo1" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="guarantorMemberID" class="form-label">No. Anggota</label>
                                <input type="text" name="guarantorMemberID" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="sign" class="form-label">Tandatangan (PDF)</label>
                                <input type="file" name="sign" class="form-control" required>
                            </div>
                        </div>

                        <!-- Maklumat Penjamin 2 -->
                        <h5 class="mb-3 mt-5">Butir-butir Penjamin 2</h5>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="guarantor_N2" class="form-label">Nama (Seperti Dalam K/P)</label>
                                <input type="text" name="guarantor_N2" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="guarantor_ic2" class="form-label">No. Kad Pengenalan</label>
                                <input type="text" name="guarantor_ic2" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="guarantor_pNo2" class="form-label">No. Telefon</label>
                                <div class="input-group">
                                    <span class="input-group-text">+60</span>
                                    <input type="tel" name="guarantor_pNo2" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="PFNo2" class="form-label">No. PF</label>
                                <input type="text" name="PFNo2" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="guarantorMemberID2" class="form-label">No. Anggota</label>
                                <input type="text" name="guarantorMemberID2" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="sign2" class="form-label">Tandatangan (PDF)</label>
                                <input type="file" name="sign2" class="form-control" required>
                            </div>
                        </div>


                        <!-- Pengesahan Majikan -->
                        <h5 class="mb-3 mt-5">Pengesahan Majikan</h5>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="employer_N" class="form-label">Nama Majikan (Seperti Dalam K/P)</label>
                                <input type="text" name="employer_N" class="form-control" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="employer_ic" class="form-label">No. Kad Pengenalan</label>
                                <input type="text" name="employer_ic" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label for="basic_salary" class="form-label">Gaji Pokok Sebulan Kakitangan</label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" name="basic_salary" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="net_salary" class="form-label">Gaji Bersih Sebulan Kakitangan</label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" name="net_salary" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="basic_s" class="form-label">Lampiran Slip Gaji Pokok Kakitangan</label>
                                <input type="file" name="basic_s" class="form-control" required>
                                <div class="form-text">Sila lampirkan slip gaji pokok dalam format PDF</div>
                            </div>

                            <div class="col-md-6">
                                <label for="net_s" class="form-label">Lampiran Slip Gaji Bersih Kakitangan</label>
                                <input type="file" name="net_s" class="form-control" required>
                                <div class="form-text">Sila lampirkan slip gaji bersih dalam format PDF</div>
                            </div>

                            <div class="col-md-6">
                                <label for="signature" class="form-label">Tandatangan Sah/Cop (PDF)</label>
                                <input type="file" name="signature" class="form-control" required>
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
                                <button class="btn btn-primary" type="submit" id="submitBtn" href="statuspermohonanloan.php">Hantar Permohonan</button>
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
