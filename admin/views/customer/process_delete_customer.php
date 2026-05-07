<?php
if (session_status() == PHP_SESSION_NONE) session_start();
// Đã sửa /../ thành /../../
require_once __DIR__ . '/../../controllers/CustomerAdminController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['CustomerId'])) {
    $id = (int)$_POST['CustomerId'];
    $controller = new CustomerAdminController();
    
    if ($controller->deleteCustomer($id)) {
        $_SESSION['message'] = 'Xóa khách hàng thành công!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Lỗi: Khách hàng này có thể đang có đơn hàng, không thể xóa!';
        $_SESSION['message_type'] = 'error';
    }
    // Đã sửa lùi ra trang index gốc
    header('Location: ../index.php?page=customer');
    exit;
}
?>