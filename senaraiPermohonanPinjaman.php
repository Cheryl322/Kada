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
			mad.applicationDate,
			la.loanStatus
		FROM 
			tb_loanapplication la
		JOIN 
			tb_memberapplicationdetails mad ON la.loanApplicationID = mad.emplyeeID
		JOIN 
			tb_member m ON mad.emplyeeID = m.employeeId";

$result = mysqli_query($conn, $sql);
?>

<div class="container mt-3">
	<table class="table">
		<thead class="table-dark">
			<tr>
			    <th>No. Invois</th>
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
                    echo "<td>" . htmlspecialchars($row['loanApplicationID']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['memberName']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['ic']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['applicationDate']) . "</td>";
                    echo "<td><a href='#" . $row['memberRegistrationID'] . "' class='btn btn-primary'>Tekan borang</a></td>";
					echo "<td>";
                    echo "<select class='form-select'>";
                    echo "<option value='Belum Selesai'" . ($row['loanStatus'] == 'Belum Selesai' ? ' selected' : '') . ">Belum Selesai</option>";
                    echo "<option value='Diluluskan'" . ($row['loanStatus'] == 'Diluluskan' ? ' selected' : '') . ">Diluluskan</option>";
                    echo "<option value='Ditolak'" . ($row['loanStatus'] == 'Ditolak' ? ' selected' : '') . ">Ditolak</option>";
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



<!-- <tbody>
			<tr>
			    <td>#001</td>
			    <td>Yuna Liew</td>
			    <td>000000-00-0000</td>
			    <td>2 Jun 2024</td>
				<td><div class="d-grid">
                        <a class="btn btn-primary btn-lg" href="#" role="button">Ditolak</a>
                </div></td>
			</tr>
			<tr>
			    <td>#002</td>
			    <td>Yuna Liew</td>
			    <td>000000-00-0000</td>
			    <td>20 Dec 2024</td>
				<td><div class="d-grid">
                        <a class="btn btn-primary btn-lg" href="penyatakewangan.php" role="button">Diluluskan</a>
                </div></td>
			</tr>
			<tr>
			    <td>#003</td>
			   	<td>Jenny Ho</td>
			    <td>000000-00-0001</td>
			    <td>20 Dec 2024</td>
				<td><div class="d-grid">
                        <a class="btn btn-primary btn-lg" href="penyatakewangan.php" role="button">Diluluskan</a>
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
			</tr>
		</tbody> -->