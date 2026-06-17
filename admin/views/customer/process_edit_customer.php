<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../controllers/CustomerAdminController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new CustomerAdminController();
    $id = $_POST['CustomerId'] ?? 0;
    $firstName = $_POST['FirstName'] ?? '';
    $lastName = $_POST['LastName'] ?? '';
    $email = $_POST['Email'] ?? '';
    $phone = $_POST['Phone'] ?? '';
    $address = $_POST['Address'] ?? '';
    $isActive = $_POST['IsActive'] ?? 1;

    // Cập nhật thông tin
    $infoSuccess = $controller->updateCustomerInfo($id, $firstName, $lastName, $email, $phone, $address);
    // Cập nhật trạng thái
    $statusSuccess = $controller->updateCustomer($id, $isActive);

    if ($infoSuccess && $statusSuccess) {
        $_SESSION['message'] = 'Cập nhật khách hàng thành công!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Lỗi: Email đã tồn tại hoặc có lỗi khi cập nhật!';
        $_SESSION['message_type'] = 'error';
    }
    
    header('Location: ../index.php?page=customer');
    exit;
}
?>