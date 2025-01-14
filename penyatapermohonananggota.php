<?php

include"headeradmin.php";
include "footer.php";

?>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<br><br><br>
<div class="container mt-3">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Penyata Pemohonan Anggota KADA</h4>
        </div>

        <div class="card-body">
            <form id="loanForm" action="#" method="POST" enctype="multipart/form-data">
                <h5>MAKLUMAT PEMOHON</h5>
                <!-- Personal Info -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nama" class="form-label">Nama Penuh (Seperti Dalam K/P)</label>
                        <input type="text" class="form-control" id="nama" name="memberName" 
                            value="<?php echo isset($memberData['memberName']) ? htmlspecialchars($memberData['memberName']) : ''; ?>" readonly>
                    </div>
                    <div class="col-md-6">
						<label for="jantina" class="form-label">Jantina</label>
                        <input type="text" class="form-control" id="jantina" name="sex" 
                	         value="<?php echo isset($memberData['sex']) ? htmlspecialchars($memberData['sex']) : ''; ?>" readonly>
                    </div>
                   </div>
                <div class="row mb-3">
                    <div class="col-md-6">
						<label for="ic" class="form-label">No. Kad Pengenalan</label>
                	    <input type="text" class="form-control" id="ic" name="ic" 
                               value="<?php echo isset($memberData['ic']) ? htmlspecialchars($memberData['ic']) : ''; ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="agama" class="form-label">Agama</label>
                        <input type="text" class="form-control" id="agama" name="religion" 
                              value="<?php echo isset($memberData['religion']) ? htmlspecialchars($memberData['religion']) : ''; ?>" readonly>
                    </div>
                </div>
				<div class="row mb-3">
                    <div class="col-md-6">
						<label for="ic" class="form-label">Taraf Perkahwinan</label>
            	        <input type="text" class="form-control" id="ic" name="ic" 
                               value="<?php echo isset($memberData['maritalStatus']) ? htmlspecialchars($memberData['maritalStatus']) : ''; ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="agama" class="form-label">Bangsa</label>
                        <input type="text" class="form-control" id="agama" name="religion" 
                               value="<?php echo isset($memberData['nation']) ? htmlspecialchars($memberData['nation']) : ''; ?>" readonly>
                    </div>
                </div>
			
                <!-- Home Address -->
                <h6>Alamat Rumah</h6>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="alamat" class="form-label">Alamat Rumah</label>
                        <textarea class="form-control" id="alamat" name="homeAddress" rows="3" readonly><?php echo isset($memberData['homeAddress']) ? htmlspecialchars($memberData['homeAddress']) : ''; ?></textarea>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="poskod" class="form-label">Poskod</label>
                        <input type="text" class="form-control" id="poskod" name="homePostcode" 
                              value="<?php echo isset($memberData['homePostcode']) ? htmlspecialchars($memberData['homePostcode']) : ''; ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="negeri" class="form-label">Negeri</label>
                        <input type="text" class="form-control" id="negeri" name="homeState" 
                             value="<?php echo isset($memberData['homeState']) ? htmlspecialchars($memberData['homeState']) : ''; ?>" readonly>
                    </div>
                </div>

				<div class="row mb-3">
                    <div class="col-md-6">
						<label for="ic" class="form-label">No. PF</label>
                	    <input type="text" class="form-control" id="ic" name="ic" 
                               value="<?php echo isset($memberData['no_pf']) ? htmlspecialchars($memberData['no_pf']) : ''; ?>" readonly>
                    </div>
                </div>

                <!-- Office Address -->
                <h6>Alamat Pejabat</h6>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="officeAddress" class="form-label">Alamat</label>
                        <textarea class="form-control" id="officeAddress" name="officeAddress" rows="3" readonly><?php echo isset($memberData['officeAddress']) ? htmlspecialchars($memberData['officeAddress']) : ''; ?></textarea>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="officePostcode" class="form-label">Poskod</label>
                        <input type="text" class="form-control" id="officePostcode" name="officePostcode" 
                                value="<?php echo isset($memberData['officePostcode']) ? htmlspecialchars($memberData['officePostcode']) : ''; ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="officeState" class="form-label">Negeri</label>
                        <input type="text" class="form-control" id="officeState" name="officeState" 
                              value="<?php echo isset($memberData['officeState']) ? htmlspecialchars($memberData['officeState']) : ''; ?>" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="officeTel" class="form-label">No. Telefon Bimbit</label>
                            <div class="input-group">
                                <span class="input-group-text">+60</span>
                                <input type="text" class="form-control" id="tel" name="tel" 
                                    value="<?php echo isset($memberData['phoneNumber']) ? htmlspecialchars($memberData['phoneNumber']) : ''; ?>" readonly>
                            </div>
                    </div>
					<div class="col-md-6">
                        <label for="officeTel" class="form-label">No. Telefon Rumah</label>
                            <div class="input-group">
                                <span class="input-group-text">+60</span>
                                <input type="text" class="form-control" id="officeTel" name="officeTel" 
                                    value="<?php echo isset($memberData['phoneHome']) ? htmlspecialchars($memberData['phoneHome']) : ''; ?>" readonly>
                            </div>
                    </div>
                </div>
                
				<br><br>
                <h5>MAKLUMAT KELUARGA DAN PEWARIS</h5>
				<div class="container mt-3">
					<table class="table">
						<thead class="table-dark">
							<tr>
							<th style="width: 5%">Bil</th>
							<th style="width: 25%">Hubungan</th>
							<th style="width: 50%">Name</th>
							<th style="width: 20%">No. Kad Pengenalan</th>
							</tr>
						</thead>
						<tbody>
							<tr> </tr>
							<tr> </tr>
							<tr> </tr>
							<tr> </tr>
						</tbody>
					</table>
				</div>

                    

				<br><br>
                <h5>YURAN DAN SUMBANGAN</h5>
				<div class="container mt-3">
					<table class="table">
						<thead class="table-dark">
							<tr>
							<th style="width: 5%">Bil</th>
							<th style="width: 70%">Perkara</th>
							<th style="width: 25%">RM</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>1</td>
								<td>Fee Masuk</td>
								<td> </td>
							</tr>
							<tr>
								<td>2</td>
								<td>Modah Syer*</td>
								<td> </td>
							</tr>
							<tr>
								<td>3</td>
								<td>Modal Yuran</td>
								<td> </td>
							</tr>
							<tr>
								<td>4</td>
								<td>Wang Deposit Anggota</td>
								<td> </td>
							</tr>
							<tr>
								<td>5</td>
								<td>Sumbangan Tabung Kebajikan (Al-Abrar)</td>
								<td> </td>
							</tr>
							<tr>
								<td>6</td>
								<td>Simpanan Tetap</td>
								<td> </td>
							</tr>
							<tr>
								<td>7</td>
								<td>Lain-lain</td>
								<td> </td>
							</tr>
						</tbody>
					</table>
				</div>

				<div class="d-grid gap-2">
                    <button type="button" class="btn btn-secondary prev-step mb-2" onclick="window.location.href='senaraiPermohonanAhli.php'">Kembali</button>
                </div>
            </form>
        </div>
    </div>
</div>
<br><br>

			

                