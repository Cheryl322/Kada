<?php
session_start();
include "dbconnect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate session and user authentication
    $employeeId = $_SESSION['employeeID'];
    $memberName = $_POST['memberName'];
    $email = $_POST['email'];
    $ic = $_POST['ic'];
    $maritalStatus = $_POST['maritalStatus'];
    $sex = $_POST['sex'];
    $religion = $_POST['religion'];
    $nation = $_POST['nation'];
    $no_pf = $_POST['no_pf'];
    $position = $_POST['position'];
    $phoneNumber = $_POST['phoneNumber'];
    $phoneHome = $_POST['phoneHome'];

    $sql_member = "UPDATE tb_member SET 
                   memberName=?, email=?, ic=?, maritalStatus=?,
                   sex=?, religion=?, nation=?, no_pf=?,
                   position=?, phoneNumber=?, phoneHome=?
                   WHERE employeeId=?";
    
    $stmt = $pdo->prepare($sql_member);
    $stmt->execute([
        $memberName, $email, $ic, $maritalStatus,
        $sex, $religion, $nation, $no_pf,
        $position, $phoneNumber, $phoneHome,
        $employeeId
    ]);


    $sql_home = "UPDATE tb_member_homeaddress SET 
                 homeAddress=?, homePostcode=?, homeState=?
                 WHERE employeeID=?";
    
    $stmt = $pdo->prepare($sql_home);
    $stmt->execute([
        $_POST['homeAddress'],
        $_POST['homePostcode'],
        $_POST['homeState'],
        $employeeId
    ]);

    // 更新办公地址
    $sql_office = "UPDATE tb_member_officeaddress SET 
                   officeAddress=?, officePostcode=?, officeState=?
                   WHERE employeeID=?";
    
    $stmt = $pdo->prepare($sql_office);
    $stmt->execute([
        $_POST['officeAddress'],
        $_POST['officePostcode'],
        $_POST['officeState'],
        $employeeId
    ]);

    $_SESSION['success_message'] = "Profil berjaya dikemaskini!";
    header("Location: profil.php");
    exit();
}
    header("Location: profil.php");
    exit();
?>
