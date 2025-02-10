<?php
session_start();
include 'dbconnect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Temporary debugging - Check parameters and database content
$period = $_GET['period'] ?? 'not set';
$type = $_GET['type'] ?? 'not set';

// Debug output
echo "DEBUG OUTPUT:<br>";
echo "Period: $period<br>";
echo "Type: $type<br>";

// Check tb_member data
$checkMemberQuery = "SELECT created_at FROM tb_member LIMIT 5";
$memberResult = mysqli_query($conn, $checkMemberQuery);
echo "<br>Recent member dates:<br>";
while ($row = mysqli_fetch_assoc($memberResult)) {
    echo $row['created_at'] . "<br>";
}

// Check tb_loan data
$checkLoanQuery = "SELECT created_at, amountRequested, loanStatus FROM tb_loan LIMIT 5";
$loanResult = mysqli_query($conn, $checkLoanQuery);
echo "<br>Recent loan data:<br>";
while ($row = mysqli_fetch_assoc($loanResult)) {
    echo "Date: " . $row['created_at'] . 
         " Amount: " . $row['amountRequested'] . 
         " Status: " . $row['loanStatus'] . "<br>";
}

// Exit after debugging
exit;

// Log incoming request
error_log("Received request - Period: " . ($_GET['period'] ?? 'not set') . ", Type: " . ($_GET['type'] ?? 'not set'));

if (!isset($_GET['period']) || !isset($_GET['type'])) {
    error_log("Missing parameters: period=" . ($_GET['period'] ?? 'not set') . ", type=" . ($_GET['type'] ?? 'not set'));
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

$period = $_GET['period'];
$reportType = $_GET['type'];

// Debug log the received parameters
error_log("Received parameters - Period: $period, Type: $reportType");

try {
    // Log the parameters
    error_log("Processing request for period: $period, type: $reportType");
    
    // Use the same format as the summary table
    $format = $reportType === 'monthly' ? '%Y-%m' : '%Y';
    
    // Get member data using the same query as the summary table
    $memberQuery = "SELECT COUNT(*) as count
                   FROM tb_member 
                   WHERE DATE_FORMAT(created_at, '$format') = ?";
    error_log("Member query: $memberQuery with period: $period");
    
    $stmt = mysqli_prepare($conn, $memberQuery);
    mysqli_stmt_bind_param($stmt, 's', $period);
    mysqli_stmt_execute($stmt);
    $memberResult = mysqli_stmt_get_result($stmt);
    $memberRow = mysqli_fetch_assoc($memberResult);
    error_log("Member count result: " . print_r($memberRow, true));
    
    // Get total members up to this period
    $endDate = $reportType === 'monthly' 
        ? date('Y-m-t', strtotime($period . '-01')) 
        : $period . '-12-31';
    $totalMemberQuery = "SELECT COUNT(*) as count 
                        FROM tb_member 
                        WHERE created_at <= ?";
    $stmt = mysqli_prepare($conn, $totalMemberQuery);
    mysqli_stmt_bind_param($stmt, 's', $endDate);
    mysqli_stmt_execute($stmt);
    $totalResult = mysqli_stmt_get_result($stmt);
    $totalRow = mysqli_fetch_assoc($totalResult);
    
    // Get loan data using the same query as the summary table
    $loanQuery = "SELECT 
                    COUNT(*) as count,
                    COALESCE(SUM(amountRequested), 0) as total_amount,
                    loanStatus,
                    COUNT(*) as status_count
                 FROM tb_loan 
                 WHERE DATE_FORMAT(created_at, '$format') = ?
                 GROUP BY loanStatus";
    
    $stmt = mysqli_prepare($conn, $loanQuery);
    mysqli_stmt_bind_param($stmt, 's', $period);
    mysqli_stmt_execute($stmt);
    $loanResult = mysqli_stmt_get_result($stmt);
    
    $totalLoans = 0;
    $totalAmount = 0;
    $loanStatus = [];
    
    while ($row = mysqli_fetch_assoc($loanResult)) {
        $totalLoans += $row['count'];
        $totalAmount += $row['total_amount'];
        
        if ($row['loanStatus']) {
            $loanStatus[$row['loanStatus']] = [
                'count' => (int)$row['status_count'],
                'percentage' => 0 // Will calculate after getting total
            ];
        }
    }
    
    // Calculate percentages
    foreach ($loanStatus as $status => &$info) {
        $info['percentage'] = $totalLoans > 0 ? ($info['count'] / $totalLoans) * 100 : 0;
    }
    
    $response = [
        'newMembers' => (int)$memberRow['count'],
        'totalMembers' => (int)$totalRow['count'],
        'newLoans' => $totalLoans,
        'totalLoanAmount' => (float)$totalAmount,
        'loanStatus' => $loanStatus
    ];
    
    // Log the final response
    error_log("Sending response: " . json_encode($response));
    
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in get_period_summary.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
?> 