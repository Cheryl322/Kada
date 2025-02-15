<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "dbconnect.php";
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure no output before this point
header('Content-Type: application/json');

// Log incoming data
error_log("Received POST data: " . print_r($_POST, true));

try {
    if (!isset($_POST['memberId']) || !isset($_POST['status'])) {
        throw new Exception('Missing required fields');
    }

    $memberId = $_POST['memberId'];
    $status = $_POST['status'];
    $explanation = isset($_POST['explanation']) ? $_POST['explanation'] : null;
    $currentDate = date('Y-m-d');

    // Log processed data
    error_log("Processing - memberId: $memberId, status: $status");

    // Start transaction
    mysqli_begin_transaction($conn);

    // First, get member's email and name
    $memberSql = "SELECT email, memberName FROM tb_member WHERE employeeID = ?";
    $memberStmt = mysqli_prepare($conn, $memberSql);
    
    if (!$memberStmt) {
        throw new Exception("Failed to prepare member query: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($memberStmt, "i", $memberId);
    mysqli_stmt_execute($memberStmt);
    mysqli_stmt_store_result($memberStmt);
    
    if (mysqli_stmt_num_rows($memberStmt) === 0) {
        throw new Exception("Member not found");
    }
    
    mysqli_stmt_bind_result($memberStmt, $email, $memberName);
    mysqli_stmt_fetch($memberStmt);
    mysqli_stmt_close($memberStmt);

    // Update status
    $sql = "UPDATE tb_memberregistration_memberapplicationdetails 
            SET regisStatus = ?, 
                regisDate = ?, 
                explanation = ? 
            WHERE memberRegistrationID = ?";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "sssi", $status, $currentDate, $explanation, $memberId);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
    }

    // If no rows were affected, insert new record
    if (mysqli_stmt_affected_rows($stmt) === 0) {
        $insertSql = "INSERT INTO tb_memberregistration_memberapplicationdetails 
                      (memberRegistrationID, regisStatus, regisDate, explanation) 
                      VALUES (?, ?, ?, ?)";
        
        $insertStmt = mysqli_prepare($conn, $insertSql);
        if (!$insertStmt) {
            throw new Exception("Insert prepare failed: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($insertStmt, "isss", $memberId, $status, $currentDate, $explanation);

        if (!mysqli_stmt_execute($insertStmt)) {
            throw new Exception("Insert execute failed: " . mysqli_stmt_error($insertStmt));
        }
        
        mysqli_stmt_close($insertStmt);
    }

    // Add member status update when status is 'Diluluskan'
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

    // Send email if member data exists
    if ($email) {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'koperasikada.site@gmail.com'; // Your Gmail address
            $mail->Password = 'rtmh vdnc mozb lion'; // Your Gmail app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('koperasikada.site@gmail.com', 'KADA Admin');
            $mail->addAddress($email, $memberName);

            // Content
            $mail->isHTML(true);
            
            switch($status) {
                case 'Diluluskan':
                    $mail->Subject = "KADA: Tahniah! Permohonan Keahlian Anda Telah Diluluskan";
                    $mail->Body = "
                        <html>
                        <body style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                                <div style='text-align: center; margin-bottom: 30px;'>
                                    <h1 style='color: #28a745;'>Tahniah {$memberName}!</h1>
                                </div>
                                
                                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;'>
                                    <p style='margin-bottom: 15px;'><strong>Status Permohonan:</strong> Diluluskan ✅</p>
                                    <p style='margin-bottom: 15px;'><strong>Tarikh Kelulusan:</strong> {$currentDate}</p>
                                </div>

                                <p>Kami dengan sukacitanya ingin memaklumkan bahawa permohonan keahlian KADA anda telah diluluskan.</p>
                                
                                <div style='margin: 25px 0;'>
                                    <p><strong>Langkah Seterusnya:</strong></p>
                                    <ol style='margin-left: 20px;'>
                                        <li>Sila log masuk ke akaun KADA anda</li>
                                        <li>Lengkapkan profil keahlian anda</li>
                                        <li>Mula nikmati faedah keahlian KADA</li>
                                    </ol>
                                </div>

                                <p>Sekiranya anda mempunyai sebarang pertanyaan, sila hubungi kami di:</p>
                                <ul style='list-style: none; padding-left: 0;'>
                                    <li>📧 Email: koperasikada.site@gmail.com</li>
                                    <li>📞 Tel: +09-7447088</li>
                                </ul>

                                <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;'>
                                    <p>Salam hormat,</p>
                                    <p><strong>Admin KADA</strong></p>
                                </div>
                            </div>
                        </body>
                        </html>";
                    break;

                case 'Ditolak':
                    $mail->Subject = "KADA: Kemaskini Status Permohonan Keahlian";
                    $mail->Body = "
                        <html>
                        <body style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                                <div style='margin-bottom: 30px;'>
                                    <h2>Kepada {$memberName},</h2>
                                </div>
                                
                                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;'>
                                    <p style='margin-bottom: 15px;'><strong>Status Permohonan:</strong> Ditolak</p>
                                    <p style='margin-bottom: 15px;'><strong>Tarikh:</strong> {$currentDate}</p>
                                    " . ($explanation ? "<p style='margin-bottom: 15px;'><strong>Penjelasan:</strong> {$explanation}</p>" : "") . "
                                </div>

                                <p>Kami memohon maaf untuk memaklumkan bahawa permohonan keahlian KADA anda tidak dapat diluluskan pada masa ini.</p>
                                
                                <div style='margin: 25px 0;'>
                                    <p>Anda boleh:</p>
                                    <ul style='margin-left: 20px;'>
                                        <li>Memohon semula selepas 3 bulan</li>
                                        <li>Hubungi kami untuk maklumat lanjut</li>
                                    </ul>
                                </div>

                                <p>Untuk sebarang pertanyaan, sila hubungi kami di:</p>
                                <ul style='list-style: none; padding-left: 0;'>
                                    <li>📧 Email: koperasikada.site@gmail.com</li>
                                    <li>📞 Tel: +09-7447088</li>
                                </ul>

                                <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;'>
                                    <p>Salam hormat,</p>
                                    <p><strong>Admin KADA</strong></p>
                                </div>
                            </div>
                        </body>
                        </html>";
                    break;

                case 'Belum Selesai':
                    $mail->Subject = "KADA: Kemaskini Status Permohonan Keahlian";
                    $mail->Body = "
                        <html>
                        <body style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                                <div style='margin-bottom: 30px;'>
                                    <h2>Kepada {$memberName},</h2>
                                </div>
                                
                                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;'>
                                    <p style='margin-bottom: 15px;'><strong>Status Permohonan:</strong> Dalam Proses</p>
                                    <p style='margin-bottom: 15px;'><strong>Tarikh Kemaskini:</strong> {$currentDate}</p>
                                </div>

                                <p>Permohonan keahlian KADA anda sedang dalam proses semakan.</p>
                                
                                <div style='margin: 25px 0;'>
                                    <p><strong>Maklumat Penting:</strong></p>
                                    <ul style='margin-left: 20px;'>
                                        <li>Proses semakan mengambil masa 3-5 hari bekerja</li>
                                        <li>Anda akan dimaklumkan melalui email sebaik sahaja keputusan dibuat</li>
                                        <li>Sila pastikan maklumat perhubungan anda adalah terkini</li>
                                    </ul>
                                </div>

                                <p>Untuk sebarang pertanyaan, sila hubungi kami di:</p>
                                <ul style='list-style: none; padding-left: 0;'>
                                   <li>📧 Email: koperasikada.site@gmail.com</li>
                                    <li>📞 Tel: +09-7447088</li>
                                </ul>

                                <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;'>
                                    <p>Salam hormat,</p>
                                    <p><strong>Admin KADA</strong></p>
                                </div>
                            </div>
                        </body>
                        </html>";
                    break;
            }

            $mail->AltBody = strip_tags($mail->Body); // Plain text version of email
            $mail->send();
            error_log("Email sent successfully to: " . $email);
        } catch (Exception $e) {
            error_log("Failed to send email: " . $mail->ErrorInfo);
        }
    }

    mysqli_stmt_close($stmt);
    mysqli_commit($conn);

    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully',
        'emailSent' => isset($mail) && !$mail->ErrorInfo
    ]);

} catch (Exception $e) {
    error_log("Error in update_status.php: " . $e->getMessage());
    
    if (isset($conn)) {
        mysqli_rollback($conn);
    }
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
// Make sure there is no whitespace, newline, or HTML after this closing PHP tag
?>