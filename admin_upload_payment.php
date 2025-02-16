<?php
// 在文件开始处添加错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);
// 确保数据库连接正确
include "dbconnect.php";
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// 检查session是否已经启动
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "functions.php";
// 检查admin权限
checkAdminAccess();
// 获取交易日期
$transDate = isset($_POST['transDate']) ? $_POST['transDate'] : date('Y-m-d');
// 添加调试信息
echo "<!-- Debug: Transaction Date = " . $transDate . " -->";
// 在文件开头添加这段代码来获取正确的扣除类型ID
$deduction_types = [];
$sql = "SELECT DeducType_ID, typeName FROM tb_deduction_type";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $deduction_types[$row['typeName']] = $row['DeducType_ID'];
}

// 定义付款类型映射
$payment_type_mapping = [
    'modalShare' => 1,    // Modal Share
    'feeCapital' => 2,    // Fee Capital
    'fixedDeposit' => 3,  // Fixed Deposit
    'contribution' => 4,  // Contribution
    'deposit' => 5,       // Deposit
    'loanRepayment' => 6, // Loan Payment
    'entryFee' => 7      // Entry Fee
];

// 1. 会员基本信息和费用查询
$sql_member_fees = "SELECT DISTINCT
    m.employeeID,
    m.memberName,
    mf.modalShare,
    mf.feeCapital,
    mf.fixedDeposit,
    mf.contribution,
    mf.deposit
FROM tb_member m
LEFT JOIN tb_memberregistration_feesandcontribution mf 
    ON m.employeeID = mf.employeeID";
// 2. 贷款还款信息查询
$sql_loan_repayments = "SELECT 
    l.employeeID,
    l.loanType,
    l.monthlyInstallments,
    l.balance
