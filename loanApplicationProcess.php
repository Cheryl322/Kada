<?php

include 'headermember.php';
include "footer.php";
include "dbconnect.php";

$t_amount = $_POST['t_amount'];
$period = $_POST['period'];
$mon_installment = $_POST['mon_installment'];
$name = $_POST['name'];
$no_ic = $_POST['no_ic'];
$sex = $_POST['sex'];
$religion = $_POST['religion'];
$nationality = $_POST['nationality'];
$DOB = $_POST['DOB'];
$add1 = $_POST['add1'];
$postcode1 = $_POST['postcode1'];
$state1 = $_POST['state1'];
$memberID = $_POST['memberID'];
$PFNo = $_POST['PFNo'];
$position = $_POST['position'];
$add2 = $_POST['add2'];
$postcode2 = $_POST['postcode2'];
$state2 = $_POST['state2'];
$office_pNo = $_POST['office_pNo'];
$pNo = $_POST['pNo'];
$bankName = $_POST['bankName'];
$bankAcc = $_POST['bankAcc'];
$guarantor_N = $_POST['guarantor_N'];
$guarantor_ic = $_POST['guarantor_ic'];
$guarantor_pNo = $_POST['guarantor_pNo'];
$PFNo1 = $_POST['PFNo1'];
$guarantorMemberID = $_POST['guarantorMemberID'];
$sign = isset($_FILES['sign']) && $_FILES['sign']['error'] === UPLOAD_ERR_OK ? 
    mysqli_real_escape_string($con, file_get_contents($_FILES['sign']['tmp_name'])) : '';
$guarantor_N2 = $_POST['guarantor_N2'];
$guarantor_ic2 = $_POST['guarantor_ic2'];
$guarantor_pNo2 = $_POST['guarantor_pNo2'];
$PFNo2 = $_POST['PFNo2'];
$guarantorMemberID2 = $_POST['guarantorMemberID2'];
$sign2 = isset($_FILES['sign2']) && $_FILES['sign2']['error'] === UPLOAD_ERR_OK ? 
    mysqli_real_escape_string($con, file_get_contents($_FILES['sign2']['tmp_name'])) : '';
$employer_N = $_POST['employer_N'];
$employer_ic = $_POST['employer_ic'];
$basic_salary = $_POST['basic_salary'];
$net_salary = $_POST['net_salary'];
$basic_s = isset($_FILES['basic_s']) && $_FILES['basic_s']['error'] === UPLOAD_ERR_OK ? 
    mysqli_real_escape_string($con, file_get_contents($_FILES['basic_s']['tmp_name'])) : '';
$net_s = isset($_FILES['net_s']) && $_FILES['net_s']['error'] === UPLOAD_ERR_OK ? 
    mysqli_real_escape_string($con, file_get_contents($_FILES['net_s']['tmp_name'])) : '';
$signature = isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK ? 
    mysqli_real_escape_string($con, file_get_contents($_FILES['signature']['tmp_name'])) : '';

// Escape other string inputs
$t_amount = mysqli_real_escape_string($con, $t_amount);
$period = mysqli_real_escape_string($con, $period);
$name = mysqli_real_escape_string($con, $name);

//SQL Insert operation
$sql = "INSERT INTO tb_loanapplication(t_amount, period, mon_installment, name, no_ic, sex, religion, nationality, DOB, add1, postcode1, state1, memberID, PFNo, position, add2, postcode2, state2, office_pNo, pNo, bankName, bankAcc, guarantor_N, guarantor_ic, guarantor_pNo, PFNo1, guarantorMemberID, sign, guarantor_N2, guarantor_ic2, guarantor_pNo2, PFNo2, guarantorMemberID2, sign2, employer_N, employer_ic, basic_salary, net_salary, basic_s, net_s, signature)
	VALUES ('$t_amount','$period','$mon_installment','$name','$no_ic','$sex','$religion','$nationality','$DOB','$add1','$postcode1','$state1','$memberID','$PFNo','$position','$add2','$postcode2','$state2','$office_pNo','$pNo','$bankName','$bankAcc','$guarantor_N','$guarantor_ic','$guarantor_pNo','$PFNo1','$guarantorMemberID','$sign','$guarantor_N2','$guarantor_ic2','$guarantor_pNo2','$PFNo2','$guarantorMemberID2','$sign2','$employer_N','$employer_ic','$basic_salary','$net_salary','$basic_s','$net_s','$signature')";

// Execute SQL
if(mysqli_query($con, $sql)) {
    // Redirect user to loan application page
    header("Location: permohonanloan.php");
    exit();
} else {
    echo "Error: " . mysqli_error($con);
}

// Close connection
mysqli_close($con);

?>