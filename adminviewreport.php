<?php 
session_start();
include 'headeradmin.php';

// Add title tag right after header inclusion
echo '<title>Cek Laporan</title>';

// Initialize reportData array
if (!isset($_SESSION['reportData'])) {
    $_SESSION['reportData'] = [];
}
$reportData = $_SESSION['reportData'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Connect to database and fetch report data
    include 'dbconnect.php';
    
    try {
        // Get selected members from POST data and ensure it's an array
        $selectedMembers = isset($_POST['selected_members']) ? (array)$_POST['selected_members'] : [];
        
        if (!empty($selectedMembers)) {
            // Create placeholders for the IN clause
            $placeholders = implode(',', array_fill(0, count($selectedMembers), '?'));
            
            $query = "SELECT 
                        m.memberName,
                        '' as status,
                        DATE_FORMAT(m.created_at, '%d/%m/%Y') as tarikh_daftar,
                        DATE_FORMAT(l.created_at, '%d/%m/%Y') as tarikh_pembiayaan,
                        m.employeeID
                     FROM tb_member m
                     LEFT JOIN tb_loan l ON m.employeeID = l.employeeID
                     WHERE m.employeeID IN ($placeholders)";
            
            // Prepare and execute the statement
            $stmt = mysqli_prepare($conn, $query);
            if ($stmt) {
                // Bind parameters dynamically
                $types = str_repeat('s', count($selectedMembers));
                mysqli_stmt_bind_param($stmt, $types, ...$selectedMembers);
                
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                // Append new data to existing reportData
                while ($row = mysqli_fetch_assoc($result)) {
                    // Check if this employee ID already exists in the report
                    $exists = false;
                    foreach ($reportData as $existing) {
                        if ($existing['employeeID'] === $row['employeeID']) {
                            $exists = true;
                            break;
                        }
                    }
                    // Only add if it doesn't exist already
                    if (!$exists) {
                        $reportData[] = $row;
                    }
                }
                
                // Store updated data in session
                $_SESSION['reportData'] = $reportData;
                
                mysqli_stmt_close($stmt);
            }
        }
    } catch (Exception $e) {
        // Handle error silently or log it
    }
}
?>

<div class="main-content" style="margin-top: 80px;">
    <h2 style="color: rgb(34, 119, 210);">Cek Laporan</h2>
    <hr style="border: 1px solid #ddd; margin-top: 10px; margin-bottom: 20px;">
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
                            <button class="btn btn-primary" onclick="viewMemberStatement(<?php echo $data['employeeID']; ?>)">
                                Lihat Penyata
                            </button>
                        </td>
                        <td><?php echo htmlspecialchars($data['tarikh_pembiayaan'] ?? '-'); ?></td>
                        <td class="text-center">
                            <button class="btn btn-primary" onclick="viewFinancialStatement(<?php echo $data['employeeID']; ?>)">
                                Lihat Penyata
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">Tiada data</td>
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
</script>

<?php include 'footer.php'; ?>

