<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

class EmailHelper {
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host       = 'smtp.gmail.com'; // Replace with your SMTP host
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = 'lauyeewen@graduate.utm.my'; // Replace with your email
        $this->mail->Password   = 'tjkf rzqm rbar rzee';    // Replace with your password
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port       = 465;
        
        // Default sender
        $this->mail->setFrom('lauyeewen@graduate.utm.my', 'Koperasi KADA Online System');


    }
    
    /**
     * Send registration confirmation email
     * 
     * @param string $to Recipient email address
     * @param array $data Array containing registration details
     * @return bool True if email sent successfully, false otherwise
     * @throws Exception If email sending fails
     */
    public function sendRegistrationEmail($to, $data) {
        try {
            $this->mail->addAddress($to);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Pengesahan Pendaftaran KADA Ahli';
            
            // Create email body with all fees information
            $body = "
                <h2>Terima kasih kerana mendaftar dengan KADA Ahli</h2>
                <p>Berikut adalah ringkasan yuran dan sumbangan anda:</p>
                <table style='border-collapse: collapse; width: 100%;'>
                    <tr>
                        <td style='padding: 8px; border: 1px solid #ddd;'>Fee Masuk</td>
                        <td style='padding: 8px; border: 1px solid #ddd;'>RM {$data['fee_masuk']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px; border: 1px solid #ddd;'>Modal Syer</td>
                        <td style='padding: 8px; border: 1px solid #ddd;'>RM {$data['modal_syer']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px; border: 1px solid #ddd;'>Modal Yuran</td>
                        <td style='padding: 8px; border: 1px solid #ddd;'>RM {$data['modal_yuran']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px; border: 1px solid #ddd;'>Wang Deposit</td>
                        <td style='padding: 8px; border: 1px solid #ddd;'>RM {$data['wang_deposit']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px; border: 1px solid #ddd;'>Sumbangan Tabung</td>
                        <td style='padding: 8px; border: 1px solid #ddd;'>RM {$data['sumbangan_tabung']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px; border: 1px solid #ddd;'>Simpanan Tetap</td>
                        <td style='padding: 8px; border: 1px solid #ddd;'>RM {$data['simpanan_tetap']}</td>
                    </tr>
                </table>
                <p>Sila simpan email ini untuk rujukan anda.</p>
                <p>Terima kasih.</p>
            ";
            
            $this->mail->Body = $body;
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get registration email template
     * 
     * @param array $data Array containing registration details
     * @return string HTML email content
     */
    private function getRegistrationEmailTemplate($data) {
        return "
        <html>
        <head>
            <title>Pendaftaran Ahli Berjaya</title>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { padding: 20px; }
                .header { color: #2c3e50; }
                .details { margin: 20px 0; }
                .footer { margin-top: 20px; color: #7f8c8d; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2 class='header'>Terima kasih atas pendaftaran anda!</h2>
                <p>Salam sejahtera,</p>
                <div class='details'>
                    <p>Pendaftaran anda sebagai ahli telah berjaya direkodkan. Berikut adalah ringkasan bayaran anda:</p>
                    <ul>
                        <li>Fee Masuk: RM{$data['fee_masuk']}</li>
                        <li>Modal Syer: RM{$data['modal_syer']}</li>
                        <li>Modal Yuran: RM{$data['modal_yuran']}</li>
                    </ul>
                </div>
                <p>Sila tunggu untuk proses pengesahan dari pihak pentadbir.</p>
                <div class='footer'>
                    <p>Terima kasih.</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Send a generic email
     * 
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @return bool True if email sent successfully, false otherwise
     * @throws Exception If email sending fails
     */
    public function sendEmail($to, $subject, $body) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($to);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Failed to send email: " . $e->getMessage());
            throw new Exception("Gagal menghantar email: " . $e->getMessage());
        }
    }

    public function sendPasswordResetEmail($to, $body) {
        try {
            // Use the exact path to the image
            $logoPath = 'C:/xampp/htdocs/Kada/img/kadalogo.jpg';
            // For debugging
            error_log("Using logo path: " . $logoPath);
            
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->addAddress($to);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Reset Kata Laluan KADA Ahli';
            
            // Add the logo
            if (file_exists($logoPath)) {
                $this->addEmbeddedImage($logoPath, 'kadalogo', 'KADA Logo');
                error_log("Logo successfully embedded");
            } else {
                error_log("Logo file not found at: " . $logoPath);
            }
            
            $this->mail->Body = $body;
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add an embedded image to the email
     * 
     * @param string $path Path to the image file
     * @param string $cid Content ID for the image
     * @param string $name Name of the image
     * @return bool True if image was added successfully
     * @throws Exception If image cannot be added
     */
    public function addEmbeddedImage($path, $cid, $name = '') {
        try {
            return $this->mail->addEmbeddedImage($path, $cid, $name);
        } catch (Exception $e) {
            error_log("Failed to add embedded image: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Clear all attachments and embedded images
     */
    public function clearAttachments() {
        $this->mail->clearAttachments();
    }
} 