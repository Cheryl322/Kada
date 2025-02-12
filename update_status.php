<?php
include "dbconnect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId = $_POST['memberId'];
    $status = $_POST['status'];
    $explanation = isset($_POST['explanation']) ? $_POST['explanation'] : null;
    $currentDate = date('Y-m-d H:i:s');
    
    // First, check if there's an existing record
    $checkSql = "SELECT memberRegistrationID FROM tb_memberregistration_memberapplicationdetails 
                 WHERE memberRegistrationID = ?";
    
    $checkStmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($checkStmt, "i", $memberId);
    mysqli_stmt_execute($checkStmt);
    $result = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Update existing record
        if ($status === 'Ditolak' && $explanation) {
            $sql = "UPDATE tb_memberregistration_memberapplicationdetails 
                    SET regisStatus = ?, regisDate = ?, explanation = ? 
                    WHERE memberRegistrationID = ?";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssi", $status, $currentDate, $explanation, $memberId);
        } else {
            $sql = "UPDATE tb_memberregistration_memberapplicationdetails 
                    SET regisStatus = ?, regisDate = ?, explanation = NULL 
                    WHERE memberRegistrationID = ?";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssi", $status, $currentDate, $memberId);
        }
    } else {
        // Insert new record
        if ($status === 'Ditolak' && $explanation) {
            $sql = "INSERT INTO tb_memberregistration_memberapplicationdetails 
                    (memberRegistrationID, regisDate, regisStatus, explanation) 
                    VALUES (?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "isss", $memberId, $currentDate, $status, $explanation);
        } else {
            $sql = "INSERT INTO tb_memberregistration_memberapplicationdetails 
                    (memberRegistrationID, regisDate, regisStatus) 
                    VALUES (?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iss", $memberId, $currentDate, $status);
        }
    }
    
    if (mysqli_stmt_execute($stmt)) {
        // Get member's email and name
        $memberSql = "SELECT memberName, email FROM tb_member WHERE employeeID = ?";
        $memberStmt = mysqli_prepare($conn, $memberSql);
        mysqli_stmt_bind_param($memberStmt, "i", $memberId);
        mysqli_stmt_execute($memberStmt);
        $memberResult = mysqli_stmt_get_result($memberStmt);
        $memberData = mysqli_fetch_assoc($memberResult);

        if ($memberData) {
            // Configure PHPMailer
            require 'phpmailer/src/Exception.php';
            require 'phpmailer/src/PHPMailer.php';
            require 'phpmailer/src/SMTP.php';

            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'koperasikada.site@gmail.com';
                $mail->Password = 'rtmh vdnc mozb lion';    // Updated with App Password
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('koperasikada.site@gmail.com', 'KADA Admin');
                $mail->addAddress($memberData['email'], $memberData['memberName']);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Status Permohonan Ahli KADA';
                
                // Add explanation to email if status is 'Ditolak'
                $explanationText = ($status === 'Ditolak' && $explanation) ? 
                    "<br><strong>Penjelasan:</strong> {$explanation}" : '';
                
                $mail->Body = "Salam Sejahtera {$memberData['memberName']},<br><br>
                              Status permohonan ahli anda telah dikemaskini:<br><br>
                              <strong>Status:</strong> {$status}{$explanationText}<br><br>
                              Terima kasih,<br>KADA Admin";

                $mail->send();
                
                // Return success response with updated status and date
                echo json_encode([
                    'success' => true,
                    'status' => $status,
                    'date' => date('d/m/Y', strtotime($currentDate))
                ]);
            } catch (Exception $e) {
                // Log the error but don't expose it to the user
                error_log("Email sending failed: {$mail->ErrorInfo}");
                
                // Still return success for the status update
                echo json_encode([
                    'success' => true,
                    'status' => $status,
                    'date' => date('d/m/Y', strtotime($currentDate))
                ]);
            }
        } else {
            // Member not found but status was updated
            echo json_encode([
                'success' => true,
                'status' => $status,
                'date' => date('d/m/Y', strtotime($currentDate))
            ]);
        }
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => mysqli_error($conn)
        ]);
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}