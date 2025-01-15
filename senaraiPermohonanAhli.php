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
<div class="wrapper">
<div class="container mt-5">
    <h1 class="mb-4">Senarai Permohonan Ahli</h1>

    <div class="row">
            <div class="col-md-12">
                <div class="table-wrapper">
	<table class="table table-hover">
		<thead>
			<tr>
			    <th style="background-color: LightSeaGreen; color: white;">No. Permohonan</th>
			    <th style="background-color: LightSeaGreen; color: white;">Nama</th>
			    <th style="background-color: LightSeaGreen; color: white;">IC</th>
			    <th style="background-color: LightSeaGreen; color: white;">Tarikh Penyerahan</th>
				<th style="background-color: LightSeaGreen; color: white;">Borang Permohonan</th>
			    <th style="background-color: LightSeaGreen; color: white;">Status</th>        
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
            </div>
        </div>
    </div>
</div>
</div>

<style>
body {
    margin: 0;
    padding: 0;
}

.wrapper {
    min-height: calc(100vh - 40px);
    position: relative;
    background: linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)), url('img/padi.jpg') no-repeat center center fixed;
    background-size: cover;
}

.container {
    position: relative;
    z-index: 1;
    padding: 40px;
    margin-top: 40px;
}

.table-wrapper {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}

.custom-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.custom-table th, 
.custom-table td {
    padding: 12px;
    text-align: left;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.custom-table th {
    background-color: MediumAquamarine;
    color: white;
}

.custom-table td {
    border-bottom: 1px solid #ddd;
}

.custom-table td:first-child {
    background-color: #e0f7fa;
}

h1 {
    color: #5CBA9B;
    font-weight: 600;
}
</style>

<?php
// No footer included as per your request
?>

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

