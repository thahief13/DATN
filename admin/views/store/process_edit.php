<?php
session_start();
require_once __DIR__ . '/../../models/StoreAdmin.php';
require_once __DIR__ . '/../../controllers/StoreAdminController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new StoreAdminController();
    
    $store = new StoreAdmin();
    $store->Id = (int)($_POST['Id'] ?? 0);
    $store->StoreName = trim($_POST['StoreName'] ?? '');
    $store->Address = trim($_POST['Address'] ?? '');
    $store->Phone = trim($_POST['Phone'] ?? '');
    $store->OpenTime = trim($_POST['OpenTime'] ?? '');
    $store->CloseTime = trim($_POST['CloseTime'] ?? '');
    
    if ($store->Id > 0 && !empty($store->StoreName)) {
        $controller->updateStore($store);
    }
} 

// Trả về đúng trang Store trên Dashboard
header('Location: ../index.php?page=store');
exit();
?>