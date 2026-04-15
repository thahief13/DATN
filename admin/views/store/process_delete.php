<?php
session_start();
require_once '../../../config/env.php';
require_once '../../models/StoreAdmin.php';
require_once '../../controllers/StoreAdminController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new StoreAdminController();
    
    $storeId = (int)($_POST['Id'] ?? 0);
    
    if ($storeId <= 0) {
        $_SESSION['error_message'] = 'ID cửa hàng không hợp lệ';
        header('Location: ../index.php?page=store');
        exit();
    }
    
    if ($controller->deleteStore($storeId)) {
        $_SESSION['success_message'] = 'Xóa cửa hàng thành công';
    } else {
        $_SESSION['error_message'] = 'Lỗi xóa cửa hàng (có thể đang được sử dụng)';
    }
} 

header('Location: ../index.php?page=store');
exit();
?>

