<?php
session_start();
require_once '../../controllers/ProductController.php';

$store = $_GET['store'] ?? 0;
$type = $_GET['type'] ?? 'top_selling'; 
$limit = $_GET['limit'] ?? 6;

$productController = new ProductController();

$products = [];
switch ($type) {
    case 'top_selling':
        $products = $productController->getTopSelling($store, $limit);
        break;
    case 'cheapest':
        $products = $productController->getCheapest($store, $limit);
        break;
    case 'featured':
        $products = $productController->getFeaturedProducts($store, $limit);
        break;
    case 'latest':
        $products = $productController->getLatestProducts($store, $limit);
        break;
    default:
        $products = $productController->getFeaturedProducts($store, $limit);
}

echo '<div class="products-grid">';
if (!empty($products)) {
    foreach ($products as $product) {
        // Lấy % giảm giá thật từ DB
        $discount = isset($product->DiscountPercent) ? $product->DiscountPercent : 0;
        
        echo '<div class="card">';
        echo '<img src="/app/img/SanPham/' . htmlspecialchars($product->Img) . '" alt="' . htmlspecialchars($product->Title) . '" class="product-image" data-id="' . $product->Id . '">';
        echo '<div class="card-body">';
        echo '<h4>' . htmlspecialchars($product->Title) . '</h4>';
        echo '<p>' . htmlspecialchars(substr($product->Content, 0, 80)) . '...</p>';
        echo '<div style="display: flex; gap: 10px; align-items: center;">';
        
        // CHỈ hiển thị nhãn giảm giá nếu đang ở tab 'cheapest' và thực sự có giảm giá trong CSDL
        if ($type === 'cheapest' && $discount > 0) {
            $oldPrice = $product->Price;
            $salePrice = $product->Price * (1 - $discount / 100);
            
            echo '<span class="price" style="font-size: 18px; font-weight: bold; color: #ffb300;">' . number_format($salePrice, 0, ',', '.') . '₫</span>';
            echo '<span style="text-decoration: line-through; color: #999; font-size: 14px;">' . number_format($oldPrice, 0, ',', '.') . '₫</span>';
            echo '<span style="background: #ff4444; color: white; padding: 2px 6px; border-radius: 12px; font-size: 12px;">-' . $discount . '%</span>';
        } else {
            // Các mục còn lại in giá chuẩn
            echo '<span class="price" style="font-size: 18px; font-weight: bold; color: #ffb300;">' . number_format($product->Price, 0, ',', '.') . '₫</span>';
        }
        
        echo '</div>';
        echo '<button class="btn add_to_cart" data-id="' . $product->Id . '">Thêm vào giỏ</button>';
        echo '</div>';
        echo '</div>';
    }
} else {
    // Thông báo nếu rỗng
    echo '<p style="text-align: center; color: #666; grid-column: 1/-1;">Chưa có dữ liệu gợi ý.</p>';
}
echo '</div>';
?>