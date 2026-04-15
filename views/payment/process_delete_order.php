<?php
session_start();
require_once '../../controllers/CustomerPaymentController.php';

if (!isset($_SESSION['CustomerId'])) {
    die(json_encode(['success' => false, 'message' => 'Chưa login']));
}

$customerId = $_SESSION['CustomerId'];
$paymentController = new CustomerPaymentController();
$paymentId = (int)$_POST['paymentId'] ?? 0;

if ($paymentId <= 0) {
    die(json_encode(['success' => false, 'message' => 'ID không hợp lệ']));
}

$success = $paymentController->deletePayment($paymentId, $customerId);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Đã xóa đơn hàng thành công']);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể xóa (chỉ pending/COD)']);
}
?>

