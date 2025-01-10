<?php
session_start();
include "dbconnect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate session and user authentication
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error_message'] = "Sesi tamat. Sila log masuk semula.";
        header("Location: login.php");
        exit();
    }

    // Sanitize inputs
    $name = trim(mysqli_real_escape_string($con, $_POST['nama']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $ic = trim(mysqli_real_escape_string($con, $_POST['ic_passport']));
    // ... rest of the data sanitization ...

    // Validate required fields
    if (empty($name) || empty($email) || empty($ic)) {
        $_SESSION['error_message'] = "Sila isi semua maklumat yang diperlukan.";
        header("Location: profil.php");
        exit();
    }


    // Prepare the update query using prepared statements
    $sql = "UPDATE tb_member SET 
            p_name=?, p_email=?, p_ic=?, p_marital=?, 
            p_address=?, p_poskod=?, p_country=?, p_sex=?, 
            p_agama=?, p_bangsa=?, p_nostaff=?, p_nopf=?, 
            p_jawatan=?, p_addpejabat=?, p_notel=?, p_notelhome=?
            WHERE id=?"; // Add your WHERE condition based on your table structure

    $stmt = mysqli_prepare($con, $sql);
    
    // Bind parameters
    mysqli_stmt_bind_param($stmt, "ssssssssssssssssi", 
        $name, $email, $ic, $marital, 
        $address, $poskod, $country, $sex, 
        $agama, $bangsa, $nostaff, $nopf, 
        $jawatan, $addpejabat, $notel, $notelhome,
        $_SESSION['user_id'] // Assuming you have user_id in session
    );

  

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        $_SESSION['success_message'] = "Profil berjaya dikemaskini!";
        
        // Fetch the updated profile data
        $sql_select = "SELECT * FROM tb_profile WHERE id = ?";
        $stmt_select = mysqli_prepare($con, $sql_select);
        mysqli_stmt_bind_param($stmt_select, "i", $_SESSION['user_id']);
        mysqli_stmt_execute($stmt_select);
        $result = mysqli_stmt_get_result($stmt_select);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $_SESSION['profile_data'] = $row; // Store updated profile in session
        }
        
        mysqli_stmt_close($stmt_select);
        header("Location: profil.php");
        exit();
    }


}
?>