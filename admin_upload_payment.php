<?php
session_start();
include "dbconnect.php";
require_once "functions.php";

// 检查admin权限
checkAdminAccess();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employeeID = $_POST['employeeID'];
    $transType = $_POST['transType'];
    $transAmt = $_POST['transAmt'];
    $transDate = $_POST['transDate'];
    
    // 插入交易记录
    $sql = "INSERT INTO tb_transaction (employeeID, transType, transAmt, transDate) 
            VALUES (?, ?, ?, ?)";
            
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssds", $employeeID, $transType, $transAmt, $transDate);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Payment record uploaded successfully!";
        header("Location: admin_upload_payment.php");
        exit();
    } else {
        $_SESSION['error'] = "Error uploading payment record: " . mysqli_error($conn);
        header("Location: admin_upload_payment.php");
        exit();
    }
}

// 获取所有会员列表
$sql_members = "SELECT employeeID, memberName FROM tb_member ORDER BY employeeID ASC";
$result_members = mysqli_query($conn, $sql_members);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Payment Record</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .main-container {
            padding-top: 20px;
        }
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include "headeradmin.php"; ?>
    
    <div class="main-container">
        <div class="container">
            <div class="form-container">
                <h2 class="mb-4">Upload Payment Record</h2>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Member</label>
                        <select name="employeeID" class="form-control" required>
                            <option value="">Select Member</option>
                            <?php while ($member = mysqli_fetch_assoc($result_members)): ?>
                                <option value="<?php echo $member['employeeID']; ?>">
                                    <?php echo $member['employeeID'] . ' - ' . $member['memberName']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Type</label>
                        <select name="transType" class="form-control" required>
                            <option value="Bayaran Balik Pinjaman">Bayaran Balik Pinjaman</option>
                            <option value="Simpanan">Simpanan</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Amount (RM)</label>
                        <input type="number" name="transAmt" class="form-control" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" name="transDate" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Upload Payment Record</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 