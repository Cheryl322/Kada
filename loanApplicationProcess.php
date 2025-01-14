<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        if (!isset($_SESSION['employeeID'])) {
            throw new Exception("No employee ID found in session");
        }
        
        $employeeID = $_SESSION['employeeID'];
        
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

        // Convert numeric values to proper format
        $amountRequested = floatval($_POST['amountRequested']);
        $financingPeriod = intval($_POST['financingPeriod']);
        $monthlyPayment = floatval($_POST['monthlyPayment']);
        $basicSalary = floatval($_POST['basicSalary']);
        $netSalary = floatval($_POST['netSalary']);

        if (!mysqli_stmt_bind_param($stmtLoan, 'sdddssddd',
            $employeeID,
            $amountRequested,
            $financingPeriod,
            $monthlyPayment,
            $_POST['employerName'],
            $_POST['employerIC'],
            $basicSalary,
            $netSalary
        )) {
            throw new Exception("Error binding loan parameters: " . mysqli_stmt_error($stmtLoan));
        }

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

        if (!mysqli_stmt_bind_param($stmtBank, 'sss',
            $employeeID,
            $_POST['bankName'],
            $_POST['bankAccountNo']
        )) {
            throw new Exception("Error binding bank parameters: " . mysqli_stmt_error($stmtBank));
        }

        if (!mysqli_stmt_execute($stmtBank)) {
            throw new Exception("Error executing bank statement: " . mysqli_stmt_error($stmtBank));
        }

        mysqli_commit($conn);
        
        $_SESSION['status'] = "success";
        $_SESSION['message'] = "Permohonan anda telah berjaya dihantar!";
        
        header("Location: success2.php");
        exit();

    } catch (Exception $e) {
        if (isset($conn)) {
            mysqli_rollback($conn);
        }
        error_log("Error in loan application: " . $e->getMessage());
        
        $_SESSION['status'] = "error";
        $_SESSION['error'] = $e->getMessage();
        
        header("Location: success2.php");
        exit();
    } finally {
        if (isset($stmtLoan)) mysqli_stmt_close($stmtLoan);
        if (isset($stmtBank)) mysqli_stmt_close($stmtBank);
        if (isset($conn)) mysqli_close($conn);
    }
} else {
    header("Location: permohonanloan.php");
    exit();
}
?>
