<?php
session_start();
require_once __DIR__ . '/../../models/StoreAdmin.php';
require_once __DIR__ . '/../../controllers/StoreAdminController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new StoreAdminController();
    
    $storeId = (int)($_POST['Id'] ?? 0);
    
    if ($storeId > 0) {
        $controller->deleteStore($storeId);
    }
} 

// Trả về đúng trang Store trên Dashboard
header('Location: ../index.php?page=store');
exit();
?>