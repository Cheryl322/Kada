<?php
session_start();
include "dbconnect.php";

// 检查用户是否登录
if (!isset($_SESSION['employeeID'])) {
    header("Location: login.php");
    exit();
}

$employeeId = $_SESSION['employeeID'];

// 检查用户是否是会员
$check_member = "SELECT status FROM tb_member_status WHERE employeeID = ?";
$stmt = mysqli_prepare($conn, $check_member);
mysqli_stmt_bind_param($stmt, "s", $employeeId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);    

// 如果用户不是会员，显示错误消息并重定向
if (mysqli_num_rows($result) === 0) {
    ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Akses Ditolak!',
                text: 'Anda perlu membuat pendaftaran anggota dahulu sebelum membuat permohonan pinjaman.',
                icon: 'error',
                confirmButtonText: 'OK'
            }).then((result) => {
                window.location.href = 'daftar_ahli.php';
            });
        });
    </script>
    <?php
    exit();
}

if (isset($_SESSION['formData'])) {
    $formData = $_SESSION['formData'];
    unset($_SESSION['formData']); // Clear the stored form data
}

// Check registration status
$employeeID = $_SESSION['employeeID'];
$sql = "SELECT COALESCE(mr.regisStatus, 'Belum Selesai') as regisStatus 
        FROM tb_member m 
        LEFT JOIN tb_memberregistration_memberapplicationdetails mr 
        ON m.employeeID = mr.memberRegistrationID 
        WHERE m.employeeID = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $employeeID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

// Redirect if status is not 'Diluluskan'
if ($row['regisStatus'] !== 'Diluluskan') {
    // Show alert and redirect
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Akses Ditolak!',
                text: 'Anda perlu mendapat kelulusan pendaftaran anggota dahulu sebelum membuat permohonan pinjaman.',
                icon: 'error',
                confirmButtonText: 'OK'
            }).then((result) => {
                window.location.href = 'mainpage.php';
            });
        });
    </script>";
    exit();
}

// 检查会员状态
$sql_check_status = "SELECT status FROM tb_member_status WHERE employeeID = ?";
$stmt_status = mysqli_prepare($conn, $sql_check_status);
mysqli_stmt_bind_param($stmt_status, 's', $employeeID);
mysqli_stmt_execute($stmt_status);
$result_status = mysqli_stmt_get_result($stmt_status);
$member_status = mysqli_fetch_assoc($result_status);

// 如果状态是 "Berhenti"，不允许申请贷款
if ($member_status['status'] == 'Berhenti') {
    // 使用 SweetAlert2 显示错误消息
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Akses Ditolak!',
                text: 'Maaf, anda tidak boleh membuat permohonan pinjaman kerana status keahlian anda telah berhenti.',
                icon: 'error',
                confirmButtonText: 'OK'
            }).then((result) => {
                window.location.href = 'mainpage.php';
            });
        });
    </script>";
    exit();
}

include 'headermember.php';

$employeeID = $_SESSION['employeeID'];

// Main query without the DESCRIBE statement
$sql = "SELECT m.*, h.homeAddress, h.homePostcode, h.homeState, 
               o.officeAddress, o.officePostcode, o.officeState
        FROM tb_member m 
        LEFT JOIN tb_member_homeaddress h ON m.employeeID = h.employeeID 
        LEFT JOIN tb_member_officeaddress o ON m.employeeID = o.employeeID 
        WHERE m.employeeID = ?";

// Prepare the statement
$stmt = mysqli_prepare($conn, $sql);
if ($stmt === false) {
    die("Error preparing statement: " . mysqli_error($conn));
}

// Bind the parameter properly
if (!mysqli_stmt_bind_param($stmt, 's', $employeeID)) {
    die("Error binding parameters: " . mysqli_stmt_error($stmt));
}

// Execute the statement
if (!mysqli_stmt_execute($stmt)) {
    die("Error executing statement: " . mysqli_stmt_error($stmt));
}

// Get the result
$result = mysqli_stmt_get_result($stmt);
$memberData = mysqli_fetch_assoc($result);

// Close the statement
mysqli_stmt_close($stmt);

