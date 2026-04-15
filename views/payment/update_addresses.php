<?php
session_start();
require_once '../../controllers/PaymentController.php';

if (!isset($_SESSION['CustomerId'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit();
}

$paymentId = intval($_POST['paymentId'] ?? 0);
$storeAddress = trim($_POST['storeAddress'] ?? '');
$deliveryAddress = trim($_POST['deliveryAddress'] ?? '');

if (!$paymentId) {
    echo json_encode(['success' => false, 'message' => 'ID đơn hàng không hợp lệ']);
    exit();
}

$paymentController = new PaymentController();
$result = $paymentController->updatePaymentAddresses($paymentId, $storeAddress, $deliveryAddress);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Cập nhật địa chỉ thành công']);
} else {
    echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại']);
}
?>