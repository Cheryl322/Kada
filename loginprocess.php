<!-- <?php
session_start();
include "dbconnect.php";

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: login.php');
    exit;
}

// Check if form fields are set
if (!isset($_POST['employeeID']) || !isset($_POST['password'])) {
    $_SESSION['error_message'] = "Sila isi semua maklumat yang diperlukan.";
    header('Location: login.php');
    exit;
}

$employeeID = mysqli_real_escape_string($conn, trim($_POST['employeeID']));
$password = trim($_POST['password']);

try {
    // Check if connection is successful
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // SQL query to select user
    $sql = "SELECT * FROM tb_employee WHERE employeeID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "s", $employeeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Verify the password
        if (password_verify($password, $row['password'])) {
            // Set session
            $_SESSION['employeeID'] = $row['employeeID'];
            
            // Check user type
            if ($row['employeeID'] == '1234') {
                // Admin
                header('Location: adminpage.php');
            } else {
                // Regular user
                header('Location: mainpage.php');
            }
            exit();
        } else {
            $_SESSION['error_message'] = "Kata laluan salah.";
            header('Location: login.php');
            exit();
        }
    } else {
        $_SESSION['error_message'] = "ID pekerja tidak wujud.";
        header('Location: login.php');
        exit();
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = "Login failed: " . $e->getMessage();
    header('Location: login.php');
    exit();
} finally {
    if (isset($stmt)) mysqli_stmt_close($stmt);
    if (isset($conn)) mysqli_close($conn);
}
?>