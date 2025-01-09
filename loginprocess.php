<?php
session_start();
include 'dbconnect.php';


// if(isset($_POST['login'])) {
//    $employeeID = mysqli_real_escape_string($conn, $_POST['employeeID']);
//    $password = mysqli_real_escape_string($conn, $_POST['password']);
   
   // Validate input
//    if(empty($email) || empty($password)) {
//        header("Location: login.php?error=Sila isi semua maklumat yang diperlukan");
//        exit();
//    }
    // Check email format
//    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
//        header("Location: login.php?error=Format emel tidak sah");
//        exit();
//    }
   
//    $sql = "SELECT * FROM users WHERE email = ?";
//    $stmt = mysqli_prepare($conn, $sql);
//    mysqli_stmt_bind_param($stmt, "s", $email);
//    mysqli_stmt_execute($stmt);
//    $result = mysqli_stmt_get_result($stmt);
   
//    if(mysqli_num_rows($result) > 0) {
//        $row = mysqli_fetch_assoc($result);
//        if(password_verify($password, $row['password'])) {
//            // Set session variables
//            $_SESSION['user_id'] = $row['id'];
//            $_SESSION['role'] = $row['role'];
//            $_SESSION['email'] = $row['email'];
           
//            // Redirect based on role
//            if($row['role'] == 'admin') {
//                header("Location: admin/dashboard.php");
//            } else {
//                header("Location: member/dashboard.php");
//            }
//            exit();
//        } else {
//            header("Location: login.php?error=Kata laluan tidak sah");
//            exit();
//        }
//    } else {
//        header("Location: login.php?error=Emel tidak dijumpai");
//        exit();
//     }
// } else {
//    header("Location: index.php");
//    exit();
//}

//Retrieve data from form
$employeeID = $_POST['employeeID'];
$password = $_POST['password'];


//SQL Insert operation
$sql="SELECT * FROM tb_employee 
		WHERE employeeID='$employeeID' AND password='$password'";

//Execute SQL
$result=mysqli_query($con,$sql);

//Retrieve data
$row=mysqli_fetch_array($result);

//Count result to check
$count=mysqli_num_rows($result);

//Rule-based AI login
if($count == 1)
{
	//set session
	$_SESSION['employeeID']= session_id();
	$_SESSION['password']= $password;//funame不是database的

	if($row['employeeID'] == '1234')//check user type
	{
		//Lecturer
		header('Location: adminpage.php');

	}
	// if($row['u_utype'] == 2)//check user type
	// {
	// 	//Student
	// 	header('Location: student.php');
	// }
	// if($row['u_utype'] == 3)//check user type
	// {
	// 	//IT
	// 	header('Location: staff.php');

	// }
    else
    {
        header('Location: mainpage.php');
    }
}
else
{
	//Redirect to login error page
	header('Location: login.php');

}


//Close connection
mysqli_close($con);
?>