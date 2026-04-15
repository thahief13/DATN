<?php
session_start();
require_once '../../../config/env.php';
require_once '../../models/StoreAdmin.php';
require_once '../../controllers/StoreAdminController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new StoreAdminController();
    
    $store = new StoreAdmin();
    $store->StoreName = $_POST['StoreName'] ?? '';
    $store->Address = $_POST['Address'] ?? '';
    $store->Phone = $_POST['Phone'] ?? '';
    $store->OpenTime = $_POST['OpenTime'] ?? '';
    $store->CloseTime = $_POST['CloseTime'] ?? '';
    
    if (empty($store->StoreName)) {
        $_SESSION['error_message'] = 'Tên cửa hàng không được để trống';
        header('Location: ../index.php?page=store');
        exit();
    }
    
    if ($controller->createStore($store)) {
        $_SESSION['success_message'] = 'Thêm cửa hàng thành công';
    } else {
        $_SESSION['error_message'] = 'Lỗi thêm cửa hàng';
    }
} 

header('Location: ../index.php?page=store');
exit();
?>

