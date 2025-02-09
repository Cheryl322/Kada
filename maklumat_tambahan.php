<?php
ob_start();
session_start();

require_once 'email_helper.php';

include "headermember.php";
include "dbconnect.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug function
function debug_to_console($data) {
    echo "<script>console.log('Debug: " . json_encode($data) . "');</script>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        mysqli_begin_transaction($conn);

        // Get employeeID from session
        $employeeID = $_SESSION['employeeID'];
        
        // 1. Save Fees and Contribution
        $insertFees = "INSERT INTO tb_memberregistration_feesandcontribution 
                      (employeeID, entryFee, modalShare, feeCapital, deposit, contribution, fixedDeposit) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE 
                      entryFee = VALUES(entryFee),
                      modalShare = VALUES(modalShare),
                      feeCapital = VALUES(feeCapital),
                      deposit = VALUES(deposit),
                      contribution = VALUES(contribution),
                      fixedDeposit = VALUES(fixedDeposit)";

        $stmt = mysqli_prepare($conn, $insertFees);
        
        // Convert form values to integers and handle empty values
        $entryFee = empty($_POST['fee_masuk']) ? 0 : (int)$_POST['fee_masuk'];
        $modalShare = empty($_POST['modal_syer']) ? 0 : (int)$_POST['modal_syer'];
        $feeCapital = empty($_POST['modal_yuran']) ? 0 : (int)$_POST['modal_yuran'];
        $deposit = empty($_POST['wang_deposit']) ? 0 : (int)$_POST['wang_deposit'];
        $contribution = empty($_POST['sumbangan_tabung']) ? 0 : (int)$_POST['sumbangan_tabung'];
        $fixedDeposit = empty($_POST['simpanan_tetap']) ? 0 : (int)$_POST['simpanan_tetap'];

        mysqli_stmt_bind_param($stmt, "iiiiiii", 
            $employeeID,
            $entryFee,
            $modalShare,
            $feeCapital,
            $deposit,
            $contribution,
            $fixedDeposit
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error saving fees");
        }

        // 2. Save Family Member Information
        // First delete existing records for this employee
        $deleteFamily = "DELETE FROM tb_memberregistration_familymemberinfo WHERE employeeID = ?";
        $stmt = mysqli_prepare($conn, $deleteFamily);
        mysqli_stmt_bind_param($stmt, "i", $employeeID);
        mysqli_stmt_execute($stmt);

        // Then insert new family members
        if (isset($_POST['hubungan']) && is_array($_POST['hubungan'])) {
            $insertFamily = "INSERT INTO tb_memberregistration_familymemberinfo 
                           (employeeID, relationship, name, icFamilyMember) 
                           VALUES (?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $insertFamily);

            foreach ($_POST['hubungan'] as $i => $hubungan) {
                if (empty($hubungan) || empty($_POST['nama_waris'][$i]) || empty($_POST['no_kp_waris'][$i])) {
                    continue;
                }

                mysqli_stmt_bind_param($stmt, "isss",
                    $employeeID,
                    $_POST['hubungan'][$i],
                    $_POST['nama_waris'][$i],
                    $_POST['no_kp_waris'][$i]
                );

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Error saving family member");
                }
            }
        }

        // 3. Get user's email from database
        $emailQuery = "SELECT email FROM tb_member WHERE employeeID = ?";
        $stmt = mysqli_prepare($conn, $emailQuery);
        mysqli_stmt_bind_param($stmt, "i", $employeeID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $userEmail = mysqli_fetch_assoc($result)['email'];

        // 4. Try to send email
        try {
            $emailHelper = new EmailHelper();
            $emailData = [
                'fee_masuk' => $entryFee,
                'modal_syer' => $modalShare,
                'modal_yuran' => $feeCapital,
                'wang_deposit' => $deposit,
                'sumbangan_tabung' => $contribution,
                'simpanan_tetap' => $fixedDeposit
            ];
            
            $emailHelper->sendRegistrationEmail($userEmail, $emailData);
            $_SESSION['success_message'] = 'Pendaftaran anda telah berjaya disimpan dan email pengesahan telah dihantar.';
            $_SESSION['email_sent'] = true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            $_SESSION['success_message'] = 'Pendaftaran anda telah berjaya disimpan tetapi email pengesahan tidak dapat dihantar.';
        }
        
        mysqli_commit($conn);
        ob_end_clean();
        header('Location: success.php');
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error_message'] = $e->getMessage();
        ob_end_clean();
        header('Location: success.php');
        exit();
    }
}

// Add this at the bottom to show any errors
if (isset($conn->error) && $conn->error) {
    echo "<div class='alert alert-danger'>Database Error: " . $conn->error . "</div>";
}

// Add this at the bottom of your form to show current session data
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

.is-invalid {
    border-color: #dc3545 !important;
}

.invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 80%;
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

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="registrationForm" onsubmit="return validateForm()">
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
                            <td><input type="number" name="fee_masuk" class="form-control" value="50" readonly></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>MODAL SYER *</td>
                            <td><input type="number" name="modal_syer" class="form-control" min="300" step="1"></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>MODAL YURAN</td>
                            <td><input type="number" name="modal_yuran" class="form-control" min="35" step="1"></td>
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
                    <input type="checkbox" class="form-check-input" id="agree" name="agree" required>
                    <label class="form-check-label" for="agree">Saya mengesahkan semua maklumat adalah benar</label>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="daftar_ahli.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" name="submit" class="btn btn-primary" id="submitBtn">
                        Hantar Pendaftaran <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Add this hidden field -->
        <input type="hidden" name="debug" value="1">
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

    const form = document.querySelector('form');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
        // Form will submit normally to the same page
    });
});

function validateForm() {
    // Debug: Log form data
    const formData = new FormData(document.getElementById('registrationForm'));
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }

    // Get all form values
    const fees = {
        fee_masuk: document.querySelector('input[name="fee_masuk"]').value,
        modal_syer: document.querySelector('input[name="modal_syer"]').value,
        modal_yuran: document.querySelector('input[name="modal_yuran"]').value,
        wang_deposit: document.querySelector('input[name="wang_deposit"]').value,
        sumbangan_tabung: document.querySelector('input[name="sumbangan_tabung"]').value,
        simpanan_tetap: document.querySelector('input[name="simpanan_tetap"]').value
    };

    // Debug: Log fees data
    console.log('Fees data:', fees);

    // Get family members data
    const familyMembers = [];
    const rows = document.querySelectorAll('#familyTable tbody tr');
    rows.forEach((row, index) => {
        if (index === 0) return; // Skip header row
        const member = {
            hubungan: row.querySelector('select[name="hubungan[]"]').value,
            nama: row.querySelector('input[name="nama_waris[]"]').value,
            ic: row.querySelector('input[name="no_kp_waris[]"]').value
        };
        familyMembers.push(member);
    });

    // Debug: Log family members data
    console.log('Family members:', familyMembers);

    // Validate fee masuk is 50
    if (parseInt(fees.fee_masuk) !== 50) {
        alert('Fee masuk mestilah RM50');
        return false;
    }

    // Validate modal yuran is at least 35
    if (parseInt(fees.modal_yuran) < 35) {
        alert('Modal yuran mestilah minimum RM35');
        return false;
    }

    return true;
}
</script>

</body>
</html>
<?php include "footer.php"; ?> 