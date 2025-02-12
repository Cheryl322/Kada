<?php
session_start();

// Add this near the top of the file, after session_start()
include 'dbconnect.php';

// Yearly query
$yearlyQuery = "SELECT 
    YEAR(created_at) as year,
    COUNT(DISTINCT CASE WHEN type = 'member' THEN employeeID END) as new_members,
    COUNT(DISTINCT CASE WHEN type = 'loan' THEN loanApplicationID END) as loan_applications,
    SUM(CASE WHEN type = 'loan' THEN amountRequested ELSE 0 END) as total_loan_amount
FROM (
    SELECT employeeID, NULL as loanApplicationID, created_at, 'member' as type, 0 as amountRequested 
    FROM tb_member
    UNION ALL
    SELECT employeeID, loanApplicationID, created_at, 'loan' as type, amountRequested 
    FROM tb_loan
) combined_data
GROUP BY YEAR(created_at)
ORDER BY year DESC";

$yearlyResult = mysqli_query($conn, $yearlyQuery);

// Summary query (monthly/yearly based on report type)
$reportType = isset($_POST['reportType']) ? $_POST['reportType'] : 'yearly';

if ($reportType === 'monthly') {
    $summaryQuery = "SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as period,
        COUNT(DISTINCT CASE WHEN type = 'member' THEN employeeID END) as new_members,
        COUNT(DISTINCT CASE WHEN type = 'loan' THEN loanApplicationID END) as loan_applications,
        SUM(CASE WHEN type = 'loan' THEN amountRequested ELSE 0 END) as total_loan_amount
    FROM (
        SELECT employeeID, NULL as loanApplicationID, created_at, 'member' as type, 0 as amountRequested 
        FROM tb_member
        UNION ALL
        SELECT employeeID, loanApplicationID, created_at, 'loan' as type, amountRequested 
        FROM tb_loan
    ) combined_data
    WHERE YEAR(created_at) = YEAR(CURRENT_DATE)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY period ASC";
} else {
    $summaryQuery = $yearlyQuery;
}

$summaryResult = mysqli_query($conn, $summaryQuery);

// Add error checking
if (!$summaryResult) {
    error_log("MySQL Error: " . mysqli_error($conn));
}

// Include header after handling redirects
include 'headeradmin.php';
echo '<title>Cek Laporan</title>';

// Add delete period functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['period']) && isset($_POST['reportType'])) {
    include 'dbconnect.php';
    
    $period = $_POST['period'];
    $reportType = $_POST['reportType'];
    
    // Format date condition based on report type
    $dateFormat = $reportType === 'monthly' ? '%Y-%m' : '%Y';
    
    try {
        // Remove the period data from session
        if (isset($_SESSION['reportData'])) {
            $_SESSION['reportData'] = array_filter($_SESSION['reportData'], function($item) use ($period) {
                $itemPeriod = date('Y-m', strtotime($item['tarikh_daftar']));
                return $itemPeriod !== $period;
            });
        }
        
        $_SESSION['success_message'] = "Entri berjaya dipadamkan.";
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Ralat semasa memadamkan entri: " . $e->getMessage();
    }
    
    // Redirect to refresh the page
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Initialize reportData array and reportType if not exists
if (!isset($_SESSION['reportData'])) {
    $_SESSION['reportData'] = [];
}
if (!isset($_SESSION['reportType'])) {
    $_SESSION['reportType'] = 'member';
}

// Define $isLoanReport early and ensure it's always set
$isLoanReport = $_SESSION['reportType'] === 'pembiayaan';

