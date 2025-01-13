<?php
<<<<<<< HEAD
session_start();
include 'dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employeeID = $_SESSION['employeeID'];
    
    try {
        // Start transaction
        mysqli_begin_transaction($conn);

        // Insert loan application details
        $sql = "INSERT INTO tb_loan_application (
            employeeID, bankName, bankAccountNo, 
            amountRequested, financingPeriod, monthlyPayment,
            guarantorName1, guarantorIC1, guarantorPhone1, guarantorPF1, guarantorMemberID1,
            guarantorName2, guarantorIC2, guarantorPhone2, guarantorPF2, guarantorMemberID2,
            employerName, employerIC, basicSalary, netSalary,
            status, applicationDate
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sssdddssssssssssssdd',
            $employeeID,
            $_POST['bankName'],
            $_POST['bankAccountNo'],
            $_POST['amountRequested'],
            $_POST['financingPeriod'],
            $_POST['monthlyPayment'],
            $_POST['guarantorName1'],
            $_POST['guarantorIC1'],
            $_POST['guarantorPhone1'],
            $_POST['guarantorPF1'],
            $_POST['guarantorMemberID1'],
            $_POST['guarantorName2'],
            $_POST['guarantorIC2'],
            $_POST['guarantorPhone2'],
            $_POST['guarantorPF2'],
            $_POST['guarantorMemberID2'],
            $_POST['employerName'],
            $_POST['employerIC'],
            $_POST['basicSalary'],
            $_POST['netSalary']
        );

        mysqli_stmt_execute($stmt);
        $loanID = mysqli_insert_id($conn);

        // Handle file uploads
        $uploadDir = 'uploads/loan_documents/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Function to handle file upload
        function uploadFile($file, $uploadDir, $prefix) {
            $fileName = $prefix . '_' . time() . '_' . basename($file['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                return $fileName;
            }
            throw new Exception("Error uploading file: " . $file['name']);
        }

        // Upload all documents
        $guarantorSig1 = uploadFile($_FILES['guarantorSignature1'], $uploadDir, 'guarantor1');
        $guarantorSig2 = uploadFile($_FILES['guarantorSignature2'], $uploadDir, 'guarantor2');
        $basicSalarySlip = uploadFile($_FILES['basicSalarySlip'], $uploadDir, 'basic_salary');
        $netSalarySlip = uploadFile($_FILES['netSalarySlip'], $uploadDir, 'net_salary');
        $employerSig = uploadFile($_FILES['employerSignature'], $uploadDir, 'employer');

        // Update document paths in database
        $sqlDocs = "INSERT INTO tb_loan_documents (
            loanID, guarantorSignature1, guarantorSignature2, 
            basicSalarySlip, netSalarySlip, employerSignature
        ) VALUES (?, ?, ?, ?, ?, ?)";

        $stmtDocs = mysqli_prepare($conn, $sqlDocs);
        mysqli_stmt_bind_param($stmtDocs, 'isssss',
            $loanID,
            $guarantorSig1,
            $guarantorSig2,
            $basicSalarySlip,
            $netSalarySlip,
            $employerSig
        );
        mysqli_stmt_execute($stmtDocs);

        // Commit transaction
        mysqli_commit($conn);

        // Return success response
        $response = array(
            'status' => 'success',
            'message' => 'Permohonan anda telah berjaya dihantar!'
        );
        echo json_encode($response);

    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        
        $response = array(
            'status' => 'error',
            'message' => 'Ralat semasa memproses permohonan: ' . $e->getMessage()
        );
        echo json_encode($response);
    }

    // Close connections
    mysqli_stmt_close($stmt);
    mysqli_stmt_close($stmtDocs);
    mysqli_close($conn);
    exit;
}
=======
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'dbconnect.php';

// Debug: Print connection status
if ($con) {
    echo "Database connected successfully<br>";
} else {
    echo "Database connection failed: " . mysqli_connect_error() . "<br>";
    exit();
}

