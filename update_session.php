<?php
session_start();

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$employeeID = $data['employeeID'] ?? '';
$loanApplicationID = $data['loanApplicationID'] ?? '';

// Update session data
if (isset($_SESSION['reportData']) && is_array($_SESSION['reportData'])) {
    $tempData = [];
    
    foreach ($_SESSION['reportData'] as $entry) {
        if ($loanApplicationID) {
            // For loan entries
            if ($entry['employeeID'] !== $employeeID || 
                $entry['loanApplicationID'] !== $loanApplicationID) {
                $tempData[] = $entry;
            }
        } else {
            // For member entries
            if ($entry['employeeID'] !== $employeeID) {
                $tempData[] = $entry;
            }
        }
    }
    
    $_SESSION['reportData'] = $tempData;
}

// Send response
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?> 