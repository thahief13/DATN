<?php
session_start();
require_once __DIR__ . '/../../controllers/CustomerAdminController.php';

// 1. Kiểm tra đăng nhập/quyền
if (!isset($_SESSION['CustomerId'])) {
    header("Location: index.php");
    exit();
}

// 2. Kiểm tra dữ liệu POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['CustomerId'])) {
    $customerId = (int)$_POST['CustomerId'];
    $isActive = (int)$_POST['IsActive'];

    $controller = new CustomerAdminController();
    
    // 3. Gọi hàm cập nhật trong Controller
    $result = $controller->updateCustomer($customerId, $isActive);

    if ($result) {
        // Thành công: Chuyển hướng kèm thông báo (tùy chọn)
        header("Location: index.php?status=updated");
    } else {
        // Thất bại
        echo "<script>alert('Cập nhật không thành công hoặc không có thay đổi!'); window.location.href='index.php';</script>";
    }
} else {
    header("Location: index.php");
}
exit();