<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

session_start();
include "dbconnect.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Only include PHPMailer if it hasn't been included yet
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    require_once 'phpmailer/src/Exception.php';
    require_once 'phpmailer/src/PHPMailer.php';
    require_once 'phpmailer/src/SMTP.php';
}

ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
ini_set('memory_limit', '256M');

include "email_helper.php";

// Debug logging
error_log("Script started");
error_log("POST data: " . print_r($_POST, true));

// Debug: Print all received data
error_log("Loan application process started");
error_log("POST data received: " . print_r($_POST, true));
error_log("FILES data received: " . print_r($_FILES, true));

// Check specifically for guarantor fields
$guarantorFields = [
    'guarantorName1', 'guarantorIC1', 'guarantorPhone1', 'guarantorPF1', 'guarantorMemberNo1',
    'guarantorName2', 'guarantorIC2', 'guarantorPhone2', 'guarantorPF2', 'guarantorMemberNo2'
];

foreach ($guarantorFields as $field) {
    error_log("Checking field $field: " . (isset($_POST[$field]) ? "exists" : "missing"));
}

// Check if guarantor data exists
if (empty($_POST['guarantorName1']) || empty($_POST['guarantorName2'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Guarantor information is missing"
    ]);
    exit;
}

// Test directory permissions
$uploadDir = "uploads/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (!is_writable($uploadDir)) {
    error_log("Upload directory is not writable!");
    echo json_encode([
        "status" => "error",
        "message" => "Server configuration error: Upload directory is not writable"
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Start transaction
        mysqli_begin_transaction($conn);

        // Get member data
        $memberQuery = "SELECT memberName, email 
                       FROM tb_member 
                       WHERE employeeID = ?";
        
        $stmt = mysqli_prepare($conn, $memberQuery);
        mysqli_stmt_bind_param($stmt, "s", $_SESSION['employeeID']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $memberData = mysqli_fetch_assoc($result);

        if (!$memberData) {
            throw new Exception("Member data not found");
        }

        // Debug file upload
        error_log("FILES array content: " . print_r($_FILES, true));
        
        // File upload handling
        $uploadDir = "uploads/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Detailed file upload validation
        if (!isset($_FILES['netSalaryFile'])) {
            throw new Exception("Sila muat naik slip gaji");
        }

        if ($_FILES['netSalaryFile']['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = array(
                UPLOAD_ERR_INI_SIZE => "Fail terlalu besar",
                UPLOAD_ERR_FORM_SIZE => "Fail terlalu besar",
                UPLOAD_ERR_PARTIAL => "Fail tidak dimuat naik sepenuhnya",
                UPLOAD_ERR_NO_FILE => "Sila muat naik slip gaji",
                UPLOAD_ERR_NO_TMP_DIR => "Folder sementara tidak dijumpai",
                UPLOAD_ERR_CANT_WRITE => "Gagal menulis fail",
                UPLOAD_ERR_EXTENSION => "Jenis fail tidak dibenarkan",
            );
            $errorMessage = isset($uploadErrors[$_FILES['netSalaryFile']['error']]) 
                ? $uploadErrors[$_FILES['netSalaryFile']['error']] 
                : "Ralat tidak diketahui";
            throw new Exception($errorMessage);
        }

        // Handle netSalaryFile upload
        $netSalaryFile= null;
        if (isset($_FILES['netSalaryFile']) && $_FILES['netSalaryFile']['error'] === UPLOAD_ERR_OK) {
            $netSalaryFile = uniqid() . '_' . basename($_FILES['netSalaryFile']['name']);
            $uploadPath = $uploadDir . $netSalaryFile;
            
            if (!move_uploaded_file($_FILES['netSalaryFile']['tmp_name'], $uploadPath)) {
                throw new Exception("Failed to upload net salary file");
            }
        }

        // Get form data
        $employeeID = $_SESSION['employeeID'];
        $amountRequested = $_POST['amountRequested'];
        $financingPeriod = $_POST['financingPeriod'];
        $monthlyInstallments = $_POST['monthlyInstallments'];
        $employerName = $_POST['employerName'];
        $employerIC = $_POST['employerIC'];
        $basicSalary = $_POST['basicSalary'];
        $netSalary = $_POST['netSalary'];
        $loanType = $_POST['loanType'];
        
        // Add debug logging
        error_log("Bank Name: " . (isset($_POST['bankName']) ? $_POST['bankName'] : 'not set'));
        error_log("Bank Account: " . (isset($_POST['accountNo']) ? $_POST['accountNo'] : 'not set'));

        // Insert loan application
        $sql1 = "INSERT INTO tb_loanapplication (employeeID, loanApplicationDate, amountRequested, financingPeriod, monthlyInstallments) 
                VALUES (?, CURDATE(), ?, ?, ?)";
        
        $stmt1 = mysqli_prepare($conn, $sql1);
        mysqli_stmt_bind_param($stmt1, "iddd", 
            $_SESSION['employeeID'],
            $_POST['amountRequested'],
            $_POST['financingPeriod'],
            $_POST['monthlyInstallments']
        );
        mysqli_stmt_execute($stmt1);
        
        $loanApplicationID = mysqli_insert_id($conn);

        // Insert loan details with netSalaryFile
        $sql2 = "INSERT INTO tb_loan (loanApplicationID, employeeID, amountRequested, financingPeriod, monthlyInstallments, 
                employerName, employerIC, basicSalary, netSalary, netSalaryFile, loanType) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt2 = mysqli_prepare($conn, $sql2);
        mysqli_stmt_bind_param($stmt2, "iidddssddss", 
            $loanApplicationID,
            $_SESSION['employeeID'],
            $_POST['amountRequested'],
            $_POST['financingPeriod'],
            $_POST['monthlyInstallments'],
            $_POST['employerName'],
            $_POST['employerIC'],
            $_POST['basicSalary'],
            $_POST['netSalary'],
            $netSalaryFile,
            $_POST['loanType']
        );
        mysqli_stmt_execute($stmt2);

        // Insert guarantor information
        $guarantorSql = "INSERT INTO tb_guarantor (
            loanApplicationID,
            employeeID,
            guarantorName,
            guarantorIC,
            guarantorPhone,
            guarantorPFNo,
            guarantorMemberNo
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        // Insert first guarantor
        $stmt3 = mysqli_prepare($conn, $guarantorSql);
        mysqli_stmt_bind_param($stmt3, "iisssss", 
            $loanApplicationID,
            $_SESSION['employeeID'],
            $_POST['guarantorName1'],
            $_POST['guarantorIC1'],
            $_POST['guarantorPhone1'],
            $_POST['guarantorPF1'],
            $_POST['guarantorMemberNo1']
        );
        mysqli_stmt_execute($stmt3);

        // Insert second guarantor
        $stmt4 = mysqli_prepare($conn, $guarantorSql);
        mysqli_stmt_bind_param($stmt4, "iisssss", 
            $loanApplicationID,
            $_SESSION['employeeID'],
            $_POST['guarantorName2'],
            $_POST['guarantorIC2'],
            $_POST['guarantorPhone2'],
            $_POST['guarantorPF2'],
            $_POST['guarantorMemberNo2']
        );
        mysqli_stmt_execute($stmt4);

        // Insert bank information
        $sql_bank = "INSERT INTO tb_bank (loanApplicationID, employeeID, bankName, accountNo) 
                     VALUES (?, ?, ?, ?)";
        
        $stmt_bank = mysqli_prepare($conn, $sql_bank);
        if ($stmt_bank === false) {
            throw new Exception("Error preparing bank statement: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt_bank, "iiss", 
            $loanApplicationID,
            $_SESSION['employeeID'],
            $_POST['bankName'],
            $_POST['accountNo']
        );

        // Add debug logging for bank insertion
        if (!mysqli_stmt_execute($stmt_bank)) {
            error_log("Bank insert error: " . mysqli_stmt_error($stmt_bank));
            throw new Exception("Error inserting bank details: " . mysqli_stmt_error($stmt_bank));
        }

        // Commit transaction
        mysqli_commit($conn);
        
        $_SESSION['status'] = "success";
        $_SESSION['message'] = "Permohonan berjaya dihantar! Sila rujuk email anda untuk pengesahan.";
        
        // Send email
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->SMTPDebug = 0;                      // Disable debug output
            $mail->isSMTP();                           // Send using SMTP
            $mail->Host       = 'smtp.gmail.com';      // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                  // Enable SMTP authentication
            $mail->Username   = 'koperasikada.site@gmail.com';  // SMTP username
            $mail->Password   = 'rtmh vdnc mozb lion'; // SMTP password
            $mail->SMTPSecure = 'tls';                 // Enable implicit TLS encryption
            $mail->Port       = 587;                   // TCP port to connect to; use 587 for TLS
            
            // Additional SMTP settings to help with connection
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Recipients
            $mail->setFrom('koperasikada.site@gmail.com', 'Koperasi KADA');
            $mail->addAddress($memberData['email'], $memberData['memberName']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Pengesahan Permohonan Pembiayaan KADA';
            
            // Email body
            $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background-color: #5CBA9B; padding: 20px; text-align: center;'>
                    <h2 style='color: white; margin: 0;'>Pengesahan Permohonan Pembiayaan</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f9f9f9;'>
                    <p>Salam Sejahtera {$memberData['memberName']},</p>
                    
                    <p>Permohonan pembiayaan anda telah berjaya dihantar. Berikut adalah maklumat permohonan:</p>
                    
                    <div style='background-color: white; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <p><strong>Status Permohonan:</strong> Dalam Proses</p>
                        <p><strong>Tarikh Permohonan:</strong> " . date('d/m/Y') . "</p>
                    </div>

                    <p>Sila ambil perhatian:</p>
                    <ul>
                        <li>Permohonan anda akan diproses dalam tempoh 14 hari bekerja</li>
                        <li>Anda boleh menyemak status permohonan melalui sistem KADA</li>
                        <li>Pihak kami akan menghubungi anda sekiranya dokumen tambahan diperlukan</li>
                    </ul>

                    <div style='background-color: #e8f5e9; padding: 15px; border-radius: 5px; margin-top: 20px;'>
                        <p style='margin: 0;'><strong>Sebarang Pertanyaan:</strong></p>
                        <p style='margin: 5px 0;'>📞 09-7481101</p>
                        <p style='margin: 5px 0;'>✉️ koperasikada.site@gmail.com</p>
                    </div>
                </div>
                
                <div style='text-align: center; padding: 15px; background-color: #f1f1f1; font-size: 12px;'>
                    <p style='margin: 0;'>Ini adalah email automatik. Sila jangan balas email ini.</p>
                    <p style='margin: 5px 0;'>© " . date('Y') . " Koperasi KADA. Hak Cipta Terpelihara.</p>
                </div>
            </div>";

            $mail->Body = $body;
            $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $body));

            $mail->send();
            error_log("Email sent successfully");
            
            // Instead of JSON response, output HTML with SweetAlert
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        html: `
                            <div style="padding: 20px;">
                                <div style="width: 60px; height: 60px; margin: 0 auto 20px;">
                                    <svg viewBox="0 0 24 24" width="100%" height="100%">
                                        <circle cx="12" cy="12" r="11" fill="none" stroke="#5CBA9B" stroke-width="2"/>
                                        <path d="M6 12l4 4 8-8" stroke="#5CBA9B" stroke-width="2" fill="none"/>
                                    </svg>
                                </div>
                                <h2 style="color: #333; font-size: 24px; margin-bottom: 10px;">Berjaya!</h2>
                                <p style="color: #666; font-size: 16px; margin-bottom: 20px;">Permohonan berjaya dihantar! Sila rujuk email anda untuk pengesahan.</p>
                                <button onclick="window.location.href='statuspermohonanloan.php'" 
                                        style="background-color: #5CBA9B; color: white; border: none; padding: 8px 20px; border-radius: 5px; cursor: pointer; font-size: 14px;">
                                    Ke Status Permohonan
                                </button>
                            </div>
                        `,
                        showConfirmButton: false,
                        width: 400,
                        background: '#ffffff',
                        customClass: {
                            popup: 'custom-popup-class'
                        }
                    }).then((result) => {
                        window.location.href = 'membermainpage.php';
                    });
                </script>
                <style>
                    .custom-popup-class {
                        border-radius: 10px !important;
                        padding: 20px !important;
                    }
                    button:hover {
                        background-color: #4BA98B !important;
                    }
                </style>
            </body>
            </html>
            <?php
            exit;
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            mysqli_rollback($conn);
            
            // Delete uploaded file if it exists and there was an error
            if (isset($uploadPath) && file_exists($uploadPath)) {
                unlink($uploadPath);
            }
            
            // Handle errors with a popup too
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Ralat!',
                        text: '<?php echo addslashes($e->getMessage()); ?>',
                        confirmButtonColor: '#5CBA9B'
                    }).then(() => {
                        window.history.back();
                    });
                </script>
            </body>
            </html>
            <?php
            exit;
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        
        // Delete uploaded file if it exists and there was an error
        if (isset($uploadPath) && file_exists($uploadPath)) {
            unlink($uploadPath);
        }
        
        error_log("Error: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Ralat semasa memproses permohonan: ' . $e->getMessage()
        ]);
        exit;
    }
} else {
    header("Location: permohonanloan.php");
    exit();
}

// Close statements and connection
if (isset($stmt1)) mysqli_stmt_close($stmt1);
if (isset($stmt2)) mysqli_stmt_close($stmt2);
if (isset($stmt3)) mysqli_stmt_close($stmt3);
if (isset($stmt4)) mysqli_stmt_close($stmt4);
mysqli_close($conn);
?>
