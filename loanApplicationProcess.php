<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Debug log
        error_log("Starting loan application process");
        
        if (!isset($_SESSION['employeeID'])) {
            throw new Exception("No employee ID found in session");
        }
        
        $employeeID = $_SESSION['employeeID'];
        
        // Start transaction
        mysqli_begin_transaction($conn);

        // First insert into tb_loan
        $sqlLoan = "INSERT INTO tb_loan (
            employeeID,
            amountRequested,
            financingPeriod,
            monthlyInstallments,
            employerName,
            employerIC,
            basicSalary,
            netSalary,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";

        $stmtLoan = mysqli_prepare($conn, $sqlLoan);
        
        if (!$stmtLoan) {
            throw new Exception("Error preparing loan statement: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmtLoan, 'sdddssdd',
            $employeeID,
            $_POST['amountRequested'],
            $_POST['financingPeriod'],
            $_POST['monthlyPayment'],
            $_POST['employerName'],
            $_POST['employerIC'],
            $_POST['basicSalary'],
            $_POST['netSalary']
        );

        if (!mysqli_stmt_execute($stmtLoan)) {
            throw new Exception("Error executing loan statement: " . mysqli_stmt_error($stmtLoan));
        }

        // Then insert into tb_bank
        $sqlBank = "INSERT INTO tb_bank (employeeID, bankName, accountNo) 
                   VALUES (?, ?, ?)";
        
        $stmtBank = mysqli_prepare($conn, $sqlBank);
        if (!$stmtBank) {
            throw new Exception("Error preparing bank statement: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmtBank, 'sss',
            $employeeID,
            $_POST['bankName'],
            $_POST['bankAccountNo']
        );

        if (!mysqli_stmt_execute($stmtBank)) {
            throw new Exception("Error executing bank statement: " . mysqli_stmt_error($stmtBank));
        }

        // If we get here, commit the transaction
        mysqli_commit($conn);
        
        $_SESSION['status'] = "success";
        $_SESSION['message'] = "Permohonan anda telah berjaya dihantar!";
        
        error_log("Transaction successful - redirecting to success2.php");
        
        // Make sure nothing has been output before this point
        if (headers_sent($filename, $linenum)) {
            error_log("Headers already sent in $filename on line $linenum");
        }

        header("Location: success2.php");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error in loan application: " . $e->getMessage());
        
        $_SESSION['status'] = "error";
        $_SESSION['error'] = $e->getMessage();
        
        header("Location: success2.php");
        exit();
    } finally {
        if (isset($stmtLoan)) mysqli_stmt_close($stmtLoan);
        if (isset($stmtBank)) mysqli_stmt_close($stmtBank);
        mysqli_close($conn);
    }
} else {
    header("Location: permohonanloan.php");
    exit();
}
?>
