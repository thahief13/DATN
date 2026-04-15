<?php
require_once __DIR__ . '/../../../admin/controllers/StoreController.php';
$storeController = new StoreController();
$stores = $storeController->getAllStores();
header('Content-Type: application/json');
echo json_encode($stores);
?>

