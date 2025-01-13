<!-- <?php
session_start();
include "dbconnect.php";

$employeeID = $_POST['employeeID'];
$password = $_POST['password'];

// 添加调试信息
error_reporting(E_ALL);
ini_set('display_errors', 1);

// SQL query to select user
$sql = "SELECT * FROM tb_employee 
        WHERE employeeID = $employeeID AND password = $password";

//Retrieve data
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

	if($row['tb_utype'] == 1)//check user type
	{
		//Admin
		header('Location: admin.php');

	}
	if($row['u_utype'] == 2)//check user type
	{
		//Student
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


?> -->

<?php
session_start();
include "dbconnect.php";

$employeeID = $_POST['employeeID'];
$password = $_POST['password'];

// SQL query 需要修改为使用预处理语句
$stmt = $con->prepare("SELECT * FROM tb_employee WHERE employeeID = ? AND password = ?");
$stmt->bind_param("ss", $employeeID, $password);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$count = $result->num_rows;

if($count == 1) {
    // 设置 session
    $_SESSION['employeeID'] = $employeeID;  // 修改这里，不要用 session_id()
    $_SESSION['is_logged_in'] = true;       // 添加登录状态标记
    $_SESSION['user_role'] = $row['tb_utype']; // 保存用户类型

    // 根据用户类型重定向
    if($row['tb_utype'] == 1) {  // 管理员
        header('Location: admin.php');
    } else if($row['tb_utype'] == 2) {  // 普通用户
        header('Location: mainpage.php');
    }
    exit();
} else {
    header('Location: login.php?error=Invalid credentials');
    exit();
}

mysqli_close($con);
?>