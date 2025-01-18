<?php 
session_start();
include 'headeradmin.php';

// Add title tag right after header inclusion
echo '<title>Cek Laporan</title>';

// Initialize reportData array if not exists
if (!isset($_SESSION['reportData'])) {
    $_SESSION['reportData'] = [];
}

// Process new selections from hasilreport.php
if (isset($_POST['selected_members']) && is_array($_POST['selected_members'])) {
    include 'dbconnect.php';
    
    try {
        $selectedMembers = $_POST['selected_members'];
        $isLoanReport = isset($_POST['reportType']) && $_POST['reportType'] === 'pembiayaan';
        
        if (!empty($selectedMembers)) {
            $placeholders = implode(',', array_fill(0, count($selectedMembers), '?'));
            
            if ($isLoanReport) {
                // Query for loan report - get all loan applications
                $query = "SELECT 
                            m.memberName,
                            '' as status,
                            DATE_FORMAT(m.created_at, '%d/%m/%Y') as tarikh_daftar,
                            DATE_FORMAT(l.created_at, '%d/%m/%Y') as tarikh_pembiayaan,
                            m.employeeID,
                            l.loanID
                         FROM tb_member m
                         INNER JOIN tb_loan l ON m.employeeID = l.employeeID
                         WHERE m.employeeID IN ($placeholders)
                         ORDER BY l.created_at DESC
                         LIMIT " . count($_POST['selected_loans']);
                         
                $stmt = mysqli_prepare($conn, $query);
                if ($stmt) {
                    $types = str_repeat('s', count($selectedMembers));
                    mysqli_stmt_bind_param($stmt, $types, ...$selectedMembers);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    
                    // Remove existing entries for the selected members
                    $_SESSION['reportData'] = array_filter($_SESSION['reportData'], function($entry) use ($selectedMembers) {
                        return !in_array($entry['employeeID'], $selectedMembers);
                    });
                    
                    // Add only the newly selected entries
                    while ($row = mysqli_fetch_assoc($result)) {
                        $_SESSION['reportData'][] = $row;
                    }
                }
            } else {
                // Query for member report (unchanged)
                $query = "SELECT 
                            m.memberName,
                            '' as status,
                            DATE_FORMAT(m.created_at, '%d/%m/%Y') as tarikh_daftar,
                            '' as tarikh_pembiayaan,
                            m.employeeID,
                            NULL as loanID
                         FROM tb_member m
                         WHERE m.employeeID IN ($placeholders)";
                         
                $stmt = mysqli_prepare($conn, $query);
                if ($stmt) {
                    $types = str_repeat('s', count($selectedMembers));
                    mysqli_stmt_bind_param($stmt, $types, ...$selectedMembers);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        $exists = false;
                        foreach ($_SESSION['reportData'] as $existing) {
                            if ($existing['employeeID'] === $row['employeeID'] && empty($existing['tarikh_pembiayaan'])) {
                                $exists = true;
                                break;
                            }
                        }
                        
                        if (!$exists) {
                            $_SESSION['reportData'][] = $row;
                        }
                    }
                }
            }
            
            if ($stmt) {
                mysqli_stmt_close($stmt);
            }
        }
    } catch (Exception $e) {
        // Handle error silently or log it
    }
}

// Sort the report data by tarikh_daftar and then by tarikh_pembiayaan
if (!empty($_SESSION['reportData'])) {
    usort($_SESSION['reportData'], function($a, $b) {
        // First sort by tarikh_daftar
        $dateCompare = strtotime($b['tarikh_daftar']) - strtotime($a['tarikh_daftar']);
        if ($dateCompare !== 0) {
            return $dateCompare;
        }
        // If same registration date, sort by loan date (if exists)
        if (!empty($a['tarikh_pembiayaan']) && !empty($b['tarikh_pembiayaan'])) {
            return strtotime($b['tarikh_pembiayaan']) - strtotime($a['tarikh_pembiayaan']);
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
                <th>Status</th>
                <th>Tarikh Daftar</th>
                <th>Penyata Ahli</th>
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
                        <td><?php echo htmlspecialchars($data['status']); ?></td>
                        <td><?php echo htmlspecialchars($data['tarikh_daftar']); ?></td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <button class="btn btn-primary" onclick="viewMemberStatement(<?php echo $data['employeeID']; ?>)">
                                    Lihat Penyata
                                </button>
                                <button class="btn btn-success" onclick="downloadMemberStatement(<?php echo $data['employeeID']; ?>)">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </td>
                        <td><?php echo empty($data['tarikh_pembiayaan']) ? '-' : htmlspecialchars($data['tarikh_pembiayaan']); ?></td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <button class="btn btn-primary" onclick="viewFinancialStatement(<?php echo $data['employeeID']; ?>)">
                                    Lihat Penyata
                                </button>
                                <button class="btn btn-success" onclick="downloadFinancialStatement(<?php echo $data['employeeID']; ?>)">
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
                    <td colspan="8" class="text-center">Tiada data</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal for viewing statements -->
<div class="modal fade" id="statementModal" tabindex="-1" aria-labelledby="statementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statementModalLabel">Penyata</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Statement content will be loaded here -->
                <div id="statementContent"></div>
            </div>
        </div>
    </div>
</div>

<script>
function viewMemberStatement(employeeID) {
    // Set modal title
    document.getElementById('statementModalLabel').textContent = 'Penyata Ahli';
    
    // Here you would typically load the member statement content
    document.getElementById('statementContent').innerHTML = 
        `<div class="text-center">
            <h4>Penyata Ahli</h4>
            <p>ID Ahli: ${employeeID}</p>
            <!-- Add your actual statement content here -->
        </div>`;
    
    // Show the modal
    new bootstrap.Modal(document.getElementById('statementModal')).show();
}

function viewFinancialStatement(employeeID) {
    // Set modal title
    document.getElementById('statementModalLabel').textContent = 'Penyata Kewangan';
    
    // Here you would typically load the financial statement content
    document.getElementById('statementContent').innerHTML = 
        `<div class="text-center">
            <h4>Penyata Kewangan</h4>
            <p>ID Ahli: ${employeeID}</p>
            <!-- Add your actual statement content here -->
        </div>`;
    
    // Show the modal
    new bootstrap.Modal(document.getElementById('statementModal')).show();
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
    // Create the URL for downloading member statement
    const url = `download_member_statement.php?employeeID=${employeeID}`;
    
    // Create a temporary link element
    const link = document.createElement('a');
    link.href = url;
    link.target = '_blank';
    
    // Trigger the download
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function downloadFinancialStatement(employeeID) {
    // Create the URL for downloading financial statement
    const url = `download_financial_statement.php?employeeID=${employeeID}`;
    
    // Create a temporary link element
    const link = document.createElement('a');
    link.href = url;
    link.target = '_blank';
    
    // Trigger the download
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
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
</script>

<?php include 'footer.php'; ?>

