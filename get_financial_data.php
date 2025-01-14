<?php
session_start();
include "dbconnect.php";

if (!isset($_SESSION['employeeID'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$employeeID = $_SESSION['employeeID'];

// Fetch latest financial data
$sql = "SELECT f.*, m.modalShare, fc.* 
        FROM tb_financialstatus f
        JOIN tb_member m ON m.employeeID = ?
        JOIN tb_memberregistration_feesandcontribution fc ON fc.employeeID = ?
        WHERE f.accountID IN (
            SELECT accountID 
            FROM tb_member_financialstatus 
            WHERE employeeID = ?
        )
        ORDER BY f.dateUpdated DESC LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'sss', $employeeID, $employeeID, $employeeID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

header('Content-Type: application/json');
echo json_encode($data);
?> 