FROM tb_loan l
JOIN tb_loanapplication la ON l.loanApplicationID = la.loanApplicationID
WHERE la.loanStatus = 'Diluluskan'
AND l.balance > 0";
// 执行查询
$stmt_member_fees = mysqli_prepare($conn, $sql_member_fees);
mysqli_stmt_execute($stmt_member_fees);
$result_member_fees = mysqli_stmt_get_result($stmt_member_fees);
$stmt_loan_repayments = mysqli_prepare($conn, $sql_loan_repayments);
mysqli_stmt_execute($stmt_loan_repayments);
$result_loan_repayments = mysqli_stmt_get_result($stmt_loan_repayments);
// 组织数据
$members_data = array();
while ($member = mysqli_fetch_assoc($result_member_fees)) {
    $employeeID = $member['employeeID'];
    $members_data[$employeeID] = array(
        'employeeID' => $member['employeeID'],
        'memberName' => $member['memberName'],
        'modalShare' => $member['modalShare'],
        'feeCapital' => $member['feeCapital'],
        'fixedDeposit' => $member['fixedDeposit'],
        'contribution' => $member['contribution'],
        'deposit' => $member['deposit'],
        'loanRepayments' => array()
    );
}
// 添加贷款还款信息
while ($loan = mysqli_fetch_assoc($result_loan_repayments)) {
    $employeeID = $loan['employeeID'];
    if (isset($members_data[$employeeID])) {
        $members_data[$employeeID]['loanRepayments'][] = array(
            'loanType' => $loan['loanType'],
            'monthlyInstallments' => $loan['monthlyInstallments'],
            'balance' => $loan['balance']
        );
    }
}
// 表单处理部分
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['single_upload'])) {
        $employeeID = $_POST['single_upload'];
        $transDate = $_POST['transDate'];
        $payments = $_POST['payments'][$employeeID];
        
        mysqli_begin_transaction($conn);
        try {
            foreach ($payments as $type => $amount) {
                if ($type !== 'upfront_type' && $type !== 'upfront_amount' && $type !== 'entry_fee_type' && $type !== 'entry_fee_amount' && $amount > 0) {
                    if ($type === 'loanRepayment' && is_array($amount)) {
                        foreach ($amount as $loanType => $loanAmount) {
                            if ($loanAmount > 0) {
                                // 插入还款记录
                                $sql = "INSERT INTO tb_deduction 
                                       (employeeID, DeducType_ID, Deduct_Amt, Deduct_date, loanApplicationID) 
                                       VALUES (?, 6, ?, ?, ?)";
                                
                                // 获取 loanApplicationID
                                $sql_get_loan = "SELECT loanApplicationID 
                                                FROM tb_loan 
                                                WHERE employeeID = ? 
                                                AND loanType = ?";
                                
                                $stmt_get_loan = mysqli_prepare($conn, $sql_get_loan);
                                mysqli_stmt_bind_param($stmt_get_loan, "ss", $employeeID, $loanType);
                                mysqli_stmt_execute($stmt_get_loan);
                                $loan_result = mysqli_stmt_get_result($stmt_get_loan);
                                $loan_data = mysqli_fetch_assoc($loan_result);
                                
                                if ($loan_data) {
                                    // 插入还款记录
                                    $stmt = mysqli_prepare($conn, $sql);
                                    mysqli_stmt_bind_param($stmt, "sdsi", 
                                        $employeeID, 
                                        $loanAmount,
                                        $transDate,
                                        $loan_data['loanApplicationID']
                                    );
                                    mysqli_stmt_execute($stmt);
                                    
                                    // 更新贷款余额
                                    $sql_update_balance = "UPDATE tb_loan 
                                                         SET balance = balance - ? 
                                                         WHERE loanApplicationID = ?";
                                    
                                    $stmt_update = mysqli_prepare($conn, $sql_update_balance);
                                    mysqli_stmt_bind_param($stmt_update, "di", 
                                        $loanAmount, 
                                        $loan_data['loanApplicationID']
                                    );
                                    mysqli_stmt_execute($stmt_update);
                                    mysqli_stmt_close($stmt_update);
                                }
                                
                                mysqli_stmt_close($stmt_get_loan);
                            }
                        }
                    } else {
                        if (isset($payment_type_mapping[$type])) {
                            $deducTypeID = $payment_type_mapping[$type];
                            $sql = "INSERT INTO tb_deduction (employeeID, DeducType_ID, Deduct_Amt, Deduct_date) 
                                    VALUES (?, ?, ?, ?)";
                            $stmt = mysqli_prepare($conn, $sql);
                            mysqli_stmt_bind_param($stmt, "iids", $employeeID, $deducTypeID, $amount, $transDate);
                            mysqli_stmt_execute($stmt);
                        }
                    }
                }
            }

            // 处理预付款（现在包括 Entry Fee）
            if (!empty($payments['upfront_type']) && $payments['upfront_amount'] > 0) {
                $upfront_type = $payments['upfront_type'];
                $upfront_amount = $payments['upfront_amount'];

                $sql = "INSERT INTO tb_deduction (employeeID, DeducType_ID, Deduct_Amt, Deduct_date) 
                        VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sids", $employeeID, $upfront_type, $upfront_amount, $transDate);
                mysqli_stmt_execute($stmt);
            }

            mysqli_commit($conn);
            $_SESSION['success_message'] = "Payment record uploaded successfully!";
            // 重定向到同一个页面
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $_SESSION['error_message'] = "Error uploading payment record: " . $e->getMessage();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
    
    // 处理批量上传
    if (isset($_POST['update_payments']) && isset($_POST['selected'])) {
        mysqli_begin_transaction($conn);
        try {
            foreach ($_POST['selected'] as $employeeID) {
                $payments = $_POST['payments'][$employeeID];
                
                foreach ($payments as $type => $amount) {
                    if ($type !== 'upfront_type' && $type !== 'upfront_amount' && $type !== 'entry_fee_type' && $type !== 'entry_fee_amount' && $amount > 0) {
                        if ($type === 'loanRepayment' && is_array($amount)) {
                            foreach ($amount as $loanType => $loanAmount) {
                                if ($loanAmount > 0) {
                                    // 插入还款记录
                                    $sql = "INSERT INTO tb_deduction 
                                           (employeeID, DeducType_ID, Deduct_Amt, Deduct_date, loanApplicationID) 
                                           VALUES (?, 6, ?, ?, ?)";
                                    
                                    // 获取 loanApplicationID
                                    $sql_get_loan = "SELECT loanApplicationID 
                                                    FROM tb_loan 
                                                    WHERE employeeID = ? 
                                                    AND loanType = ?";
                                    
                                    $stmt_get_loan = mysqli_prepare($conn, $sql_get_loan);
                                    mysqli_stmt_bind_param($stmt_get_loan, "ss", $employeeID, $loanType);
                                    mysqli_stmt_execute($stmt_get_loan);
                                    $loan_result = mysqli_stmt_get_result($stmt_get_loan);
                                    $loan_data = mysqli_fetch_assoc($loan_result);
                                    
                                    if ($loan_data) {
                                        $stmt = mysqli_prepare($conn, $sql);
                                        mysqli_stmt_bind_param($stmt, "sdsi", 
                                            $employeeID, 
                                            $loanAmount,
                                            $transDate,
                                            $loan_data['loanApplicationID']
                                        );
                                        mysqli_stmt_execute($stmt);
                                        
                                        // 更新贷款余额
                                        $sql_update_balance = "UPDATE tb_loan 
                                                             SET balance = balance - ? 
                                                             WHERE loanApplicationID = ?";
                                        
                                        $stmt_update = mysqli_prepare($conn, $sql_update_balance);
                                        mysqli_stmt_bind_param($stmt_update, "di", 
                                            $loanAmount, 
                                            $loan_data['loanApplicationID']
                                        );
                                        mysqli_stmt_execute($stmt_update);
                                    }
                                }
                            }
                        } else {
                            if (isset($payment_type_mapping[$type])) {
                                $deducTypeID = $payment_type_mapping[$type];
                                $sql = "INSERT INTO tb_deduction (employeeID, DeducType_ID, Deduct_Amt, Deduct_date) 
                                        VALUES (?, ?, ?, ?)";
                                $stmt = mysqli_prepare($conn, $sql);
                                mysqli_stmt_bind_param($stmt, "iids", $employeeID, $deducTypeID, $amount, $transDate);
                                mysqli_stmt_execute($stmt);
                            }
                        }
                    }
                }

                // 处理预付款
                if (!empty($payments['upfront_type']) && $payments['upfront_amount'] > 0) {
                    $upfront_type = $payments['upfront_type'];
                    $upfront_amount = $payments['upfront_amount'];

                    $sql = "INSERT INTO tb_deduction (employeeID, DeducType_ID, Deduct_Amt, Deduct_date) 
                            VALUES (?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "sids", $employeeID, $upfront_type, $upfront_amount, $transDate);
                    mysqli_stmt_execute($stmt);
                }
            }

            mysqli_commit($conn);
            $_SESSION['success_message'] = "Semua rekod pembayaran berjaya dimuat naik!";
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Semua rekod pembayaran berjaya dimuat naik!',
                    showConfirmButton: false,
                    timer: 2000,
                    background: '#00875A',
                    color: '#ffffff',
                    toast: true,
                    position: 'top',
                    width: 'auto',
                    padding: '1em',
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    },
                    customClass: {
                        popup: 'modern-toast'
                    }
                });
            </script>";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $_SESSION['error_message'] = "Ralat semasa memuat naik rekod pembayaran: " . $e->getMessage();
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Ralat!',
                    text: 'Ralat semasa memuat naik rekod pembayaran.',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    toast: true,
                    position: 'top',
                    customClass: {
                        popup: 'colored-toast'
                    }
                });
            </script>";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}
