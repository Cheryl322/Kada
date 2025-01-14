<?php
session_start();
include 'dbconnect.php';
include 'headermember.php';

if (!isset($_SESSION['employeeID'])) {
    header('Location: login.php');
    exit();
}

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
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="container mt-3">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Permohonan Pembiayaan KADA</h4>
        </div>
        
        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="step active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-title">Maklumat Peribadi</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-number">2</div>
                <div class="step-title">Maklumat Pembiayaan</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-number">3</div>
                <div class="step-title">Maklumat Penjamin</div>
            </div>
            <div class="step" data-step="4">
                <div class="step-number">4</div>
                <div class="step-title">Pengesahan Majikan</div>
            </div>
        </div>

        <div class="card-body">
            <form id="loanForm" action="loanApplicationProcess.php" method="POST" enctype="multipart/form-data">
                <!-- Step 1: Maklumat Peribadi -->
                <div class="form-step" id="step1">
                    <h5>Maklumat Peribadi</h5>
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

                    <h6 class="mt-4">Maklumat Bank</h6>
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
                            <label for="bankAccountNo" class="form-label">No. Akaun Bank</label>
                            <input type="text" class="form-control" id="bankAccountNo" name="bankAccountNo" required>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-primary next-step">Seterusnya</button>
                    </div>
                </div>

                <!-- Step 2: Maklumat Pembiayaan -->
                <div class="form-step" id="step2" style="display: none;">
                    <h5>Maklumat Pembiayaan</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="jumlah_pinjaman" class="form-label">Jumlah Pinjaman</label>
                            <div class="input-group">
                                <span class="input-group-text">RM</span>
                                <input type="number" class="form-control" id="jumlah_pinjaman" name="amountRequested" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="tempoh_pembayaran" class="form-label">Tempoh Pembayaran (Bulan)</label>
                            <input type="number" class="form-control" id="tempoh_pembayaran" name="financingPeriod" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="ansuran_bulanan" class="form-label">Ansuran Bulanan</label>
                            <div class="input-group">
                                <span class="input-group-text">RM</span>
                                <input type="number" class="form-control" id="ansuran_bulanan" name="monthlyPayment" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button type="button" class="btn btn-secondary prev-step">Kembali</button>
                        <button type="button" class="btn btn-primary next-step">Seterusnya</button>
                    </div>
                </div>

                <!-- Step 3: Maklumat Penjamin -->
                <div class="form-step" id="step3" style="display: none;">
                    <h5>Maklumat Penjamin</h5>
                    
                    <!-- Guarantor 1 -->
                    <h6 class="mt-3">Butir-butir Penjamin 1</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="guarantorName1" class="form-label">Nama (Seperti Dalam K/P)</label>
                            <input type="text" class="form-control" id="guarantorName1" name="guarantorName1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="guarantorIC1" class="form-label">No. Kad Pengenalan</label>
                            <input type="text" class="form-control" id="guarantorIC1" name="guarantorIC1" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="guarantorPhone1" class="form-label">No. Telefon</label>
                            <div class="input-group">
                                <span class="input-group-text">+60</span>
                                <input type="text" class="form-control" id="guarantorPhone1" name="guarantorPhone1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="guarantorPF1" class="form-label">No. PF</label>
                            <input type="text" class="form-control" id="guarantorPF1" name="guarantorPF1" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="guarantorMemberID1" class="form-label">No. Anggota</label>
                            <input type="text" class="form-control" id="guarantorMemberID1" name="guarantorMemberID1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="guarantorSignature1" class="form-label">Tandatangan (PDF)</label>
                            <input type="file" class="form-control" id="guarantorSignature1" name="guarantorSignature1" accept=".pdf" required>
                        </div>
                    </div>

                    <!-- Guarantor 2 -->
                    <h6 class="mt-4">Butir-butir Penjamin 2</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="guarantorName2" class="form-label">Nama (Seperti Dalam K/P)</label>
                            <input type="text" class="form-control" id="guarantorName2" name="guarantorName2" required>
                        </div>
                        <div class="col-md-6">
                            <label for="guarantorIC2" class="form-label">No. Kad Pengenalan</label>
                            <input type="text" class="form-control" id="guarantorIC2" name="guarantorIC2" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="guarantorPhone2" class="form-label">No. Telefon</label>
                            <div class="input-group">
                                <span class="input-group-text">+60</span>
                                <input type="text" class="form-control" id="guarantorPhone2" name="guarantorPhone2" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="guarantorPF2" class="form-label">No. PF</label>
                            <input type="text" class="form-control" id="guarantorPF2" name="guarantorPF2" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="guarantorMemberID2" class="form-label">No. Anggota</label>
                            <input type="text" class="form-control" id="guarantorMemberID2" name="guarantorMemberID2" required>
                        </div>
                        <div class="col-md-6">
                            <label for="guarantorSignature2" class="form-label">Tandatangan (PDF)</label>
                            <input type="file" class="form-control" id="guarantorSignature2" name="guarantorSignature2" accept=".pdf" required>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-secondary prev-step">Kembali</button>
                        <button type="button" class="btn btn-primary next-step">Seterusnya</button>
                    </div>
                </div>

                <!-- Step 4: Pengesahan Majikan -->
                <div class="form-step" id="step4" style="display: none;">
                    <h5>Pengesahan Majikan</h5>
                    
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
                            <label for="basicSalarySlip" class="form-label">Lampiran Slip Gaji Pokok Kakitangan</label>
                            <input type="file" class="form-control" id="basicSalarySlip" name="basicSalarySlip" accept=".pdf" required>
                            <small class="form-text text-muted">Sila lampirkan slip gaji pokok dalam format PDF</small>
                        </div>
                        <div class="col-md-6">
                            <label for="netSalarySlip" class="form-label">Lampiran Slip Gaji Bersih Kakitangan</label>
                            <input type="file" class="form-control" id="netSalarySlip" name="netSalarySlip" accept=".pdf" required>
                            <small class="form-text text-muted">Sila lampirkan slip gaji bersih dalam format PDF</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="employerSignature" class="form-label">Tandatangan Sah/Cop (PDF)</label>
                            <input type="file" class="form-control" id="employerSignature" name="employerSignature" accept=".pdf" required>
                            <small class="form-text text-muted">Sila lampirkan tandatangan dalam format PDF</small>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="agreement1" name="agreement1" required>
                            <label class="form-check-label" for="agreement1">
                                Saya mengesahkan bahawa semua maklumat yang diberikan dalam borang ini adalah tepat dan benar. Saya faham bahawa sebarang maklumat palsu yang diberikan boleh menyebabkan permohonan ditolak dan tindakan undang-undang boleh diambil.
                            </label>
                        </div>

                        <div class="form-check mb-3">
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

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-secondary prev-step mb-2">Kembali</button>
                            <button type="submit" class="btn btn-success btn-lg" id="submitBtn" style="background-color: #75B798; border-color: #75B798;">
                                Hantar Permohonan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.form-control[readonly] {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.progress-steps {
    display: flex;
    justify-content: space-between;
    margin: 20px;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 8px;
}

.step {
    text-align: center;
    flex: 1;
}

.step.active {
    color: #0d6efd;
    font-weight: bold;
}

.step-number {
    width: 30px;
    height: 30px;
    line-height: 30px;
    border-radius: 50%;
    background-color: #dee2e6;
    margin: 0 auto 10px;
}

.step.active .step-number {
    background-color: #0d6efd;
    color: white;
}

.form-step {
    transition: all 0.3s ease;
}

.step {
    position: relative;
}

.step::after {
    content: '';
    position: absolute;
    top: 15px;
    left: 60%;
    width: 80%;
    height: 2px;
    background-color: #dee2e6;
}

.step:last-child::after {
    display: none;
}

.step.active::after {
    background-color: #0d6efd;
}

.input-group-text {
    background-color: #f8f9fa;
}

.form-text {
    font-size: 0.875em;
    color: #6c757d;
}

.form-check-input:checked {
    background-color: #75B798;
    border-color: #75B798;
}

.form-check-label {
    color: #6c757d;
    font-size: 0.95rem;
    line-height: 1.5;
}

.btn-success:hover {
    background-color: #5c9178 !important;
    border-color: #5c9178 !important;
}

.form-check {
    padding-left: 2rem;
}

.form-check-input {
    margin-left: -1.5rem;
}

.is-invalid {
    border-color: #dc3545 !important;
}

.is-invalid:focus {
    box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
}
</style>

<script>
$(document).ready(function() {
    let currentStep = 1;
    const totalSteps = 4;

    // Next button click
    $('.next-step').click(function() {
        console.log('Current step:', currentStep); // Debug log
        
        if (currentStep < totalSteps) {
            // Validate current step
            if (validateStep(currentStep)) {
                $(`#step${currentStep}`).hide();
                currentStep++;
                $(`#step${currentStep}`).show();
                updateProgressBar();
            } else {
                Swal.fire({
                    title: 'Perhatian!',
                    text: 'Sila isi semua maklumat yang diperlukan.',
                    icon: 'warning',
                    confirmButtonColor: '#75B798'
                });
            }
        }
    });

    // Previous button click
    $('.prev-step').click(function() {
        if (currentStep > 1) {
            $(`#step${currentStep}`).hide();
            currentStep--;
            $(`#step${currentStep}`).show();
            updateProgressBar();
        }
    });

    // Update progress bar
    function updateProgressBar() {
        $('.step').removeClass('active');
        for (let i = 1; i <= currentStep; i++) {
            $(`.step[data-step="${i}"]`).addClass('active');
        }
    }

    // Validate each step
    function validateStep(step) {
        let valid = true;
        const inputs = $(`#step${step} input[required], #step${step} select[required]`);
        
        inputs.each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                valid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Debug log
        console.log('Step validation:', step, valid);
        return valid;
    }

    // Remove invalid class on input change
    $('input, select').on('change', function() {
        $(this).removeClass('is-invalid');
    });

    // Initialize first step
    updateProgressBar();

    // Calculate monthly payment when loan amount or period changes
    $('#jumlah_pinjaman, #tempoh_pembayaran').on('input', function() {
        calculateMonthlyPayment();
    });

    function calculateMonthlyPayment() {
        const loanAmount = parseFloat($('#jumlah_pinjaman').val()) || 0;
        const loanPeriod = parseInt($('#tempoh_pembayaran').val()) || 0;
        
        if (loanAmount > 0 && loanPeriod > 0) {
            // Simple calculation: loan amount divided by period
            // You can modify this formula based on your interest rate requirements
            const monthlyPayment = loanAmount / loanPeriod;
            
            // Round to 2 decimal places
            const roundedPayment = Math.round(monthlyPayment * 100) / 100;
            
            // Update the monthly payment field
            $('#ansuran_bulanan').val(roundedPayment);
        } else {
            $('#ansuran_bulanan').val('');
        }
    }
});
</script>

<!-- Add SweetAlert2 library -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.getElementById('loanForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Show loading message
    Swal.fire({
        title: 'Sila Tunggu',
        text: 'Sedang memproses permohonan anda...',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });

    // Submit the form
    this.submit();
});
</script>