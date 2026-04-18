<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../../env.php';
require_once __DIR__ . '/../../controllers/PaymentAdminController.php';
// Nạp thêm controller doanh thu
require_once __DIR__ . '/../../controllers/RevenueAdminController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận POST']);
    exit();
}

$paymentId = intval($_POST['paymentId'] ?? 0);
$newStatus = trim($_POST['status'] ?? '');

if ($paymentId <= 0 || empty($newStatus)) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit();
}

global $hostname, $username, $password, $dbname, $port;
$db = new mysqli($hostname, $username, $password, $dbname, $port);

// 1. Lấy thông tin StoreId và Total của đơn hàng TRƯỚC khi cập nhật
$stmtInfo = $db->prepare("SELECT StoreId, Total, Status FROM payment WHERE Id = ?");
$stmtInfo->bind_param("i", $paymentId);
$stmtInfo->execute();
$order = $stmtInfo->get_result()->fetch_assoc();
$stmtInfo->close();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
    exit();
}

// 2. Cập nhật trạng thái đơn hàng
$paymentController = new PaymentAdminController();
$result = $paymentController->updatePaymentStatus($paymentId, $newStatus);

if ($result) {
    // 3. LOGIC CỘNG DOANH THU: 
    // Chỉ cộng khi chuyển sang 'đã giao' và trước đó đơn chưa phải là 'đã giao'
    if ($newStatus === 'đã giao' && $order['Status'] !== 'đã giao') {
        $revenueController = new RevenueAdminController();
        $revenueController->syncRevenue($order['StoreId'], $order['Total'], date('Y-m-d'));
    }
    
    echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
} else {
    echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại']);
}
$db->close();
?>