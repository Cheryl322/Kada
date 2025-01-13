<?php 

session_start();
include 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: register.php');
    exit;
}

// Check if form fields are set
if (!isset($_POST['employeeID']) || !isset($_POST['password'])) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Please fill in all required fields.'];
    header('Location: register.php');
    exit;
}

// Retrieve and sanitize data from form
$employeeID = mysqli_real_escape_string($conn, trim($_POST['employeeID']));
$password = trim($_POST['password']);

// Validate input
if (empty($employeeID) || empty($password)) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Employee ID and password cannot be empty.'];
    header('Location: register.php');
    exit;
}

try {
    // Check if connection is successful
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Check if employee ID already exists
    $sql_check = "SELECT employeeID FROM tb_employee WHERE employeeID = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    
    if (!$stmt_check) {
        throw new Exception("Prepare statement failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt_check, "s", $employeeID);
    mysqli_stmt_execute($stmt_check);
    $result = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Employee ID already registered. Please try again.'];
        header('Location: register.php');
        exit;
    }
    mysqli_stmt_close($stmt_check);

    // Hash password before storing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new employee
    $sql_insert = "INSERT INTO tb_employee (employeeID, password) VALUES (?, ?)";
    $stmt_insert = mysqli_prepare($conn, $sql_insert);
    
    if (!$stmt_insert) {
        throw new Exception("Prepare statement failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt_insert, "ss", $employeeID, $hashed_password);
    
    if (mysqli_stmt_execute($stmt_insert)) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Registration successful! Please login.'];
        header('Location: login.php');
        exit;
    } else {
        throw new Exception("Error executing statement: " . mysqli_stmt_error($stmt_insert));
    }

} catch (Exception $e) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Registration failed: ' . $e->getMessage()];
    header('Location: register.php');
    exit;
} finally {
    if (isset($stmt_check)) mysqli_stmt_close($stmt_check);
    if (isset($stmt_insert)) mysqli_stmt_close($stmt_insert);
    if (isset($conn)) mysqli_close($conn);
}

?>