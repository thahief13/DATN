<?php
session_start();
require_once '../../../config/env.php';
require_once '../../models/StoreAdmin.php';
require_once '../../controllers/StoreAdminController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new StoreAdminController();
    
    $store = new StoreAdmin();
    $store->Id = (int)($_POST['Id'] ?? 0);
    $store->StoreName = $_POST['StoreName'] ?? '';
    $store->Address = $_POST['Address'] ?? '';
    $store->Phone = $_POST['Phone'] ?? '';
    $store->OpenTime = $_POST['OpenTime'] ?? '';
    $store->CloseTime = $_POST['CloseTime'] ?? '';
    
    if ($store->Id <= 0 || empty($store->StoreName)) {
        $_SESSION['error_message'] = 'ID cửa hàng hoặc tên không hợp lệ';
        header('Location: ../index.php?page=store');
        exit();
    }
    
    if ($controller->updateStore($store)) {
        $_SESSION['success_message'] = 'Cập nhật cửa hàng thành công';
    } else {
        $_SESSION['error_message'] = 'Lỗi cập nhật cửa hàng';
    }
} 

header('Location: ../index.php?page=store');
exit();
?>

