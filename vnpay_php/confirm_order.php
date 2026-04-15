<?php
// require_once("./config.php"); // Nếu bạn đã xoá hoặc comment các biến VNPAY trong config.php, bạn vẫn có thể giữ lại dòng này để tải cấu hình chung.

// --- 1. Xử lý Dữ liệu Đơn hàng ---

// Lấy số tiền từ form (giả sử input name trong vnpay_pay.php là 'amount')
$amount_raw = $_POST['amount'] ?? 0;
// Định dạng số tiền
$amount_to_pay = (int)$amount_raw; 

// Tạo một Mã đơn hàng duy nhất cho giao dịch (sử dụng timestamp + 4 chữ số ngẫu nhiên)
// Đây là Mã BẮT BUỘC phải có trong nội dung chuyển khoản
$order_id = "ORD" . date('Ymd') . rand(1000, 9999); 
$transfer_content = "TT " . $order_id; // Nội dung chuyển khoản

// Lưu ý: Trong hệ thống thực tế, bạn cần lưu $order_id và $amount_to_pay vào Database ở bước này
// để sau này đối soát giao dịch chuyển khoản.

// --- 2. Thông tin Ngân hàng Cố định ---

$bank_name = "VIETCOMBANK";
$account_number = "098765432101"; // Thay bằng số tài khoản thật của bạn
$account_holder = "NGUYEN VAN A - CTY XYZ"; // Thay bằng tên chủ tài khoản thật

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Xác nhận Đơn hàng & Thanh toán Chuyển khoản</title>
        <link href="/vnpay_php/assets/bootstrap.min.css" rel="stylesheet"/>
        <link href="/vnpay_php/assets/jumbotron-narrow.css" rel="stylesheet">  
    </head>

    <body>
        <div class="container">
            <div class="header clearfix">
                <h3 class="text-muted">XÁC NHẬN THANH TOÁN</h3>
            </div>
            
            <div class="alert alert-warning" role="alert">
                <h4>Vui lòng hoàn tất chuyển khoản để kích hoạt đơn hàng!</h4>
                <p>Đơn hàng của bạn sẽ được xác nhận tự động sau khi chúng tôi nhận được chuyển khoản.</p>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <h4 style="color: #333;">THÔNG TIN ĐƠN HÀNG</h4>
                    <table class="table table-bordered">
                        <tr>
                            <td style="width: 30%; font-weight: bold;">Mã Đơn hàng:</td>
                            <td style="color: blue; font-weight: bold;"><?php echo htmlspecialchars($order_id); ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Tổng số tiền:</td>
                            <td style="color: red; font-weight: bold;"><?php echo number_format($amount_to_pay, 0, ',', '.') . ' VND'; ?></td>
                        </tr>
                    </table>
                    
                    <hr>

                    <h4 style="color: #333;">PHƯƠNG THỨC THANH TOÁN: CHUYỂN KHOẢN NGÂN HÀNG</h4>

                    <table class="table table-striped table-hover">
                        <tr>
                            <td style="width: 40%;">Ngân hàng thụ hưởng:</td>
                            <td style="font-weight: bold;"><?php echo htmlspecialchars($bank_name); ?></td>
                        </tr>
                        <tr>
                            <td>Số tài khoản:</td>
                            <td style="font-weight: bold;"><?php echo htmlspecialchars($account_number); ?></td>
                        </tr>
                        <tr>
                            <td>Tên chủ tài khoản:</td>
                            <td style="font-weight: bold;"><?php echo htmlspecialchars($account_holder); ?></td>
                        </tr>
                        <tr>
                            <td>Nội dung chuyển khoản:</td>
                            <td style="color: red; font-weight: bold; font-size: 1.1em;"><?php echo htmlspecialchars($transfer_content); ?></td>
                        </tr>
                    </table>

                    <div class="alert alert-danger" role="alert">
                        ⚠️ **QUAN TRỌNG:** Vui lòng **copy chính xác** nội dung chuyển khoản này để đơn hàng được xác nhận nhanh nhất.
                    </div>
                    
                    <hr>
                    
                    <h4 style="color: #333;">MÃ QR THANH TOÁN</h4>
                    <p>Quét mã QR để thanh toán nhanh chóng:</p>
                    
                    <div style="text-align: center; margin: 20px 0;">
                        


                        <img src="/vnpay_php/assets/qr_bank.png" alt="QR Code Thanh toán" style="max-width: 250px; border: 1px solid #ddd; padding: 5px;">
                    </div>
                </div>
            </div>
            <p>
                &nbsp;
            </p>
            <footer class="footer">
                <p>&copy; Hệ thống Thanh toán của bạn <?php echo date('Y') ?></p>
            </footer>
        </div>  
    </body>
</html>