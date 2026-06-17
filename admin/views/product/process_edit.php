<?php
session_start();
require_once '../../../config/env.php';
require_once '../../models/ProductAdmin.php';
require_once '../../controllers/ProductAdminController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new ProductAdminController();
    
    $product = new ProductAdmin();
    $product->Id = (int)($_POST['productId'] ?? 0);
    $product->Title = $_POST['title'] ?? '';
    $product->Content = $_POST['content'] ?? '';
    $product->Price = (int)($_POST['price'] ?? 0);
    $product->CategoryId = (int)($_POST['category_id'] ?? 0);
    
    // thông tin cửa hàng
    $storeIds = isset($_POST['store_ids']) && is_array($_POST['store_ids']) ? array_map('intval', $_POST['store_ids']) : [];
    
    if ($product->Id <= 0 || empty($product->Title) || empty($product->Content) || $product->Price <= 0 || $product->CategoryId <= 0 || empty($storeIds)) {
        $_SESSION['error_message'] = 'Vui lòng điền đầy đủ thông tin bao gồm chọn cửa hàng';
        header('Location: ../index.php?page=product');
        exit();
    }
    
    //update ảnh
    $newImage = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = '../../../img/SanPham/';
        $fileName = basename($_FILES['image']['name']);
        $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
        $newImage = uniqid() . '.' . $fileType;
        $uploadFile = $uploadDir . $newImage;
        
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $_SESSION['error_message'] = 'Lỗi upload hình ảnh';
            header('Location: ../index.php?page=product');
            exit();
        }
    }
    
    if ($controller->updateProduct($product, $newImage)) {
        // Update sản phẩm vào chi nhánh
        $controller->addProductToStore($product->Id, $storeIds);
        $_SESSION['success_message'] = 'Cập nhật sản phẩm thành công';
    } else {
        $_SESSION['error_message'] = 'Lỗi cập nhật sản phẩm';
    }
}

$currentPage = isset($_POST['current_page']) ? (int)$_POST['current_page'] : 1;
header('Location: ../index.php?page=product&product_page=' . $currentPage);
exit();
?>