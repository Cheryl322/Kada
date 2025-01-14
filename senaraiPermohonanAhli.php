<?php

include "headeradmin.php";
include "footer.php";
include "dbconnect.php";

// Establish database connection
// $conn = mysqli_connect($host, $user, $password, $database);

$defaultRegisStatus = 'Belum Selesai';

// Check connection with better error handling
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    // Redirect to an error page or show user-friendly message
    header("Location: error.php");
    exit();
}

$sql = "SELECT 
            m.employeeID as memberRegistrationID,
            m.memberName,
            m.ic,
            m.created_at as regisDate,
            COALESCE(
                (SELECT regisStatus 
                 FROM tb_memberregistration_memberapplicationdetails 
                 WHERE memberRegistrationID = m.employeeID 
                 ORDER BY regisDate DESC 
                 LIMIT 1), 
                'Belum Selesai'
            ) as regisStatus
        FROM 
            tb_member m
        GROUP BY 
            m.employeeID, m.memberName, m.ic, m.created_at";


$result = mysqli_query($conn, $sql);

?>

<br><br><br>
<div class="container mt-3">
	<table class="table">
		<thead class="table-dark">
			<tr>
			    <th>No. Permohonan</th>
			    <th>Nama</th>
			    <th>IC</th>
			    <th>Tarikh Penyerahan</th>
				<th>Borang Permohonan</th>
			    <th>Status</th>        
			</tr>
		</thead>
		<tbody>
			<?php
			if (mysqli_num_rows($result) > 0) {
				while($row = mysqli_fetch_array($result)){
					echo "<tr>";
					echo "<td>" . $row['memberRegistrationID'] . "</td>";
					echo "<td>" . $row['memberName'] . "</td>";
					echo "<td>" . $row['ic'] . "</td>";
					echo "<td>" . $row['regisDate'] . "</td>";
					echo "<td><a href='penyatapermohonananggota.php?id=" . $row['memberRegistrationID'] . "' class='btn btn-primary'>Tekan borang</a></td>";
					echo "<td>";
                    echo "<div class='d-flex align-items-center'>";
                    echo "<select class='form-select status-select me-2' data-id='" . $row['memberRegistrationID'] . "'>";
                    echo "<option value='Belum Selesai'" . ($row['regisStatus'] == 'Belum Selesai' ? ' selected' : '') . ">Belum Selesai</option>";
                    echo "<option value='Diluluskan'" . ($row['regisStatus'] == 'Diluluskan' ? ' selected' : '') . ">Diluluskan</option>";
                    echo "<option value='Ditolak'" . ($row['regisStatus'] == 'Ditolak' ? ' selected' : '') . ">Ditolak</option>";
                    echo "</select>";
                    echo "<button class='btn btn-primary save-status' data-id='" . $row['memberRegistrationID'] . "'>Simpan</button>";
                    echo "</div>";
                    echo "</td>";
					echo "</tr>";
				}
			} else {
                echo "<tr><td colspan='5'>No records found</td></tr>";
			}

			mysqli_close($conn);
            ?>
		</tbody>
	</table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.save-status').click(function() {
        const memberId = $(this).data('id');
        const statusSelect = $(this).closest('div').find('.status-select');
        const status = statusSelect.val();
        const button = $(this);
        const row = button.closest('tr');
        
        // Disable button and show loading state
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
        
        $.ajax({
            url: 'update_status.php',
            method: 'POST',
            data: {
                memberId: memberId,
                status: status
            },
            success: function(response) {
                if (response === 'Success') {
                    // Show success message
                    alert('Status berjaya dikemaskini');
                    
                    // Update the status in the dropdown
                    statusSelect.val(status);
                    
                    // Refresh the page to show updated data
                    location.reload();
                    
                    // Visual feedback
                    button.removeClass('btn-primary').addClass('btn-success');
                    setTimeout(() => {
                        button.removeClass('btn-success').addClass('btn-primary');
                        button.prop('disabled', false).html('Simpan');
                    }, 2000);
                } else {
                    // Show error message
                    alert('Ralat mengemaskini status');
                    button.prop('disabled', false).html('Simpan');
                }
            },
            error: function() {
                alert('Ralat sambungan ke pelayan');
                button.prop('disabled', false).html('Simpan');
            }
        });
    });
});
</script>

<style>
.status-select {
    min-width: 140px;
}

.save-status {
    white-space: nowrap;
    min-width: 80px;
}

.btn-success {
    transition: background-color 0.3s ease;
}

.updated-row {
    animation: highlightRow 2s ease-out;
}

@keyframes highlightRow {
    0% { background-color: #d4edda; }
    100% { background-color: transparent; }
}
</style>

