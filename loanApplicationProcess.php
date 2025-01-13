<?php
session_start();
include 'dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Start transaction
        mysqli_begin_transaction($conn);

        // Get employeeID from session
        $employeeID = $_SESSION['employeeID'];

        // Insert employer information
        $sqlEmployer = "INSERT INTO tb_employer (
            employeeID, employerName, employerIC, 
            basicSalary, netSalary
        ) VALUES (?, ?, ?, ?, ?)";

        $stmtEmployer = mysqli_prepare($conn, $sqlEmployer);
        mysqli_stmt_bind_param($stmtEmployer, 'sssdd',
            $employeeID,
            $_POST['employerName'],
            $_POST['employerIC'],
            $_POST['basicSalary'],
            $_POST['netSalary']
        );
        mysqli_stmt_execute($stmtEmployer);

        // Handle file uploads
        $uploadDir = 'uploads/loan_documents/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Function to handle file upload
        function uploadFile($file, $uploadDir, $prefix) {
            if (isset($file['tmp_name']) && !empty($file['tmp_name'])) {
                $fileName = $prefix . '_' . time() . '_' . basename($file['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    return $fileName;
                }
            }
            throw new Exception("Error uploading file");
        }

        // Upload salary slips and signature
        $basicSalarySlip = uploadFile($_FILES['basicSalarySlip'], $uploadDir, 'basic_salary');
        $netSalarySlip = uploadFile($_FILES['netSalarySlip'], $uploadDir, 'net_salary');
        $employerSignature = uploadFile($_FILES['employerSignature'], $uploadDir, 'employer_sig');

        // Update document paths in database
        $sqlDocs = "INSERT INTO tb_employer_documents (
            employeeID, basicSalarySlip, netSalarySlip, employerSignature
        ) VALUES (?, ?, ?, ?)";

        $stmtDocs = mysqli_prepare($conn, $sqlDocs);
        mysqli_stmt_bind_param($stmtDocs, 'ssss',
            $employeeID,
            $basicSalarySlip,
            $netSalarySlip,
            $employerSignature
        );
        mysqli_stmt_execute($stmtDocs);

        // Insert loan application
        $sqlLoan = "INSERT INTO tb_loan (
            employeeID, loanAmount, loanPeriod, monthlyPayment,
            status, applicationDate
        ) VALUES (?, ?, ?, ?, 'Pending', NOW())";

        $stmtLoan = mysqli_prepare($conn, $sqlLoan);
        mysqli_stmt_bind_param($stmtLoan, 'sddd',
            $employeeID,
            $_POST['amountRequested'],
            $_POST['financingPeriod'],
            $_POST['monthlyPayment']
        );
        mysqli_stmt_execute($stmtLoan);

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
    } finally {
        // Close all statements
        if (isset($stmtEmployer)) mysqli_stmt_close($stmtEmployer);
        if (isset($stmtDocs)) mysqli_stmt_close($stmtDocs);
        if (isset($stmtLoan)) mysqli_stmt_close($stmtLoan);
        mysqli_close($conn);
    }
} else {
    $response = array(
        'status' => 'error',
        'message' => 'Invalid request method'
    );
    echo json_encode($response);
}
?>