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
    // 检查数据库连接状态
    echo "<div class='alert alert-info'>Checking database connection...</div>";
    if (mysqli_ping($conn)) {
        echo "<div class='alert alert-success'>Database connection is active</div>";
    } else {
        echo "<div class='alert alert-danger'>Database connection failed</div>";
        die();
    }

    if (isset($_POST['single_upload'])) {
        $employeeID = $_POST['single_upload'];
        $transDate = $_POST['transDate'];
        $payments = $_POST['payments'][$employeeID];

        echo "<div class='alert alert-info'>";
        echo "Starting transaction for Employee ID: " . $employeeID . "<br>";
        echo "Transaction Date: " . $transDate . "<br>";
        echo "</div>";

        // 验证数据
        if (empty($employeeID) || empty($transDate) || empty($payments)) {
            echo "<div class='alert alert-danger'>Missing required data</div>";
            die();
        }

        mysqli_begin_transaction($conn);
        try {
            $successCount = 0; // 追踪成功插入的记录数

            // 处理常规付款
            foreach ($payments as $type => $amount) {
                if ($type !== 'upfront_type' && $type !== 'upfront_amount' && 
                    !is_array($amount) && $amount > 0) {
                    
                    if (isset($payment_type_mapping[$type])) {
                        $deducTypeID = $payment_type_mapping[$type];
                        
                        // 显示将要执行的插入操作
                        echo "<div class='alert alert-info'>";
                        echo "Preparing to insert payment:<br>";
                        echo "Employee ID: $employeeID<br>";
                        echo "Type: $type (ID: $deducTypeID)<br>";
                        echo "Amount: $amount<br>";
                        echo "Date: $transDate<br>";
                        echo "</div>";

                        $sql = "INSERT INTO tb_deduction (employeeID, DeducType_ID, Deduct_Amt, Deduct_date) 
                                VALUES (?, ?, ?, ?)";
                        
                        $stmt = mysqli_prepare($conn, $sql);
                        if (!$stmt) {
                            throw new Exception("Prepare failed: " . mysqli_error($conn));
                        }

                        // 绑定参数并检查是否成功
                        $bindResult = mysqli_stmt_bind_param($stmt, "sids", $employeeID, $deducTypeID, $amount, $transDate);
                        if (!$bindResult) {
                            throw new Exception("Parameter binding failed: " . mysqli_stmt_error($stmt));
                        }

                        // 执行语句并检查是否成功
                        $executeResult = mysqli_stmt_execute($stmt);
                        if (!$executeResult) {
                            throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
                        }

                        // 检查影响的行数
                        $affectedRows = mysqli_stmt_affected_rows($stmt);
                        if ($affectedRows > 0) {
                            echo "<div class='alert alert-success'>Successfully inserted $type payment: $amount</div>";
                            $successCount++;
                        } else {
                            echo "<div class='alert alert-warning'>No rows were inserted for $type payment</div>";
                        }

                        mysqli_stmt_close($stmt);
                    }
                }
            }
            
            // 处理贷款还款
            if (isset($payments['loanRepayment']) && is_array($payments['loanRepayment'])) {
                foreach ($payments['loanRepayment'] as $loanType => $loanAmount) {
                    if ($loanAmount > 0) {
                        echo "<div class='alert alert-info'>";
                        echo "Preparing to insert loan payment:<br>";
                        echo "Employee ID: $employeeID<br>";
                        echo "Loan Type: $loanType<br>";
                        echo "Amount: $loanAmount<br>";
                        echo "Date: $transDate<br>";
                        echo "</div>";

                        $sql = "INSERT INTO tb_deduction (employeeID, DeducType_ID, Deduct_Amt, Deduct_date) 
                                VALUES (?, 6, ?, ?)";
                        
                        $stmt = mysqli_prepare($conn, $sql);
                        if (!$stmt) {
                            throw new Exception("Prepare failed: " . mysqli_error($conn));
                        }

                        $bindResult = mysqli_stmt_bind_param($stmt, "sds", $employeeID, $loanAmount, $transDate);
                        if (!$bindResult) {
                            throw new Exception("Parameter binding failed: " . mysqli_stmt_error($stmt));
                        }

                        $executeResult = mysqli_stmt_execute($stmt);
                        if (!$executeResult) {
                            throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
                        }

                        $affectedRows = mysqli_stmt_affected_rows($stmt);
                        if ($affectedRows > 0) {
                            echo "<div class='alert alert-success'>Successfully inserted loan payment: $loanAmount for $loanType</div>";
                            $successCount++;
                        } else {
                            echo "<div class='alert alert-warning'>No rows were inserted for loan payment</div>";
                        }

                        mysqli_stmt_close($stmt);
                    }
                }
            }
            
            // 只有在实际插入了记录时才提交
            if ($successCount > 0) {
                mysqli_commit($conn);
                echo "<div class='alert alert-success'>Successfully inserted $successCount records for employee ID: $employeeID</div>";
                $_SESSION['success_message'] = "Successfully uploaded $successCount payment records for employee ID: $employeeID";
                // 重定向到同一页面，但使用 GET 请求
                header("Location: " . $_SERVER['PHP_SELF'] . "?success=true");
                exit();
            } else {
                throw new Exception("No records were inserted");
            }
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo "<div class='alert alert-danger'>Error occurred: " . $e->getMessage() . "</div>";
            $_SESSION['error_message'] = "Error uploading payment record: " . $e->getMessage();
            // 重定向到同一页面，但使用 GET 请求
            header("Location: " . $_SERVER['PHP_SELF'] . "?error=true");
            exit();
        }
    }
    
    // 处理批量上传
    if (isset($_POST['update_payments']) && isset($_POST['selected'])) {
        foreach ($_POST['selected'] as $employeeID) {
            // 重复上面的代码，处理每个选中的会员
        }
    }

    // 在页面顶部添加调试信息显示
    echo "<div class='alert alert-info'>";
    echo "POST request detected<br>";
    echo "POST data: <pre>" . print_r($_POST, true) . "</pre>";
    echo "</div>";
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

