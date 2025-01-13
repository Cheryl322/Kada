<?php 

session_start();
include 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: register.php');
    exit;
}

$employeeID = mysqli_real_escape_string($conn, trim($_POST['employeeID']));
$password = trim($_POST['password']);
$adminKey = isset($_POST['adminKey']) ? trim($_POST['adminKey']) : '';

// Define your admin key (store this securely in production!)
$ADMIN_KEY = "KADA2024"; // Change this to your desired admin key

try {
    // Check if employee ID already exists
    $check_sql = "SELECT employeeID FROM tb_employee WHERE employeeID = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $employeeID);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error_message'] = "Employee ID already exists!";
        header('Location: register.php');
        exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Determine role based on admin key
    $role = ($adminKey === $ADMIN_KEY) ? 'admin' : 'user';

    // Begin transaction
    mysqli_begin_transaction($conn);

    // Insert into tb_employee
    $insert_sql = "INSERT INTO tb_employee (employeeID, password, role) VALUES (?, ?, ?)";
    $insert_stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($insert_stmt, "sss", $employeeID, $hashed_password, $role);
    mysqli_stmt_execute($insert_stmt);

    // If registering as admin, also insert into tb_admin
    if ($role === 'admin') {
        $admin_sql = "INSERT INTO tb_admin (employeeID, staffName) VALUES (?, ?)";
        $admin_stmt = mysqli_prepare($conn, $admin_sql);
        $default_name = "Admin " . $employeeID; // You can modify this default name
        mysqli_stmt_bind_param($admin_stmt, "ss", $employeeID, $default_name);
        mysqli_stmt_execute($admin_stmt);
    }

    // Commit transaction
    mysqli_commit($conn);

    // Set success message
    $_SESSION['success_message'] = ($role === 'admin') 
        ? "Pendaftaran admin berjaya! Sila log masuk."
        : "Pendaftaran berjaya! Sila log masuk.";

    // Show success popup using SweetAlert2
    header('Location: login.php');
    exit;

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    $_SESSION['error_message'] = "Registration failed: " . $e->getMessage();
    header('Location: register.php');
    exit;
} finally {
    if (isset($check_stmt)) mysqli_stmt_close($check_stmt);
    if (isset($insert_stmt)) mysqli_stmt_close($insert_stmt);
    if (isset($admin_stmt)) mysqli_stmt_close($admin_stmt);
    mysqli_close($conn);
}

?>