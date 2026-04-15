<?php
session_start();
require_once '../../../config/env.php';
require_once '../../controllers/ProductAdminController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new ProductAdminController();
    
    $productId = (int)($_POST['productId'] ?? 0);
    
    if ($productId <= 0) {
        $_SESSION['error_message'] = 'ID sản phẩm không hợp lệ';
        header('Location: ../index.php?page=product');
        exit();
    }
    
    if ($controller->deleteProduct($productId)) {
        $_SESSION['success_message'] = 'Xóa sản phẩm thành công';
    } else {
        $_SESSION['error_message'] = 'Lỗi xóa sản phẩm';
    }
}

$currentPage = isset($_POST['current_page']) ? (int)$_POST['current_page'] : 1;
header('Location: ../index.php?page=product&product_page=' . $currentPage);
exit();
?>