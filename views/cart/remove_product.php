<?php
session_start();
require_once '../../controllers/CartController.php';
$redirect_url = 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = intval($_SESSION['CustomerId']);
    $productId = intval($_POST['productId']);
    $storeId = intval($_POST['storeId']);

    if ($_SESSION['storeId'] > 0) {
        $redirect_url .= '?store=' . $_SESSION['storeId'];
    }

    $cartController = new CartController();
    $result = $cartController->deleteItemInCart($customerId, $productId, $storeId);

    

} else {
    $_SESSION['error_message'] = "Phương thức truy cập không hợp lệ.";
}
header('Location: ' . $redirect_url);
exit();
