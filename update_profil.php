<!-- <?php
session_start();
include "dbconnect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate session and user authentication
    if (!isset($_SESSION['employeeId'])) {
        $_SESSION['error_message'] = "Sesi tamat. Sila log masuk semula.";
        header("Location: login.php");
        exit();
    }

    // Sanitize inputs
    $name = trim(mysqli_real_escape_string($con, $_POST['memberName']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $ic = trim(mysqli_real_escape_string($con, $_POST['ic']));
    // ... rest of the data sanitization ...

    // Validate required fields
    if (empty($name) || empty($email) || empty($ic)) {
        $_SESSION['error_message'] = "Sila isi semua maklumat yang diperlukan.";
        header("Location: profil.php");
        exit();
    }

    // Prepare the update query using prepared statements
    $sql = "UPDATE tb_member , tb_member_officeAddress AND tb_member_homeaddress SET 
            memberName=memberName, email=email, ic=ic, maritalStatus=maritalStatus, 
            homeAddress=homeAddress, homePostcode=homePostcode, homeState=homeState, sex=sex, 
            religion=religion, nation=nation, employeeId=employeeId, no_pf=no_pf, 
            position=position, officeAddress=officeAddress, officePostcode=officePostcode, officeState=officeState, phoneNumber=phoneNumber, phoneHome=phoneHome 
            WHERE id=employeeId";

    $stmt = mysqli_prepare($con, $sql);
    
    // Bind parameters
    mysqli_stmt_bind_param($stmt, "ssssssssssssssssi", 
        $name, $email, $ic, $_POST['maritalStatus'], 
        $_POST['homeAddress'], $_POST['homePostcode'], $_POST['homeState'], $_POST['sex'], 
        $_POST['religion'], $_POST['nation'], $_POST['employeeId'], $_POST['no_pf'], 
        $_POST['position'], $_POST['officeAddress'], $_POST['officePostcode'], $_POST['officeState'], $_POST['phoneNumber'], $_POST['phoneHome'],
        $_SESSION['employeeId']
    );

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Profil berjaya dikemaskini!";
        header("Location: newProfile.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Kemaskini profil gagal.";
        header("Location: profil.php");
        exit();
    }
}
?>  -->

<?php
session_start();
include "dbconnect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate session and user authentication
    if (!isset($_SESSION['employeeID'])) {
        $_SESSION['error_message'] = "Sesi tamat. Sila log masuk semula.";
        header("Location: login.php");
        exit();
    }

    // Sanitize inputs
    $memberName = trim(mysqli_real_escape_string($con, $_POST['memberName']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $ic = trim(mysqli_real_escape_string($con, $_POST['ic']));
    $maritalStatus = trim(mysqli_real_escape_string($con, $_POST['maritalStatus']));
    $homeAddress = trim(mysqli_real_escape_string($con, $_POST['homeAddress']));
    $homePostcode = trim(mysqli_real_escape_string($con, $_POST['homePostcode']));
    $homeState = trim(mysqli_real_escape_string($con, $_POST['homeState']));
    $sex = trim(mysqli_real_escape_string($con, $_POST['sex']));
    $religion = trim(mysqli_real_escape_string($con, $_POST['religion']));
    $nation = trim(mysqli_real_escape_string($con, $_POST['nation']));
    $employeeId = trim(mysqli_real_escape_string($con, $_POST['employeeId']));
    $no_pf = trim(mysqli_real_escape_string($con, $_POST['no_pf']));
    $position = trim(mysqli_real_escape_string($con, $_POST['position']));
    $officeAddress = trim(mysqli_real_escape_string($con, $_POST['officeAddress']));
    $officePostcode = trim(mysqli_real_escape_string($con, $_POST['officeAddress']));
    $officeState = trim(mysqli_real_escape_string($con, $_POST['officeAddress']));
    $phoneNumber = trim(mysqli_real_escape_string($con, $_POST['phoneNumber']));
    $phoneHome = trim(mysqli_real_escape_string($con, $_POST['phoneHome']));

    // Validate required fields
    if (empty($name) || empty($email) || empty($ic)) {
        $_SESSION['error_message'] = "Sila isi semua maklumat yang diperlukan.";
        header("Location: profil.php");
        exit();
    }

    // Prepare the update query using prepared statements
    $sql = "UPDATE tb_member , tb_member_officeAddress AND tb_member_homeaddress SET 
            memberName=memberName, email=email, ic=ic, maritalStatus=maritalStatus, 
            homeAddress=homeAddress, homePostcode=homePostcode, homeState=homeState, sex=sex, 
            religion=religion, nation=nation, employeeId=employeeId, no_pf=no_pf, 
            position=position, officeAddress=officeAddress, officePostcode=officePostcode, officeState=officeState, phoneNumber=phoneNumber, phoneHome=phoneHome 
            WHERE id=employeeId";

    $stmt = mysqli_prepare($con, $sql);
    
    // Bind parameters
    mysqli_stmt_bind_param($stmt, "ssssssssssssssssi", 
        $memberName, $email, $ic, $maritalStatus, 
        $homeAddres, $homePostcode, $homeState, $sex, 
        $religion, $nation, $employeeId, $no_pf, 
        $position, $officeAddress, $phoneNumber, $officePostcode, $officeState, $phoneHome,
        $_SESSION['employeeId']
    );

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Profil berjaya dikemaskini!";
        header("Location: newProfile.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Kemaskini profil gagal.";
        header("Location: profil.php");
        exit();
    }
}
?>
