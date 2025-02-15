<?php

include "headeradmin.php";
include "footer.php";
include "dbconnect.php";

// Database configuration
$servername = "localhost";
$username = "root";  // Your database username
$password = "";      // Your database password
$dbname = "db_kada";    // Your database name

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
        LEFT JOIN tb_memberregistration_memberapplicationdetails md 
            ON m.employeeID = md.memberRegistrationID
        GROUP BY 
            m.employeeID, m.memberName, m.ic, m.created_at";


$result = mysqli_query($conn, $sql);

?>

<br>
<div class="wrapper">
<div class="container mt-5">
    <!-- Add Kembali button -->
    <div class="mb-3">
        <a href="adminmainpage.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

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
    min-height: 100vh;
    background: linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)), url('img/padi.jpg') no-repeat center center fixed;
    background-size: cover;
}

.wrapper {
    min-height: 100vh;
    padding-top: 20px; /* Reduced from 40px */
    padding-bottom: 20px;
}

.container {
    position: relative;
    z-index: 1;
    padding: 20px; /* Reduced from 40px */
}

.table-wrapper {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    position: relative;
    z-index: 2;
    pointer-events: all;  /* Ensure clicks work */
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

.btn-secondary {
    background-color: #6c757d;
    border: none;
    padding: 8px 20px;
    border-radius: 5px;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background-color: #5a6268;
    transform: translateY(-1px);
}

.explanation-box {
    width: 100%;
    min-height: 80px;
    margin-top: 10px;
    margin-bottom: 10px;
    padding: 8px;
    border: 1px solid #ced4da;
    border-radius: 4px;
}
</style>

<?php
// No footer included as per your request
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Add event listener for status change
    $('.status-select').change(function() {
        const statusSelect = $(this);
        const explanationBox = statusSelect.closest('div').find('.explanation-box');
        
        if (statusSelect.val() === 'Ditolak') {
            // If explanation box doesn't exist, create it
            if (explanationBox.length === 0) {
                statusSelect.after('<textarea class="form-control mt-2 mb-2 explanation-box" placeholder="Sila masukkan penjelasan penolakan"></textarea>');
            }
        } else {
            // Remove explanation box if status is not 'Ditolak'
            explanationBox.remove();
        }
    });

    $('.save-status').click(function(e) {
        e.preventDefault();
        
        const memberId = $(this).data('id');
        const statusSelect = $(this).closest('div').find('.status-select');
        const status = statusSelect.val();
        const explanation = status === 'Ditolak' ? 
            $(this).closest('div').find('.explanation-box').val() : '';
        const button = $(this);
        
        // Customize confirmation message based on status
        let confirmMessage = '';
        if (status === 'Diluluskan') {
            confirmMessage = 'Adakah anda pasti untuk meluluskan permohonan ini?';
        } else if (status === 'Ditolak') {
            confirmMessage = 'Adakah anda pasti untuk menolak permohonan ini?';
        } else {
            confirmMessage = 'Adakah anda pasti untuk mengubah status permohonan ini?';
        }
        
        // Show confirmation dialog
        if (confirm(confirmMessage)) {
            button.prop('disabled', true);
            button.html('<span class="spinner-border spinner-border-sm"></span> Saving...');
            
            $.ajax({
                url: 'update_status.php',
                method: 'POST',
                data: {
                    memberId: memberId,
                    status: status,
                    explanation: explanation
                },
                dataType: 'json',
                success: function(response) {
                    if (response && response.success) {
                        // Customize success message based on status
                        let successMessage = '';
                        if (status === 'Diluluskan') {
                            successMessage = 'Permohonan telah berjaya diluluskan!';
                        } else if (status === 'Ditolak') {
                            successMessage = 'Permohonan telah ditolak.';
                        } else {
                            successMessage = 'Status permohonan telah berjaya dikemaskini.';
                        }
                        
                        // Add email notification if applicable
                        if (response.emailSent) {
                            successMessage += '\nEmail pemberitahuan telah dihantar kepada pemohon.';
                        }
                        
                        alert(successMessage);
                        location.reload();
                    } else {
                        alert(response?.error || 'Gagal mengemaskini status');
                        button.prop('disabled', false).html('Simpan');
                    }
                },
                error: function(xhr, status, error) {
                    alert('Ralat sambungan ke pelayan: ' + error);
                    button.prop('disabled', false).html('Simpan');
                }
            });
        }
    });
});
</script>