<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');



// Thời gian bắt đầu và hết hạn của giao dịch (Chỉ giữ nếu còn dùng logic thời gian)
$startTime = date("YmdHis");
$expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));

// Các thông tin cấu hình chung (nếu cần)
// $vnp_Version = "2.1.0"; 
// $vnp_Command = "pay";    
// $vnp_CurrCode = "VND";   
?>