// Get current interest rate
$rateSql = "SELECT rate FROM tb_interestrate ORDER BY updated_at DESC LIMIT 1";
$rateResult = mysqli_query($conn, $rateSql);
$rateRow = mysqli_fetch_assoc($rateResult);
$interestRate = $rateRow['rate'] ?? 2.00; // Default to 2% if no rate found
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="container">
    <!-- Header and Progress Steps -->
    <div class="loan-container">
        <!-- Header -->
        <div class="loan-header">
            <h1>Permohonan Pembiayaan KADA</h1>
        </div>

        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="step-item active">
                <div class="step-circle">1</div>
                <div class="step-line"></div>
                <div class="step-label">Maklumat Peribadi</div>
            </div>
            <div class="step-item">
                <div class="step-circle">2</div>
                <div class="step-line"></div>
                <div class="step-label">Maklumat Pembiayaan</div>
            </div>
            <div class="step-item">
                <div class="step-circle">3</div>
                <div class="step-line"></div>
                <div class="step-label">Maklumat Penjamin</div>
            </div>
            <div class="step-item">
                <div class="step-circle">4</div>
                <div class="step-line"></div>
                <div class="step-label">Pengesahan Majikan</div>
            </div>
        </div>
    </div>

    <!-- Form content starts here -->
    <div class="form-section">
        <form id="loanForm" method="POST" action="loanApplicationProcess.php" enctype="multipart/form-data" class="needs-validation" novalidate>
            <input type="hidden" name="employeeID" value="<?php echo isset($_SESSION['employeeID']) ? $_SESSION['employeeID'] : ''; ?>">
            <!-- Step 1: Maklumat Peribadi -->
            <div class="form-step" id="step1">
            <div class="section-header">
                        <h4>MAKLUMAT PERIBADI</h4>
                    </div>
                <!-- Personal Info -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nama" class="form-label">Nama Penuh (Seperti Dalam K/P)</label>
                        <input type="text" class="form-control" id="nama" name="memberName" 
                            value="<?php echo isset($memberData['memberName']) ? htmlspecialchars($memberData['memberName']) : ''; ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="ic" class="form-label">No. Kad Pengenalan</label>
                        <input type="text" class="form-control" id="ic" name="ic" 
                            value="<?php echo isset($memberData['ic']) ? htmlspecialchars($memberData['ic']) : ''; ?>" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="jantina" class="form-label">Jantina</label>
                        <input type="text" class="form-control" id="jantina" name="sex" 
                            value="<?php echo isset($memberData['sex']) ? htmlspecialchars($memberData['sex']) : ''; ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="agama" class="form-label">Agama</label>
                        <input type="text" class="form-control" id="agama" name="religion" 
                            value="<?php echo isset($memberData['religion']) ? htmlspecialchars($memberData['religion']) : ''; ?>" readonly>
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
                            <input type="text" class="form-control" id="officeTel" name="officeTel" 
                                value="<?php echo isset($memberData['phoneHome']) ? htmlspecialchars($memberData['phoneHome']) : ''; ?>" readonly>
                        </div>
                    </div>
                </div>


                <div class="mt-3">
                    <button type="button" class="btn btn-primary next-step">Seterusnya</button>
                </div>
            </div>

            <!-- Step 2: Maklumat Pembiayaan -->
            <div class="form-step" id="step2">
            <div class="section-header">
                        <h4>Maklumat Bank</h4>
                    </div>
                                    <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="bankName" class="form-label">Nama Bank</label>
                        <select class="form-control" id="bankName" name="bankName" required>
                            <option value="">Pilih Bank</option>
                            <option value="Maybank">Maybank</option>
                            <option value="CIMB Bank">CIMB Bank</option>
                            <option value="Public Bank">Public Bank</option>
                            <option value="RHB Bank">RHB Bank</option>
                            <option value="Hong Leong Bank">Hong Leong Bank</option>
                            <option value="AmBank">AmBank</option>
                            <option value="UOB Bank">UOB Bank</option>
                            <option value="Bank Rakyat">Bank Rakyat</option>
                            <option value="Bank Islam">Bank Islam</option>
                            <option value="Affin Bank">Affin Bank</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="accountNo" class="form-label">No. Akaun Bank</label>
                        <input type="text" class="form-control" id="accountNo" name="accountNo" required>
                    </div>
                </div>

                <br>
                <div class="section-header">
                        <h4>Maklumat Pembiayaan</h4>
                    </div>
                <div class="row mb-3">
                    <div class="mb-3">
                        <label for="loanType" class="form-label">Jenis Pinjaman</label>
                        <select class="form-select" id="loanType" name="loanType" required>
                            <option value="">Pilih Jenis Pinjaman</option>
                            <option value="AL-BAI">AL-BAI</option>
                            <option value="AL-INAH">AL-INAH</option>
                            <option value="SKIM KHAS">SKIM KHAS</option>
                            <option value="KARNIVAL MUSIM ISTIMEWA">KARNIVAL MUSIM ISTIMEWA</option>
                            <option value="BAIK PULIH KENDERAAN">BAIK PULIH KENDERAAN</option>
                            <option value="CUKAI JALAN">CUKAI JALAN</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="jumlah_pinjaman" class="form-label">Jumlah Pinjaman</label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" 
                                   class="form-control" 
                                   id="jumlah_pinjaman" 
                                   name="amountRequested"
                                   required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="tempoh_pembayaran" class="form-label">Tempoh Pembayaran (Bulan)</label>
                        <input type="number" 
                               class="form-control" 
                               id="tempoh_pembayaran" 
                               name="financingPeriod" 
                               required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="kadar_faedah" class="form-label">Kadar Faedah Tahunan</label>
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   id="kadar_faedah" 
                                   value="<?php echo number_format($interestRate, 2); ?>%" 
                                   readonly>
                            <input type="hidden" 
                                   id="kadar_faedah_value" 
                                   name="interestRate"
                                   value="<?php echo $interestRate; ?>">
                        </div>
                        <small class="text-muted">*Kadar faedah adalah tertakluk kepada perubahan</small>
                    </div>
                    <div class="col-md-6">
                        <label for="ansuran_bulanan" class="form-label">Ansuran Bulanan</label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" 
                                   class="form-control" 
                                   id="ansuran_bulanan" 
                                   name="monthlyInstallments" 
                                   readonly>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <button type="button" class="btn btn-secondary prev-step">Kembali</button>
                    <button type="button" class="btn btn-primary next-step">Seterusnya</button>
                </div>
            </div>

            <!-- Step 3: Maklumat Penjamin -->
            <div class="form-step" id="step3">
                
                <!-- Guarantor Information Section -->
                <div class="guarantor-section">
                    <div class="section-header">
                        <h4>Maklumat Penjamin</h4>
                    </div>

                    <!-- First Guarantor -->
                    <div class="form-container">
                        <div class="form-title">
                            <h5>Butir-butir Penjamin 1</h5>
                        </div>

                        <div class="form-grid">
                            <div class="form-row">
                            <div class="form-group col-md-6">
                                    <label>No. Kad Pengenalan (eg. XXXXXXXXXXXX)</label>
                                    <input type="text" class="form-control" name="guarantorIC1" id="guarantorIC1" required>
                                    <div id="guarantor1Feedback" class="invalid-feedback"></div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Nama (Seperti Dalam K/P)</label>
                                    <input type="text" class="form-control" name="guarantorName1" required>
                                </div>
                                
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>No. Telefon</label>
                                    <div class="input-group">
                                        <span class="input-group-text">+60</span>
                                        <input type="text" class="form-control" name="guarantorPhone1" required>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>No. PF</label>
                                    <input type="text" class="form-control" name="guarantorPF1" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>No. Anggota</label>
                                    <input type="text" class="form-control" name="guarantorMemberNo1" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Second Guarantor -->
                    <div class="form-container mt-4">
                        <div class="form-title">
                            <h5>Butir-butir Penjamin 2</h5>
                        </div>

                        <div class="form-grid">
                            <div class="form-row">
                            <div class="form-group col-md-6">
                                    <label>No. Kad Pengenalan (eg. XXXXXXXXXXXX)</label>
                                    <input type="text" class="form-control" name="guarantorIC2" id="guarantorIC2" required>
                                    <div id="guarantor2Feedback" class="invalid-feedback"></div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Nama (Seperti Dalam K/P)</label>
                                    <input type="text" class="form-control" name="guarantorName2" required>
                                </div>
                                
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>No. Telefon</label>
                                    <div class="input-group">
                                        <span class="input-group-text">+60</span>
                                        <input type="text" class="form-control" name="guarantorPhone2" required>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>No. PF</label>
                                    <input type="text" class="form-control" name="guarantorPF2" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>No. Anggota</label>
                                    <input type="text" class="form-control" name="guarantorMemberNo2" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="mt-3">
                    <button type="button" class="btn btn-secondary prev-step">Kembali</button>
                    <button type="button" class="btn btn-success next-step">Seterusnya</button>
                </div>
            </div>

            <!-- Step 4: Pengesahan Majikan -->
            <div class="form-step" id="step4">
                <div class="section-header">
                        <h4>Pengesahan Majikan</h4>
                    </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="employerName" class="form-label">Nama Majikan (Seperti Dalam K/P)</label>
                        <input type="text" class="form-control" id="employerName" name="employerName" required>
                    </div>
                    <div class="col-md-6">
                        <label for="employerIC" class="form-label">No. Kad Pengenalan</label>
                        <input type="text" class="form-control" id="employerIC" name="employerIC" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="basicSalary" class="form-label">Gaji Pokok Sebulan Kakitangan</label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" step="0.01" class="form-control" id="basicSalary" name="basicSalary" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="netSalary" class="form-label">Gaji Bersih Sebulan Kakitangan</label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" step="0.01" class="form-control" id="netSalary" name="netSalary" required>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="netSalaryFile" class="form-label">Lampiran Slip Gaji</label>
                        <div class="mb-2">
                            <small class="text-grey">
                                <i class="fas fa-info-circle"></i> 
                                Nota: Sila pastikan slip gaji TELAH DISAHKAN sebelum dimuat naik
                            </small>
                        </div>
                        <div class="input-group">
                            <input type="file" 
                                   class="form-control" 
                                   id="netSalaryFile" 
                                   name="netSalaryFile" 
                                   accept=".pdf"
                                   max-size="5120"
                                   required>
                            <a href="img\slipgaji.pdf" 
                               class="btn btn-outline-secondary" 
                               target="_blank">
                                <i class="fas fa-eye"></i> Lihat Contoh
                            </a>
                        </div>
                        <div class="invalid-feedback">
                            Sila muat naik slip gaji yang TELAH DISAHKAN
                        </div>
                    </div>
                </div>

                <!-- Agreement Section -->
                <div class="agreement-section mt-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Terma dan Syarat</h5>
                            
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
                                    Saya bersetuju menerima sebarang keputusan dari KOPERASI ini untuk menolak permohonan tanpa memberi sebarang alasan.
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="button-group mt-3">
                        <button type="button" class="btn btn-secondary prev-step">Kembali</button>
                        <button type="submit" class="btn btn-primary">Hantar Permohonan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
