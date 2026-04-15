<?php
session_start();
require_once '../../controllers/CartController.php';
require_once '../../controllers/ProductController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $customerId = (int)$_SESSION['CustomerId'] ?? 0;
    $productId = intval($_POST['product_id'] ?? 0);
    $storeId = intval($_POST['store_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    $productTitle = '';
    if ($customerId <= 0) {
        header('Location: ../customer/sign_in.php');
        exit();
    } elseif ($productId <= 0 || $storeId <= 0 || $quantity <= 0) {
        $_SESSION['error_message'] = "Dữ liệu sản phẩm không hợp lệ.";
    } else {
        try {
            $productController = new ProductController();
            $product = $productController->getProductById($productId);
            $productTitle = $product->Title ?? 'Sản phẩm';
            $cartController = new CartController();
            $result = $cartController->addToCart($customerId, $productId, $storeId, $quantity);
            if ($result) {
                $_SESSION['success_message'] = "Đã thêm " . htmlspecialchars($productTitle) . " vào giỏ hàng thành công!";
            } else {
                $_SESSION['error_message'] = "Lỗi khi thêm sản phẩm vào cơ sở dữ liệu.";
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}
$redirect_url = '../product/index.php';
if (isset($_POST['current_url_params']) && !empty($_POST['current_url_params'])) {
    $redirect_url .= '?' . $_POST['current_url_params'];
}
header('Location: ' . $redirect_url);
exit();
