<?php
session_start();
require_once __DIR__ . '/../../controllers/RoleAdminController.php';
require_once __DIR__ . '/../../models/RoleAdmin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = new RoleAdmin();
    $role->RoleName = trim($_POST['RoleName'] ?? '');

    if (!empty($role->RoleName)) {
        $controller = new RoleAdminController();
        $controller->addRole($role);
    }
    
    // Xử lý xong quay lại trang danh sách
    header("Location: index.php");
    exit();
}
?>