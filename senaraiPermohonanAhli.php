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
$conn = mysqli_connect($servername, $username, $password, $dbname);

$defaultRegisStatus = 'Belum Selesai';

// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT 
			mr.memberRegistrationID,
			m.memberName,
			m.ic,
			mr.regisDate,
			mr.regisStatus
		FROM 
			tb_memberregistration_memberapplicationdetails mr
		JOIN 
			tb_member m ON mr.memberRegistrationID = m.employeeID";

$result = mysqli_query($conn, $sql);

?>

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
				while ($row = mysqli_fetch_assoc($result)) {
					echo "<tr>";
					echo "<td>" . $row['memberRegistrationID'] . "</td>";
					echo "<td>" . $row['memberName'] . "</td>";
					echo "<td>" . $row['ic'] . "</td>";
					echo "<td>" . $row['regisDate'] . "</td>";
					echo "<td><a href='#" . $row['memberRegistrationID'] . "' class='btn btn-primary'>Tekan borang</a></td>";
					echo "<td>";
                    echo "<select class='form-select'>";
                    echo "<option value='Belum Selesai'" . ($row['regisStatus'] == 'Belum Selesai' ? ' selected' : '') . ">Belum Selesai</option>";
                    echo "<option value='Diluluskan'" . ($row['regisStatus'] == 'Diluluskan' ? ' selected' : '') . ">Diluluskan</option>";
                    echo "<option value='Ditolak'" . ($row['regisStatus'] == 'Ditolak' ? ' selected' : '') . ">Ditolak</option>";
                    echo "</select>";
                    echo "</td>";
					echo "</tr>";
				}
			} else {
				echo "0 results";
			}

			mysqli_close($conn);
            ?>
		</tbody>
	</table>
</div>



<!-- <tr>
			    <td>#002</td>
			    <td>Yuna Liew</td>
			    <td>000000-00-0000</td>
			    <td>20 Dec 2024</td>
				<td><div class="d-grid">
                        <a class="btn btn-primary btn-lg" href="#" role="button">Diluluskan</a>
                </div></td>
			</tr>
			<tr>
			    <td>#003</td>
			   	<td>Jenny Ho</td>
			    <td>000000-00-0001</td>
			    <td>20 Dec 2024</td>
				<td><div class="d-grid">
                        <a class="btn btn-primary btn-lg" href="#" role="button">Diluluskan</a>
                </div></td>
			</tr>
			<tr>
			    <td>#004</td>
			   	<td>Cherry Lim</td>
			    <td>000000-00-0002</td>
			    <td>25 Dec 2024</td>
				<td><div class="d-grid">
                        <a class="btn btn-primary btn-lg" href="#" role="button">Belum disemak</a>
                </div></td>
			</tr> -->