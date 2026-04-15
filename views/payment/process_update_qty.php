<?php
session_start();
require_once '../../controllers/CustomerPaymentController.php';

if (!isset($_SESSION['CustomerId'])) {
    die(json_encode(['success' => false, 'message' => 'Chưa login']));
}

$customerId = $_SESSION['CustomerId'];
$paymentController = new CustomerPaymentController();
$paymentId = (int)$_POST['paymentId'] ?? 0;
$productId = (int)$_POST['productId'] ?? 0;
$newQuantity = (int)$_POST['qty'] ?? 1;

if ($paymentId <= 0 || $productId <= 0 || $newQuantity < 1) {
    die(json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']));
}

$success = $paymentController->updatePaymentQuantity($paymentId, $customerId, $productId, $newQuantity);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Cập nhật số lượng thành công']);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể cập nhật (chỉ pending/COD)']);
}
?>

