<?php
if (session_status() == PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['CustomerId'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit();
}


require_once '../../env.php'; 
require_once '../../controllers/CustomerPaymentController.php';

$customerId = (int)$_SESSION['CustomerId'];
$paymentController = new CustomerPaymentController();
$currentOrders = $paymentController->getCustomerPayments($customerId);

$notifications = [];

// Nếu chưa có Session theo dõi, khởi tạo và ghi nhớ trạng thái hiện tại rồi thoát
if (!isset($_SESSION['tracked_orders'])) {
    $_SESSION['tracked_orders'] = [];
    foreach ($currentOrders as $order) {
        $_SESSION['tracked_orders'][$order['Id']] = mb_strtolower($order['Status'] ?? 'pending', 'UTF-8');
    }
    echo json_encode(['success' => true, 'hasNew' => false]);
    exit();
}

// 2. Nếu đã có Session, đem so sánh trạng thái mới trong DB với trạng thái cũ trong Session
foreach ($currentOrders as $order) {
    $id = $order['Id'];
    $newStatus = mb_strtolower($order['Status'] ?? 'pending', 'UTF-8');
    
    if (isset($_SESSION['tracked_orders'][$id])) {
        $oldStatus = $_SESSION['tracked_orders'][$id];
        
        // NẾU TRẠNG THÁI BỊ ADMIN THAY ĐỔI
        if ($oldStatus !== $newStatus) {
            $statusText = $newStatus;
            if ($newStatus == 'đang giao') $statusText = 'Đang giao hàng';
            if ($newStatus == 'đã giao' || $newStatus == 'thành công') $statusText = 'Đã giao thành công';
            if ($newStatus == 'hủy' || $newStatus == 'cancelled' || $newStatus == 'đã hủy') $statusText = 'Đã bị hủy';

            
            $notifications[] = "Đơn hàng <b>#{$id}</b> của bạn vừa được cập nhật thành: <b>" . mb_strtoupper($statusText, 'UTF-8') . "</b>";
            
            
            $_SESSION['tracked_orders'][$id] = $newStatus;
        }
    } else {
       
        $_SESSION['tracked_orders'][$id] = $newStatus;
    }
}


echo json_encode([
    'success' => true,
    'hasNew' => count($notifications) > 0,
    'data' => $notifications
]);
?>