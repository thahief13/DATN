<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

/*
 * Config VNPAY environment
 * Đây là môi trường Sandbox để test, không dùng cho thanh toán thật
 */

// Mã định danh merchant kết nối (Terminal Id)
$vnp_TmnCode = "V9X6JGH5";

// Secret key dùng tạo checksum
$vnp_HashSecret = "E46TZXILBCHPTLJ21V2PK3B9KW4BJ6KW";

// URL thanh toán môi trường test
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";

// URL trả về sau khi thanh toán xong (merchant phải tạo)

$vnp_Returnurl = "http://localhost/app/vnpay_php/vnpay_return.php";


// API URL môi trường test
$vnp_apiUrl = "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html";
$apiUrl = "https://sandbox.vnpayment.vn/merchant_webapi/api/transaction";

// Thời gian bắt đầu và hết hạn của giao dịch
$startTime = date("YmdHis");
$expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));

// Các thông tin khác (nếu cần)
$vnp_Version = "2.1.0"; // version API
$vnp_Command = "pay";    // command mặc định
$vnp_CurrCode = "VND";   // đơn vị tiền tệ
