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
    
    mysqli_begin_transaction($conn);
    
    try {
        if ($transType == "Simpanan") {
            // 检查是否已支付 entry fee 和 deposit
            $sql_check = "SELECT COUNT(*) as count 
                         FROM tb_transaction 
                         WHERE employeeID = ? 
                         AND transType IN ('Entry Fee', 'Deposit')";
            
            $stmt_check = mysqli_prepare($conn, $sql_check);
            mysqli_stmt_bind_param($stmt_check, 's', $employeeID);
            mysqli_stmt_execute($stmt_check);
            $fees_paid = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_check))['count'] > 0;
            
            // 如果是首次付款，记录入会费和押金
            if (!$fees_paid) {
                // 获取入会费和押金金额
                $sql_fees = "SELECT entryFee, deposit 
                            FROM tb_memberregistration_feesandcontribution 
                            WHERE employeeID = ?";
                $stmt_fees = mysqli_prepare($conn, $sql_fees);
                mysqli_stmt_bind_param($stmt_fees, 's', $employeeID);
                mysqli_stmt_execute($stmt_fees);
                $fees = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_fees));
                
                // 记录入会费和押金
                $sql_insert = "INSERT INTO tb_transaction (employeeID, transType, transAmt, transDate) 
                              VALUES (?, 'Entry Fee', ?, ?), (?, 'Deposit', ?, ?)";
                $stmt_insert = mysqli_prepare($conn, $sql_insert);
                mysqli_stmt_bind_param($stmt_insert, 'sdssds', 
                    $employeeID, $fees['entryFee'], $transDate,
                    $employeeID, $fees['deposit'], $transDate
                );
                mysqli_stmt_execute($stmt_insert);
            }
            
            // 获取所有费用和分配信息
            $sql_fees = "SELECT 
                modalShare,
                feeCapital,
                fixedDeposit,
                contribution,
                entryFee,     
                deposit       
            FROM tb_memberregistration_feesandcontribution 
            WHERE employeeID = ?";
            
            $stmt_fees = mysqli_prepare($conn, $sql_fees);
            mysqli_stmt_bind_param($stmt_fees, "s", $employeeID);
            mysqli_stmt_execute($stmt_fees);
            $fees = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_fees));
            
            // 使用数据库中的入会费和押金
            $entryFee = $fees['entryFee'];
            $deposit = $fees['deposit'];
            $remainingAmount = $transAmt - $entryFee - $deposit;
            
            // 插入各类型的交易记录
            $transactions = [
                ['Simpanan-M', $fees['modalShare']],
                ['Simpanan-Y', $fees['feeCapital']],
                ['Simpanan-S', $fees['fixedDeposit']],
                ['Simpanan-T', $fees['contribution']]
            ];
            
            // 记录其他储蓄
            foreach ($transactions as $trans) {
                $sql = "INSERT INTO tb_transaction (employeeID, transType, transAmt, transDate) 
                        VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssds", $employeeID, $trans[0], $trans[1], $transDate);
                mysqli_stmt_execute($stmt);
            }
            
            mysqli_commit($conn);
            $_SESSION['success'] = "Payment record uploaded and allocated successfully!";
        } else {
            // 处理其他类型的交易（如贷款还款）
            $sql = "INSERT INTO tb_transaction (employeeID, transType, transAmt, transDate) 
                    VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssds", $employeeID, $transType, $transAmt, $transDate);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_commit($conn);
                $_SESSION['success'] = "Payment record uploaded successfully!";
            } else {
                throw new Exception("Error uploading payment record");
            }
        }
        
        header("Location: admin_upload_payment.php");
        exit();
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: admin_upload_payment.php");
        exit();
    }
}

// 获取所有会员列表..
$sql_members = "SELECT employeeID, memberName FROM tb_member ORDER BY employeeID ASC";
$result_members = mysqli_query($conn, $sql_members);

function formatNumber($number) {
    return str_pad($number, 4, '0', STR_PAD_LEFT);
}
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
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
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