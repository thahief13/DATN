<?php
session_start();
require_once '../../../config/env.php';
require_once '../../controllers/CategoryAdminController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new CategoryAdminController();
    
    $categoryId = (int)($_POST['categoryId'] ?? 0);
    
    if ($categoryId <= 0) {
        $_SESSION['error_message'] = 'ID danh mục không hợp lệ';
        header('Location: ../index.php?page=category');
        exit();
    }
    
    if ($controller->deleteCategory($categoryId)) {
        $_SESSION['success_message'] = 'Xóa danh mục thành công';
    } else {
        $_SESSION['error_message'] = 'Lỗi xóa danh mục (có thể đang được sử dụng)';
    }
} 

header('Location: ../index.php?page=category');
exit();
?>

