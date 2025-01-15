<?php
session_start();
include "dbconnect.php";

// Debug: Print all received data
error_log("POST Data: " . print_r($_POST, true));
error_log("FILES Data: " . print_r($_FILES, true));

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

try {
    // Check if files were uploaded
    if (!isset($_FILES['basicSalarySlip']) || $_FILES['basicSalarySlip']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Sila muat naik slip gaji pokok");
    }

    if (!isset($_FILES['netSalarySlip']) || $_FILES['netSalarySlip']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Sila muat naik slip gaji bersih");
    }

    // Process basic salary slip
    $basicSalaryFile = $uploadDir . uniqid() . '_' . basename($_FILES['basicSalarySlip']['name']);
    if (!move_uploaded_file($_FILES['basicSalarySlip']['tmp_name'], $basicSalaryFile)) {
        throw new Exception("Gagal memuat naik slip gaji pokok");
    }

    // Process net salary slip
    $netSalaryFile = $uploadDir . uniqid() . '_' . basename($_FILES['netSalarySlip']['name']);
    if (!move_uploaded_file($_FILES['netSalarySlip']['tmp_name'], $netSalaryFile)) {
        throw new Exception("Gagal memuat naik slip gaji bersih");
    }

    // Start database transaction
    mysqli_begin_transaction($conn);

    // 1. First insert into tb_loanapplication
    $loanAppSql = "INSERT INTO tb_loanapplication (
        employeeID,
        loanApplicationDate,
        loanStatus,
        amountRequested,
        financingPeriod,
        monthlyInstallments
    ) VALUES (?, CURRENT_DATE, 'Pending', ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $loanAppSql);
    mysqli_stmt_bind_param($stmt, "iddd", 
        $_POST['employeeID'],
        $_POST['amountRequested'],
        $_POST['financingPeriod'],
        $_POST['monthlyInstallments']
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error in loan application: " . mysqli_error($conn));
    }

    $loanApplicationID = mysqli_insert_id($conn);

    // 2. Insert into tb_bank
    $bankSql = "INSERT INTO tb_bank (
        loanApplicationID,
        employeeID,
        bankName,
        accountNo
    ) VALUES (?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $bankSql);
    mysqli_stmt_bind_param($stmt, "iiss", 
        $loanApplicationID,
        $_POST['employeeID'],
        $_POST['bankName'],
        $_POST['accountNo']
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error in bank details: " . mysqli_error($conn));
    }

    // 3. Insert into tb_loan
    $loanType = $_POST['loanType'];

    $sql = "INSERT INTO tb_loan (loanApplicationID, employeeID, amountRequested, financingPeriod, monthlyInstallments, employerName, employerIC, basicSalary, netSalary, basicSalaryFile, netSalaryFile, loanType) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdddssddsss",
        $loanApplicationID,
        $_POST['employeeID'],
        $_POST['amountRequested'],
        $_POST['financingPeriod'],
        $_POST['monthlyInstallments'],
        $_POST['employerName'],
        $_POST['employerIC'],
        $_POST['basicSalary'],
        $_POST['netSalary'],
        $basicSalaryFile,
        $netSalaryFile,
        $loanType
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error in loan details: " . mysqli_error($conn));
    }

    // 4. Insert first guarantor
    $guarantorSql = "INSERT INTO tb_guarantor (
        loanApplicationID,
        employeeID,
        guarantorName,
        guarantorIC,
        guarantorPhone,
        guarantorPFNo,
        guarantorMemberNo
    ) VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $guarantorSql);
    mysqli_stmt_bind_param($stmt, "iisssss", 
        $loanApplicationID,
        $_POST['employeeID'],
        $_POST['guarantorName1'],
        $_POST['guarantorIC1'],
        $_POST['guarantorPhone1'],
        $_POST['guarantorPF1'],
        $_POST['guarantorMemberNo1']
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error inserting first guarantor: " . mysqli_error($conn));
    }

    // 5. Insert second guarantor
    $stmt = mysqli_prepare($conn, $guarantorSql);
    mysqli_stmt_bind_param($stmt, "iisssss", 
        $loanApplicationID,
        $_POST['employeeID'],
        $_POST['guarantorName2'],
        $_POST['guarantorIC2'],
        $_POST['guarantorPhone2'],
        $_POST['guarantorPF2'],
        $_POST['guarantorMemberNo2']
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error in second guarantor: " . mysqli_error($conn));
    }

    // Commit transaction
    mysqli_commit($conn);

    // Set session variables before sending response
    $_SESSION['status'] = "success";
    $_SESSION['message'] = "Permohonan berjaya dihantar";

    echo json_encode([
        "status" => "success",
        "message" => "Permohonan berjaya dihantar"
    ]);

} catch (Exception $e) {
    // If there's an error, rollback the transaction
    if (isset($conn)) {
        mysqli_rollback($conn);
    }
    
    // Delete uploaded files if they exist
    if (isset($basicSalaryFile) && file_exists($basicSalaryFile)) {
        unlink($basicSalaryFile);
    }
    if (isset($netSalaryFile) && file_exists($netSalaryFile)) {
        unlink($netSalaryFile);
    }

    // Set session variables for error
    $_SESSION['status'] = "error";
    $_SESSION['error'] = $e->getMessage();

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn);
}
?>
