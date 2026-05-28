<?php
session_start();
require_once __DIR__ . '/../env.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION['CustomerId'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$customerId = (int)$_SESSION['CustomerId'];
global $hostname, $username, $password, $dbname, $port;
$db = new mysqli($hostname, $username, $password, $dbname, $port);
$db->set_charset("utf8mb4");

// 1. ĐÃ SỬA TÊN BẢNG THÀNH `payment` CHO ĐÚNG VỚI DATABASE
$sql = "SELECT Id, Status FROM payment WHERE CustomerId = $customerId";
$res = $db->query($sql);

if (!$res) {
    echo json_encode(['success' => false, 'error_sql' => 'Lỗi SQL: ' . $db->error]);
    exit;
}

$currentStatuses = [];
if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        // 2. LƯU LẠI NGUYÊN BẢN CHỮ TIẾNG VIỆT (Bỏ (int) đi)
        $currentStatuses[$row['Id']] = trim($row['Status']);
    }
}
$db->close();

$notifications = [];

if (!isset($_SESSION['OrderStatuses'])) {
    $_SESSION['OrderStatuses'] = $currentStatuses;
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

foreach ($currentStatuses as $orderId => $status) {
    if (isset($_SESSION['OrderStatuses'][$orderId])) {
        $oldStatus = $_SESSION['OrderStatuses'][$orderId];
        
        if ($oldStatus != $status) {
            $msg = "";
            // 3. SO SÁNH TRỰC TIẾP BẰNG CHỮ ĐÚNG THEO DỮ LIỆU CỦA BẠN
            $statusLower = mb_strtolower($status, 'UTF-8');
            
            if ($statusLower === 'đang giao') {
                $msg = "Đơn hàng của bạn đã được đóng gói và ĐANG ĐƯỢC GIAO.";
            } elseif ($statusLower === 'đã giao' || $statusLower === 'đã giao thành công') {
                $msg = "ĐÃ GIAO THÀNH CÔNG. Cảm ơn bạn đã mua sắm!";
            } elseif ($statusLower === 'hủy') {
                $msg = "Rất tiếc, đơn hàng ĐÃ BỊ HỦY.";
            }
            
            if ($msg != "") {
                $notifications[] = [
                    'OrderId' => $orderId,
                    'Message' => $msg
                ];
            }
        }
    }
}

$_SESSION['OrderStatuses'] = $currentStatuses;

echo json_encode(['success' => true, 'data' => $notifications]);
?>