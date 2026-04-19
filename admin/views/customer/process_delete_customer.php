<?php
session_start();
require_once __DIR__ . '/../../controllers/CustomerAdminController.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['CustomerId'])) {
    $id = (int)$_POST['CustomerId'];
    $controller = new CustomerAdminController();
    
    if ($controller->deleteCustomer($id)) {
        header("Location: index.php?page=customer&status=deleted");
    } else {
        echo "<script>alert('Lỗi: Khách hàng này có thể đang có đơn hàng, không thể xóa!'); window.location.href='index.php?page=customer';</script>";
    }
}
exit();