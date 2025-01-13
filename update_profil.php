<?php
session_start();
include "dbconnect.php";

// Add debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug POST data
file_put_contents('debug.txt', print_r($_POST, true));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        if (!isset($_SESSION['employeeID'])) {
            throw new Exception("Session tidak sah");
        }

        // Debug values
        file_put_contents('debug.txt', print_r($_POST, true), FILE_APPEND);
        
        $employeeId = $_SESSION['employeeID'];
        $memberName = $_POST['memberName'] ?? '';
        $email = $_POST['email'] ?? '';
        $ic = $_POST['ic'] ?? '';
        $maritalStatus = $_POST['maritalStatus'] ?? '';
        $sex = $_POST['sex'] ?? '';
        $religion = $_POST['religion'] ?? '';
        $nation = $_POST['nation'] ?? '';
        $no_pf = $_POST['no_pf'] ?? '';
        $position = $_POST['position'] ?? '';
        $phoneNumber = $_POST['phoneNumber'] ?? '';
        $phoneHome = $_POST['phoneHome'] ?? '';

        // Debug SQL
        $sql_member = "UPDATE tb_member SET 
                   memberName=?, email=?, ic=?, maritalStatus=?,
                   sex=?, religion=?, nation=?, no_pf=?,
                   position=?, phoneNumber=?, phoneHome=?
                   WHERE employeeId=?";
        
        file_put_contents('debug.txt', $sql_member . "\n", FILE_APPEND);

        $pdo->beginTransaction();

        $stmt = $pdo->prepare($sql_member);
        $result = $stmt->execute([
            $memberName, $email, $ic, $maritalStatus,
            $sex, $religion, $nation, $no_pf,
            $position, $phoneNumber, $phoneHome,
            $employeeId
        ]);

        // Debug execution result
        file_put_contents('debug.txt', "Execute result: " . var_export($result, true) . "\n", FILE_APPEND);

        // Similar debug for home and office address updates
        $sql_home = "UPDATE tb_member_homeaddress SET 
                     homeAddress=?, homePostcode=?, homeState=?
                     WHERE employeeID=?";
        
        $stmt = $pdo->prepare($sql_home);
        $stmt->execute([
            $_POST['homeAddress'] ?? '',
            $_POST['homePostcode'] ?? '',
            $_POST['homeState'] ?? '',
            $employeeId
        ]);

        $sql_office = "UPDATE tb_member_officeaddress SET 
                       officeAddress=?, officePostcode=?, officeState=?
                       WHERE employeeID=?";
        
        $stmt = $pdo->prepare($sql_office);
        $stmt->execute([
            $_POST['officeAddress'] ?? '',
            $_POST['officePostcode'] ?? '',
            $_POST['officeState'] ?? '',
            $employeeId
        ]);

        $pdo->commit();
        $_SESSION['success_message'] = "Profil berjaya dikemaskini!";

    } catch (Exception $e) {
        $pdo->rollBack();
        // Debug error
        file_put_contents('debug.txt', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
        $_SESSION['error_message'] = "Ralat: " . $e->getMessage();
    }

    header("Location: profil.php");
    exit();
}

// Debug if we reach here
file_put_contents('debug.txt', "Not a POST request\n", FILE_APPEND);
header("Location: profil.php");
exit();
?>
