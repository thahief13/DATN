<?php
session_start();
require_once '../../../config/env.php';
require_once '../../models/CategoryAdmin.php';
require_once '../../controllers/CategoryAdminController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new CategoryAdminController();
    
    $category = new CategoryAdmin();
    $category->Title = $_POST['title'] ?? '';
    $category->Content = $_POST['content'] ?? '';
    
    if (empty($category->Title)) {
        $_SESSION['error_message'] = 'Tên danh mục không được để trống';
        header('Location: ../index.php?page=category');
        exit();
    }
    
    if ($controller->createCategory($category)) {
        $_SESSION['success_message'] = 'Thêm danh mục thành công';
    } else {
        $_SESSION['error_message'] = 'Lỗi thêm danh mục';
    }
}

header('Location: ../index.php?page=category');
exit();
?>