// 显示消息（放在HTML部分的开始）
if (isset($_SESSION['success_message'])): ?>
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div class="toast show bg-success text-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']); 
                ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Payment Record</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            display: flex;
            align-items: center;
            margin-right: 10px;
        }

        /* 自定义复选框样式 */
        .custom-checkbox {
            display: none;
        }

        .checkbox-label {
            width: 20px;
            height: 20px;
            border: 2px solid var(--primary);
            border-radius: 4px;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
        }

        .checkbox-label:hover {
            background-color: var(--primary-light);
        }

        /* 选中状态样式 */
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
            gap: 1rem;
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

        .main-content {
            padding: 0;
            width: 100%;
            margin-left: 0;
        }

        /* 修改为深绿色 header 样式 */
        .page-header {
            background-color: #00796b; /* 深绿色 */
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            width: 100%;
        }

        .page-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: normal;
        }

        .container {
            padding: 0 20px;
        }

        /* 当 sidebar 打开时 */
        body.sidebar-open .main-content {
            padding-left: 250px;
        }

        /* 当 sidebar 关闭时 */
        body:not(.sidebar-open) .main-content {
            padding-left: 0;
        }

        @media (max-width: 768px) {
            .main-content {
                padding-left: 0 !important;
            }
        }

        .toast-container {
            z-index: 1050;
        }

        .toast {
            opacity: 1 !important;
        }

        .toast.bg-success {
            background-color: #009688 !important;
        }

        .toast .btn-close-white {
            filter: brightness(0) invert(1);
        }
    </style>
