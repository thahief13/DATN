<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require_once __DIR__ . '/../../../PHPMailer/src/Exception.php';
    require_once __DIR__ . '/../../../PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../../../PHPMailer/src/SMTP.php';
    require_once __DIR__ . '/../../../config/mail.php';

    function sendOtpEmail($recipientEmail, $otp) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();                                           
            $mail->Host       = 'smtp.gmail.com';                      
            $mail->SMTPAuth   = true;                                  
            $mail->Username   = 'kinxedo78@gmail.com';                
            $mail->Password   = 'cdxt uyvp glpa kzat';                   
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            
            $mail->Port       = 587;                                   

            $mail->setFrom('kinxedo78@gmail.com', 'Hệ thống Trung Nguyên Coffee');
            $mail->addAddress($recipientEmail);     
            $mail->CharSet = 'UTF-8';               

            $mail->isHTML(true);                                  
            $mail->Subject = 'Mã OTP đặt lại mật khẩu';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ccc; max-width: 600px; margin: auto;'>
                    <h2 style='color: #8b4513;'>Yêu cầu đặt lại mật khẩu</h2>
                    <p>Xin chào,</p>
                    <p>Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản của mình. Vui lòng sử dụng mã xác thực (OTP) dưới đây để tiếp tục:</p>
                    <h1 style='color: #ffb300; text-align: center; border: 2px dashed #ffb300; padding: 10px;'>{$otp}</h1>
                    <p>Mã này sẽ hết hạn sau <strong>5 phút</strong>.</p>
                    <p>Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email này.</p>
                    <p>Trân trọng,<br>Đội ngũ hỗ trợ Trung Nguyên Coffee</p>
                </div>
            ";
            $mail->AltBody = "Mã OTP của bạn là: {$otp}. Mã này sẽ hết hạn sau 5 phút.";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Lỗi gửi email PHPMailer: {$mail->ErrorInfo}");
            return false;
        }
    }
?>