/* Main Container Styling */
.container {
    max-width: 1200px;
    margin: 30px auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Header Styling */
.loan-header {
    background: linear-gradient(135deg, #5CBA9B 0%, #3d8b6f 100%);
    padding: 20px 30px;
    border-radius: 12px 12px 0 0;
}

.loan-header h1 {
    color: white;
    font-size: 2.2rem;
    margin: 0;
    font-weight: 600;
}

/* Steps Navigation */
.loan-steps {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2.5rem 4rem;
    background: #fff;
    margin-bottom: 30px;
}

.step-item {
    flex: 1;
    text-align: center;
    position: relative;
}

.step-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #f0f0f0;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    font-weight: 600;
    font-size: 1.2rem;
    position: relative;
    z-index: 2;
    transition: all 0.3s ease;
}

.step-line {
    position: absolute;
    top: 25px;
    left: 50%;
    width: 100%;
    height: 3px;
    background: #e0e0e0;
    transform: translateY(-50%);
    z-index: 1;
}

.step-item:last-child .step-line {
    display: none;
}

.step-label {
    color: #666;
    font-size: 1rem;
    font-weight: 500;
    margin-top: 0.8rem;
    transition: all 0.3s ease;
}

/* Active State */
.step-item.active .step-circle {
    background: #5CBA9B;
    color: white;
    box-shadow: 0 0 0 4px rgba(92,186,155,0.2);
    transform: scale(1.1);
}

/* Completed State */
.step-item.completed .step-circle {
    background: #5CBA9B;
    color: white;
}

.step-item.completed .step-line {
    background: #5CBA9B;
}

/* Responsive Design */
@media (max-width: 768px) {
    .progress-steps {
        padding: 2rem 1.5rem;
    }

    .step-circle {
        width: 45px;
        height: 45px;
        font-size: 1.1rem;
    }

    .step-label {
        font-size: 0.9rem;
    }

    .loan-header h1 {
        font-size: 1.5rem;
    }
}

/* Animation */
.step-item {
    transition: all 0.3s ease;
}

.step-item:hover .step-circle {
    transform: scale(1.1);
    box-shadow: 0 0 0 3px rgba(92,186,155,0.1);
}

/* Form Styling */
.form-section {
    padding: 30px;
    background: #fff;
}

.form-group {
    margin-bottom: 25px;
}

.form-label {
    font-weight: 500;
    color: #333;
    margin-bottom: 8px;
}

.form-control {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 12px 15px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #5CBA9B;
    box-shadow: 0 0 0 0.2rem rgba(92,186,155,0.25);
}

/* Button Styling */
.btn {
    padding: 12px 25px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-success {
    background-color: #5CBA9B;
    border-color: #5CBA9B;
}

.btn-success:hover {
    background-color: #4a9c82;
    border-color: #4a9c82;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(92,186,155,0.2);
}

/* Card Styling */
.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 25px;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #eee;
    padding: 15px 20px;
    border-radius: 12px 12px 0 0;
}

.card-body {
    padding: 20px;
}

/* Input Group Styling */
.input-group-text {
    background-color: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 8px 0 0 8px;
}

/* File Upload Styling */
.custom-file-input {
    cursor: pointer;
}

.custom-file-label {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 12px 15px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .loan-steps {
        padding: 15px 20px;
    }
    
    .step-title {
        font-size: 0.85rem;
    }
}

/* Agreement Section Styling */
.agreement-section .card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}