</head>
<body>
    <?php include "headeradmin.php"; ?>
    
    <div class="main-content" style="margin-top: 60px;">
        <div class="container">
            <!-- 绿色标题栏 -->
            <div style="background-color: #009688; padding: 20px; margin-bottom: 20px; border-radius: 4px;">
                <h2 style="color: white; margin: 0; font-size: 28px;">Upload Payment Record</h2>
            </div>

            <!-- 显示消息区域 -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']); // 显示后立即删除消息
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- 表单开始 -->
            <form method="POST" id="paymentForm">
                <div class="card">
                    <div class="card-body">
                        <!-- 日期选择器 -->
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <div>
                                <label>Transaction Date:</label>
                                <input type="date" name="transDate" class="form-control" 
                                       value="<?php echo $transDate; ?>" required>
                            </div>
                        </div>

                        <div class="content-area">
                            <?php foreach ($members_data as $member): ?>
                                <div class="member-card">
                                    <div class="member-header">
                                        <div class="member-info">
                                            <input type="checkbox" name="selected[]" 
                                                   value="<?php echo $member['employeeID']; ?>" 
                                                   class="member-checkbox">
                                            <span class="member-id-badge"><?php echo $member['employeeID']; ?></span>
                                            <span class="member-name"><?php echo $member['memberName']; ?></span>
                                        </div>
                                        <button type="submit" 
                                                name="single_upload" 
                                                value="<?php echo $member['employeeID']; ?>" 
                                                class="btn btn-success upload-single">
                                            Upload
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
                                                <input type="number" 
                                                       name="payments[<?php echo $member['employeeID']; ?>][modalShare]" 
                                                       value="<?php echo $member['modalShare']; ?>"
                                                       min="<?php echo $member['modalShare']; ?>"
                                                       step="0.01"
                                                       class="form-control payment-input-field"
                                                       data-original-amount="<?php echo $member['modalShare']; ?>"
                                                       oninput="validateAndUpdateTotal(this)">
                                                <div class="validation-message"></div>
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
                                                <input type="number" 
                                                       name="payments[<?php echo $member['employeeID']; ?>][feeCapital]" 
                                                       value="<?php echo $member['feeCapital']; ?>"
                                                       min="<?php echo $member['feeCapital']; ?>"
                                                       step="0.01"
                                                       class="form-control payment-input-field"
                                                       data-original-amount="<?php echo $member['feeCapital']; ?>"
                                                       oninput="validateAndUpdateTotal(this)">
                                                <div class="validation-message"></div>
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
                                                <input type="number" 
                                                       name="payments[<?php echo $member['employeeID']; ?>][fixedDeposit]" 
                                                       value="<?php echo $member['fixedDeposit']; ?>"
                                                       min="<?php echo $member['fixedDeposit']; ?>"
                                                       step="0.01"
                                                       class="form-control payment-input-field"
                                                       data-original-amount="<?php echo $member['fixedDeposit']; ?>"
                                                       oninput="validateAndUpdateTotal(this)">
                                                <div class="validation-message"></div>
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
                                                <input type="number" 
                                                       name="payments[<?php echo $member['employeeID']; ?>][contribution]" 
                                                       value="<?php echo $member['contribution']; ?>"
                                                       min="<?php echo $member['contribution']; ?>"
                                                       step="0.01"
                                                       class="form-control payment-input-field"
                                                       data-original-amount="<?php echo $member['contribution']; ?>"
                                                       oninput="validateAndUpdateTotal(this)">
                                                <div class="validation-message"></div>
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
                                                <input type="number" 
                                                       name="payments[<?php echo $member['employeeID']; ?>][deposit]" 
                                                       value="<?php echo $member['deposit']; ?>"
                                                       min="<?php echo $member['deposit']; ?>"
                                                       step="0.01"
                                                       class="form-control payment-input-field"
                                                       data-original-amount="<?php echo $member['deposit']; ?>"
                                                       oninput="validateAndUpdateTotal(this)">
                                                <div class="validation-message"></div>
                                            </div>
                                        </div>

                                        <!-- Loan Repayments -->
                                        <?php if (!empty($member['loanRepayments'])): 
                                            foreach ($member['loanRepayments'] as $loan): 
                                                // 获取之前提交的值（如果存在）
                                                $submittedValue = isset($_POST['payments'][$member['employeeID']]['loanRepayment'][$loan['loanType']]) 
                                                    ? $_POST['payments'][$member['employeeID']]['loanRepayment'][$loan['loanType']] 
                                                    : $loan['monthlyInstallments'];
                                        ?>
                                            <div class="payment-item">
                                                <div class="payment-label">
                                                    Bayaran Balik (<?php echo htmlspecialchars($loan['loanType']); ?>)
                                                    <span class="minimum-amount">(Min: RM <?php echo number_format($loan['monthlyInstallments'], 2); ?>)</span>
                                                </div>
                                                <div class="payment-amount">
                                                    RM <?php echo number_format($loan['monthlyInstallments'], 2); ?>
                                                </div>
                                                <div class="payment-input">
                                                    <input type="number" 
                                                           name="payments[<?php echo $member['employeeID']; ?>][loanRepayment][<?php echo $loan['loanType']; ?>]" 
                                                           value="<?php echo $submittedValue; ?>"
                                                           min="<?php echo $loan['monthlyInstallments']; ?>"
                                                           step="0.01"
                                                           class="form-control payment-input-field"
                                                           data-original-amount="<?php echo $loan['monthlyInstallments']; ?>"
                                                           oninput="validateAndUpdateTotal(this)">
                                                    <div class="validation-message"></div>
                                                </div>
                                            </div>
                                        <?php endforeach; endif; ?>

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
                                            <span class="total-label">Total Amount</span>
                                            <span class="total-amount" id="total_<?php echo $member['employeeID']; ?>">
                                                RM <?php 
                                                $total = floatval($member['modalShare']) + 
                                                        floatval($member['feeCapital']) + 
                                                        floatval($member['fixedDeposit']) + 
                                                        floatval($member['contribution']) + 
                                                        floatval($member['deposit']);
                                                
                                                // 添加贷款还款到总额
                                                if (!empty($member['loanRepayments'])) {
                                                    foreach ($member['loanRepayments'] as $loan) {
                                                        $total += floatval($loan['monthlyInstallments']);
                                                    }
                                                }
                                                
                                                // 添加预付款初始值（如果有）
                                                $upfront_value = isset($_POST['payments'][$member['employeeID']]['upfront_amount']) 
                                                    ? floatval($_POST['payments'][$member['employeeID']]['upfront_amount']) 
                                                    : 0;
                                                $total += $upfront_value;
                                                
                                                echo number_format($total, 2);
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 定义验证函数
        window.validateAndUpdateTotal = function(input) {
            const originalAmount = parseFloat(input.dataset.originalAmount);
            const currentValue = parseFloat(input.value) || 0;
            const validationMessage = input.parentElement.querySelector('.validation-message');
            
            if (currentValue < originalAmount) {
                input.classList.add('is-invalid');
                const difference = (originalAmount - currentValue).toFixed(2);
                validationMessage.textContent = `Amount is RM ${difference} below minimum required`;
                validationMessage.style.display = 'block';
                validationMessage.style.color = '#dc3545';
                validationMessage.style.fontSize = '0.875rem';
                validationMessage.style.marginTop = '0.25rem';
            } else {
                input.classList.remove('is-invalid');
                validationMessage.style.display = 'none';
            }
            
            updateTotalAmount(input);
        };

        // 定义更新总额函数
        function updateTotalAmount(input) {
            const memberCard = input.closest('.member-card');
            let total = 0;
            
            // 计算所有常规付款（Modal Share, Fee Capital 等）
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

        // 自动更新总额的事件监听器
        document.querySelectorAll('.payment-input-field').forEach(input => {
            input.addEventListener('input', function() {
                updateTotalAmount(this);
            });
        });

        // Additional Payment 下拉菜单变化时更新总额
        document.querySelectorAll('select[name$="[upfront_type]"]').forEach(select => {
            select.addEventListener('change', function() {
                // 获取相应的金额输入框
                const inputField = this.closest('.payment-item').querySelector('input[type="number"]');
                updateTotalAmount(inputField);
            });
            
            // 当选择类型时，如果金额为0，自动设置为100
            select.addEventListener('change', function() {
                const inputField = this.closest('.payment-item').querySelector('input[type="number"]');
                if (this.value && (!inputField.value || inputField.value === '0')) {
                    inputField.value = '50.00';
                    updateTotalAmount(inputField);
                }
            });
        });

        // Additional Payment 输入框值变化时立即更新总额
        document.querySelectorAll('input[name$="[upfront_amount]"]').forEach(input => {
            input.addEventListener('input', function() {
                updateTotalAmount(this);
            });
        });

        // 页面加载时计算每个会员的初始总额
        document.querySelectorAll('.member-card').forEach(card => {
            const anyInput = card.querySelector('.payment-input-field');
            if (anyInput) {
                updateTotalAmount(anyInput);
            }
        });

        // 修改表单提交验证
        const form = document.getElementById('paymentForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                // 检查是否是单个上传按钮触发的提交
                const submitter = e.submitter;
                const isSingleUpload = submitter && submitter.name === 'single_upload';

                // 如果不是单个上传，才检查是否有选中的会员
                if (!isSingleUpload) {
                    const selectedMembers = document.querySelectorAll('input[name="selected[]"]:checked');
                    if (selectedMembers.length === 0) {
                        e.preventDefault();
                        alert('Please select at least one member.');
                        return false;
                    }
                }

                // 验证所有相关会员的输入值
                let isValid = true;
                let inputsToValidate;

                if (isSingleUpload) {
                    // 如果是单个上传，只验证该会员的输入
                    const memberCard = submitter.closest('.member-card');
                    inputsToValidate = memberCard.querySelectorAll('input[type="number"]');
                } else {
                    // 如果是批量上传，验证所有选中会员的输入
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
                    alert('Please ensure all payment amounts are not less than their minimum values.');
                    return false;
                }

                // 显示加载状态
                const uploadBtn = submitter || document.getElementById('uploadBtn');
                if (uploadBtn) {
                    uploadBtn.disabled = true;
                    uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...';
                }

                return true;
            });
        }

        // 自动隐藏提示消息
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000); // 5秒后自动关闭
        });

        // 添加表单提交事件监听器
        const form = document.getElementById('paymentForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                console.log('Form submitted');
                console.log('Submit button:', e.submitter);
                console.log('Form data:', new FormData(form));
            });
        }

        // 为每个上传按钮添加点击事件监听器
        document.querySelectorAll('.upload-single').forEach(button => {
            button.addEventListener('click', function(e) {
                console.log('Upload button clicked');
                console.log('Employee ID:', this.value);
            });
        });

        // 初始化所有 toast
        var toastElList = [].slice.call(document.querySelectorAll('.toast'));
        var toastList = toastElList.map(function(toastEl) {
            return new bootstrap.Toast(toastEl, {
                autohide: true,
                delay: 3000 // 3秒后自动消失
            });
        });

        // 自动显示 toast
        toastList.forEach(toast => toast.show());
    });
    </script>
</body>
</html>
