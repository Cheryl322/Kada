<?php
include 'dbconnect.php';

if(isset($_POST['ic'])) {
    $ic = $_POST['ic'];
    
    // Check if IC exists in tb_member
    $sql = "SELECT memberName FROM tb_member WHERE ic = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $ic);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($row = mysqli_fetch_assoc($result)) {
        echo json_encode([
            'valid' => true,
            'name' => $row['memberName']
        ]);
    } else {
        echo json_encode([
            'valid' => false,
            'message' => 'Penjamin ini bukan ahli Koperasi KADA yang sah.'
        ]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode([
        'valid' => false,
        'message' => 'No IC provided'
    ]);
}

$conn->close();
?>