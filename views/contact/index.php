
<?php
if (session_status() == PHP_SESSION_NONE) session_start();

require_once '../../env.php'; 
global $hostname, $username, $password, $dbname, $port;
$conn = new mysqli($hostname, $username, $password, $dbname, $port);
$conn->set_charset("utf8mb4");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

$success_msg = '';
$error_msg = '';

// Xử lý khi người dùng bấm nút Gửi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $storeId = (int)($_POST['store_id'] ?? 0); 

    if (!empty($name) && !empty($email) && !empty($message)) {
        
       
        $storeNameDisplay = 'Ban quản lý tổng';
        if ($storeId > 0) {
            $stmtStore = $conn->prepare("SELECT StoreName FROM store WHERE Id = ?");
            if ($stmtStore) {
                $stmtStore->bind_param("i", $storeId);
                $stmtStore->execute();
                $resStore = $stmtStore->get_result();
                if ($rowStore = $resStore->fetch_assoc()) {
                    $storeNameDisplay = $rowStore['StoreName'];
                }
                $stmtStore->close();
            }
        }
     

        // Lưu tin nhắn vào bảng contacts
        $sql = "INSERT INTO contacts (Name, Email, Message, StoreId, CreatedAt) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sssi", $name, $email, $message, $storeId);
            if ($stmt->execute()) {
                
             
                $mail = new PHPMailer(true);
                try {
                    // Cấu hình Server SMTP Gmail
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    
                    // Thông tin tài khoản gửi
                    $mail->Username   = 'kinxedo78@gmail.com';  
                    $mail->Password   = '';      
                    
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';

                    // Cấu hình người nhận và người gửi
                    $mail->setFrom('thanh.nn.64cntt@ntu.edu.vn', 'Hệ thống Trung Nguyên Legend');
                    $mail->addAddress('kinxedo7@gmail.com'); 
                    $mail->addReplyTo($email, $name); 

                    $mail->isHTML(true);
                    $mail->Subject = '[Trung Nguyên Legend] Có liên hệ mới từ khách hàng: ' . $name;
                    
                    
                    $mailBody = "
                        <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                            <h3 style='color: #007bff;'>Thông tin liên hệ mới từ hệ thống website</h3>
                            <p><strong>Họ và tên:</strong> {$name}</p>
                            <p><strong>Email khách hàng:</strong> {$email}</p>
                            <p><strong>Chi nhánh liên hệ:</strong> {$storeNameDisplay}</p>
                            <p><strong>Nội dung tin nhắn:</strong></p>
                            <p style='background: #f4f4f4; padding: 15px; border-left: 4px solid #007bff;'>".nl2br($message)."</p>
                            <hr>
                            <p><small>Tin nhắn này được gửi tự động từ form liên hệ của website Trung Nguyên Legend.</small></p>
                        </div>
                    ";
                    $mail->Body = $mailBody;

                    $mail->send();
                    $success_msg = "Cảm ơn bạn! Tin nhắn đã được gửi thành công đến Admin.";
                } catch (Exception $e) {
                    $success_msg = "Cảm ơn bạn! Tin nhắn đã được lưu hệ thống.";
                    $error_msg = "Lỗi gửi mail: {$mail->ErrorInfo}";
                }
               
            } else {
                $error_msg = "Có lỗi xảy ra trong quá trình lưu dữ liệu, vui lòng thử lại sau.";
            }
            $stmt->close();
        } else {
            $error_msg = "Lỗi hệ thống: Không thể kết nối bảng contacts.";
        }
    } else {
        $error_msg = "Vui lòng nhập đầy đủ các trường thông tin.";
    }
}

// Lấy danh sách các chi nhánh
$stores = [];
$res = $conn->query("SELECT Id, StoreName FROM store");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $stores[] = $row;
    }
}