// 获取扣款类型
$sql_deduction_types = "SELECT * FROM tb_deduction_Type";
$result_types = mysqli_query($conn, $sql_deduction_types);
$deduction_types = [];
while ($type = mysqli_fetch_assoc($result_types)) {
    $deduction_types[$type['DeducType_ID']] = $type;
}
// 为了调试，添加这段代码
echo "<!-- POST data: ";
print_r($_POST);
echo " -->";

// 在页面顶部，检查是否有成功消息
if (isset($_SESSION['success_message'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Semua rekod pembayaran berjaya dimuat naik!',
                showConfirmButton: false,
                timer: 2000,
                background: '#00875A',
                color: '#ffffff',
                toast: true,
                position: 'top',
                width: 'auto',
                padding: '1em',
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                },
                customClass: {
                    popup: 'modern-toast'
                }
            });
        });
    </script>";
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Ralat!',
                text: '" . $_SESSION['error_message'] . "',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                toast: true,
                position: 'top',
                customClass: {
                    popup: 'colored-toast'
                }
            });
        });
    </script>";
    unset($_SESSION['error_message']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Payment Record</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* 添加在现有样式的顶部 */
        body {
            padding-top: 60px; /* 根据你的header高度调整这个值 */
        }
        .container {
            margin-top: 20px; /* 额外的顶部间距 */
        }
        .alert {
            position: fixed;
            top: 70px; /* header高度 + 一些间距 */
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            padding: 15px;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        /* 定义颜色变量 */
        :root {
            --primary-dark: #00796B;    /* 深薄荷绿 */
            --primary: #009688;         /* 标准薄荷绿 */
            --primary-light: #E0F2F1;   /* 浅薄荷绿 */
            --secondary: #4DB6AC;       /* 次要薄荷绿 */
            --neutral-dark: #2c3e50;    /* 深灰 */
            --neutral: #495057;         /* 中灰 */
            --neutral-light: #f8f9fa;   /* 浅灰 */
            --border: #dee2e6;          /* 边框颜色 */
        }
        /* 页面整体容器样式 */
        .container {
            padding-top: 80px;
            max-width: 1200px;
            background: #fff;
        }
        /* 页面标题样式 */
        .page-header {
            background: var(--primary-dark);
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .page-title {
            color: #fff;
            font-size: 24px;
            font-weight: 500;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        /* 控制面板样式 */
        .top-nav {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .control-panel-container {
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        /* 按钮样式 */
        .btn-group {
            display: flex;
            align-items: center;
        }
        /* 复选框容器样式 */
        .select-all-wrapper {
            display: inline-flex;
            align-items: center;
            margin-right: 10px;
        }
        /* 自定义复选框样式 */
        .custom-checkbox {
            opacity: 1 !important; /* 确保复选框可见 */
            position: relative !important;
            display: inline-block !important;
            width: 20px !important;
            height: 20px !important;
            margin-right: 10px !important;
        }
        .checkbox-label {
            width: 20px;
            height: 20px;
            border: 2px solid var(--primary);
            border-radius: 4px;
            cursor: pointer;
            position: relative;
            display: inline-block;
            transition: all 0.2s ease;
        }
        .checkbox-label:hover {
            background-color: var(--primary-light);
        }
        .custom-checkbox:checked + .checkbox-label {
            background-color: var(--primary);
        }
        .custom-checkbox:checked + .checkbox-label::after {
            content: '';
            position: absolute;
            left: 6px;
            top: 2px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        /* 日期选择器样式 */
        .date-picker {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .date-picker label {
            color: #495057;
            font-weight: 500;
        }
        .date-picker input {
            border: 1px solid #dee2e6;
            padding: 0.5rem;
            border-radius: 4px;
        }
        /* 会员卡片样式 */
        .member-card {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .member-header {
            background: #f8f9fa;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .member-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .member-id-badge {
            background: var(--primary);
            color: #fff;
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .member-name {
            color: #2c3e50;
            font-weight: 500;
            font-size: 1.1rem;
        }
        /* 付款项目样式 */
        .payment-grid {
            padding: 1.5rem;
        }
        .payment-item {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 2rem;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        .payment-label {
            color: #2c3e50;
            font-weight: 500;
        }
        .minimum-amount {
            color: #666;
            font-size: 0.9rem;
        }
        .payment-amount {
            color: var(--primary-dark);
            font-family: 'Courier New', monospace;
            font-weight: 500;
        }
        .payment-input input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
        .payment-input input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 150, 136, 0.1);
        }
        /* 总额部分样式 */
        .total-section {
            background: #f8f9fa;
            padding: 1rem 1.5rem;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .total-label {
            color: #2c3e50;
            font-weight: 500;
        }
        .total-amount {
            color: var(--primary-dark);
            font-family: 'Courier New', monospace;
            font-weight: 600;
            font-size: 1.2rem;
        }
        /* 上传按钮样式 */
        .upload-single {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .upload-single:hover {
            background: var(--primary-dark);
        }
        /* 验证消息样式 */
        .validation-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        /* 响应式调整 */
        @media (max-width: 768px) {
            .payment-item {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .control-panel-container {
                flex-direction: column;
                gap: 1rem;
            }
        }
        .payment-item:hover {
            background: var(--primary-light);
        }
        .page-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)), 
                        url('img/padi.jpg') no-repeat center center fixed;
            background-size: cover;
            z-index: -1;
        }
        .back-section {
            padding: 10px 20px; /* 减少上下内边距 */
            margin-top: 20px; /* 减少顶部边距 */
        }
        .btn-kembali {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px; /* 减少按钮内边距 */
            background-color: #FF9B9B;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .btn-kembali:hover {
            background-color: #ff8282;
            color: white;
            text-decoration: none;
        }
        .btn-kembali i {
            margin-right: 8px;
        }
        /* Update container styles */
        .container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px; 
            margin-top: 10px; 
        }
        .page-header {
            margin-bottom: 15px; 
            padding: 10px 15px; 
        }
        .page-title {
            font-size: 20px; 
            margin: 0;
        }
       
        .form-group {
            margin-bottom: 10px; 
        }
        .card {
            margin-bottom: 15px; 
        }
        .card-body {
            padding: 12px; 
        }
        
        @media (max-width: 768px) {
            .back-section {
                padding: 8px 15px;
                margin-top: 15px;
            }
            
            .container {
                padding: 10px;
            }
        }
        .checkbox-blue, .checkbox-green, .member-checkbox {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            cursor: pointer;
        }

        .checkbox-blue {
            accent-color: #007bff;
        }

        .checkbox-green {
            accent-color: #28a745;
        }

        .member-checkbox {
            accent-color: #007bff;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 15px;
        }

        .member-item {
            display: flex;
            align-items: center;
            padding: 10px;
            gap: 10px;
        }
        .checkbox-icons {
            display: flex;
            gap: 5px;
            margin-right: 10px;
        }

        .checkbox-blue {
            color: #007bff;
            cursor: pointer;
        }

        .checkbox-green {
            color: #28a745;
            cursor: pointer;
        }

        .fa-square-check, .fa-square {
            font-size: 1.2rem;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* 统一的复选框样式 */
        input[type="checkbox"] {
            width: 16px;
            height: 16px;
            border-radius: 3px;
            accent-color: #0d6efd; /* Bootstrap primary blue */
            cursor: pointer;
        }

        .btn-primary {
            margin-left: 10px;
        }

        .member-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
        }

        .muat-naik-single {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .muat-naik-single:hover {
            background: var(--primary-dark);
        }

        .modern-toast {
            border-radius: 8px !important;
            font-family: system-ui, -apple-system, sans-serif !important;
            font-weight: 500 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }

        .modern-toast .swal2-icon {
            margin: 0.5em !important;
            font-size: 0.8em !important;
        }

        .modern-toast .swal2-title {
            font-size: 15px !important;
            padding: 0.5em 1em !important;
            line-height: 1.3 !important;
        }

        .swal2-popup.swal2-toast .swal2-icon.swal2-success .swal2-success-ring {
            border-color: #ffffff !important;
        }

        .swal2-popup.swal2-toast .swal2-icon.swal2-success [class^=swal2-success-line] {
            background-color: #ffffff !important;
        }

        .colored-toast {
            background-color: #f27474 !important;
            color: white !important;
        }

        .minimum-amount {
            color: #666;
            font-size: 0.9em;
            margin-left: 5px;
        }

        .is-invalid {
            border-color: #dc3545 !important;
            background-color: #fff8f8;
        }

        .payment-input-field.is-invalid {
            border-color: #dc3545;
            background-color: #fff8f8;
        }

        .payment-input-field.is-valid {
            border-color: #28a745;
            background-color: #f8fff8;
        }

        .validation-message {
            color: #dc3545;
            font-size: 0.85em;
            margin-top: 5px;
            display: none;
        }

        .payment-item {
            margin-bottom: 15px;
        }

        .payment-label {
            margin-bottom: 5px;
            font-weight: 500;
        }

        .payment-amount {
            color: #0056b3;
            margin-bottom: 5px;
        }

        /* 移除默认的验证图标 */
        input::-webkit-validation-bubble-message,
        input::-webkit-validation-bubble,
        input::-webkit-validation-bubble-arrow-clipper {
            display: none;
        }

        /* 移除 Chrome 的默认验证样式 */
        input:valid {
            box-shadow: none !important;
        }

        .payment-input-field {
            width: 100%;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            background-color: #fff !important;
            transition: border-color 0.15s ease-in-out;
        }

        /* 移除验证成功和失败状态的所有特殊样式 */
        .payment-input-field.is-valid,
        .payment-input-field.is-invalid,
        .payment-input-field:valid,
        .payment-input-field:invalid {
            background-color: #fff !important;
            background-image: none !important;
            border-color: #ced4da;
        }

        /* 只保留hover和focus状态的样式 */
        .payment-input-field:hover,
        .payment-input-field:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
    </style>
</head>
<body>
<?php include "headeradmin.php"; ?>
    <div class="page-background"></div>
    <div class="back-section">
        <a href="adminmainpage.php" class="btn-kembali">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    
    <div class="container">
        <div class="page-header">
            <h2 class="page-title">Rekod Transaksi</h2>
        </div>
        <form method="POST" id="paymentForm">
            <div class="top-nav">
                <div class="control-panel-container">
                    <div class="btn-group">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" class="blue-checkbox" id="selectAll" onclick="toggleAll()">
                            <button type="submit" name="update_payments" class="btn btn-primary">
                                Muat Naik Terpilih
                            </button>
                        </div>
                    </div>
                    <div class="date-picker">
                        <label>Tarikh Transaksi:</label>
                        <input type="date" name="transDate" class="form-control" 
                               value="<?php echo $transDate; ?>" required>
                    </div>
                </div>
            </div>
            <div class="content-area">
                <?php foreach ($members_data as $member): ?>
                    <div class="member-card" data-employee-id="<?php echo $member['employeeID']; ?>">
                        <div class="member-header">
                            <div class="member-info">
                                <input type="checkbox" 
                                       name="selected[]" 
                                       value="<?php echo $member['employeeID']; ?>" 
                                       class="member-checkbox">
                                <span class="member-id-badge"><?php echo $member['employeeID']; ?></span>
                                <span class="member-name"><?php echo $member['memberName']; ?></span>
                            </div>
                            <button type="button" class="btn btn-primary muat-naik-single" 
                                    onclick="submitSingleMember('<?php echo $member['employeeID']; ?>')">
                                Muat Naik
                            </button>
                        </div>
                        <div class="payment-grid">
                            <!-- Modal Share -->
                            <div class="payment-item">
                                <div class="payment-label">
                                Modal Syer
                                    <span class="minimum-amount">(Min: RM <?php echo number_format($member['modalShare'], 2); ?>)</span>
                                </div>
                                <div class="payment-amount">
                                    RM <?php echo number_format($member['modalShare'], 2); ?>
                                </div>
                                <div class="payment-input">
                                    <div class="input-wrapper">
                                        <input type="number" 
                                           name="payments[<?php echo $member['employeeID']; ?>][modalShare]" 
                                           value="<?php echo $member['modalShare']; ?>"
                                           min="<?php echo $member['modalShare']; ?>"
                                           step="0.01"
                                           class="form-control payment-input-field"
                                           data-original-amount="<?php echo number_format($member['modalShare'], 2, '.', ''); ?>"
                                           oninput="validateAmountInline(this, <?php echo number_format($member['modalShare'], 2, '.', ''); ?>)">
                                        <div class="validation-message"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Fee Capital -->
                            <div class="payment-item">
                                <div class="payment-label">
                                Modal Yuran
                                    <span class="minimum-amount">(Min: RM <?php echo number_format($member['feeCapital'], 2); ?>)</span>
                                </div>
                                <div class="payment-amount">
                                    RM <?php echo number_format($member['feeCapital'], 2); ?>
                                </div>
                                <div class="payment-input">
                                    <div class="input-wrapper">
                                        <input type="number" 
                                           name="payments[<?php echo $member['employeeID']; ?>][feeCapital]" 
                                           value="<?php echo $member['feeCapital']; ?>"
                                           min="<?php echo $member['feeCapital']; ?>"
                                           step="0.01"
                                           class="form-control payment-input-field"
                                           data-original-amount="<?php echo number_format($member['feeCapital'], 2, '.', ''); ?>"
                                           oninput="validateAmountInline(this, <?php echo number_format($member['feeCapital'], 2, '.', ''); ?>)">
                                        <div class="validation-message"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Fixed Deposit -->
                            <div class="payment-item">
                                <div class="payment-label">
                                Simpanan Tetap
                                    <span class="minimum-amount">(Min: RM <?php echo number_format($member['fixedDeposit'], 2); ?>)</span>
                                </div>
                                <div class="payment-amount">
                                    RM <?php echo number_format($member['fixedDeposit'], 2); ?>
                                </div>
                                <div class="payment-input">
                                    <div class="input-wrapper">
                                        <input type="number" 
                                           name="payments[<?php echo $member['employeeID']; ?>][fixedDeposit]" 
                                           value="<?php echo $member['fixedDeposit']; ?>"
                                           min="<?php echo $member['fixedDeposit']; ?>"
                                           step="0.01"
                                           class="form-control payment-input-field"
                                           data-original-amount="<?php echo number_format($member['fixedDeposit'], 2, '.', ''); ?>"
                                           oninput="validateAmountInline(this, <?php echo number_format($member['fixedDeposit'], 2, '.', ''); ?>)">
                                        <div class="validation-message"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Contribution -->
                            <div class="payment-item">
                                <div class="payment-label">
                                Sumbangan Tabung Kebajikan (AL-ABRAR)
                                    <span class="minimum-amount">(Min: RM <?php echo number_format($member['contribution'], 2); ?>)</span>
                                </div>
                                <div class="payment-amount">
                                    RM <?php echo number_format($member['contribution'], 2); ?>
                                </div>
                                <div class="payment-input">
                                    <div class="input-wrapper">
                                        <input type="number" 
                                           name="payments[<?php echo $member['employeeID']; ?>][contribution]" 
                                           value="<?php echo $member['contribution']; ?>"
                                           min="<?php echo $member['contribution']; ?>"
                                           step="0.01"
                                           class="form-control payment-input-field"
                                           data-original-amount="<?php echo number_format($member['contribution'], 2, '.', ''); ?>"
                                           oninput="validateAmountInline(this, <?php echo number_format($member['contribution'], 2, '.', ''); ?>)">
                                        <div class="validation-message"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Deposit -->
                            <div class="payment-item">
                                <div class="payment-label">
                                Wang Deposit Anggota 
                                    <span class="minimum-amount">(Min: RM <?php echo number_format($member['deposit'], 2); ?>)</span>
                                </div>
                                <div class="payment-amount">
                                    RM <?php echo number_format($member['deposit'], 2); ?>
                                </div>
                                <div class="payment-input">
                                    <div class="input-wrapper">
                                        <input type="number" 
                                            name="payments[<?php echo $member['employeeID']; ?>][deposit]" 
                                            value="<?php echo number_format($member['deposit'], 2, '.', ''); ?>"
                                            min="<?php echo number_format($member['deposit'], 2, '.', ''); ?>"
                                            step="0.01"
                                            class="form-control payment-input-field"
                                            data-original-amount="<?php echo number_format($member['deposit'], 2, '.', ''); ?>"
                                            oninput="validateAmountInline(this, <?php echo number_format($member['deposit'], 2, '.', ''); ?>)">
                                        <div class="validation-message"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Loan Repayments -->
                            <?php
                            // 获取贷款余额
                            $sql = "SELECT loanType, monthlyInstallments, balance FROM tb_loan WHERE employeeID = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("s", $member['employeeID']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $loanBalances = $result->fetch_all(MYSQLI_ASSOC);

                            // 只显示余额大于0且不为null的贷款
                            foreach ($loanBalances as $loan) {
                                if ($loan['balance'] !== null && $loan['balance'] > 0) { ?>
                                    <div class="payment-item">
                                        <div class="payment-label">
                                            Bayaran Balik (<?php echo $loan['loanType']; ?>)
                                            <span class="minimum-amount">(Min: RM <?php echo number_format($loan['monthlyInstallments'], 2); ?>)</span>
                                        </div>
                                        <div class="payment-amount">
                                            Balance: RM <?php echo number_format(floatval($loan['balance']), 2); ?>
                                        </div>
                                        <div class="payment-input">
                                            <input type="number" 
                                                name="payments[<?php echo $member['employeeID']; ?>][loanRepayment][<?php echo $loan['loanType']; ?>]" 
                                                value="<?php echo number_format($loan['monthlyInstallments'], 2, '.', ''); ?>"
                                                min="<?php echo number_format($loan['monthlyInstallments'], 2, '.', ''); ?>"
                                                step="0.01"
                                                class="form-control payment-input-field"
                                                data-original-amount="<?php echo number_format($loan['monthlyInstallments'], 2, '.', ''); ?>"
                                                oninput="validateAmountInline(this, <?php echo number_format($loan['monthlyInstallments'], 2, '.', ''); ?>)">
                                            <div class="validation-message"></div>
                                        </div>
                                    </div>
                                <?php }
                            } ?>

                            <!-- 修改 Upfront Payment section -->
                            <div class="payment-item">
                                <div class="payment-label">
                                    Bayaran Tambahan
                                    <span class="minimum-amount">(Optional)</span>
                                </div>
                                <div class="payment-amount">
                                    <select class="form-select" 
                                            name="payments[<?php echo $member['employeeID']; ?>][upfront_type]"
                                            onchange="updateTotalAmount(this)">
                                        <option value="">Select payment type...</option>
                                        <?php
                                        $types_sql = "SELECT DeducType_ID, typeName FROM tb_deduction_type 
                                                      WHERE DeducType_ID IN (7)";  
                                        $types_result = mysqli_query($conn, $types_sql);
                                        while ($type = mysqli_fetch_assoc($types_result)) {
                                            // 替换显示文本，但保持原始值不变
                                            $displayName = ($type['typeName'] == 'Entry Fee') ? 'Fee Masuk' : $type['typeName'];
                                            echo "<option value='" . $type['DeducType_ID'] . "'>" . $displayName . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="payment-input">
                                    <input type="number" 
                                           name="payments[<?php echo $member['employeeID']; ?>][upfront_amount]" 
                                           value="0"
                                           min="0"
                                           step="0.01"
                                           class="form-control payment-input-field"
                                           data-original-amount="0"
                                           oninput="updateTotalAmount(this)">
                                </div>
                            </div>

                            <!-- Total Section -->
                            <div class="total-section">
                                <div class="total-label">Jumlah Keseluruhan</div>
                                <div class="total-amount">
                                    RM <span id="total_<?php echo $member['employeeID']; ?>">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    let isAllSelected = false;

    function toggleAll() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const memberCheckboxes = document.querySelectorAll('input[name="selected[]"]');
        
        memberCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
    }

    function submitSingleMember(employeeID) {
        Swal.fire({
            title: 'Pengesahan',
            text: 'Adakah anda pasti untuk muat naik rekod pembayaran ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Ya, Muat Naik',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // 创建一个临时表单来提交单个会员的数据
                const tempForm = document.createElement('form');
                tempForm.method = 'POST';
                tempForm.action = window.location.href;

                // 添加会员ID
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'selected[]';
                idInput.value = employeeID;
                tempForm.appendChild(idInput);

                // 添加提交按钮标识
                const submitInput = document.createElement('input');
                submitInput.type = 'hidden';
                submitInput.name = 'update_payments';
                submitInput.value = '1';
                tempForm.appendChild(submitInput);

                // 添加日期
                const dateInput = document.createElement('input');
                dateInput.type = 'hidden';
                dateInput.name = 'transDate';
                dateInput.value = document.querySelector('input[name="transDate"]').value;
                tempForm.appendChild(dateInput);

                // 复制支付金额
                const payments = document.querySelectorAll(`input[name^="payments[${employeeID}]"]`);
                payments.forEach(payment => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = payment.name;
                    input.value = payment.value;
                    tempForm.appendChild(input);
                });

                document.body.appendChild(tempForm);
                tempForm.submit();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('paymentForm');
        const selectAllCheckbox = document.getElementById('selectAll');
        const memberCheckboxes = document.querySelectorAll('input[name="selected[]"]');
        
        // 表单提交处理
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // 阻止表单直接提交
            
            // 检查是否有选中的会员
            const selectedMembers = document.querySelectorAll('input[name="selected[]"]:checked');
            if (selectedMembers.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Sila pilih sekurang-kurangnya satu anggota.',
                    showConfirmButton: false,
                    timer: 1500,
                    position: 'top-end',
                    toast: true
                });
                return;
            }

            // 显示确认对话框
            Swal.fire({
                title: 'Pengesahan',
                text: 'Adakah anda pasti untuk muat naik rekod pembayaran ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Muat Naik',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // 如果确认，提交表单
                }
            });
        });

        // 监听个别复选框的变化
        memberCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(memberCheckboxes).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
            });
        });
    });

    function calculateTotal(memberId) {
        let total = 0;
        
        // 获取所有付款输入框的值并相加
        const inputs = document.querySelectorAll(`input[name^="payments[${memberId}]"]`);
        inputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        
        // 更新显示，保留两位小数
        document.getElementById(`total_${memberId}`).textContent = total.toFixed(2);
    }

    // 页面加载时计算初始总额
    document.addEventListener('DOMContentLoaded', function() {
        const members = document.querySelectorAll('[id^="total_"]');
        members.forEach(member => {
            const memberId = member.id.replace('total_', '');
            calculateTotal(memberId);
        });
    });

    // 保存有效的输入值到 localStorage
    function saveInputValue(input, isValid) {
        // 只保存有效的值（不小于最小值的输入）
        if (isValid) {
            const memberCard = input.closest('.member-card');
            const employeeID = memberCard.dataset.employeeId;
            const inputName = input.name;
            const inputValue = input.value;
            
            // 获取现有的保存数据或创建新对象
            let savedValues = JSON.parse(localStorage.getItem('paymentValues') || '{}');
            if (!savedValues[employeeID]) {
                savedValues[employeeID] = {};
            }
            savedValues[employeeID][inputName] = inputValue;
            
            // 保存回 localStorage
            localStorage.setItem('paymentValues', JSON.stringify(savedValues));
        }
    }

    // 从 localStorage 加载保存的值
    function loadSavedValues() {
        const savedValues = JSON.parse(localStorage.getItem('paymentValues') || '{}');
        
        document.querySelectorAll('.member-card').forEach(memberCard => {
            const employeeID = memberCard.dataset.employeeId;
            if (savedValues[employeeID]) {
                memberCard.querySelectorAll('.payment-input-field').forEach(input => {
                    const minValue = parseFloat(input.getAttribute('min'));
                    const savedValue = savedValues[employeeID][input.name];
                    
                    // 只加载大于或等于最小值的保存值
                    if (savedValue && parseFloat(savedValue) >= minValue) {
                        input.value = savedValue;
                    }
                });
            }
        });
    }

    // 验证并保存有效值
    function validateAmountInline(input, minAmount) {
        const value = parseFloat(input.value);
        const messageDiv = input.nextElementSibling;
        let isValid = true;
        
        // 允许输入框为空或正在编辑
        if (input.value === '' || input.value === null) {
            messageDiv.style.display = 'none';
            input.classList.remove('is-invalid');
            input.classList.remove('is-valid');
            return; // 直接返回，不做其他处理
        }
        
        if (isNaN(value) || value < minAmount) {
            messageDiv.textContent = `Jumlah minimum adalah RM ${minAmount.toFixed(2)}`;
            messageDiv.style.display = 'block';
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            isValid = false;
        } else {
            messageDiv.style.display = 'none';
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        }
        
        // 只保存有效的值
        if (isValid) {
            saveInputValue(input, true);
        }
        
        // 更新总金额
        updateTotalAmount(input);
    }

    // 页面加载时
    document.addEventListener('DOMContentLoaded', function() {
        // 首先加载保存的值
        loadSavedValues();
        
        // 设置输入监听器
        document.querySelectorAll('.payment-input-field').forEach(input => {
            const minAmount = parseFloat(input.getAttribute('min'));
            
            // 验证初始值
            validateAmountInline(input, minAmount);
            
            // 添加输入事件监听
            input.addEventListener('input', function() {
                validateAmountInline(this, minAmount);
            });
        });

        // 初始计算所有总额
        document.querySelectorAll('.member-card').forEach(card => {
            const firstInput = card.querySelector('.payment-input-field');
            if (firstInput) {
                updateTotalAmount(firstInput);
            }
        });
    });

    // 更新总金额的函数
    function updateTotalAmount(input) {
        const memberCard = input.closest('.member-card');
        let total = 0;

        // 计算所有常规付款
        memberCard.querySelectorAll('.payment-input-field').forEach(inputField => {
            // 检查是否是 Additional Payment
            const isAdditionalPayment = inputField.name.includes('upfront_amount');
            const paymentItem = inputField.closest('.payment-item');

            if (isAdditionalPayment) {
                // 如果是 Additional Payment，检查是否选择了类型
                const typeSelect = paymentItem.querySelector('select');
                if (typeSelect && typeSelect.value) {
                    total += parseFloat(inputField.value) || 0;
                }
            } else {
                // 其他所有常规付款
                total += parseFloat(inputField.value) || 0;
            }
        });

        // 更新显示
        const totalAmountElement = memberCard.querySelector('.total-amount');
        if (totalAmountElement) {
            totalAmountElement.textContent = 'RM ' + total.toFixed(2);
        }
    }

    // 表单提交验证
    document.querySelector('form').addEventListener('submit', function(e) {
        const submitter = e.submitter;
        const isSingleUpload = submitter && submitter.name === 'single_upload';
        let inputsToValidate;
        let isValid = true;

        if (isSingleUpload) {
            // 单个上传验证
            const memberCard = submitter.closest('.member-card');
            inputsToValidate = memberCard.querySelectorAll('input[type="number"]');
        } else {
            // 批量上传验证
            const selectedMembers = document.querySelectorAll('input[name="selected[]"]:checked');
            inputsToValidate = Array.from(selectedMembers).reduce((inputs, member) => {
                const memberCard = member.closest('.member-card');
                return inputs.concat(Array.from(memberCard.querySelectorAll('input[type="number"]')));
            }, []);
        }

        inputsToValidate.forEach(input => {
            const originalAmount = parseFloat(input.dataset.originalAmount);
            const currentValue = parseFloat(input.value) || 0;
            if (currentValue < originalAmount) {
                isValid = false;
                input.classList.add('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Ralat!',
                text: 'Sila pastikan semua jumlah pembayaran tidak kurang daripada nilai minimum.',
                confirmButtonText: 'OK'
            });
            return false;
        }
    });
    </script>
</body>
</html>