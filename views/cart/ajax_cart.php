<?php
session_start();
require_once '../../controllers/CartController.php';

$cartController = new CartController();
$customerId = $_SESSION['CustomerId'] ?? 0;

$action = $_POST['action'] ?? '';

if ($action === 'remove') {
    $productId = intval($_POST['productId']);
    $storeId = intval($_POST['storeId']);
    $success = $cartController->deleteItemInCart($customerId, $productId, $storeId);
    echo json_encode(['success' => $success]);
    exit;
}

if ($action === 'update_quantity') {
    $productId = intval($_POST['productId']);
    $storeId = intval($_POST['storeId']);
    $quantity = intval($_POST['quantity']);
    $success = $cartController->updateQuantity($customerId, $productId, $storeId, $quantity);
    echo json_encode(['success' => $success]);
    exit;
}
