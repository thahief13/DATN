<?php
session_start();
require_once '../../controllers/CartController.php';
require_once '../../controllers/ProductController.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_to_cart') {
    $customerId = (int)($_SESSION['CustomerId'] ?? 0);
    $productId = intval($_POST['product_id'] ?? 0);
    $storeId = intval($_POST['store_id'] ?? 0);
    $quantity = max(1, intval($_POST['quantity'] ?? 1)); // đảm bảo >=1

    if ($customerId <= 0) {
        $response['message'] = "Vui lòng đăng nhập để đặt hàng!";
    } elseif ($productId <= 0 || $storeId <= 0) {
        $response['message'] = "Dữ liệu sản phẩm không hợp lệ.";
    } else {
        try {
            $productController = new ProductController();
            $product = $productController->getProductById($productId);
            $productTitle = $product->Title ?? 'Sản phẩm';

            $cartController = new CartController();
            $result = $cartController->addToCart($customerId, $productId, $storeId, $quantity);

            if ($result) {
                $response['success'] = true;
                $response['productName'] = $productTitle;
                $response['message'] = "Đã thêm " . htmlspecialchars($productTitle) . " vào giỏ hàng thành công!";
            } else {
                $response['message'] = "Lỗi khi thêm sản phẩm vào giỏ hàng.";
            }
        } catch (Exception $e) {
            $response['message'] = "Lỗi hệ thống: " . $e->getMessage();
        }
    }
} else {
    $response['message'] = "Yêu cầu không hợp lệ.";
}

echo json_encode($response);
exit();