.agreement-section .card-title {
    color: #2c3e50;
    font-weight: 600;
}

.form-check {
    padding: 1rem;
    border-radius: 8px;
    background-color: #f8f9fa;
    margin-bottom: 1rem;
    transition: all 0.2s ease;
}

.form-check:hover {
    background-color: #e9ecef;
}

.form-check-input {
    width: 1.2em;
    height: 1.2em;
    margin-top: 0.2em;
}

.form-check-label {
    color: #495057;
    font-size: 0.95rem;
    margin-left: 0.5rem;
    line-height: 1.5;
}

.btn-success {
    background-color: #5CBA9B;
    border-color: #5CBA9B;
    font-weight: 500;
    padding: 1rem;
}

.btn-success:hover {
    background-color: #4a9c82;
    border-color: #4a9c82;
    transform: translateY(-1px);
}

.btn-secondary {
    font-weight: 500;
    padding: 1rem;
}

@media (max-width: 768px) {
    .form-check-label {
        font-size: 0.9rem;
    }
}

/* Success Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}

.modal-dialog {
    margin: 15% auto;
    width: 80%;
}

.modal-content {
    background-color: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.modal-header {
    background-color: #5CBA9B;
    color: #fff;
    border-bottom: none;
}

.modal-title {
    font-weight: 600;
}

.close {
    color: #fff;
}

.close:hover {
    color: #ccc;
}

.modal-body {
    padding: 20px;
}

.success-icon {
    animation: scaleIn 0.3s ease-in-out;
}

@keyframes scaleIn {
    from {
        transform: scale(0.5);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.loan-container {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 2rem;
}

.loan-header {
    background: linear-gradient(135deg, #5CBA9B 0%, #3d8b6f 100%);
    padding: 2rem;
    text-align: center;
}

.loan-header h1 {
    color: white;
    margin: 0;
    font-size: 2rem;
    font-weight: 600;
}

.progress-steps {
    display: flex;
    justify-content: space-between;
    padding: 2.5rem 4rem;
    background: #fff;
}

.step-item {
    flex: 1;
    text-align: center;
    position: relative;
}

.step-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #f0f0f0;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    font-weight: 600;
    font-size: 1.2rem;
    position: relative;
    z-index: 2;
    transition: all 0.3s ease;
}

.step-line {
    position: absolute;
    top: 25px;
    left: 50%;
    width: 100%;
    height: 3px;
    background: #e0e0e0;
    transform: translateY(-50%);
    z-index: 1;
}

.step-item:last-child .step-line {
    display: none;
}

.step-label {
    color: #666;
    font-size: 1rem;
    font-weight: 500;
    margin-top: 0.8rem;
    transition: all 0.3s ease;
}

/* Active State */
.step-item.active .step-circle {
    background: #5CBA9B;
    color: white;
    box-shadow: 0 0 0 4px rgba(92,186,155,0.2);
    transform: scale(1.1);
}

