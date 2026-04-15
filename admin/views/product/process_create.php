<?php
session_start();
require_once '../../../config/env.php';
require_once '../../models/ProductAdmin.php';
require_once '../../controllers/ProductAdminController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new ProductAdminController();
    
    $product = new ProductAdmin();
    $product->Title = $_POST['title'] ?? '';
    $product->Content = $_POST['content'] ?? '';
    $product->Price = (int)($_POST['price'] ?? 0);
    $product->CategoryId = (int)($_POST['category_id'] ?? 0);
    $product->Rate = 0; // Default rate
    
    // Get store IDs
    $storeIds = isset($_POST['store_ids']) && is_array($_POST['store_ids']) ? array_map('intval', $_POST['store_ids']) : [];
    
    // Handle image upload
    $imageName = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = '../../../img/SanPham/';
        $fileName = basename($_FILES['image']['name']);
        $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
        $imageName = uniqid() . '.' . $fileType;
        $uploadFile = $uploadDir . $imageName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $product->Img = $imageName;
        } else {
            $_SESSION['error_message'] = 'Lỗi upload hình ảnh';
            header('Location: ../index.php?page=product');
            exit();
        }
    } else {
        $_SESSION['error_message'] = 'Vui lòng chọn hình ảnh';
        header('Location: ../index.php?page=product');
        exit();
    }
    
    if (empty($product->Title) || empty($product->Content) || $product->Price <= 0 || $product->CategoryId <= 0 || empty($storeIds)) {
        $_SESSION['error_message'] = 'Vui lòng điền đầy đủ thông tin bao gồm chọn cửa hàng';
        header('Location: ../index.php?page=product');
        exit();
    }
    
    $newProductId = $controller->addProduct($product);
    if ($newProductId > 0) {
        // Add product to stores
        $controller->addProductToStore($newProductId, $storeIds);
        $_SESSION['success_message'] = 'Thêm sản phẩm thành công';
    } else {
        $_SESSION['error_message'] = 'Lỗi thêm sản phẩm';
    }
}

header('Location: ../index.php?page=product&product_page=1');
exit();
?>