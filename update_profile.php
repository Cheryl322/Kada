<?php
session_start();
include "dbconnect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['nama'];
    $email = $_POST['email'];
    $ic = $_POST['ic_passport'];
    $marital = $_POST['marital_status'];
    $address = $_POST['address'];
    $poskod = $_POST['postcode'];
    $country = $_POST['state'];
    $sex = $_POST['gender'];
    $agama = $_POST['religion'];
    $bangsa = $_POST['bangsa'];
    $nostaff = $_POST['noAnggota'];
    $nopf = $_POST['noPF'];
    $jawatan = $_POST['jawatanGred'];
    $addpejabat = $_POST['alamatPejabat'];
    $notel = $_POST['noTelBimbit'];
    $notelhome = $_POST['noTelRumah'];

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

    // Execute the query
    if (mysqli_stmt_execute($stmt)) {