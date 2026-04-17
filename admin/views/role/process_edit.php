<?php
session_start();
require_once __DIR__ . '/../../controllers/RoleAdminController.php';
require_once __DIR__ . '/../../models/RoleAdmin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = new RoleAdmin();
    $role->Id = intval($_POST['Id'] ?? 0);
    $role->RoleName = trim($_POST['RoleName'] ?? '');

    if ($role->Id > 0 && !empty($role->RoleName)) {
        $controller = new RoleAdminController();
        $controller->updateRole($role);
    }
    
    // Xử lý xong quay lại trang danh sách
    header("Location: index.php");
    exit();
}
?>