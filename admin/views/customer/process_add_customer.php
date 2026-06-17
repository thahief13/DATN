<?php
if (session_status() == PHP_SESSION_NONE) session_start();
// Đã sửa /../ thành /../../
require_once __DIR__ . '/../../controllers/CustomerAdminController.php';
// kiểm tra các thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new CustomerAdminController();
    $firstName = $_POST['FirstName'] ?? '';
    $lastName = $_POST['LastName'] ?? '';
    $email = $_POST['Email'] ?? '';
    $phone = $_POST['Phone'] ?? '';
    $address = $_POST['Address'] ?? '';
    $password = $_POST['Password'] ?? '';

    if ($controller->createCustomer($firstName, $lastName, $email, $phone, $address, $password)) {
        $_SESSION['message'] = 'Thêm khách hàng thành công!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Lỗi: Email đã tồn tại hoặc có lỗi khi thêm!';
        $_SESSION['message_type'] = 'error';
    }
    
    header('Location: ../index.php?page=customer');
    exit;
}
?>