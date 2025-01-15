<?php
// Database connection
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "kada_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get form data
    $reportType = $_POST['reportType'];
    $fromDate = $_POST['fromDate'];
    $toDate = $_POST['toDate'];
    $selectedMembers = $_POST['selected_members'];
    $reportFormat = $_POST['reportFormat'];

    // Store report details in database
    $stmt = $conn->prepare("INSERT INTO reports (report_type, from_date, to_date, report_format, created_at) 
                           VALUES (:type, :from, :to, :format, NOW())");
    
    $stmt->execute([
        ':type' => $reportType,
        ':from' => $fromDate,
        ':to' => $toDate,
        ':format' => $reportFormat
    ]);
    
    $reportId = $conn->lastInsertId();

    // Store selected members for this report
    $stmt = $conn->prepare("INSERT INTO report_members (report_id, member_id) VALUES (:report_id, :member_id)");
    foreach ($selectedMembers as $memberId) {
        $stmt->execute([
            ':report_id' => $reportId,
            ':member_id' => $memberId
        ]);
    }

    // Redirect to view report page
    header("Location: adminviewreport.php?report_id=" . $reportId);
    exit();

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>