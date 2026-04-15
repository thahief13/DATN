<?php
header('Content-Type: application/json');
session_start();

require_once '../../../env.php';
require_once '../../controllers/PaymentAdminController.php';

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

// Check admin permission

$paymentController = new PaymentAdminController();
$result = $paymentController->updatePaymentStatus($paymentId, $newStatus);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
} else {
    echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại']);
}
?>

