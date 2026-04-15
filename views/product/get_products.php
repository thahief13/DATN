<?php
require_once '../../controllers/ProductController.php';

$store = $_GET['store'] ?? 0;
$category = $_GET['category'] ?? 0;
$search = $_GET['searchString'] ?? '';
$sort = $_GET['sort'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 6;

$productController = new ProductController();
$products = $productController->getProducts($store, $category, $search, $sort, ($page - 1) * $limit, $limit);
$totalProducts = $productController->countProducts($store, $category, $search);
$totalPages = ceil($totalProducts / $limit);

// Bao container
echo '<div class="products-container">';

// Grid chứa các card
echo '<div class="products-grid">';
if (!empty($products)) {
    foreach ($products as $product) {
        echo '<div class="card" style="position: relative; overflow: visible;">';
        echo '<img src="/app/img/SanPham/' . htmlspecialchars($product->Img) . '" alt="' . htmlspecialchars($product->Title) . '" class="product-image" data-id="' . $product->Id . '" style="width: 100%; height: 220px; object-fit: cover; cursor: pointer; transition: transform 0.3s ease;">';
        echo '<div class="card-body" style="padding: 20px;">';
        echo '<h4 class="card-title">' . htmlspecialchars($product->Title) . '</h4>';
        echo '<p class="card-text">' . htmlspecialchars(substr($product->Content, 0, 100)) . '...</p>';
        echo '<span class="price">' . number_format($product->Price, 0, ',', '.') . ' ₫</span>';
        echo '<button class="btn add_to_cart" data-id="' . $product->Id . '">Thêm vào giỏ</button>';
        echo '</div></div>';
    }
} else {
    echo '<p>Không có sản phẩm nào.</p>';
}
echo '</div>'; // end .products-grid

// Pagination nằm dưới grid
if ($totalPages > 1) {
    echo '<div class="pagination-container">';
    echo '<ul class="pagination">';
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i == $page ? 'active' : '';
        echo '<li><a href="#" class="page-link ' . $active . '" data-page="' . $i . '">' . $i . '</a></li>';
    }
    echo '</ul>';
    echo '</div>'; // end .pagination-container
}

echo '</div>'; // end .products-container

