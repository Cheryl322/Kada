<?php

include "headeradmin.php";
include "footer.php";
include "dbconnect.php";

// Get the loan application ID from URL
$loanId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$loanId) {
    header("Location: senaraiPermohonanPinjaman.php");
    exit;
}

// First, get the loan application and related member data
$sql = "SELECT 
    la.loanApplicationID,
    la.amountRequested,
    la.financingPeriod,
    la.monthlyInstallments,
    l.loanType,
    l.employerName,
    l.employerIC,
    l.basicSalary,
    l.netSalary,
    m.memberName,
    m.ic,
    m.maritalStatus,
    m.sex,
    m.religion,
    m.nation,
    m.no_pf,
    m.phoneNumber,
    mha.homeAddress,
    mha.homePostcode,
    mha.homeState,
    moa.officeAddress,
    moa.officePostcode,
    moa.officeState,
    b.bankName,
    b.accountNo,
    -- l.basicSalaryFile,
    l.netSalaryFile
    FROM tb_loanapplication la
    LEFT JOIN tb_loan l ON l.loanApplicationID = la.loanApplicationID
    LEFT JOIN tb_member m ON la.employeeID = m.employeeID
    LEFT JOIN tb_member_homeaddress mha ON m.employeeID = mha.employeeID
    LEFT JOIN tb_member_officeaddress moa ON m.employeeID = moa.employeeID
    LEFT JOIN tb_bank b ON b.loanApplicationID = la.loanApplicationID
    WHERE la.loanApplicationID = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $loanId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$memberData = mysqli_fetch_assoc($result);

if (!$memberData) {
    die("No data found for loan ID: " . $loanId);
}

// First get the loan data
$loanApplicationID = isset($_GET['id']) ? $_GET['id'] : null; // Use the same ID as before

if ($loanApplicationID) {
    $query = "SELECT basicSalary, netSalary FROM tb_loan WHERE loanApplicationID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $loanApplicationID);
    $stmt->execute();
    $result = $stmt->get_result();
    $salaryData = $result->fetch_assoc();

    // Set default values if no data found
    // $basicSalary = $salaryData ? number_format($salaryData['basicSalary'], 2, '.', '') : '0.00';
    $netSalary = $salaryData ? number_format($salaryData['netSalary'], 2, '.', '') : '0.00';
} else {
    // $basicSalary = '0.00';
    $netSalary = '0.00';
}

// Update the form values to use the correct array keys
?>

<style>
body {
    margin: 0;
    padding: 0;
    min-height: 100vh;
    background: linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)), url('img/padi.jpg') no-repeat center center fixed;
    background-size: cover;
}

.wrapper {
    min-height: calc(100vh - 60px); /* Account for footer height */
    padding-top: 20px;
    padding-bottom: 80px; /* Increased padding to prevent overlap */
}

.container {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    padding: 30px;
    margin-bottom: 80px; /* Increased margin to ensure space above footer */
    position: relative;
    z-index: 1;
}

.table thead.table-dark {
    background-color: #20B2AA !important;
    border-color: #20B2AA !important;
}

.table thead.table-dark th {
    background-color: #20B2AA !important;
    color: white;
    font-weight: 500;
    border: none;
}

footer {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background-color: #7DCFB6;
    padding: 15px 0;
    text-align: center;
    z-index: 1000;
    height: 60px; /* Fixed height for footer */
}