include '../header.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ - Trung Nguyên Cà Phê</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #fff1e0; color: #333; padding-top: 125px; }
        .page-header { position: relative; background-image: url('https://mmvietnam.com/wp-content/uploads/2020/12/lien-he-new-scaled.jpg'); background-size: cover; background-position: center; height: 300px; display: flex; flex-direction: column; justify-content: center; align-items: center; }
        .page-header::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 0; }
        .page-header h1, .breadcrumb { position: relative; z-index: 1; text-align: center; color: white; }
        .breadcrumb { list-style: none; display: flex; justify-content: center; gap: 10px; margin-top: 15px; font-size: 18px; }
        .breadcrumb a { color: white; text-decoration: none; }
        .contact .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .contact form input, .contact form textarea, .contact form select { width: 100%; padding: 12px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #ccc; }
        .contact form button { width: 100%; padding: 12px; background: #007bff; color: #fff; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; }
        .info-box { display: flex; align-items: flex-start; background: #fff; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .info-box i { font-size: 28px; color: #007bff; margin-right: 15px; }
    </style>
</head>
<body>
    <div class="container-fluid page-header py-4 bg-light">
        <h1 class="display-6 fw-bold font-monospace">Liên hệ</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../home/index.php">Trang chủ</a></li>
                <span class="separator" style="color: white;">/</span>
                <li class="breadcrumb-item"><a href="../product/index.php">Cửa hàng</a></li>
                <span class="separator" style="color: white;">/</span>
                <li class="breadcrumb-item active" style="color: yellow;">Liên hệ</li>
            </ol>
        </nav>
    </div>

    <div class="contact">
        <div class="container">
            <div class="bg-light" style="padding:50px; border-radius:15px;">
                <div class="row">
                    <div class="col-12 text-center mb-4">
                        <h1>Liên hệ với chúng tôi</h1>
                        <p>Để lại tin nhắn để chúng tôi có thể hỗ trợ bạn tốt nhất.</p>
                    </div>

                    <div class="col-lg-12 mb-4">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.460232421111!2d106.6900223147491!3d10.77134329232462!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f3f885e3477%3A0xc3f58a7413d09e!2zODItODQgQsO5aSBUaOG7iyBYdcOibiwgUGjGsOG7nW5nIELhur9uIFRow6BuaCwgUXXhuq1uIDEsIFRow6BuaCBwaOG7kSBI4buTIENow60gTWluaCwgVmnhu4d0IE5hbQ!5e0!3m2!1svi!2s!4v1684305882312!5m2!1svi!2s" width="100%" height="450" style="border:0; border-radius:10px;" allowfullscreen="" loading="lazy"></iframe>
                    </div>

                    <div class="col-lg-7">
                        <form action="" method="post" style="background:#fff; padding:30px; border-radius:10px; box-shadow:0 4px 15px rgba(0,0,0,0.1);">
                            <?php if($success_msg): ?>
                                <div class="alert alert-success fw-bold text-center"><?= $success_msg ?></div>
                            <?php endif; ?>
                            <?php if($error_msg): ?>
                                <div class="alert alert-danger fw-bold text-center"><?= $error_msg ?></div>
                            <?php endif; ?>

                            <input type="text" name="name" placeholder="Tên của bạn" required>
                            <input type="email" name="email" placeholder="Email của bạn" required>
                            
                            <select name="store_id" required>
                                <option value="0">--- Liên hệ Ban quản lý Hệ Thống ---</option>
                                <?php foreach($stores as $s): ?>
                                    <option value="<?= $s['Id'] ?>">Liên hệ chi nhánh: <?= htmlspecialchars($s['StoreName']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <textarea name="message" rows="5" placeholder="Tin nhắn của bạn" required></textarea>
                            <button type="submit"><i class="fas fa-paper-plane me-2"></i>Gửi tin nhắn</button>
                        </form>
                    </div>

                    <div class="col-lg-5">
                        <div class="info-box">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h4>Địa chỉ</h4>
                                <p>82-84 Bùi Thị Xuân, Q.1, TP. Hồ Chí Minh</p>
                            </div>
                        </div>
                        <div class="info-box">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h4>Email</h4>
                                <p>contact@trungnguyencoffee.com</p>
                            </div>
                        </div>
                        <div class="info-box">
                            <i class="fas fa-phone-alt"></i>
                            <div>
                                <h4>Điện thoại</h4>
                                <p>(+84) 1900 6011</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>
</html>

