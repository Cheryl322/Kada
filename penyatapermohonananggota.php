<?php
// Add error reporting at the top of the file
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "headeradmin.php";
include "footer.php";
include "dbconnect.php";

// Check database connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get employeeID from URL parameter
$employeeID = null;
if (isset($_GET['id'])) {
    $employeeID = filter_var($_GET['id'], FILTER_VALIDATE_INT);
} elseif (isset($_GET['ID'])) {
    $employeeID = filter_var($_GET['ID'], FILTER_VALIDATE_INT);
}

if ($employeeID) {
    try {
        // Single comprehensive query to get all member data
        $query = "SELECT 
            m.*,
            COALESCE(mha.homeAddress, '') as homeAddress, 
            COALESCE(mha.homePostcode, '') as homePostcode, 
            COALESCE(mha.homeState, '') as homeState,
            COALESCE(moa.officeAddress, '') as officeAddress, 
            COALESCE(moa.officePostcode, '') as officePostcode, 
            COALESCE(moa.officeState, '') as officeState,
            COALESCE(mfc.entryFee, 0) as entryFee, 
            COALESCE(mfc.modalShare, 0) as modalShare, 
            COALESCE(mfc.feeCapital, 0) as feeCapital, 
            COALESCE(mfc.deposit, 0) as deposit, 
            COALESCE(mfc.contribution, 0) as contribution, 
            COALESCE(mfc.fixedDeposit, 0) as fixedDeposit, 
            COALESCE(mfc.others, 0) as others
        FROM tb_member m
        LEFT JOIN tb_member_homeaddress mha ON m.employeeID = mha.employeeID
        LEFT JOIN tb_member_officeaddress moa ON m.employeeID = moa.employeeID
        LEFT JOIN tb_memberregistration_feesandcontribution mfc ON m.employeeID = mfc.employeeID
        WHERE m.employeeID = ?";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Database prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $employeeID);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $memberData = $result->fetch_assoc();

        if (!$memberData) {
            throw new Exception("No member found with ID: " . $employeeID);
        }

        // Get family members
        $familyQuery = "SELECT * FROM tb_memberregistration_familymemberinfo 
                       WHERE employeeID = ?";
        $stmt = $conn->prepare($familyQuery);
        $stmt->bind_param("i", $employeeID);
        $stmt->execute();
        $familyResult = $stmt->get_result();
        $familyMembers = $familyResult->fetch_all(MYSQLI_ASSOC);

    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        exit;
    }
} else {
    echo "<div class='alert alert-danger'>Invalid employee ID provided</div>";
    exit;
}

?>

<style>
body {
    margin: 0;
    padding: 0;
    min-height: 100vh;
    background: linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)), url('img/padi.jpg') no-repeat center center fixed;
    background-size: cover;
    display: flex;
    flex-direction: column;
    position: relative;
}

.wrapper {
    flex: 1;
    padding: 20px;
    padding-bottom: 60px; /* Increased padding for larger footer */
}

.container {
    background-color: rgba(255, 255, 255, 0.9);
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    padding: 20px;
}

