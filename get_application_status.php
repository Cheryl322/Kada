<?php
session_start();
include "dbconnect.php";

if (!isset($_GET['id'])) {
    exit(json_encode(['error' => 'No application ID provided']));
}

$applicationId = $_GET['id'];

// Fetch application details
$sql = "SELECT a.*, m.memberName, m.email,
        (SELECT GROUP_CONCAT(CONCAT(status_name, ':', status_date) ORDER BY status_date)
         FROM application_status 
         WHERE application_id = a.application_id) as status_history
        FROM applications a
        JOIN tb_member m ON a.member_id = m.employeeId
        WHERE a.application_id = ? AND a.member_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $applicationId, $_SESSION['employeeID']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

// Format status history
$history = [];
foreach(explode(',', $data['status_history']) as $status) {
    list($name, $date) = explode(':', $status);
    $history[] = [
        'status' => $name,
        'date' => date('d/m/Y', strtotime($date))
    ];
}

$response = [
    'application_id' => $data['application_id'],
    'memberName' => $data['memberName'],
    'email' => $data['email'],
    'year' => date('Y', strtotime($data['application_date'])),
    'category' => $data['category'],
    'status_history' => $history
];

echo json_encode($response);
?> 