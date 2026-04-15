<?php
session_start();
require_once '../../../config/env.php';
require_once '../../models/CategoryAdmin.php';
require_once '../../controllers/CategoryAdminController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new CategoryAdminController();
    
    $category = new CategoryAdmin();
    $category->Id = (int)($_POST['categoryId'] ?? 0);
    $category->Title = $_POST['title'] ?? '';
    $category->Content = $_POST['content'] ?? '';
    
    if ($category->Id <= 0 || empty($category->Title)) {
        $_SESSION['error_message'] = 'ID danh mục hoặc tên không hợp lệ';
        header('Location: ../index.php?page=category');
        exit();
    }
    
    if ($controller->updateCategory($category)) {
        $_SESSION['success_message'] = 'Cập nhật danh mục thành công';
    } else {
        $_SESSION['error_message'] = 'Lỗi cập nhật danh mục';
    }
} 

header('Location: ../index.php?page=category');
exit();
?>