/* Table styles */
.table-wrapper {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

/* Last table wrapper shouldn't have margin bottom */
.table-wrapper:last-child {
    margin-bottom: 0;
}

/* Footer styling */
footer {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background-color: #7DCFB6 !important; /* Match the mint green color */
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    z-index: 1000;
    margin: 0;
    padding: 15px 0;
    text-align: center;
}

footer p {
    margin: 0;
    color: white;
    font-size: 16px;
    font-weight: 500;
    line-height: 1.5;
}

/* Button styling */
.btn-secondary {
    margin-bottom: 20px;
}

/* Remove any margins that might cause spacing */
p:last-child, 
div:last-child, 
table:last-child {
    margin-bottom: 0;
}

/* Table Styling */
.table {
    width: 100%;
    margin-bottom: 1rem;
    background-color: transparent;
    border-collapse: collapse;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

.table thead th {
    background-color: #20B2AA !important; /* LightSeaGreen - brighter color */
    color: white;
    font-weight: 600;
    padding: 15px;
    border: none;
    text-align: left;
    font-size: 15px;
    white-space: nowrap;
    letter-spacing: 0.5px;
}

.table tbody tr {
    background-color: #ffffff;
    transition: all 0.3s ease;
}

.table tbody tr:nth-child(even) {
    background-color: #f8fffe; /* Very light cyan tint */
}

.table tbody tr:hover {
    background-color: #e6f7f7; /* Light cyan on hover */
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.table td {
    padding: 15px;
    vertical-align: middle;
    border-bottom: 1px solid #e3f2f2;
    color: #2c3e50;
    font-size: 14px;
}

/* Card Styling */
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
    background-color: #ffffff;
}

.card-header {
    background-color: #20B2AA !important;
    border-radius: 15px 15px 0 0 !important;
    padding: 20px;
}

.card-header h4 {
    margin: 0;
    color: white;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.card-body {
    padding: 30px;
    background-color: #ffffff;
}

/* Form Section Headers */
h5 {
    color: #20B2AA;
    font-weight: 600;
    margin-top: 30px;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #20B2AA;
    letter-spacing: 0.5px;
}

h6 {
    color: #2c3e50;
    font-weight: 600;
    margin-top: 20px;
    margin-bottom: 15px;
    letter-spacing: 0.3px;
}

/* Back Button */
.btn-secondary {
    background-color: #6c757d;
    border: none;
    padding: 12px 24px;
    border-radius: 5px;
    color: white;
    transition: all 0.3s ease;
    font-weight: 500;
    letter-spacing: 0.5px;
    margin-bottom: 20px; /* Add space after button */
}

.btn-secondary:hover {
    background-color: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}

/* Form Controls */
.form-control {
    border-radius: 5px;
    border: 1px solid #e3f2f2;
    padding: 10px 15px;
    transition: all 0.3s ease;
    background-color: #ffffff;
}

.form-control:read-only {
    background-color: #f8fffe;
    border-color: #e3f2f2;
}

/* Container Spacing */
.container {
    padding-top: 25px;
    padding-bottom: 25px;
    background-color: transparent;
}

/* Table Responsive */
@media (max-width: 768px) {
    .table-responsive {
        display: block;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 10px;
        background-color: #ffffff;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    }
}

/* Amount Column Styling */
.table td:last-child {
    font-weight: 600;
    color: #20B2AA;
}

/* Table header styling */
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
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<br><br><br>
<body>
    <?php include "headeradmin.php"; ?>
    
    <main>
        <div class="wrapper">
            <div class="container">
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
                                    <label for="alamat" class="form-label">Alamat</label>
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
                                        <?php 
                                        if (isset($familyMembers) && !empty($familyMembers)) {
                                            $counter = 1;
                                            foreach ($familyMembers as $family) {
                                                echo "<tr>";
                                                echo "<td>" . $counter++ . "</td>";
                                                echo "<td>" . htmlspecialchars($family['relationship']) . "</td>";
                                                echo "<td>" . htmlspecialchars($family['name']) . "</td>";
                                                echo "<td>" . htmlspecialchars($family['icFamilyMember']) . "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='4' class='text-center'>No family members found</td></tr>";
                                        }
                                        ?>
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
                                            <td><?php echo isset($memberData['entryFee']) ? number_format($memberData['entryFee'], 2) : '0.00'; ?></td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>Modah Syer*</td>
                                            <td><?php echo isset($memberData['modalShare']) ? number_format($memberData['modalShare'], 2) : '0.00'; ?></td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>Modal Yuran</td>
                                            <td><?php echo isset($memberData['feeCapital']) ? number_format($memberData['feeCapital'], 2) : '0.00'; ?></td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td>Wang Deposit Anggota</td>
                                            <td><?php echo isset($memberData['deposit']) ? number_format($memberData['deposit'], 2) : '0.00'; ?></td>
                                        </tr>
                                        <tr>
                                            <td>5</td>
                                            <td>Sumbangan Tabung Kebajikan (Al-Abrar)</td>
                                            <td><?php echo isset($memberData['contribution']) ? number_format($memberData['contribution'], 2) : '0.00'; ?></td>
                                        </tr>
                                        <tr>
                                            <td>6</td>
                                            <td>Simpanan Tetap</td>
                                            <td><?php echo isset($memberData['fixedDeposit']) ? number_format($memberData['fixedDeposit'], 2) : '0.00'; ?></td>
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
        </div>
    </main>

    <div class="table-wrapper">
        <!-- Your tables -->
    </div>

    <?php include "footer.php"; ?>
</body>

<footer>
    <p>Sistem Koperasi KADA developed by TechniCrab @ 2024/2025</p>
</footer>
</html>
                