.step-item.active .step-label {
    color: #5CBA9B;
    font-weight: 600;
}

/* Completed State */
.step-item.completed .step-circle {
    background: #5CBA9B;
    color: white;
}

.step-item.completed .step-line {
    background: #5CBA9B;
}

/* Responsive Design */
@media (max-width: 768px) {
    .progress-steps {
        padding: 2rem 1.5rem;
    }

    .step-circle {
        width: 45px;
        height: 45px;
        font-size: 1.1rem;
    }

    .step-label {
        font-size: 0.9rem;
    }

    .loan-header h1 {
        font-size: 1.5rem;
    }
}

/* Animation */
.step-item {
    transition: all 0.3s ease;
}

.step-item:hover .step-circle {
    transform: scale(1.1);
    box-shadow: 0 0 0 3px rgba(92,186,155,0.1);
}

/* Section Styling */
.guarantor-section {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
    padding: 1rem;
}

.section-header {
    background-color: #ffe4e4;  /* Light pink background */
    padding: 15px 20px;
    border-radius: 8px 8px 0 0;
    border-bottom: 1px solid #f8d7d7;
    margin-bottom: 20px;
}

.section-header h4 {
    margin: 0;
    color: #333;
    font-size: 1.1rem;
    font-weight: 600;
    text-transform: uppercase;
}

.form-container {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 1.5rem;
}

.form-title {
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e0e0e0;
}

.form-title h5 {
    color: #2c3e50;
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
}

.form-row {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1rem;
}

.form-group {
    flex: 1;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #4a5568;
    font-size: 0.9rem;
    font-weight: 500;
}

.form-control {
    width: 100%;
    height: 38px;
    padding: 0.375rem 0.75rem;
    font-size: 0.9rem;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background-color: #fff;
}

.input-group {
    display: flex;
}

.input-group-text {
    padding: 0.375rem 0.75rem;
    background: #f8f9fa;
    border: 1px solid #e2e8f0;
    border-right: none;
    border-radius: 6px 0 0 6px;
    color: #4a5568;
}

.input-group .form-control {
    border-radius: 0 6px 6px 0;
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
        gap: 1rem;
    }

    .form-container {
        padding: 1rem;
    }
}

.modal-content {
    border: none;
    border-radius: 12px;
}

.success-icon {
    animation: scaleIn 0.3s ease-in-out;
}

.btn-success {
    background-color: #5CBA9B;
    border: none;
    padding: 0.75rem 2.5rem;
    font-size: 1rem;
    font-weight: 500;
    border-radius: 8px;
}

.btn-success:hover {
    background-color: #4ea085;
}

