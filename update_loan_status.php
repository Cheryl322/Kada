<?php
include "dbconnect.php";
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $loanId = $_POST['loanId'];
        $status = $_POST['status'];
        $explanation = isset($_POST['explanation']) ? $_POST['explanation'] : null;

        // Update status first
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
            // Get member data for email
            $memberSql = "SELECT m.email, m.memberName 
                         FROM tb_loanapplication la 
                         JOIN tb_member m ON la.employeeID = m.employeeID 
                         WHERE la.loanApplicationID = ?";
            $memberStmt = mysqli_prepare($conn, $memberSql);
            mysqli_stmt_bind_param($memberStmt, "i", $loanId);
            mysqli_stmt_execute($memberStmt);
            $memberResult = mysqli_stmt_get_result($memberStmt);
            $memberData = mysqli_fetch_assoc($memberResult);

            // Try to send email but don't fail if it doesn't work
            if ($memberData && $memberData['email']) {
                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'koperasikada.site@gmail.com';
                    $mail->Password = 'rtmh vdnc mozb lion';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->setFrom('koperasikada.site@gmail.com', 'KADA Admin');
                    $mail->addAddress($memberData['email'], $memberData['memberName']);
                    $mail->isHTML(true);
                    $mail->CharSet = 'UTF-8';
                    
                    // Set email content based on status
                    $mail->Subject = 'Status Permohonan Pembiayaan KADA';
                    $mail->Body = getEmailBody($status, $memberData, $explanation);
                    
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Email sending failed: " . $e->getMessage());
                    // Don't throw the error, just log it
                }
            }
            
            echo json_encode(['status' => 'success']);
        } else {
            throw new Exception(mysqli_error($conn));
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    } finally {
        if (isset($stmt)) mysqli_stmt_close($stmt);
        if (isset($memberStmt)) mysqli_stmt_close($memberStmt);
        mysqli_close($conn);
    }
}

function getEmailBody($status, $memberData, $explanation = null) {
    if ($status === 'Diluluskan') {
        return "
            <div style='font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 5px;'>
                <div style='text-align: center; margin-bottom: 20px;'>
                    <h1 style='color: #28a745;'> Tahniah! </h1>
                </div>
                
                <h2 style='color: #333;'>Kepada {$memberData['memberName']},</h2>
                
                <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <p style='font-size: 16px; line-height: 1.5;'>
                        Dengan sukacitanya kami maklumkan bahawa permohonan pembiayaan anda telah 
                        <strong style='color: #28a745;'>DILULUSKAN</strong>.
                    </p>
                </div>

                <div style='margin: 20px 0;'>
                    <h3 style='color: #333;'>Langkah Seterusnya:</h3>
                    <ol style='line-height: 1.6;'>
                        <li>Sila log masuk ke Sistem Koperasi KADA untuk melihat butiran pembiayaan anda</li>
                        <li>Anda boleh menyemak penyata kewangan di bahagian 'Penyata Kewangan'</li>
                        <li>Segala maklumat pembayaran dan jadual akan dipaparkan dalam sistem</li>
                    </ol>
                </div>

                <div style='background-color: #e9ecef; padding: 15px; border-radius: 5px; margin-top: 20px;'>
                    <p style='margin: 0;'><strong>Hubungi Kami:</strong></p>
                    <p style='margin: 5px 0;'>📞 Tel: +09-7447088</p>
                    <p style='margin: 5px 0;'>📍 Alamat: Koperasi KADA Sdn Bhd</p>
                </div>

                <hr style='border: 0; border-top: 1px solid #ddd; margin: 20px 0;'>
                
                <p style='color: #666; font-size: 12px; text-align: center;'>
                    Email ini dijana secara automatik. Sila jangan balas.
                </p>
            </div>";
    } elseif ($status === 'Ditolak') {
        return "
            <div style='font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 5px;'>
                <h2 style='color: #333;'>Kepada {$memberData['memberName']},</h2>
                
                <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <p style='font-size: 16px; line-height: 1.5;'>
                        Dukacita dimaklumkan bahawa permohonan pembiayaan anda telah 
                        <strong style='color: #dc3545;'>TIDAK DILULUSKAN</strong>.
                    </p>
                </div>

                <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <p style='margin: 0;'><strong>Sebab:</strong></p>
                    <p style='margin: 10px 0; padding-left: 15px; border-left: 3px solid #dc3545;'>
                        $explanation
                    </p>
                </div>


                <div style='background-color: #e9ecef; padding: 15px; border-radius: 5px; margin-top: 20px;'>
                    <p style='margin: 0;'><strong>Untuk sebarang pertanyaan:</strong></p>
                    <p style='margin: 5px 0;'>📞 Tel: +09-7447088</p>
                    <p style='margin: 5px 0;'>📍 Alamat: Koperasi KADA Sdn Bhd</p>
                </div>

                <hr style='border: 0; border-top: 1px solid #ddd; margin: 20px 0;'>
                
                <p style='color: #666; font-size: 12px; text-align: center;'>
                    Email ini dijana secara automatik. Sila jangan balas.
                </p>
            </div>";
    } else {
        return "
            <div style='font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 5px;'>
                <h2 style='color: #333;'>Kepada {$memberData['memberName']},</h2>
                
                <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <p style='font-size: 16px; line-height: 1.5;'>
                        Status permohonan pembiayaan anda telah dikemaskini kepada 
                        <strong style='color: #17a2b8;'>$status</strong>.
                    </p>
                </div>


                <div style='background-color: #e9ecef; padding: 15px; border-radius: 5px; margin-top: 20px;'>
                    <p style='margin: 0;'><strong>Untuk sebarang pertanyaan:</strong></p>
                    <p style='margin: 5px 0;'>📞 Tel: +09-7447088</p>
                    <p style='margin: 5px 0;'>📍 Alamat: Koperasi KADA Sdn Bhd</p>
                </div>

                <hr style='border: 0; border-top: 1px solid #ddd; margin: 20px 0;'>
                
                <p style='color: #666; font-size: 12px; text-align: center;'>
                    Email ini dijana secara automatik. Sila jangan balas.
                </p>
            </div>";
    }
}
?>