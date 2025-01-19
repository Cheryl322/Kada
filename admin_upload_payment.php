<?php
session_start();
include "dbconnect.php";
require_once "functions.php";
include "headeradmin.php";

// 检查admin权限
checkAdminAccess();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employeeID = $_POST['employeeID'];
    $transType = $_POST['transType'];
    $transAmt = $_POST['transAmt'];
    $transDate = $_POST['transDate'];
    
    mysqli_begin_transaction($conn);
    
    try {
        if ($transType == 'Simpanan') {
            // 获取会员的缴费设置
            $sql_fees = "SELECT modalShare, feeCapital, fixedDeposit, contribution 
                        FROM tb_memberregistration_feesandcontribution 
                        WHERE employeeID = ?";
            $stmt = mysqli_prepare($conn, $sql_fees);
            mysqli_stmt_bind_param($stmt, 's', $employeeID);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);  // 获取结果集
            $fees = mysqli_fetch_assoc($result);      // 现在可以安全地获取数据
            
            // 检查是否成功获取费用设置
            if ($fees) {
                // 记录交易
                $transaction_types = [
                    ['Simpanan-M', $fees['modalShare']],
                    ['Simpanan-Y', $fees['feeCapital']],
                    ['Simpanan-S', $fees['fixedDeposit']],
                    ['Simpanan-T', $fees['contribution']]
                ];
                
                foreach ($transaction_types as $trans) {
                    if ($trans[1] > 0) {
                        $sql = "INSERT INTO tb_transaction (employeeID, transType, transAmt, transDate) 
                               VALUES (?, ?, ?, ?)";
                        $stmt = mysqli_prepare($conn, $sql);
                        mysqli_stmt_bind_param($stmt, 'ssds', 
                            $employeeID, 
                            $trans[0],  // 使用新的交易类型
                            $trans[1],  // 使用设定的固定金额
                            $transDate
                        );
                        mysqli_stmt_execute($stmt);
                    }
                }
            } else {
                throw new Exception("无法获取会员的缴费设置");
            }
        } elseif ($transType == 'Bayaran Balik Pinjaman') {
            // 获取所有活跃贷款类型的余额
            $get_loans = "SELECT loanType, balance FROM tb_loan 
                         WHERE employeeID = ? ";
            $stmt = mysqli_prepare($conn, $get_loans);
            mysqli_stmt_bind_param($stmt, 's', $employeeID);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $total_balance = 0;
            $loans = [];
            while ($loan = mysqli_fetch_assoc($result)) {
                $total_balance += $loan['balance'];
                $loans[] = $loan;
            }
            
            if ($total_balance > 0) {
                // 按比例分配还款金额到每种贷款类型
                foreach ($loans as $loan) {
                    $ratio = $loan['balance'] / $total_balance;
                    $payment_amount = round($transAmt * $ratio, 2);
                    
                    if ($payment_amount > 0) {
                        // 更新特定类型贷款的余额
                        $update_loan = "UPDATE tb_loan 
                                      SET balance = balance - ?
                                      WHERE employeeID = ? AND loanType = ? ";
                        $stmt = mysqli_prepare($conn, $update_loan);
                        mysqli_stmt_bind_param($stmt, 'dss', $payment_amount, $employeeID, $loan['loanType']);
                        mysqli_stmt_execute($stmt);
                        
                        // 记录每种类型的还款交易
                        $trans_type = "Bayaran Balik Pinjaman - " . $loan['loanType'];
                        $sql = "INSERT INTO tb_transaction (employeeID, transType, transAmt, transDate) 
                               VALUES (?, ?, ?, ?)";
                        $stmt = mysqli_prepare($conn, $sql);
                        mysqli_stmt_bind_param($stmt, 'ssds', 
                            $employeeID,
                            $trans_type,
                            $payment_amount,
                            $transDate
                        );
                        mysqli_stmt_execute($stmt);
                    }
                }
            }
        }

        mysqli_commit($conn);
        $_SESSION['success'] = "Rekod pembayaran berjaya dimuat naik";
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = "Ralat semasa memuat naik rekod pembayaran: " . $e->getMessage();
    }
    
    header("Location: admin_upload_payment.php");
    exit();
}

// 获取所有会员列表
$sql_members = "SELECT employeeID, memberName FROM tb_member 
                ORDER BY employeeID ASC";
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
        /* 添加样式确保内容不被导航栏遮挡 */
        body {
            padding-top: 80px; /* 增加顶部内边距，值要大于导航栏高度 */
        }
        
        .main-container {
            position: relative;
            z-index: 1;
            background: white;
        }

        /* 调整表单容器样式 */
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
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
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