@keyframes scaleIn {
    from {
        transform: scale(0.5);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

/* Button styling */
.button-group {
    display: flex;
    gap: 15px;
    margin-top: 20px;
    padding: 20px 0;
}

.btn {
    padding: 12px 24px;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    text-align: center;
}

.btn-secondary {
    background-color: #e4a7a7;
    color: white;
    width: auto;
    min-width: 120px;
}

.btn-secondary:hover {
    background-color: #d89595;
}

.btn-success {
    background-color: #5CBA9B;
    color: white;
    width: auto;
    min-width: 120px;
}

.btn-success:hover {
    background-color: #4ea085;
}

/* Form step visibility */
.form-step {
    display: none;
}

.form-step.active {
    display: block;
}

/* Button styling */
.button-group {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.btn {
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 0.95rem;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-secondary {
    background-color: #e4a7a7;
    color: white;
    width: auto;
    min-width: 120px;
}

.btn-secondary:hover {
    background-color: #d89595;
}

.btn-success {
    background-color: #5CBA9B;
    color: white;
    width: auto;
    min-width: 120px;
}

.btn-success:hover {
    background-color: #4ea085;
}

.custom-popup-class {
    border-radius: 10px !important;
    padding: 20px !important;
}

.custom-confirm-button:hover {
    background-color: #4BA98B !important;
}

/* Remove SweetAlert2 focus outline */
.swal2-popup:focus {
    outline: none !important;
}
</style>
<script>
$(document).ready(function() {
    let currentStep = 1;
    const totalSteps = 4;

    function updateSteps(step) {
        // Update step indicators
        $('.step-item').removeClass('active completed');
        for(let i = 1; i <= totalSteps; i++) {
            if(i < step) {
                $(`.step-item:nth-child(${i})`).addClass('completed');
            } else if(i === step) {
                $(`.step-item:nth-child(${i})`).addClass('active');
            }
        }

        // Show/hide form steps
        $('.form-step').hide(); // Hide all steps
        $(`#step${step}`).fadeIn(); // Show current step
    }

    // Next button click
    $('.next-step').click(function(e) {
        e.preventDefault();
        
        // Get current step
        const currentStep = $(this).closest('.form-step');
        const stepNumber = parseInt(currentStep.attr('id').replace('step', ''));
        
        // Check required fields
        let canProceed = true;
        currentStep.find('[required]').each(function() {
            if (!$(this).val()) {
                canProceed = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        // Show error if fields are empty
        if (!canProceed) {
            Swal.fire({
                title: 'Ralat!',
                text: 'Sila lengkapkan semua maklumat yang diperlukan.',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#5CBA9B'
            });
            return false; // Stop here and don't proceed
        }
        
        // Additional validation for guarantor step (step 3)
        if (stepNumber === 3) {
            const ic1Valid = $('#guarantorIC1').hasClass('is-valid');
            const ic2Valid = $('#guarantorIC2').hasClass('is-valid');
            
            if (!ic1Valid || !ic2Valid) {
                Swal.fire({
                    title: 'Ralat!',
                    text: 'Penjamin ini bukan ahli Koperasi KADA yang sah.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#5CBA9B'
                });
                return false; // Stop here and don't proceed
            }
        }
        
        // Only if all validations pass, proceed to next step
        if (canProceed) {
            currentStep.hide();
            $(`#step${stepNumber + 1}`).show();
            updateProgressBar(stepNumber + 1);
        }
    });

    // Previous button click
    $('.prev-step').click(function(e) {
        e.preventDefault();
        const currentStep = $(this).closest('.form-step');
        const stepNumber = parseInt(currentStep.attr('id').replace('step', ''));
        
        // Go to previous step
        currentStep.hide();
        $(`#step${stepNumber - 1}`).show();
        updateProgressBar(stepNumber - 1);
    });

    // Initialize first step
    updateSteps(1);
});
</script>

<!-- Add SweetAlert2 library -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Add file size validation
    const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB in bytes
    
    function validateFileSize(file) {
        if (file && file.size > MAX_FILE_SIZE) {
            return false;
        }
        return true;
    }

    $('#loanForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate file sizes before submission
        const netSalaryFile = $('#netSalaryFile')[0].files[0];
        
        if (!validateFileSize(netSalaryFile)) {
            Swal.fire({
                title: 'Ralat!',
                text: 'Saiz fail tidak boleh melebihi 5MB',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Show loading state
        Swal.fire({
            title: 'Sila Tunggu',
            text: 'Sedang memproses permohonan anda...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Create new FormData object
        let formData = new FormData(this);

        // Submit form via AJAX with timeout
        fetch('loanApplicationProcess.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Response:', data);
            if (data.status === 'success') {
                Swal.fire({
                    html: `
                        <div style="padding: 20px;">
                            <div style="width: 60px; height: 60px; background-color: #F0F9F4; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" fill="#5CBA9B"/>
                                </svg>
                            </div>
                            <h2 style="color: #333; font-size: 24px; margin-bottom: 10px;">Berjaya!</h2>
                            <p style="color: #666; font-size: 16px; margin-bottom: 20px;">Permohonan berjaya dihantar!</p>
                            <button class="custom-confirm-button" style="background-color: #5CBA9B; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 14px;">Ke Status Permohonan</button>
                        </div>
                    `,
                    showConfirmButton: false,
                    width: 400,
                    background: '#ffffff',
                    customClass: {
                        popup: 'custom-popup-class'
                    }
                }).then(() => {
                    window.location.href = 'membermainpage.php';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Ralat!',
                    text: data.message,
                    confirmButtonColor: '#5CBA9B'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error); // Debug log
            Swal.fire({
                icon: 'error',
                title: 'Ralat!',
                text: 'Ralat semasa memproses permohonan.',
                confirmButtonColor: '#5CBA9B'
            });
        });
    });
});
</script>

<script>
$(document).ready(function() {
    // Function to calculate monthly payment with interest
    function calculateMonthlyPayment() {
        const loanAmount = parseFloat($('#jumlah_pinjaman').val()) || 0;
        const loanTerm = parseFloat($('#tempoh_pembayaran').val()) || 0;
        const annualInterestRate = parseFloat($('#kadar_faedah_value').val()) || 0;
        
        if (loanAmount > 0 && loanTerm > 0) {
            // Convert annual rate to monthly rate (divide by 12 months and 100 for percentage)
            const monthlyRate = (annualInterestRate / 100) / 12;
            
            // Calculate monthly payment using loan amortization formula
            // Formula: PMT = P * (r * (1 + r)^n) / ((1 + r)^n - 1)
            // Where: P = Principal, r = Monthly Interest Rate, n = Number of Payments
            
            const numerator = monthlyRate * Math.pow(1 + monthlyRate, loanTerm);
            const denominator = Math.pow(1 + monthlyRate, loanTerm) - 1;
            
            let monthlyPayment;
            if (monthlyRate === 0) {
                // If no interest, simple division
                monthlyPayment = loanAmount / loanTerm;
            } else {
                // With interest calculation
                monthlyPayment = loanAmount * (numerator / denominator);
            }
            
            // Round to 2 decimal places
            monthlyPayment = Math.round(monthlyPayment * 100) / 100;
            
            // Update the monthly payment field
            $('#ansuran_bulanan').val(monthlyPayment.toFixed(2));
            
            // Optionally show total interest and total payment
            const totalPayment = monthlyPayment * loanTerm;
            const totalInterest = totalPayment - loanAmount;
            
            // If you want to display total interest and payment somewhere
            if ($('#total_interest').length) {
                $('#total_interest').text('RM ' + totalInterest.toFixed(2));
                $('#total_payment').text('RM ' + totalPayment.toFixed(2));
            }
        } else {
            $('#ansuran_bulanan').val('');
            if ($('#total_interest').length) {
                $('#total_interest').text('RM 0.00');
                $('#total_payment').text('RM 0.00');
            }
        }
    }

    // Add event listeners to recalculate when amount or period changes
    $('#jumlah_pinjaman, #tempoh_pembayaran').on('input', calculateMonthlyPayment);
});
</script>

<script>
$(document).ready(function() {
    // Function to validate guarantor
    function validateGuarantor(icNumber, guarantorNum) {
        $.ajax({
            url: 'check_guarantor.php',
            type: 'POST',
            data: { ic: icNumber },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    const inputField = $(`#guarantorIC${guarantorNum}`);
                    const nameField = $(`input[name="guarantorName${guarantorNum}"]`);
                    
                    if (result.valid) {
                        inputField.removeClass('is-invalid').addClass('is-valid');
                        nameField.val(result.name);
                        nameField.prop('readonly', true);
                        
                        // Enable Seterusnya button if both ICs are valid
                        if ($('#guarantorIC1').hasClass('is-valid') && $('#guarantorIC2').hasClass('is-valid')) {
                            $('.btn-success').prop('disabled', false);
                        }
                    } else {
                        inputField.removeClass('is-valid').addClass('is-invalid');
                        nameField.val('');
                        nameField.prop('readonly', false);
                        $('.btn-success').prop('disabled', true);
                        
                        Swal.fire({
                            title: 'Ralat!',
                            text: 'Penjamin ini bukan ahli Koperasi KADA yang sah.',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#5CBA9B'
                        });
                    }
                } catch (e) {
                    console.error('Error:', e);
                }
            }
        });
    }

    // Add event listeners for IC input fields
    $('#guarantorIC1, #guarantorIC2').on('change', function() {
        const guarantorNum = this.id.slice(-1);
        validateGuarantor($(this).val(), guarantorNum);
    });
});
</script>

<script>
// Function to show specific step and update progress
function showStep(stepNumber) {
    // Hide all steps first
    const steps = document.querySelectorAll('.form-step');
    steps.forEach(step => {
        step.style.display = 'none';
    });

    // Show the target step
    const targetStep = document.getElementById('step' + stepNumber);
    if (targetStep) {
        targetStep.style.display = 'block';
    }

    // Update progress indicator
    updateProgress(stepNumber);
}

// Function to update progress indicator
function updateProgress(stepNumber) {
    const progressSteps = document.querySelectorAll('.step-item');
    progressSteps.forEach((step, index) => {
        if (index + 1 <= stepNumber) {
            step.classList.add('active');
        } else {
            step.classList.remove('active');
        }
    });
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Show step 4 initially (since we're on the final step)
    showStep(4);
});
</script>

<script>
document.getElementById('loanForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (this.checkValidity()) {
        // Submit form data
        this.submit();
    }
});
</script>

<script>
// Add form submission handling
document.getElementById('submitBtn').addEventListener('click', function(e) {
    // Prevent double submission
    this.disabled = true;
    this.form.submit();
});
</script>

<script>
// Add this validation function
function validateStep(stepNumber) {
    const currentStep = document.getElementById(`step${stepNumber}`);
    const requiredFields = currentStep.querySelectorAll('[required]');
    let isValid = true;

    // Check each required field
    requiredFields.forEach(field => {
        if (!field.value || field.value.trim() === '') {
            isValid = false;
            field.classList.add('is-invalid');
            
            // Add error message if not exists
            if (!field.nextElementSibling?.classList.contains('invalid-feedback')) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'Sila isi ruangan ini';
                field.parentNode.appendChild(errorDiv);
            }
        }
    });

    if (!isValid) {
        Swal.fire({
            // title: 'Ralat!',
            text: 'Sila lengkapkan semua maklumat yang diperlukan.',
            // icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#5CBA9B'
        });
        return false;
    }

    return true;
}

// Replace your existing next-step button click handler
$('.next-step').click(function(e) {
    e.preventDefault();
    const currentStep = $(this).closest('.form-step');
    const stepNumber = parseInt(currentStep.attr('id').replace('step', ''));
    
    if (validateStep(stepNumber)) {
        currentStep.hide();
        $(`#step${stepNumber + 1}`).show();
        updateProgressBar(stepNumber + 1);
    }
});
</script>

<script>
function updateProgressBar(stepNumber) {
    // Remove all active and completed classes first
    $('.step-item').removeClass('active completed');
    
    // Update all steps up to current
    for(let i = 1; i <= 4; i++) {
        if(i < stepNumber) {
            // Previous steps should be marked as completed
            $(`.step-item:nth-child(${i})`).addClass('completed');
        } else if(i === stepNumber) {
            // Current step should be marked as active
            $(`.step-item:nth-child(${i})`).addClass('active');
        }
    }
}

// Add this to your existing click handlers
$('.next-step').click(function(e) {
    e.preventDefault();
    const currentStep = $(this).closest('.form-step');
    const stepNumber = parseInt(currentStep.attr('id').replace('step', ''));
    
    // Check if all required fields in current step are filled
    const requiredFields = currentStep.find('[required]');
    let isValid = true;
    
    requiredFields.each(function() {
        if (!$(this).val()) {
            isValid = false;
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    if (!isValid) {
        Swal.fire({
            title: 'Ralat!',
            text: 'Sila lengkapkan semua maklumat yang diperlukan.',
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#5CBA9B'
        });
        return false;
    }
    
    // Additional validation for guarantor step
    if (stepNumber === 3) {
        const ic1Valid = $('#guarantorIC1').hasClass('is-valid');
        const ic2Valid = $('#guarantorIC2').hasClass('is-valid');
        
        if (!ic1Valid || !ic2Valid) {
            Swal.fire({
                title: 'Ralat!',
                text: 'Penjamin ini bukan ahli Koperasi KADA yang sah.',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#5CBA9B'
            });
            return false;
        }
    }
    
    // If all validations pass, proceed to next step
    if (isValid) {
        currentStep.hide();
        $(`#step${stepNumber + 1}`).show();
        updateProgressBar(stepNumber + 1);
    }
});

$('.prev-step').click(function(e) {
    e.preventDefault();
    const currentStep = $(this).closest('.form-step');
    const stepNumber = parseInt(currentStep.attr('id').replace('step', ''));
    
    // Go to previous step
    currentStep.hide();
    $(`#step${stepNumber - 1}`).show();
    updateProgressBar(stepNumber - 1);
});
</script>

<script>
document.getElementById('loanForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (this.checkValidity()) {
        // Show loading state
        Swal.fire({
            title: 'Sila Tunggu',
            text: 'Sedang memproses permohonan anda...',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData(this);
        
        fetch('loanApplicationProcess.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Response:', data);
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berjaya!',
                    text: data.message,
                    confirmButtonColor: '#5CBA9B',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'membermainpage.php';
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Ralat!',
                    text: data.message,
                    confirmButtonColor: '#5CBA9B'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error); // Debug log
            Swal.fire({
                icon: 'error',
                title: 'Ralat!',
                text: 'Ralat semasa memproses permohonan.',
                confirmButtonColor: '#5CBA9B'
            });
        });
    } else {
        Swal.fire({
            text: 'Sila lengkapkan semua maklumat yang diperlukan.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#5CBA9B'
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get the form and button
    const form = document.getElementById('loanForm');
    const submitButton = document.querySelector('button[type="submit"]');

    // Add click event listener to the submit button
    submitButton.addEventListener('click', function(e) {
        e.preventDefault(); // Prevent default form submission

        // Show confirmation dialog
        Swal.fire({
            title: 'Pengesahan',
            text: 'Adakah anda pasti untuk menghantar permohonan ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5CBA9B',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hantar',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Sila Tunggu',
                    text: 'Sedang memproses permohonan anda...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit the form
                form.submit();
            }
        });
    });
});
</script>
