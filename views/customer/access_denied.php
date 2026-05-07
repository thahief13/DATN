<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài khoản bị khóa - Trung Nguyên Legend</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .denied-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 550px;
            width: 90%;
            text-align: center;
            border-top: 5px solid #dc3545;
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            background: #ffeeba;
            color: #dc3545;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 20px;
        }
        .reason-box {
            background: #fdfdfe;
            border: 1px solid #f5c6cb;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
            color: #856404;
            font-size: 15px;
        }
        .contact-info {
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>

    <div class="denied-card">
        <div class="icon-circle">
            <i class="fas fa-user-lock"></i>
        </div>
        <h3 class="text-danger fw-bold mb-3">Tài khoản đã bị tạm khóa</h3>
        <p class="text-muted">Rất tiếc, tài khoản của bạn hiện không thể thực hiện các chức năng mua hàng hay đánh giá trên hệ thống.</p>
        
        <div class="reason-box">
            <strong><i class="fas fa-exclamation-triangle me-1"></i> Nguyên nhân có thể do:</strong>
            <ul class="mb-0 mt-2">
                <li>Phát hiện hành vi spam bình luận, đánh giá ảo.</li>
                <li>Tài khoản có lịch sử từ chối nhận hàng (bom hàng) nhiều lần.</li>
                <li>Vi phạm các tiêu chuẩn cộng đồng của hệ thống.</li>
            </ul>
        </div>

        <p class="mb-4 text-muted" style="font-size: 14px;">
            Nếu bạn cho rằng đây là sự nhầm lẫn, vui lòng liên hệ với bộ phận CSKH để được hỗ trợ kiểm tra và mở khóa tài khoản.
        </p>

        <div class="mb-4 contact-info">
            <p class="mb-1"><i class="fas fa-phone-alt text-primary me-2"></i> Hotline: 1900 6789</p>
            <p class="mb-0"><i class="fas fa-envelope text-danger me-2"></i> Email: support@trungnguyenlegend.com</p>
        </div>

        <div class="d-flex justify-content-center gap-3">
            <a href="../../views/customer/log_out.php" class="btn btn-outline-secondary px-4 rounded-pill">
                <i class="fas fa-sign-out-alt me-1"></i> Đăng xuất
            </a>
            <a href="../../views/home/index.php" class="btn btn-primary px-4 rounded-pill">
                <i class="fas fa-home me-1"></i> Về trang chủ
            </a>
        </div>
    </div>

</body>
</html>