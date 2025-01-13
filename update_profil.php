<?php
session_start();
include "dbconnect.php";

// 添加调试信息
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 确保数据库连接正确
try {
    $host = "localhost";
    $dbname = "kada";  // 你的数据库名称
    $username = "root";  // 你的数据库用户名
    $password = "";    // 你的数据库密码

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

} catch(PDOException $e) {
    $_SESSION['error_message'] = "Database connection failed: " . $e->getMessage();
    header("Location: profil.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        if (!isset($_SESSION['employeeID'])) {
            throw new Exception("Session tidak sah");
        }

        $employeeId = $_SESSION['employeeID'];
        
        // Update member information
        $sql_member = "UPDATE tb_member SET 
            memberName = ?,
            email = ?,
            ic = ?,
            maritalStatus = ?,
            sex = ?,
            religion = ?,
            nation = ?,
            no_pf = ?,
            position = ?,
            phoneNumber = ?,
            phoneHome = ?
            WHERE employeeId = ?";
        
        $stmt = $pdo->prepare($sql_member);
        $stmt->execute([
            $_POST['memberName'],
            $_POST['email'],
            $_POST['ic'],
            $_POST['maritalStatus'],
            $_POST['sex'],
            $_POST['religion'],
            $_POST['nation'],
            $_POST['no_pf'],
            $_POST['position'],
            $_POST['phoneNumber'],
            $_POST['phoneHome'],
            $employeeId
        ]);

        // Update home address
        $sql_home = "UPDATE tb_member_homeaddress SET 
            homeAddress = ?,
            homePostcode = ?,
            homeState = ?
            WHERE employeeID = ?";
        
        $stmt = $pdo->prepare($sql_home);
        $stmt->execute([
            $_POST['homeAddress'],
            $_POST['homePostcode'],
            $_POST['homeState'],
            $employeeId
        ]);

        // Update office address
        $sql_office = "UPDATE tb_member_officeaddress SET 
            officeAddress = ?,
            officePostcode = ?,
            officeState = ?
            WHERE employeeID = ?";
        
        $stmt = $pdo->prepare($sql_office);
        $stmt->execute([
            $_POST['officeAddress'],
            $_POST['officePostcode'],
            $_POST['officeState'],
            $employeeId
        ]);

        $_SESSION['success_message'] = "Profil berjaya dikemaskini!";
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Ralat: " . $e->getMessage();
    }
    
    header("Location: profil.php");
    exit();
}

header("Location: profil.php");
exit();
?>