// Process new selections from hasilreport.php
if (isset($_POST['selected_members']) && is_array($_POST['selected_members'])) {
    try {
        // Include database connection
        require_once 'dbconnect.php';
        
        if (!isset($conn) || !$conn) {
            throw new Exception("Database connection failed");
        }
        
        // Initialize reportType if not set
        if (!isset($_SESSION['reportType'])) {
            $_SESSION['reportType'] = 'member';
        }
        
        // Safely get reportType from POST or use session value
        $reportType = isset($_POST['reportType']) ? $_POST['reportType'] : $_SESSION['reportType'];
        $_SESSION['reportType'] = $reportType;
        
        $selectedMembers = array_filter($_POST['selected_members']); // Remove empty values
        $isLoanReport = $reportType === 'pembiayaan';
        
        // Initialize reportData if not set
        if (!isset($_SESSION['reportData'])) {
            $_SESSION['reportData'] = array();
        }
        
        // Create existingIds array
        $existingIds = array();
        if (!empty($_SESSION['reportData']) && is_array($_SESSION['reportData'])) {
            foreach ($_SESSION['reportData'] as $item) {
                if (is_array($item)) {  // Add check to ensure $item is an array
                    if ($isLoanReport && isset($item['employeeID'], $item['loanApplicationID'])) {
                        $existingIds[] = $item['employeeID'] . '_' . $item['loanApplicationID'];
                    } elseif (isset($item['employeeID'])) {
                        $existingIds[] = $item['employeeID'];
                    }
                }
            }
        }
        
        if (!empty($selectedMembers)) {
            // Filter out already existing members/loans
            $newMembers = array_filter($selectedMembers, function($id) use ($existingIds, $isLoanReport) {
                if ($isLoanReport) {
                    // For loan reports, check the combined ID
                    return !in_array($id, $existingIds);
                } else {
                    // For member reports, check just the ID
                    return !in_array($id, $existingIds);
                }
            });
            
            if (!empty($newMembers)) {
                if ($isLoanReport) {
                    $placeholders = implode(',', array_fill(0, count($newMembers), '?'));
                    $query = "SELECT 
                                m.memberName,
                                m.employeeID,
                                DATE_FORMAT(m.created_at, '%d/%m/%Y') as tarikh_daftar,
                                DATE_FORMAT(l.created_at, '%d/%m/%Y') as tarikh_pembiayaan,
                                l.loanID as loanID,
                                l.amountRequested,
                                l.loanApplicationID,
                                'pembiayaan' as reportType
                             FROM tb_member m
                             INNER JOIN tb_loan l ON m.employeeID = l.employeeID
                             WHERE l.loanApplicationID IN ($placeholders)";
                    
                    $stmt = mysqli_prepare($conn, $query);
                    if ($stmt) {
                        $types = str_repeat('s', count($newMembers));
                        mysqli_stmt_bind_param($stmt, $types, ...$newMembers);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        
                        while ($row = mysqli_fetch_assoc($result)) {
                            $entryExists = false;
                            $originalEntryFound = false;
                            $originalEntryIndex = null;
                            
                            // First, look for an existing member entry without loan details
                            foreach ($_SESSION['reportData'] as $index => $existingRow) {
                                if ($existingRow['employeeID'] === $row['employeeID']) {
                                    if ($existingRow['loanApplicationID'] === $existingRow['employeeID']) {
                                        // This is an original member entry without loan details
                                        $originalEntryIndex = $index;
                                        $originalEntryFound = true;
                                        break;
                                    } else if ($existingRow['loanApplicationID'] === $row['loanApplicationID']) {
                                        // This exact loan application already exists
                                        $entryExists = true;
                                        break;
                                    }
                                }
                            }
                            
                            if ($originalEntryFound) {
                                // Update the original member entry with the first loan details
                                $_SESSION['reportData'][$originalEntryIndex]['loanApplicationID'] = $row['loanApplicationID'];
                                $_SESSION['reportData'][$originalEntryIndex]['tarikh_pembiayaan'] = $row['tarikh_pembiayaan'];
                                $_SESSION['reportData'][$originalEntryIndex]['reportType'] = 'pembiayaan';
                                $_SESSION['reportData'][$originalEntryIndex]['amountRequested'] = $row['amountRequested'];
                            } else if (!$entryExists) {
                                // Add as new entry if it's not a duplicate loan application
                                $row['reportType'] = 'pembiayaan';
                                $_SESSION['reportData'][] = $row;
                            }
                        }
                    }
                } else {
                    // For member report, add reportType
                    $placeholders = implode(',', array_fill(0, count($newMembers), '?'));
                    $query = "SELECT 
                                m.memberName,
                                m.employeeID,
                                DATE_FORMAT(m.created_at, '%d/%m/%Y') as tarikh_daftar,
                                '-' as tarikh_pembiayaan,
                                '-' as loanID,
                                '-' as amountRequested,
                                m.employeeID as loanApplicationID,
                                'member' as reportType
                             FROM tb_member m
                             WHERE m.employeeID IN ($placeholders)";
                             
                    $stmt = mysqli_prepare($conn, $query);
                    if ($stmt) {
                        $types = str_repeat('s', count($newMembers));
                        mysqli_stmt_bind_param($stmt, $types, ...$newMembers);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        
                        while ($row = mysqli_fetch_assoc($result)) {
                            $memberExists = false;
                            foreach ($_SESSION['reportData'] as $existingRow) {
                                if ($existingRow['employeeID'] === $row['employeeID']) {
                                    $memberExists = true;
                                    break;
                                }
                            }
                            
                            if (!$memberExists) {
                                $_SESSION['reportData'][] = $row;
                            }
                        }
                    }
                }
                
                if ($stmt) {
                    mysqli_stmt_close($stmt);
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error in processing: " . $e->getMessage());
        $_SESSION['error_message'] = "Error processing data: " . $e->getMessage();
    }
}

// Modified sorting to handle multiple loan entries
if (!empty($_SESSION['reportData'])) {
    usort($_SESSION['reportData'], function($a, $b) {
        // First sort by member name
        $nameCompare = strcmp($a['memberName'], $b['memberName']);
        if ($nameCompare !== 0) {
            return $nameCompare;
        }
        
        // If same member, sort by loan date in descending order
        if (!empty($a['tarikh_pembiayaan']) && !empty($b['tarikh_pembiayaan'])) {
            // Convert dates to timestamps for comparison
            $dateA = strtotime(str_replace('/', '-', $a['tarikh_pembiayaan']));
            $dateB = strtotime(str_replace('/', '-', $b['tarikh_pembiayaan']));
            return $dateB - $dateA; // Descending order (newest first)
        }
        
        return 0;
    });
}

$reportData = $_SESSION['reportData'];

// Use the session data for display
$reportData = $_SESSION['reportData'];

// Add this function at the top of the file
function convertMonthToMalay($date) {
    $english_months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
    $malay_months = array('Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember');
    
    return str_replace($english_months, $malay_months, $date);
}

?>

<!-- Update the Cek Laporan section with container styling -->
<div class="report-container" style="margin: 20px; margin-top: 100px; background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 style="color: rgb(34, 119, 210); margin-bottom: 0;">Cek Laporan</h3>
        <div style="width: 300px;">
            <input type="text" id="searchInput" class="form-control" placeholder="Cari...">
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover" id="dataTable">
            <thead class="table-light">
                <tr>
                    <th>No.</th>
                    <th>Nama</th>
                    <th>No. Anggota</th>
                    <th>Tarikh Daftar</th>
                    <th>Penyata Ahli</th>
                    <th>No. Pembiayaan</th>
                    <th>Tarikh Pembiayaan</th>
                    <th>Penyata Kewangan</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Calculate pagination variables
                $itemsPerPage = 10;
                $totalItems = count($reportData);
                $totalPages = ceil($totalItems / $itemsPerPage);
                $currentPage = isset($_GET['page']) ? max(1, min($totalPages, intval($_GET['page']))) : 1;
                $startIndex = ($currentPage - 1) * $itemsPerPage;
                
                // Get items for current page
                $pageItems = array_slice($reportData, $startIndex, $itemsPerPage);
                
                if (!empty($pageItems)): 
                    foreach ($pageItems as $index => $data): 
                        $displayIndex = $startIndex + $index + 1;
                ?>
                    <tr id="row_<?php echo $data['employeeID'] . '_' . ($data['reportType'] === 'pembiayaan' ? $data['loanApplicationID'] : ''); ?>">
                        <td><?php echo $displayIndex; ?></td>
                        <td><?php echo htmlspecialchars($data['memberName']); ?></td>
                        <td><?php echo htmlspecialchars($data['employeeID']); ?></td>
                        <td><?php echo htmlspecialchars($data['tarikh_daftar']); ?></td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <button class="btn btn-primary" onclick="viewMemberStatement('<?php echo $data['employeeID']; ?>')">
                                    Lihat Penyata
                                </button>
                                <button class="btn btn-success" onclick="downloadMemberStatement('<?php echo $data['employeeID']; ?>')">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </td>
                        <td><?php echo $data['reportType'] === 'member' ? '-' : htmlspecialchars($data['loanApplicationID']); ?></td>
                        <td><?php echo $data['reportType'] === 'member' ? '-' : htmlspecialchars($data['tarikh_pembiayaan']); ?></td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <?php if ($data['reportType'] === 'pembiayaan'): ?>
                                    <button class="btn btn-primary" onclick="viewFinancialStatement('<?php echo $data['loanApplicationID']; ?>', '<?php echo $data['reportType']; ?>')">
                                        Lihat Penyata
                                    </button>
                                    <button class="btn btn-success" onclick="downloadFinancialStatement('<?php echo $data['loanApplicationID']; ?>', '<?php echo $data['reportType']; ?>')">
                                        <i class="fas fa-download"></i>
                                    </button>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <form method="POST" style="display: inline;" onsubmit="return handleDelete(this, '<?php echo $data['employeeID']; ?>', '<?php echo ($data['reportType'] === 'pembiayaan' ? $data['loanApplicationID'] : ''); ?>')">
                                <input type="hidden" name="delete_employeeID" value="<?php echo htmlspecialchars($data['employeeID']); ?>">
                                <?php if ($data['reportType'] === 'pembiayaan'): ?>
                                    <input type="hidden" name="delete_loanApplicationID" value="<?php echo htmlspecialchars($data['loanApplicationID']); ?>">
                                <?php endif; ?>
                                <button type="submit" class="btn btn-danger">Padam</button>
                            </form>
                        </td>
                    </tr>
                <?php 
                    endforeach; 
                else: 
                ?>
                    <tr>
                        <td colspan="9" class="text-center">Tiada rekod ditemui</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="d-flex justify-content-end mt-3">
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <!-- Previous button -->
                    <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <!-- Page numbers -->
                    <?php for ($i = 1; $i <= max(1, $totalPages); $i++): ?>
                        <?php if ($i == 1 || $i == $totalPages || abs($i - $currentPage) <= 2): ?>
                            <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php elseif (abs($i - $currentPage) == 3): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Next button -->
                    <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Ringkasan Laporan container -->
<div class="report-summary" style="margin: 20px; background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
    <h3 style="color: rgb(34, 119, 210); margin-bottom: 20px;">Ringkasan Laporan</h3>
    
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Bulan</th>
                    <th>Jumlah Ahli Baru</th>
                    <th>Jumlah Pembiayaan</th>
                    <th>Nilai Pembiayaan (RM)</th>
                    <th>Laporan</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $months = array(
                    '01' => 'Januari', '02' => 'Februari', '03' => 'Mac',
                    '04' => 'April', '05' => 'Mei', '06' => 'Jun',
                    '07' => 'Julai', '08' => 'Ogos', '09' => 'September',
                    '10' => 'Oktober', '11' => 'November', '12' => 'Disember'
                );

                if ($summaryResult && mysqli_num_rows($summaryResult) > 0): 
                    $monthlyData = array();
                    while ($row = mysqli_fetch_assoc($summaryResult)) {
                        $month = date('m', strtotime($row['period']));
                        $monthlyData[$month] = $row;
                    }

                    $total_members = 0;
                    $total_loans = 0;
                    $total_amount = 0;

                    foreach ($months as $monthNum => $monthName):
                        $row = isset($monthlyData[$monthNum]) ? $monthlyData[$monthNum] : array(
                            'new_members' => 0,
                            'loan_applications' => 0,
                            'total_loan_amount' => 0
                        );

                        $total_members += $row['new_members'];
                        $total_loans += $row['loan_applications'];
                        $total_amount += $row['total_loan_amount'];
                        
                        $period = date('Y') . '-' . $monthNum;
                ?>
                    <tr>
                        <td><?php echo $monthName . ' ' . date('Y'); ?></td>
                        <td class="text-center"><?php echo number_format($row['new_members']); ?></td>
                        <td class="text-center"><?php echo number_format($row['loan_applications']); ?></td>
                        <td class="text-end"><?php echo number_format($row['total_loan_amount'], 2); ?></td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button class="btn btn-success" onclick="viewPeriodStatement('<?php echo $period; ?>', 'monthly')">
                                    Lihat Penyata
                                </button>
                                <button class="btn btn-success" onclick="window.location.href='download_report_monthly.php?period=<?php echo $period; ?>'">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-danger" onclick="deletePeriodStatement('<?php echo $period; ?>')">
                                Padam
                            </button>
                        </td>
                    </tr>
                <?php 
                    endforeach;
                ?>
                    <tr style="background-color: #f0f0f0; font-weight: bold;">
                        <td>Jumlah Keseluruhan</td>
                        <td class="text-center"><?php echo number_format($total_members); ?></td>
                        <td class="text-center"><?php echo number_format($total_loans); ?></td>
                        <td class="text-end"><?php echo number_format($total_amount, 2); ?></td>
                        <td colspan="2"></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Tiada rekod ditemui</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Perincian Tahunan container -->
<div class="report-summary" style="margin: 20px; background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
    <h3 style="color: rgb(34, 119, 210); margin-bottom: 20px;">Perincian Tahunan</h3>
    
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Tahun</th>
                    <th>Jumlah Ahli Baru</th>
                    <th>Jumlah Pembiayaan</th>
                    <th>Nilai Pembiayaan (RM)</th>
                    <th>Laporan</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($yearlyResult && mysqli_num_rows($yearlyResult) > 0): 
                    while ($row = mysqli_fetch_assoc($yearlyResult)): 
                ?>
                    <tr>
                        <td><?php echo $row['year']; ?></td>
                        <td><?php echo number_format($row['new_members']); ?></td>
                        <td><?php echo number_format($row['loan_applications']); ?></td>
                        <td><?php echo number_format($row['total_loan_amount'], 2); ?></td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-success" onclick="viewPeriodStatement('<?php echo $row['year']; ?>', 'yearly')">
                                    Lihat Penyata
                                </button>
                                <button class="btn btn-success" onclick="window.location.href='download_report_yearly.php?period=<?php echo $row['year']; ?>-01'">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-danger" onclick="deletePeriodStatement('<?php echo $row['year']; ?>-01')">
                                Padam
                            </button>
                        </td>
                    </tr>
                <?php 
                    endwhile;
                    // Reset pointer for totals
                    mysqli_data_seek($yearlyResult, 0);
                    $total_members = 0;
                    $total_loans = 0;
                    $total_amount = 0;
                    while ($row = mysqli_fetch_assoc($yearlyResult)) {
                        $total_members += $row['new_members'];
                        $total_loans += $row['loan_applications'];
                        $total_amount += $row['total_loan_amount'];
                    }
                ?>
                    <tr style="background-color: #f0f0f0; font-weight: bold;">
                        <td>Jumlah Keseluruhan</td>
                        <td><?php echo number_format($total_members); ?></td>
                        <td><?php echo number_format($total_loans); ?></td>
                        <td><?php echo number_format($total_amount, 2); ?></td>
                        <td colspan="2"></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Tiada rekod ditemui</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Move the back button here -->
<div class="d-flex justify-content-start mt-4 mb-5" style="margin-left: 20px;">
    <button type="button" class="btn btn-primary" onclick="confirmBack()">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </button>
</div>

<!-- Modal for viewing statements -->
<div class="modal fade" id="statementModal" tabindex="-1" aria-labelledby="statementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statementModalLabel">Penyata</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="height: 80vh; padding: 0;">
                <iframe id="statementFrame" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal for viewing statements -->
<div class="modal fade" id="periodStatementModal" tabindex="-1" aria-labelledby="periodStatementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="periodStatementModalLabel">Penyata</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="height: 80vh; padding: 0;">
                <iframe id="periodStatementFrame" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Modify the summary report modal structure -->
<div class="modal fade" id="summaryReportModal" tabindex="-1" aria-labelledby="summaryReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="summaryReportModalLabel">Ringkasan Penyata</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Maklumat Ahli</h6>
                                <p class="mb-1">Jumlah Ahli Baru: <strong id="newMemberCount">0</strong></p>
                                <p class="mb-1">Jumlah Keseluruhan Ahli: <strong id="totalMemberCount">0</strong></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Maklumat Pembiayaan</h6>
                                <p class="mb-1">Jumlah Pembiayaan Baru: <strong id="newLoanCount">0</strong></p>
                                <p class="mb-1">Nilai Pembiayaan: <strong>RM <span id="totalLoanAmount">0.00</span></strong></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Status</th>
                                <th>Jumlah</th>
                                <th>Peratus</th>
                            </tr>
                        </thead>
                        <tbody id="loanStatusTable">
                            <!-- Loan status data will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success" onclick="downloadPeriodStatement(currentPeriod, currentReportType)">
                    <i class="fas fa-download me-2"></i>Muat Turun
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Update the custom modal for back confirmation -->
<div class="modal fade" id="backConfirmationModal" tabindex="-1" aria-labelledby="backConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pengesahan Laporan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <p>Adakah anda pasti untuk kembali menghasilkan laporan baru?</p>
            </div>
            <div class="modal-footer justify-content-end">
                <button type="button" class="btn rounded-2" style="background-color: #E9969E; color: white; border: none; padding: 6px 20px;" data-bs-dismiss="modal">Tidak</button>
                <button type="button" class="btn rounded-2" style="background-color: #8CD3C5; color: white; border: none; padding: 6px 20px;" onclick="proceedBack()">Ya</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewMemberStatement(employeeID) {
    const modal = new bootstrap.Modal(document.getElementById('statementModal'));
    const frame = document.getElementById('statementFrame');
    
    frame.src = `view_report_member.php?id=${employeeID}`;
    document.getElementById('statementModalLabel').textContent = 'Penyata Ahli';
    
    frame.dataset.employeeId = employeeID;
    modal.show();
}

function viewFinancialStatement(id, reportType) {
    const modal = new bootstrap.Modal(document.getElementById('statementModal'));
    const frame = document.getElementById('statementFrame');
    
    // Set the source URL for the iframe
    frame.src = `view_report_loan.php?id=${id}`;
    
    // Update modal title
    document.getElementById('statementModalLabel').textContent = 'Penyata Kewangan';
    
    // Show the modal
    modal.show();
    
    // Add error handling
    frame.onerror = function() {
        console.error('Failed to load financial statement');
        alert('Gagal memuat penyata. Sila cuba lagi.');
    };
}

function confirmDeleteEntry(form) {
    if (confirm('Adakah anda pasti mahu memadamkan entri ini?')) {
        form.submit();
    }
}

function downloadMemberStatement(employeeID) {
    window.location.href = `download_report_ahli.php?employeeID=${employeeID}`;
}

function downloadFinancialStatement(id, reportType) {
    const url = reportType === 'pembiayaan' ? 
        `download_report_loan.php?loanApplicationID=${id}` : 
        `download_report_ahli.php?employeeID=${id}`;
    window.location.href = url;
}

function deletePeriodEntry(period) {
    if (confirm('Adakah anda pasti mahu memadamkan entri ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo $_SERVER["PHP_SELF"]; ?>';  // Submit to same page
        
        const periodInput = document.createElement('input');
        periodInput.type = 'hidden';
        periodInput.name = 'period';
        periodInput.value = period;
        
        const typeInput = document.createElement('input');
        typeInput.type = 'hidden';
        typeInput.name = 'reportType';
        typeInput.value = '<?php echo $_SESSION["reportType"]; ?>';
        
        form.appendChild(periodInput);
        form.appendChild(typeInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function confirmBack() {
    const modal = new bootstrap.Modal(document.getElementById('backConfirmationModal'));
    modal.show();
}

function proceedBack() {
    window.location.href = 'hasilreport.php';
}

function closeModal() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('statementModal'));
    if (modal) {
        modal.hide();
    }
}

// Add this new function to handle the download from within the modal
function downloadCurrentStatement() {
    const frame = document.getElementById('statementFrame');
    const employeeID = frame.dataset.employeeId;
    
    if (employeeID) {
        downloadMemberStatement(employeeID);
    } else {
        alert('Ralat semasa memuat turun penyata. Sila cuba lagi.');
    }
}

function handleDelete(form, employeeID, loanApplicationID) {
    if (!confirm('Adakah anda pasti mahu memadamkan entri ini?')) {
        return false;
    }
    
    // Remove the row from the table
    const rowId = `row_${employeeID}${loanApplicationID ? '_' + loanApplicationID : ''}`;
    const row = document.getElementById(rowId);
    if (row) {
        row.remove();
        updateRowNumbers();
        
        // Update session data by removing the entry
        updateSessionData(employeeID, loanApplicationID);
    }
    
    return false;
}

// Add new function to update session data
function updateSessionData(employeeID, loanApplicationID) {
    fetch('update_session.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            employeeID: employeeID,
            loanApplicationID: loanApplicationID
        })
    })
    .catch(error => {
        console.error('Error updating session:', error);
    });
}

function updateRowNumbers() {
    const tbody = document.querySelector('#dataTable tbody');
    const rows = tbody.querySelectorAll('tr');
    rows.forEach((row, index) => {
        const firstCell = row.querySelector('td:first-child');
        if (firstCell) {
            firstCell.textContent = index + 1;
        }
    });
}

function viewPeriodStatement(period, reportType) {
    // Check if period contains only year (for yearly reports)
    if (period.length === 7) { // Format: "2024-01" (monthly)
        window.location.href = 'view_report_monthly.php?period=' + period;
    } else { // Format: "2024" (yearly)
        window.location.href = 'view_report_yearly.php?period=' + period;
    }
}

function downloadPeriodStatement(period, reportType) {
    window.location.href = 'download_report_monthly.php?period=' + period;
}
</script>

<style>
.modal-content {
    border-radius: 8px;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid #dee2e6;
}

.modal-header .btn-close {
    font-size: 10px;
    opacity: 0.7;
    padding: 8px;
}

.modal-body {
    padding: 15px 20px;
    border-bottom: 1px solid #dee2e6;
}

.modal-body p {
    margin: 0;
    color: #666;
    font-size: 0.95rem;
}

.modal-footer {
    padding: 15px 20px;
    border-top: none;
}

.modal-title {
    font-size: 1rem;
    font-weight: 500;
    color: #333;
}

.btn {
    font-size: 0.9rem;
}

/* Make modal background slightly darker */
.modal-backdrop.show {
    opacity: 0.3;
}

/* Adjust modal width */
.modal-dialog {
    max-width: 600px;
}

/* Add gap between buttons */
.modal-footer .btn + .btn {
    margin-left: 8px;
}

/* Add these styles to your existing <style> section */
.table {
    color: black !important;
}

.table tbody tr,
.table tbody td,
.table tbody th {
    color: black !important;
    opacity: 1 !important;
    font-weight: 400 !important;
}

/* Reset any Bootstrap text utilities that might be affecting the table */
.table .text-muted,
.table .text-secondary,
.table .text-black-50 {
    color: black !important;
}

/* Ensure the table-light class doesn't affect text opacity */
.table-light,
.table-light td,
.table-light th {
    color: black !important;
    opacity: 1 !important;
}

.table > :not(caption) > * > * {
    border-bottom-width: 1px;
    border-bottom-color: #dee2e6;
}

.table-light {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    border-top: 1px solid #dee2e6;
}

.card {
    border-radius: 8px;
    border: none;
}

.card-header {
    border-top-left-radius: 8px !important;
    border-top-right-radius: 8px !important;
}

.table {
    margin-bottom: 0;
}

.table th {
    border-top: none;
    background-color: white;
    font-weight: 500;
}

.table td, .table th {
    padding: 12px;
    border: 1px solid #dee2e6;
}

.table tr:last-child td {
    border-bottom: none;
}

.btn-success {
    background-color: #8BC4A9;
    border-color: #8BC4A9;
    color: white;
    padding: 0.375rem 0.75rem;
}

.btn-success:hover {
    background-color: #7ab396;
    border-color: #7ab396;
}

/* Add specific styling for download button */
.btn-success i {
    font-size: 14px;
}

.me-2 {
    margin-right: 0.5rem;
}

.btn-danger {
    background-color: #ff7675;
    border-color: #ff7675;
}

.btn-danger:hover {
    background-color: #e66767;
    border-color: #e66767;
}

.btn-group {
    display: inline-flex;
}

.btn-group .btn:first-child {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.btn-group .btn:last-child {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border-left: 1px solid rgba(255,255,255,0.3);
}

/* Update the table header styles */
.table thead th {
    background-color: #f2f2f2 !important;  /* Light gray background */
    color: black !important;
    font-weight: 500;
    border: 1px solid #dee2e6;
}

/* Ensure the table-light class in thead maintains the gray background */
.table thead.table-light th {
    background-color: #f2f2f2 !important;
}

/* Add styles for the total rows in summary sections */
.report-summary .table tr:last-child {
    background-color: #f2f2f2 !important;
}

.report-summary .table tr:last-child td {
    font-weight: bold !important;
    border: 1px solid #dee2e6;
}
</style>

<?php include 'footer.php'; ?>
</rewritten_file>