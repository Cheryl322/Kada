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
        // 记录接收到的数据
        file_put_contents('debug.txt', "Received POST data:\n" . print_r($_POST, true) . "\n", FILE_APPEND);

        // 准备更新数据
        $updateData = [
            ':memberName' => $_POST['memberName'] ?? null,
            ':email' => $_POST['email'] ?? null,
            ':ic' => $_POST['ic'] ?? null,
            ':maritalStatus' => $_POST['maritalStatus'] ?? null,
            ':sex' => $_POST['sex'] ?? null,
            ':religion' => $_POST['religion'] ?? null,
            ':nation' => $_POST['nation'] ?? null,
            ':no_pf' => $_POST['no_pf'] ?? null,
            ':position' => $_POST['position'] ?? null,
            ':phoneNumber' => $_POST['phoneNumber'] ?? null,
            ':phoneHome' => $_POST['phoneHome'] ?? null,
            ':employeeId' => $_SESSION['employeeID']
        ];

        // 记录要更新的数据
        file_put_contents('debug.txt', "Update data:\n" . print_r($updateData, true) . "\n", FILE_APPEND);

        // 更新查询
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
        $result = $stmt->execute($updateData);

        // 记录更新结果
        file_put_contents('debug.txt', "Update result: " . ($result ? "Success" : "Failed") . "\n", FILE_APPEND);

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