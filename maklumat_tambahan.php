<?php
session_start();
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
        // Debug: Print POST data
        echo "<div class='alert alert-info'>";
        echo "POST Data received:<br>";
        print_r($_POST);
        echo "</div>";
        
        $employeeID = $_SESSION['personal_info']['no_anggota'];
        
        // Debug: Print the query we're about to execute
        echo "<div class='alert alert-info'>";
        echo "Checking member with ID: " . $employeeID;
        echo "</div>";
        
        // First check if member exists
        $checkMember = "SELECT * FROM tb_member WHERE employeeID = ?";
        $stmt = $conn->prepare($checkMember);
        $stmt->bind_param("i", $employeeID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            echo "<div class='alert alert-warning'>Member not found, creating new member record...</div>";
            
            // Insert into tb_member first
            $insertMember = "INSERT INTO tb_member (employeeID) VALUES (?)";
            $stmt = $conn->prepare($insertMember);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("i", $employeeID);
            if (!$stmt->execute()) {
                throw new Exception("Failed to create member record: " . $conn->error);
            }
            echo "<div class='alert alert-success'>Member record created successfully!</div>";
        }
        
        // Now try to insert fees
        echo "<div class='alert alert-info'>Attempting to insert fees...</div>";
        
        $insertFees = "INSERT INTO tb_memberregistration_feesandcontribution 
                      (employeeID, entryFee, modalShare, feeCapital, deposit, contribution, fixedDeposit, others) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE
                      entryFee = VALUES(entryFee),
                      modalShare = VALUES(modalShare),
                      feeCapital = VALUES(feeCapital),
                      deposit = VALUES(deposit),
                      contribution = VALUES(contribution),
                      fixedDeposit = VALUES(fixedDeposit),
                      others = VALUES(others)";
        
        $stmt = $conn->prepare($insertFees);
        if (!$stmt) {
            throw new Exception("Prepare failed for fees insert: " . $conn->error);
        }
        
        // Get and validate form values
        $entryFee = isset($_POST['fee_masuk']) ? intval($_POST['fee_masuk']) : 0;
        $modalShare = isset($_POST['modal_syer']) ? intval($_POST['modal_syer']) : 0;
        $feeCapital = isset($_POST['modal_yuran']) ? intval($_POST['modal_yuran']) : 0;
        $deposit = isset($_POST['wang_deposit']) ? intval($_POST['wang_deposit']) : 0;
        $contribution = isset($_POST['sumbangan_tabung']) ? intval($_POST['sumbangan_tabung']) : 0;
        $fixedDeposit = isset($_POST['simpanan_tetap']) ? intval($_POST['simpanan_tetap']) : 0;
        $others = isset($_POST['lain_lain']) ? intval($_POST['lain_lain']) : 0;
        
        // Debug: Print values being inserted
        echo "<div class='alert alert-info'>";
        echo "Values being inserted:<br>";
        echo "Employee ID: $employeeID<br>";
        echo "Entry Fee: $entryFee<br>";
        echo "Modal Share: $modalShare<br>";
        echo "Fee Capital: $feeCapital<br>";
        echo "Deposit: $deposit<br>";
        echo "Contribution: $contribution<br>";
        echo "Fixed Deposit: $fixedDeposit<br>";
        echo "Others: $others<br>";
        echo "</div>";
        
        $stmt->bind_param("iiiiiiii",
            $employeeID,
            $entryFee,
            $modalShare,
            $feeCapital,
            $deposit,
            $contribution,
            $fixedDeposit,
            $others
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to save data");
        }
        
        echo "<div class='alert alert-success'>Data saved successfully!</div>";
        
        // Update member information
        $updateMember = "UPDATE tb_member SET 
            memberName = ?,
            email = ?,
            ic = ?,
            maritalStatus = ?,
            sex = ?,
            religion = ?,
            nation = ?,
            no_pf = ?,
            position = ?,
            phoneNumber = ?,
            phoneHome = ?,
            monthlySalary = ?
            WHERE employeeID = ?";
            
        $stmt = $conn->prepare($updateMember);
        if (!$stmt) {
            throw new Exception("Failed to prepare member update: " . $conn->error);
        }
        
        $stmt->bind_param("ssssssssssssi",
            $_SESSION['personal_info']['nama_penuh'],
            $_SESSION['personal_info']['alamat_emel'],
            $_SESSION['personal_info']['ic'],
            $_SESSION['personal_info']['maritalStatus'],
            $_SESSION['personal_info']['sex'],
            $_SESSION['personal_info']['religion'],
            $_SESSION['personal_info']['nation'],
            $_SESSION['personal_info']['no_pf'],
            $_SESSION['personal_info']['position'],
            $_SESSION['personal_info']['phoneNumber'],
            $_SESSION['personal_info']['phoneHome'],
            $_SESSION['personal_info']['monthlySalary'],
            $employeeID
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update member information: " . $stmt->error);
        }
        
        // Check if we have family member data
        if (isset($_POST['hubungan']) && isset($_POST['nama_waris']) && isset($_POST['no_kp_waris'])) {
            
            // First, delete existing family members for this employee
            $deleteFamily = "DELETE FROM tb_memberregistration_familymemberinfo WHERE employeeID = ?";
            $stmt = $conn->prepare($deleteFamily);
            $stmt->bind_param("i", $employeeID);
            $stmt->execute();
            
            // Prepare the insert statement for family members
            $insertFamily = "INSERT INTO tb_memberregistration_familymemberinfo 
                           (employeeID, relationship, name, icFamilyMember) 
                           VALUES (?, ?, ?, ?)";
            
            $stmt = $conn->prepare($insertFamily);
            if (!$stmt) {
                throw new Exception("Failed to prepare family member insert: " . $conn->error);
            }
            
            // Debug output
            echo "<div class='alert alert-info'>";
            echo "Number of family members: " . count($_POST['hubungan']) . "<br>";
            print_r($_POST['hubungan']);
            echo "</div>";
            
            // Loop through each family member
            for ($i = 0; $i < count($_POST['hubungan']); $i++) {
                // Skip empty entries
                if (empty($_POST['hubungan'][$i]) || empty($_POST['nama_waris'][$i])) {
                    continue;
                }
                
                $stmt->bind_param("isss",
                    $employeeID,
                    $_POST['hubungan'][$i],      // relationship
                    $_POST['nama_waris'][$i],    // name
                    $_POST['no_kp_waris'][$i]    // icFamilyMember
                );
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to save family member info: " . $stmt->error . 
                                      " (Member: " . $_POST['nama_waris'][$i] . ")");
                }
            }
            
            echo "<div class='alert alert-success'>Family member information saved successfully!</div>";
        } else {
            echo "<div class='alert alert-warning'>No family member data received</div>";
        }
        
        // Insert home address
        $insertHomeAddress = "INSERT INTO tb_member_homeaddress 
            (employeeID, homeAddress, postcode, state) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            homeAddress = VALUES(homeAddress),
            postcode = VALUES(postcode),
            state = VALUES(state)";
            
        $stmt = $conn->prepare($insertHomeAddress);
        if (!$stmt) {
            throw new Exception("Failed to prepare home address insert: " . $conn->error);
        }
        
        $stmt->bind_param("isss",
            $employeeID,
            $_SESSION['personal_info']['address'],    // home address
            $_SESSION['personal_info']['posscode'],   // postcode
            $_SESSION['personal_info']['state']       // state
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to save home address: " . $stmt->error);
        }
        
        // Insert office address
        $insertOfficeAddress = "INSERT INTO tb_member_officeaddress 
            (employeeID, officeAddress) 
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE 
            officeAddress = VALUES(officeAddress)";
            
        $stmt = $conn->prepare($insertOfficeAddress);
        if (!$stmt) {
            throw new Exception("Failed to prepare office address insert: " . $conn->error);
        }
        
        $stmt->bind_param("is",
            $employeeID,
            $_SESSION['personal_info']['officeAddress']  // office address
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to save office address: " . $stmt->error);
        }
        
        echo "<div class='alert alert-success'>All information saved successfully!</div>";
        
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        error_log("Error in maklumat_tambahan.php: " . $e->getMessage());
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
        e.preventDefault();

        // Get form data
        const formData = new FormData(form);

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Menghantar... <i class="fas fa-spinner fa-spin"></i>';

        // Send form data using fetch
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            // Redirect to success page
            window.location.href = 'success.php';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ralat semasa menghantar borang. Sila cuba lagi.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Menghantar <i class="fas fa-paper-plane"></i>';
        });
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
        simpanan_tetap: document.querySelector('input[name="simpanan_tetap"]').value,
        lain_lain: document.querySelector('input[name="lain_lain"]').value
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

    return true;
}

// Add event listener to form submission
document.getElementById('registrationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (validateForm()) {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Menghantar... <i class="fas fa-spinner fa-spin"></i>';
        
        // Submit the form
        this.submit();
    }
});
</script>

</body>
</html>
<?php include "footer.php"; ?> 