/* Keep other existing styles ... */
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<br><br><br>
<div class="container mt-3">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Penyata Pemohonan Pembiayaan Anggota KADA</h4>
        </div>

        <div class="card-body">
            <form id="loanForm" action="#" method="POST" enctype="multipart/form-data">
                <h5>BUTIR-BUTIR PEMBIAYAAN</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nama" class="form-label">Jenis Pembiayaan</label>
                        <input type="text" class="form-control" id="loanType" name="loanType" 
                            value="<?php echo isset($memberData['loanType']) ? htmlspecialchars($memberData['loanType']) : ''; ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="officeTel" class="form-label">Amaun Dipohon</label>
                            <div class="input-group">
                                <span class="input-group-text">RM</span>
                                <input type="text" class="form-control" id="amountRequested" name="amountRequested" 
                                    value="<?php echo isset($memberData['amountRequested']) ? htmlspecialchars($memberData['amountRequested']) : ''; ?>" readonly>
                            </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="officeTel" class="form-label">Tempoh Pembiayaan</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="financingPeriod" name="financingPeriod" 
                                value="<?php echo isset($memberData['financingPeriod']) ? htmlspecialchars($memberData['financingPeriod']) : ''; ?>" readonly>
                            <span class="input-group-text">bulan</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="officeTel" class="form-label">Ansuran Bulan</label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="text" class="form-control" id="monthlyInstalment" name="monthlyInstalment" 
                                value="<?php echo isset($memberData['monthlyInstallments']) ? htmlspecialchars($memberData['monthlyInstallments']) : ''; ?>" readonly>
                        </div>
                    </div>
                </div>

                <h5>BUTIR-BUTIR PERIBADI PEMOHON</h5>
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
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="officeTel" class="form-label">Nama Bank/CWG</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="bankName" name="bankName" 
                                    value="<?php echo isset($memberData['bankName']) ? htmlspecialchars($memberData['bankName']) : ''; ?>" readonly>
                            </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="officeTel" class="form-label">No. Akaun Bank/CWG</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="accountNo" name="accountNo" 
                                    value="<?php echo isset($memberData['accountNo']) ? htmlspecialchars($memberData['accountNo']) : ''; ?>" readonly>
                            </div>
                    </div>
                </div>

                <!-- <br><br>
                <h5>PENGAKUAN PEMOHON</h5> -->



                <br><br>
                <h5>BUTIR-BUTIR PENJAMIN</h5>
                <div class="container mt-3">
                    <table class="table">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 5%">Bil</th>
                                <th style="width: 50%">Nama</th>
                                <th style="width: 20%">No. Kad Pengenalan</th>
                                <th style="width: 15%">No. PF</th>
                                <th style="width: 15%">No. Anggota</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query to get guarantor information
                            $guarantorSql = "SELECT * FROM tb_guarantor WHERE loanApplicationID = ?";
                            $guarantorStmt = mysqli_prepare($conn, $guarantorSql);
                            mysqli_stmt_bind_param($guarantorStmt, "i", $loanId);
                            mysqli_stmt_execute($guarantorStmt);
                            $guarantorResult = mysqli_stmt_get_result($guarantorStmt);
                            
                            $counter = 1;
                            while ($guarantor = mysqli_fetch_assoc($guarantorResult)) {
                                echo "<tr>";
                                echo "<td>" . $counter++ . "</td>";
                                echo "<td>" . htmlspecialchars($guarantor['guarantorName']) . "</td>";
                                echo "<td>" . htmlspecialchars($guarantor['guarantorIC']) . "</td>";
                                echo "<td>" . htmlspecialchars($guarantor['guarantorPFNo']) . "</td>";
                                echo "<td>" . htmlspecialchars($guarantor['guarantorMemberNo']) . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <br><br>
                <h5>PENGESAHAN MAJIKAN</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="officePostcode" class="form-label">Nama Majikan</label>
                        <input type="text" class="form-control" id="employerName" name="employerName" 
                                value="<?php echo isset($memberData['employerName']) ? htmlspecialchars($memberData['employerName']) : ''; ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="officeState" class="form-label">No. Kad Pengenalan</label>
                        <input type="text" class="form-control" id="employerIC" name="employerIC" 
                              value="<?php echo isset($memberData['employerIC']) ? htmlspecialchars($memberData['employerIC']) : ''; ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="basicSalary" class="form-label">Gaji Pokok Sebulan Kakitangan</label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" step="0.01" class="form-control" id="basicSalary" name="basicSalary" 
                                   value="<?php echo htmlspecialchars($basicSalary); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="netSalary" class="form-label">Gaji Bersih Sebulan Kakitangan</label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" step="0.01" class="form-control" id="netSalary" name="netSalary" 
                                   value="<?php echo htmlspecialchars($netSalary); ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <!-- <div class="col-md-6">
                        <label for="basicSalarySlip" class="form-label">Slip Gaji Pokok Kakitangan</label>
                        <?php if (!empty($memberData['basicSalaryFile'])): ?>
                            <div class="mb-2">
                                <a href="<?php echo htmlspecialchars($memberData['basicSalaryFile']); ?>" 
                                   class="btn btn-primary btn-sm" target="_blank">
                                    <i class="fas fa-file-pdf"></i> Lihat Slip Gaji Pokok
                                </a>
                            </div>
                        <?php endif; ?>
                    </div> -->
                    <div class="col-md-6">
                        <label for="netSalarySlip" class="form-label">Slip Gaji Kakitangan</label>
                        <?php if (!empty($memberData['netSalaryFile'])): ?>
                            <div class="mb-2">
                                <a href="<?php echo htmlspecialchars($memberData['netSalaryFile']); ?>" 
                                   class="btn btn-primary btn-sm" target="_blank">
                                    <i class="fas fa-file-pdf"></i> Lihat Slip Gaji
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <br>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-secondary prev-step mb-2" onclick="window.location.href='senaraiPermohonanPinjaman.php'">Kembali</button>
                </div>
            </form>
        </div>
    </div>
</div>
<br><br>

			

                