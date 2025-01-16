<?php
include "dbconnect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['loanId']) && isset($_POST['status'])) {
    $loanId = mysqli_real_escape_string($conn, $_POST['loanId']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $sql = "UPDATE tb_loanapplication 
            SET loanStatus = ?, 
                loanApplicationDate = CASE 
                    WHEN loanStatus != ? THEN CURRENT_DATE
                    ELSE loanApplicationDate 
                END
            WHERE loanApplicationID = ?";
            
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $status, $status, $loanId);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Success";
    } else {
        echo "Error";
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "Invalid request";
}

mysqli_close($conn);
?> 