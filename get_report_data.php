<?php
header('Content-Type: application/json');
include 'dbconnect.php';

try {
    // Check if we're requesting all IDs
    if (isset($_GET['getAllIds']) && $_GET['getAllIds'] === 'true') {
        $type = $_GET['type'];
        $search = isset($_GET['search']) ? $_GET['search'] : '';

        if ($type === 'ahli') {
            $sql = "SELECT m.employeeID as id
                    FROM tb_member m
                    LEFT JOIN (
                        SELECT memberRegistrationID, regisStatus
                        FROM tb_memberregistration_memberapplicationdetails
                        WHERE regisStatus = 'Diluluskan'
                        GROUP BY memberRegistrationID
                        HAVING regisStatus = 'Diluluskan'
                    ) md ON m.employeeID = md.memberRegistrationID
                    WHERE md.regisStatus = 'Diluluskan'";
            
            if (!empty($search)) {
                $sql .= " AND (m.employeeID LIKE ? OR m.memberName LIKE ?)";
            }
        } else {
            $sql = "SELECT l.loanApplicationID as id
                    FROM tb_loanapplication l
                    JOIN tb_member m ON l.employeeID = m.employeeID
                    WHERE l.loanStatus = 'Diluluskan'";
            
            if (!empty($search)) {
                $sql .= " AND (l.loanApplicationID LIKE ? OR m.memberName LIKE ?)";
            }
        }

        $stmt = $conn->prepare($sql);
        
        if (!empty($search)) {
            $searchParam = "%$search%";
            $stmt->bind_param('ss', $searchParam, $searchParam);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        
        $allIds = [];
        while ($row = $result->fetch_assoc()) {
            $allIds[] = $row['id'];
        }

        echo json_encode(['allIds' => $allIds]);
        exit;
    }

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $type = isset($_GET['type']) ? $_GET['type'] : 'ahli';
    $fromDate = isset($_GET['fromDate']) ? $_GET['fromDate'] : '';
    $toDate = isset($_GET['toDate']) ? $_GET['toDate'] : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
    $offset = ($page - 1) * $limit;

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
    if ($type === 'ahli') {
        $countQuery = "SELECT COUNT(*) as total 
                      FROM tb_member m
                      LEFT JOIN (
                          SELECT memberRegistrationID, regisStatus
                          FROM tb_memberregistration_memberapplicationdetails
                          WHERE regisStatus = 'Diluluskan'
                          GROUP BY memberRegistrationID
                          HAVING regisStatus = 'Diluluskan'
                      ) md ON m.employeeID = md.memberRegistrationID
                      WHERE md.regisStatus = 'Diluluskan'";
        
        if (!empty($search)) {
            $countQuery .= " AND (m.employeeID LIKE '%$search%' OR m.memberName LIKE '%$search%')";
        }
    } else {
        $countQuery = "SELECT COUNT(*) as total 
                      FROM tb_loanapplication l
                      JOIN tb_member m ON l.employeeID = m.employeeID
                      WHERE l.loanStatus = 'Diluluskan'";
        
        if (!empty($search)) {
            $countQuery .= " AND (l.loanApplicationID LIKE '%$search%' OR m.memberName LIKE '%$search%')";
        }
    }

    $countResult = $conn->query($countQuery);
    $totalRecords = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalRecords / $limit);

    $members = [];

    if ($type === 'ahli') {
        $sql = "SELECT m.employeeID, m.memberName, m.created_at 
                FROM tb_member m
                LEFT JOIN (
                    SELECT memberRegistrationID, regisStatus
                    FROM tb_memberregistration_memberapplicationdetails
                    WHERE regisStatus = 'Diluluskan'
                    GROUP BY memberRegistrationID
                    HAVING regisStatus = 'Diluluskan'
                ) md ON m.employeeID = md.memberRegistrationID
                WHERE md.regisStatus = 'Diluluskan'";
        
        if (!empty($search)) {
            $sql .= " AND (m.employeeID LIKE ? OR m.memberName LIKE ?)";
        }
        
        $sql .= " ORDER BY m.created_at DESC LIMIT ? OFFSET ?";
    } else {
        $sql = "SELECT 
                    l.loanApplicationID, 
                    m.memberName, 
                    l.loanApplicationDate as created_at,
                    l.loanStatus
                FROM tb_loanapplication l
                JOIN tb_member m ON l.employeeID = m.employeeID
                WHERE l.loanStatus = 'Diluluskan'";
        
        if (!empty($search)) {
            $sql .= " AND (l.loanApplicationID LIKE ? OR m.memberName LIKE ?)";
        }
        
        $sql .= " ORDER BY l.loanApplicationDate DESC LIMIT ? OFFSET ?";
    }

    $stmt = $conn->prepare($sql);
    
    if (!empty($search)) {
        $searchParam = "%$search%";
        $stmt->bind_param('ssii', $searchParam, $searchParam, $limit, $offset);
    } else {
        $stmt->bind_param('ii', $limit, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }

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