<?php
include "dbconnect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId = $_POST['memberId'];
    $status = $_POST['status'];
    $explanation = isset($_POST['explanation']) ? $_POST['explanation'] : null;
    $currentDate = date('Y-m-d H:i:s');
    
    try {
        mysqli_begin_transaction($conn);
        
        // Update registration history
        $update_history_sql = "UPDATE tb_member_registration_history 
                             SET registrationStatus = ?, 
                                 registrationDate = ? 
                             WHERE employeeID = ? AND isActive = TRUE";
        $history_stmt = mysqli_prepare($conn, $update_history_sql);
        mysqli_stmt_bind_param($history_stmt, "sss", $status, $currentDate, $memberId);
        mysqli_stmt_execute($history_stmt);

        // Update member application details
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
        mysqli_stmt_execute($stmt);

        // If status is 'Diluluskan', check if record exists in tb_member_status
        if ($status === 'Diluluskan') {
            $check_sql = "SELECT statusID FROM tb_member_status WHERE employeeID = ?";
            $check_stmt = mysqli_prepare($conn, $check_sql);
            mysqli_stmt_bind_param($check_stmt, "s", $memberId);
            mysqli_stmt_execute($check_stmt);
            $result = mysqli_stmt_get_result($check_stmt);

            if (mysqli_num_rows($result) > 0) {
                // Update existing record
                $update_sql = "UPDATE tb_member_status 
                             SET status = 'Aktif', 
                                 dateUpdated = ? 
                             WHERE employeeID = ?";
                $update_stmt = mysqli_prepare($conn, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "ss", $currentDate, $memberId);
                mysqli_stmt_execute($update_stmt);
            } else {
                // Insert new record
                $insert_sql = "INSERT INTO tb_member_status 
                             (employeeID, status, dateUpdated) 
                             VALUES (?, 'Aktif', ?)";
                $insert_stmt = mysqli_prepare($conn, $insert_sql);
                mysqli_stmt_bind_param($insert_stmt, "ss", $memberId, $currentDate);
                mysqli_stmt_execute($insert_stmt);
            }
        }

        mysqli_commit($conn);

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

    } catch (Exception $e) {
        mysqli_rollback($conn);
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit();
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}