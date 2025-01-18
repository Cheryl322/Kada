<?php 
session_start();
include 'headeradmin.php';

// Add title tag right after header inclusion
echo '<title>Cek Laporan</title>';

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
    include 'dbconnect.php';
    
    // Debug output
    error_log("Selected members: " . print_r($_POST['selected_members'], true));
    error_log("Report type: " . $_POST['reportType']);
    
    try {
        $selectedMembers = $_POST['selected_members'];
        $_SESSION['reportType'] = isset($_POST['reportType']) ? $_POST['reportType'] : 'member';
        $isLoanReport = $_SESSION['reportType'] === 'pembiayaan';
        
        // Debug output
        error_log("Is loan report: " . ($isLoanReport ? 'true' : 'false'));
        
        if (!empty($selectedMembers)) {
            // Create a set of existing IDs to avoid duplicates
            $existingIds = array();
            foreach ($_SESSION['reportData'] as $item) {
                $existingIds[] = $isLoanReport ? $item['loanApplicationID'] : $item['employeeID'];
            }
            
            // Filter out already existing members/loans
            $newMembers = array_filter($selectedMembers, function($id) use ($existingIds) {
                return !in_array($id, $existingIds);
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
                            // Check if this specific loan already exists
                            $loanExists = false;
                            foreach ($_SESSION['reportData'] as $existingRow) {
                                if ($existingRow['loanApplicationID'] === $row['loanApplicationID']) {
                                    $loanExists = true;
                                    break;
                                }
                            }
                            
                            // Add new loan entry if it doesn't exist
                            if (!$loanExists) {
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
                            $_SESSION['reportData'][] = $row;
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

// Handle delete requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_index'])) {
    $indexToDelete = (int)$_POST['delete_index'];
    if (isset($_SESSION['reportData'][$indexToDelete])) {
        array_splice($_SESSION['reportData'], $indexToDelete, 1);
    }
}

// Use the session data for display
$reportData = $_SESSION['reportData'];
?>

<div class="main-content" style="margin-top: 80px;">
    <h2 style="color: rgb(34, 119, 210);">Cek Laporan</h2>
    <hr style="border: 1px solid #ddd; margin-top: 10px; margin-bottom: 20px;">
    
    <!-- Search bar only -->
    <div class="d-flex justify-content-end align-items-center mb-3" style="margin: 0 20px;">
        <div style="width: 300px;">
            <input type="text" id="searchInput" class="form-control" placeholder="Cari...">
        </div>
    </div>
</div>

<div class="table-responsive" style="margin: 20px;">
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
            <?php if (!empty($reportData)): ?>
                <?php foreach ($reportData as $index => $data): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
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
                                <button class="btn btn-primary" onclick="viewFinancialStatement('<?php echo $data['loanApplicationID']; ?>', '<?php echo isset($data['reportType']) ? $data['reportType'] : 'member'; ?>')">
                                    Lihat Penyata
                                </button>
                                <button class="btn btn-success" onclick="downloadFinancialStatement('<?php echo $data['loanApplicationID']; ?>', '<?php echo isset($data['reportType']) ? $data['reportType'] : 'member'; ?>')">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-danger btn-sm" onclick="deleteEntry(<?php echo $index; ?>)">
                                Padam
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">Tiada data</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
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

<script>
function viewMemberStatement(employeeID) {
    const modal = new bootstrap.Modal(document.getElementById('statementModal'));
    const frame = document.getElementById('statementFrame');
    
    // Set the source first
    frame.src = `view_report_member.php?id=${employeeID}`;
    document.getElementById('statementModalLabel').textContent = 'Penyata Ahli';
    
    // Show modal after setting source
    modal.show();
    
    // Add error handling for iframe
    frame.onerror = function() {
        console.error('Failed to load member statement');
        alert('Gagal memuat penyata. Sila cuba lagi.');
    };
}

function viewFinancialStatement(id, reportType) {
    const modal = new bootstrap.Modal(document.getElementById('statementModal'));
    const frame = document.getElementById('statementFrame');
    
    // Use the specific report type for each entry
    const url = reportType === 'pembiayaan' ? 
        `view_report_loan.php?id=${id}&type=loan` : 
        `view_report_loan.php?id=${id}&type=member`;
    
    frame.src = url;
    document.getElementById('statementModalLabel').textContent = 'Penyata Kewangan';
    
    modal.show();
    
    frame.onerror = function() {
        console.error('Failed to load financial statement');
        alert('Gagal memuat penyata. Sila cuba lagi.');
    };
}

function deleteEntry(index) {
    if (confirm('Adakah anda pasti mahu memadamkan entri ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_index';
        input.value = index;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

function downloadMemberStatement(employeeID) {
    window.location.href = `download_report_ahli.php?employeeID=${employeeID}`;
}

function downloadFinancialStatement(id, reportType) {
    const url = reportType === 'pembiayaan' ? 
        `download_report_loan.php?loanApplicationID=${id}` : 
        `download_report_loan.php?loanApplicationID=${id}`;
    window.location.href = url;
}

// Add search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const table = document.getElementById('dataTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    for (let row of rows) {
        let text = '';
        const cells = row.getElementsByTagName('td');
        
        // Skip the search if it's the "no data" row
        if (cells.length === 1 && cells[0].getAttribute('colspan')) {
            continue;
        }

        // Concatenate the text content of each cell (excluding button cells)
        for (let i = 0; i < cells.length; i++) {
            // Skip the button columns (index 4, 6, and 7)
            if (i !== 4 && i !== 6 && i !== 7) {
                text += cells[i].textContent.toLowerCase() + ' ';
            }
        }

        // Show/hide row based on search match
        if (text.includes(searchValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
});

function viewLoanReport(employeeID) {
    var iframe = document.getElementById('loanReportFrame');
    // Add a console log to check the URL being generated
    console.log('view_report_loan.php?id=' + employeeID);
    iframe.src = 'view_report_loan.php?id=' + employeeID;
    $('#loanReportModal').modal('show');
}
</script>

<?php include 'footer.php'; ?>

