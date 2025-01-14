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
            COALESCE(mr.regisStatus, 'Belum Selesai') as regisStatus
        FROM 
            tb_member m
        LEFT JOIN 
            tb_memberregistration_memberapplicationdetails mr ON m.employeeID = mr.memberRegistrationID";


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
                    echo "<select class='form-select status-select' data-id='" . $row['memberRegistrationID'] . "'>";
                    echo "<option value='Belum Selesai'" . ($row['regisStatus'] == 'Belum Selesai' ? ' selected' : '') . ">Belum Selesai</option>";
                    echo "<option value='Diluluskan'" . ($row['regisStatus'] == 'Diluluskan' ? ' selected' : '') . ">Diluluskan</option>";
                    echo "<option value='Ditolak'" . ($row['regisStatus'] == 'Ditolak' ? ' selected' : '') . ">Ditolak</option>";
                    echo "</select>";
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

