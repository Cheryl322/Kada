<?php 

session_start();
include 'dbconnect.php';

// Retrieve data from form
$employeeID = $_POST['employeeID'];
$password = $_POST['password'];


//$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql_check = "SELECT employeeID FROM tb_employee WHERE employeeID = ?";
$stmt_check = mysqli_prepare($con, $sql_check);

if ($stmt_check) {
    mysqli_stmt_bind_param($stmt_check, "s", $employeeID); 
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);

    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        // 如果 EmployeeID 已存在
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Employee ID already registered. Please try again.'];
        header('Location: register.php');
        exit;
    }
    mysqli_stmt_close($stmt_check);
}

// Prepare SQL statement
$sql = "INSERT INTO tb_employee (employeeID, password) VALUES (?, ?)";
$stmt = mysqli_prepare($con, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $employeeID, $password);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Successfully registered!'];
        header('Location: register.php');
        exit;
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Failed to register! Please try again.'];
        header('Location: register.php');
        exit;
    }
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Error preparing the statement: ' . mysqli_error($con)];
    header('Location: register.php');
    exit;
}

mysqli_close($con);

header('Location: login.php');

?>