<?php
session_start();
include "dbconnect.php";

if (isset($_POST['employeeID'])) {
    $employeeID = $_POST['employeeID'];
    
    $sql = "SELECT loanStatus, loanApplicationDate 
            FROM tb_loanapplication 
            WHERE employeeID = ? 
            ORDER BY loanApplicationDate DESC 
            LIMIT 1";
            
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $employeeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    if ($row) {
        echo json_encode([
            'status' => $row['loanStatus'],
            'date' => date('d/m/Y', strtotime($row['loanApplicationDate']))
        ]);
    } else {
        echo json_encode([
            'status' => 'Belum Selesai',
            'date' => date('d/m/Y')
        ]);
    }
    
    mysqli_close($conn);
}
?> 