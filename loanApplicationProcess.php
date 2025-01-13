<?php
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
?>