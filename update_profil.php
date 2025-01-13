<?php
session_start();
include "dbconnect.php";

// 启用错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 记录请求数据
file_put_contents('debug.txt', "Request received at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
file_put_contents('debug.txt', "POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);
file_put_contents('debug.txt', "SESSION data: " . print_r($_SESSION, true) . "\n", FILE_APPEND);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // 数据库连接
        $host = "localhost";
        $dbname = "kada";
        $username = "root";
        $password = "";

        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8",
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // 开始事务
        $pdo->beginTransaction();

        // 更新会员信息
        $sql = "UPDATE tb_member SET 
                memberName = :memberName,
                email = :email,
                ic = :ic,
                maritalStatus = :maritalStatus,
                sex = :sex,
                religion = :religion,
                nation = :nation,
                no_pf = :no_pf,
                position = :position,
                phoneNumber = :phoneNumber,
                phoneHome = :phoneHome
                WHERE employeeId = :employeeId";

        $stmt = $pdo->prepare($sql);
        
        $result = $stmt->execute([
            ':memberName' => $_POST['memberName'],
            ':email' => $_POST['email'],
            ':ic' => $_POST['ic'],
            ':maritalStatus' => $_POST['maritalStatus'],
            ':sex' => $_POST['sex'],
            ':religion' => $_POST['religion'],
            ':nation' => $_POST['nation'],
            ':no_pf' => $_POST['no_pf'],
            ':position' => $_POST['position'],
            ':phoneNumber' => $_POST['phoneNumber'],
            ':phoneHome' => $_POST['phoneHome'],
            ':employeeId' => $_SESSION['employeeID']
        ]);

        if ($result) {
            $pdo->commit();
            $_SESSION['success_message'] = "Profil berjaya dikemaskini!";
            
            // 确保数据已经更新
            $pdo = null; // 关闭当前连接
            
            // 强制刷新
            header("Cache-Control: no-cache, must-revalidate");
            header("Location: profil.php");
                exit();
            }

    } catch (Exception $e) {
        // 如果出错，回滚事务
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        file_put_contents('debug.txt', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    }
}

// 重定向回个人资料页面
header('Location: profil.php');
exit();
?>