<?php

include "headeradmin.php";
include "footer.php";
include "dbconnect.php";

// Database configuration
$servername = "localhost";
$username = "root";  // Your database username
$password = "";      // Your database password
$dbname = "db_kada";    // Your database name

// Now the connection will work properly
$conn = mysqli_connect($servername, $username, $password, $dbname);

$defaultloanStatus = 'Belum Selesai';

// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT 
			la.loanApplicationID,
			m.memberName,
			m.ic,
			la.loanApplicationDate as applicationDate,
			COALESCE(la.loanStatus, 'Belum Selesai') as loanStatus
		FROM 
			tb_loanapplication la
		JOIN 
			tb_member m ON la.employeeID = m.employeeID
		ORDER BY 
			la.loanApplicationDate DESC";

$result = mysqli_query($conn, $sql);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<br><br>
<div class="wrapper">
<div class="container">
    <!-- Move Kembali button before the title and style it -->
    <div class="mb-3">
        <a href="adminmainpage.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    
    <h1 class="mb-4">Senarai Permohonan Pinjaman</h1>

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
                            while($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['loanApplicationID']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['memberName']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['ic']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['applicationDate']) . "</td>";
                                echo "<td><a href='penyatapermohonanpinjaman.php?id=" . $row['loanApplicationID'] . "' class='btn btn-primary'>Tekan borang</a></td>";
                                echo "<td>";
                                echo "<div class='d-flex align-items-center'>";
                                echo "<select class='form-select status-select me-2' data-id='" . $row['loanApplicationID'] . "'>";
                                echo "<option value='Belum Selesai'" . ($row['loanStatus'] == 'Belum Selesai' ? ' selected' : '') . ">Belum Selesai</option>";
                                echo "<option value='Diluluskan'" . ($row['loanStatus'] == 'Diluluskan' ? ' selected' : '') . ">Diluluskan</option>";
                                echo "<option value='Ditolak'" . ($row['loanStatus'] == 'Ditolak' ? ' selected' : '') . ">Ditolak</option>";
                                echo "</select>";
                                echo "<button class='btn btn-primary save-status' data-id='" . $row['loanApplicationID'] . "'>Simpan</button>";
                                echo "</div>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No records found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Add jQuery and JavaScript for status updates -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.save-status').click(function() {
        const loanId = $(this).data('id');
        const statusSelect = $(this).closest('div').find('.status-select');
        const status = statusSelect.val();
        const button = $(this);
        
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
        
        $.ajax({
            url: 'update_loan_status.php',  // Create this file for handling loan status updates
            method: 'POST',
            data: {
                loanId: loanId,
                status: status
            },
            success: function(response) {
                if (response === 'Success') {
                    alert('Status berjaya dikemaskini');
                    location.reload();
                } else {
                    alert('Status berjaya dikemaskini');
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
body {
    margin: 0;
    padding: 0;
    position: relative;
}

.wrapper {
    min-height: calc(100vh - 40px);
    position: relative;
    background: linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)), 
                url('img/padi.jpg') no-repeat center top fixed;
    background-size: cover;
    padding-top: 20px;
}

.container {
    position: relative;
    z-index: 1;
    padding: 20px;
    margin-top: 0;
}

.table-wrapper {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    position: relative;
    z-index: 2;
    pointer-events: all;
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
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

/* Make sure Font Awesome is included */
.fa-arrow-left {
    margin-right: 5px;
}
</style>