try {
    mysqli_begin_transaction($con);

    // Debug: Print POST data
    echo "<pre>POST data: ";
    print_r($_POST);
    echo "</pre>";

    // Debug: Print FILES data
    echo "<pre>FILES data: ";
    print_r($_FILES);
    echo "</pre>";

    // 1. First insert member personal information
    $sql = "INSERT INTO tb_member (employeeID, memberName, ic, sex, religion, nation, no_pf, position, phoneNumber) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $employeeID = $_POST['employeeID']; // Store this for later use
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "issssssss", 
        $employeeID,
        $_POST['memberName'],
        $_POST['ic'],
        $_POST['sex'],
        $_POST['religion'],
        $_POST['nation'],
        $_POST['no_pf'],
        $_POST['position'],
        $_POST['phoneNumber']
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error inserting member information: " . mysqli_error($con));
    }

    // 2. Insert loan using the same employeeID as loanApplicationID
    $sql = "INSERT INTO tb_loan (loanApplicationID, amountRequested, financingPeriod, monthlyInstallments, 
            employerName, employerIc, basicSalary, netSalary, basicSalaryFile, 
            netSalaryFile, signature) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    // Handle file uploads
    $basicSalaryFile = null;
    $netSalaryFile = null;
    $signature = null;

    if (isset($_FILES['basicSalaryFile']) && $_FILES['basicSalaryFile']['error'] === 0) {
        $basicSalaryFile = file_get_contents($_FILES['basicSalaryFile']['tmp_name']);
    }
    if (isset($_FILES['netSalaryFile']) && $_FILES['netSalaryFile']['error'] === 0) {
        $netSalaryFile = file_get_contents($_FILES['netSalaryFile']['tmp_name']);
    }
    if (isset($_FILES['signature']) && $_FILES['signature']['error'] === 0) {
        $signature = file_get_contents($_FILES['signature']['tmp_name']);
    }
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "iiiisiiibbb", 
        $employeeID,  // Use the same employeeID as loanApplicationID
        $_POST['amountRequested'],
        $_POST['financingPeriod'],
        $_POST['monthlyInstallments'],
        $_POST['employerName'],
        $_POST['employerIc'],
        $_POST['basicSalary'],
        $_POST['netSalary'],
        $basicSalaryFile,
        $netSalaryFile,
        $signature
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error inserting loan: " . mysqli_error($con));
    }

    // 3. Insert member addresses
    // Home Address
    $sql = "INSERT INTO tb_member_homeaddress (employeeID, homeAddress, homePostcode, homeState) 
            VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "isss", 
        $_POST['employeeID'],
        $_POST['homeAddress'],
        $_POST['homePostcode'],
        $_POST['homeState']
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error inserting home address: " . mysqli_error($con));
    }

    // Office Address
    $sql = "INSERT INTO tb_member_officeaddress (employeeID, officeAddress, officePostcode, officeState) 
            VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "isss", 
        $_POST['employeeID'],
        $_POST['officeAddress'],
        $_POST['officePostcode'],
        $_POST['officeState']
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error inserting office address: " . mysqli_error($con));
    }

    // 4. Insert guarantor information
    $sql = "INSERT INTO tb_guarantor (employeeID, icGuarantor, guarantorName, 
            memberIdGuarantor, telGuarantor, noPFGuarantor, tandatanganGua) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $tandatanganGua = null;
    if (isset($_FILES['tandatanganGua']) && $_FILES['tandatanganGua']['error'] === 0) {
        $tandatanganGua = file_get_contents($_FILES['tandatanganGua']['tmp_name']);
    }
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "iisiisb", 
        $_POST['employeeID'],
        $_POST['icGuarantor'],
        $_POST['guarantorName'],
        $_POST['guarantorMemberID'],
        $_POST['telGuarantor'],
        $_POST['noPFGuarantor'],
        $tandatanganGua
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error inserting guarantor: " . mysqli_error($con));
    }

    mysqli_commit($con);
    $_SESSION['success_message'] = "Loan application submitted successfully!";
    header("Location: permohonanloan.php");
    exit();

} catch (Exception $e) {
    mysqli_rollback($con);
    echo "Error: " . $e->getMessage() . "<br>";
    $_SESSION['error_message'] = $e->getMessage();
    // Comment out the redirect temporarily for debugging
    // header("Location: permohonanloan.php");
    // exit();
} finally {
    mysqli_close($con);
}
>>>>>>> 337faef42b37e621d788cec950200a116ab7c33a
?>