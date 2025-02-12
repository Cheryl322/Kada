<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Only include PHPMailer if it hasn't been included yet
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    require_once 'phpmailer/src/Exception.php';
    require_once 'phpmailer/src/PHPMailer.php';
    require_once 'phpmailer/src/SMTP.php';
}

class EmailHelper {
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host       = 'smtp.gmail.com';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = 'koperasikada.site@gmail.com'; // Updated email
        $this->mail->Password   = 'rtmh vdnc mozb lion';    // Your app password
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port       = 465;
        
        // Default sender
        $this->mail->setFrom('koperasikada.site@gmail.com', 'Koperasi KADA Online System'); // Updated sender email


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

    function sendLoanApplicationEmail($to, $name, $loanID, $loanAmount) {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'koperasikada.site@gmail.com';
            $mail->Password = 'your_app_password'; // Make sure to use your actual app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            // Recipients
            $mail->setFrom('koperasikada.site@gmail.com', 'Koperasi KADA');
            $mail->addAddress($to, $name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Permohonan Pembiayaan KADA Berjaya';

            // Email template
            $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background-color: #5CBA9B; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
                    <img src='https://kada.gov.my/wp-content/uploads/2021/12/Logo-KADA.png' alt='KADA Logo' style='max-width: 150px; margin-bottom: 15px;'>
                    <h2 style='margin: 0;'>Pengesahan Permohonan Pembiayaan</h2>
                </div>
                
                <div style='padding: 30px; background-color: #f9f9f9; border-radius: 0 0 10px 10px;'>
                    <p style='color: #333;'>Assalamualaikum dan Salam Sejahtera,</p>
                    <p style='color: #333;'><strong>{$name}</strong>,</p>
                    
                    <p style='color: #333;'>Tahniah! Permohonan pembiayaan anda telah berjaya dihantar. Berikut adalah butiran permohonan:</p>
                    
                    <div style='background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #5CBA9B;'>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 8px 0; color: #666;'>ID Permohonan</td>
                                <td style='padding: 8px 0; color: #333; font-weight: bold;'>{$loanID}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; color: #666;'>Jumlah Pembiayaan</td>
                                <td style='padding: 8px 0; color: #333; font-weight: bold;'>RM " . number_format($loanAmount, 2) . "</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; color: #666;'>Tarikh Permohonan</td>
                                <td style='padding: 8px 0; color: #333; font-weight: bold;'>" . date('d/m/Y') . "</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; color: #666;'>Status</td>
                                <td style='padding: 8px 0; color: #5CBA9B; font-weight: bold;'>Dalam Proses</td>
                            </tr>
                        </table>
                    </div>

                    <p style='color: #333;'>Sila ambil perhatian:</p>
                    <ul style='color: #333;'>
                        <li>Permohonan anda akan diproses dalam tempoh 14 hari bekerja</li>
                        <li>Anda boleh menyemak status permohonan melalui sistem KADA</li>
                        <li>Pihak kami akan menghubungi anda sekiranya dokumen tambahan diperlukan</li>
                    </ul>

                    <div style='background-color: #f0f7f4; padding: 15px; border-radius: 8px; margin-top: 20px;'>
                        <p style='margin: 0; color: #333;'><strong>Sebarang Pertanyaan:</strong></p>
                        <p style='margin: 5px 0; color: #333;'>📞 09-7481101</p>
                        <p style='margin: 5px 0; color: #333;'>✉️ koperasikada.site@gmail.com</p>
                    </div>

                    <p style='color: #333; margin-top: 30px;'>Terima kasih atas kepercayaan anda kepada Koperasi KADA.</p>
                </div>
                
                <div style='text-align: center; padding: 20px; background-color: #f1f1f1; font-size: 12px; color: #666; border-radius: 0 0 10px 10px;'>
                    <p style='margin: 0;'>Ini adalah email automatik. Sila jangan balas email ini.</p>
                    <p style='margin: 5px 0;'>© " . date('Y') . " Koperasi KADA. Hak Cipta Terpelihara.</p>
                </div>
            </div>";

            $mail->Body = $body;
            $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $body));

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: {$mail->ErrorInfo}");
            return false;
        }
    }
} 