<?php
include "dbconnect.php";
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loanId = $_POST['loanId'];
    $status = $_POST['status'];
    $explanation = isset($_POST['explanation']) ? $_POST['explanation'] : null;

    // First get the applicant's email and name
    $memberSql = "SELECT m.email, m.memberName 
                  FROM tb_loanapplication la 
                  JOIN tb_member m ON la.employeeID = m.employeeID 
                  WHERE la.loanApplicationID = ?";
    $memberStmt = mysqli_prepare($conn, $memberSql);
    mysqli_stmt_bind_param($memberStmt, "i", $loanId);
    mysqli_stmt_execute($memberStmt);
    $memberResult = mysqli_stmt_get_result($memberStmt);
    $memberData = mysqli_fetch_assoc($memberResult);

    // Prepare the SQL statement based on whether there's an explanation
    if ($status === 'Ditolak' && $explanation) {
        $sql = "UPDATE tb_loanapplication 
                SET loanStatus = ?, explanation = ? 
                WHERE loanApplicationID = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $status, $explanation, $loanId);
    } else {
        $sql = "UPDATE tb_loanapplication 
                SET loanStatus = ?, explanation = NULL 
                WHERE loanApplicationID = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $status, $loanId);
    }

    if (mysqli_stmt_execute($stmt)) {
        if ($memberData) {
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'koperasikada.site@gmail.com';
                $mail->Password = 'rtmh vdnc mozb lion';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('koperasikada.site@gmail.com', 'KADA Admin');
                $mail->addAddress($memberData['email'], $memberData['memberName']);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Status Permohonan Pembiayaan KADA';
                
                // Add explanation to email if status is 'Ditolak'
                $explanationText = ($status === 'Ditolak' && $explanation) ? 
                    "<br><strong>Penjelasan:</strong> {$explanation}" : '';
                
                $mail->Body = "Salam Sejahtera {$memberData['memberName']},<br><br>
                              Status permohonan pembiayaan anda telah dikemaskini:<br><br>
                              <strong>Status:</strong> {$status}{$explanationText}<br><br>
                              Terima kasih,<br>KADA Admin";

                $mail->send();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                error_log("Email sending failed: {$mail->ErrorInfo}");
                echo json_encode(['success' => true]); // Still return success for the status update
            }
        } else {
            echo json_encode(['success' => true]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>