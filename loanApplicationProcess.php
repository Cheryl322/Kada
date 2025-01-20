<?php
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
ini_set('memory_limit', '256M');

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "dbconnect.php";

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

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    error_log("POST request received");
    
    try {
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

        // Begin transaction
        mysqli_begin_transaction($conn);

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
        $_SESSION['message'] = "Permohonan berjaya dihantar!";
        
        // Direct redirect to success2.php
        header("Location: success2.php");
        exit();
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        
        // Delete uploaded file if it exists and there was an error
        if (isset($uploadPath) && file_exists($uploadPath)) {
            unlink($uploadPath);
        }
        
        $_SESSION['status'] = "error";
        $_SESSION['error'] = $e->getMessage();
        header("Location: success2.php");
        exit();
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
