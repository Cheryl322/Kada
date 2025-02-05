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
                                $sql = "INSERT INTO tb_deduction (employeeID, DeducType_ID, Deduct_Amt, Deduct_date) 
                                        VALUES (?, 6, ?, ?)";
                                $stmt = mysqli_prepare($conn, $sql);
                                mysqli_stmt_bind_param($stmt, "ids", $employeeID, $loanAmount, $transDate);
                                mysqli_stmt_execute($stmt);
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
        foreach ($_POST['selected'] as $employeeID) {
            // 重复上面的代码，处理每个选中的会员
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

// 显示消息（放在HTML部分的开始）
if (isset($_SESSION['success_message'])) {
    echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
    unset($_SESSION['success_message']); // 显示后立即删除消息
}
if (isset($_SESSION['error_message'])) {
    echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
    unset($_SESSION['error_message']); // 显示后立即删除消息
}
?>

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
    </style>
</head>
<body>
    <?php include "headeradmin.php"; ?>
    
    <div class="container">
        <div class="page-header">
            <h2 class="page-title">Upload Payment Record</h2>
        </div>

        <form method="POST" id="paymentForm">
            <div class="top-nav">
                <div class="control-panel-container">
                    <div class="btn-group">
                        <div class="select-all-wrapper">
                            <input type="checkbox" id="selectAll" class="custom-checkbox">
                            <label for="selectAll" class="checkbox-label"></label>
                        </div>
                        <button type="submit" name="update_payments" class="btn btn-primary" id="uploadBtn">
                            Upload Selected
                        </button>
                    </div>
                    <div class="date-picker">
                        <label>Transaction Date:</label>
                        <input type="date" name="transDate" class="form-control" 
                               value="<?php echo $transDate; ?>" required>
                    </div>
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
                                    class="upload-single">
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
        </form>
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

        // 表单提交验证
        function validateForm() {
            let isValid = true;
            const inputs = document.querySelectorAll('.payment-input-field');
            
            inputs.forEach(input => {
                const originalAmount = parseFloat(input.dataset.originalAmount);
                const currentValue = parseFloat(input.value) || 0;
                
                if (currentValue < originalAmount) {
                    isValid = false;
                    input.classList.add('is-invalid');
                }
            });

            if (!isValid) {
                alert('Please ensure all payment amounts are not less than their minimum values.');
                return false;
            }

            return true;
        }

        // 添加到全局作用域以供表单使用
        window.validateForm = validateForm;

        // 添加 Select All 功能
        const selectAllCheckbox = document.getElementById('selectAll');
        const memberCheckboxes = document.querySelectorAll('.member-checkbox');
        
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                memberCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });

            // 当个别复选框更改时，更新全选框状态
            memberCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(memberCheckboxes).every(cb => cb.checked);
                    const someChecked = Array.from(memberCheckboxes).some(cb => cb.checked);
                    
                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = someChecked && !allChecked;
                });
            });
        }

        // 表单提交验证
        const form = document.getElementById('paymentForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                // 检查是否有选中的会员
                const selectedMembers = document.querySelectorAll('input[name="selected[]"]:checked');
                if (selectedMembers.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one member.');
                    return false;
                }

                // 验证所有选中会员的输入值
                let isValid = true;
                selectedMembers.forEach(member => {
                    const memberCard = member.closest('.member-card');
                    const inputs = memberCard.querySelectorAll('input[type="number"]');
                    
                    inputs.forEach(input => {
                        const originalAmount = parseFloat(input.dataset.originalAmount);
                        const currentValue = parseFloat(input.value) || 0;
                        
                        if (currentValue < originalAmount) {
                            isValid = false;
                            input.classList.add('is-invalid');
                        }
                    });
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Please ensure all payment amounts are not less than their minimum values.');
                    return false;
                }

                // 显示加载状态
                const uploadBtn = document.getElementById('uploadBtn');
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
            }, 10000); // 10秒后自动关闭
        });
    });
    </script>
</body>
</html>