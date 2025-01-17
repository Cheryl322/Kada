<?php
include 'dbconnect.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'ahli';
$fromDate = isset($_GET['fromDate']) ? $_GET['fromDate'] : '';
$toDate = isset($_GET['toDate']) ? $_GET['toDate'] : '';
$limit = 5;
$offset = ($page - 1) * $limit;

header('Content-Type: application/json');

try {
    // Build search and date conditions
    $searchCondition = '';
    if (!empty($search)) {
        $searchCondition = " AND (m.memberName LIKE '%$search%' OR m.employeeID LIKE '%$search%')";
    }

    $dateCondition = '';
    if (!empty($fromDate) && !empty($toDate)) {
        // Add one day to toDate to make it inclusive
        $endDate = date('Y-m-d', strtotime($toDate . ' +1 day'));
        
        if ($type === 'pembiayaan') {
            $dateCondition = " AND DATE(l.created_at) >= '$fromDate' AND DATE(l.created_at) < '$endDate'";
        } else {
            $dateCondition = " AND DATE(m.created_at) >= '$fromDate' AND DATE(m.created_at) < '$endDate'";
        }
    }

    // Get total records for pagination
    if ($type === 'pembiayaan') {
        $countQuery = "SELECT COUNT(*) as total FROM tb_loan l 
                       JOIN tb_member m ON l.employeeID = m.employeeID 
                       WHERE 1=1 $dateCondition $searchCondition";
    } else {
        $countQuery = "SELECT COUNT(*) as total FROM tb_member m 
                       WHERE 1=1 $dateCondition $searchCondition";
    }

    $countResult = $conn->query($countQuery);
    $totalRecords = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalRecords / $limit);

    $members = [];

    // Get records for current page (limited to 5 rows)
    if ($type === 'pembiayaan') {
        $query = "SELECT l.loanID, m.employeeID, m.memberName, l.amountRequested, l.created_at 
                  FROM tb_loan l 
                  JOIN tb_member m ON l.employeeID = m.employeeID 
                  WHERE 1=1 $dateCondition $searchCondition
                  ORDER BY l.created_at DESC 
                  LIMIT $offset, $limit";
    } else {
        $query = "SELECT employeeID, memberName, created_at 
                  FROM tb_member m 
                  WHERE 1=1 $dateCondition $searchCondition
                  ORDER BY created_at DESC 
                  LIMIT $offset, $limit";
    }

    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        if ($type === 'pembiayaan') {
            $members[] = [
                'employeeID' => $row['employeeID'],
                'memberName' => $row['memberName'],
                'amountRequested' => $row['amountRequested'],
                'created_at' => $row['created_at']
            ];
        } else {
            $members[] = [
                'employeeID' => $row['employeeID'],
                'memberName' => $row['memberName'],
                'created_at' => $row['created_at']
            ];
        }
    }

    // Return JSON response with additional pagination info
    echo json_encode([
        'members' => $members,
        'totalPages' => $totalPages,
        'currentPage' => $page,
        'totalRecords' => $totalRecords
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close(); 