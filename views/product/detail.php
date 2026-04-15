<?php
session_start();
$selectedStore = $_SESSION['SelectedStore'] ?? 0;
require_once '../../controllers/ProductController.php';
require_once '../../controllers/CategoryController.php';
require_once '../../env.php';
$productController = new ProductController();
$categoryController = new CategoryController();
$categoryId = isset($_GET['category']) ? intval($_GET['category']) : 0;
$storeId = $_SESSION['SelectedStore'] ?? 0; 
$productId = $_GET['id'];

$product = $productController->getProductById($productId);
$featuredProducts = $productController->getFeaturedProducts($storeId, 3);
$relatedProducts = $productController->getRelatedProducts($storeId, $productId);
$reviews = $productController->getReviews($productId);
$categories = $categoryController->getAllCategories();
$customerId = $_SESSION['CustomerId'] ?? 0;
// Thêm logic tìm StoreProductId
$storeProductId = 0;
$dbTemp = new mysqli($hostname, $username, $password, $dbname, $port);
$resSP = $dbTemp->query("SELECT Id FROM storeproduct WHERE ProductId = $productId AND StoreId = $storeId");
if($rowSP = $resSP->fetch_assoc()) {
    $storeProductId = $rowSP['Id'];
}
$dbTemp->close();

// Khi lấy danh sách đánh giá, dùng StoreProductId
$reviews = $productController->getReviews($storeProductId);
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết sản phẩm - Trung Nguyên Cà Phê</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #fff8f0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .page-header {
            margin-top: 80px;
            padding: 120px 0 60px;
            background-size: cover;
            background-position: center;
            position: relative;
            background-image: url('https://thesaigontimes.vn/wp-content/uploads/2022/07/Dungdequatre.jpeg.jpg');
            color: white;
        }

        .page-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }

        .page-header h1,
        .page-header .breadcrumb {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .page-header h1 {
            font-size: 48px;
            font-weight: 800;
            letter-spacing: 2px;
        }

        .breadcrumb {
            justify-content: center;
            gap: 8px;
            margin-top: 15px;
            font-size: 16px;
        }

        .breadcrumb a {
            color: #fff1e0;
            text-decoration: none;
            transition: color 0.3s;
        }

        .breadcrumb a:hover {
            color: #ffc107;
        }

        .breadcrumb .active {
            color: #ffc107;
            font-weight: bold;
        }

        .img-thumbnail {
            object-fit: cover;
        }

        .product-title {
            font-size: 28px;
            font-weight: 700;
        }

        .product-price {
            font-size: 22px;
            font-weight: 700;
            color: #e67e22;
        }

        .star-rating i {
            color: #ffc107;
            margin-right: 2px;
        }

        .input-group.quantity input#quantity {
            width: 40px;
            padding: 5px 10px;
            font-size: 16px;
            text-align: center;
            border-radius: 5px;
            border: 1px solid #ccc;
            -moz-appearance: textfield;
        }

        .input-group.quantity input#quantity::-webkit-inner-spin-button,
        .input-group.quantity input#quantity::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .input-group.quantity button {
            width: 40px;
            height: 40px;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar h5 {
            font-weight: 700;
            margin-bottom: 15px;
        }

        .sidebar ul li {
            margin-bottom: 8px;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #333;
        }

        .sidebar ul li a:hover {
            color: #e67e22;
        }

        .featured-product img {
            border-radius: 5px;
        }

        .related-product {
            transition: transform 0.3s;
        }

        .related-product:hover {
            transform: scale(1.03);
        }

        .top-toast {
            background-color: #ffb300;
            color: #fff;
            padding: 15px 25px;
            border-radius: 0 0 10px 10px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transform: translateY(-100%);
            transition: transform 0.5s ease, opacity 0.5s ease;
            opacity: 0.95;
            text-align: center;
            max-width: 400px;
            margin: 0 auto;
        }

        .top-toast.show {
            transform: translateY(0);
        }
        .rating-star {
            font-size: 24px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
            margin-right: 5px;
        }
        .rating-star:hover,
        .rating-star.active {
            color: #ffc107;
        }
    </style>

</head>

<body>
    <?php include '../header.php'; ?>
    <div id="topToastContainer" style="position: fixed; top: 0; left: 50%; transform: translateX(-50%); z-index: 9999; width: auto;"></div>


    <div class="container-fluid page-header">
        <h1>Chi tiết sản phẩm</h1>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="../home/index.php">Trang chủ</a></li>
            <span class="separator">/</span>
            <li class="breadcrumb-item"><a href="index.php">Cửa hàng</a></li>
            <span class="separator">/</span>
            <li class="breadcrumb-item active">Chi tiết sản phẩm</li>
        </ul>
    </div>

    <div class="container py-5">
        <div class="row g-4">
            <!-- Product Detail -->
            <div class="col-lg-8">
                <div class="row g-4">
                    <div class="col-md-6">
                        <img src="../../img/SanPham/<?php echo $product->Img; ?>" class="img-thumbnail w-100" alt="<?php echo $product->Title; ?>">
                    </div>
                    <div class="col-md-6">
                        <h2 class="product-title"><?php echo $product->Title; ?></h2>
                        <p>Danh mục: <?php echo $product->CategoryTitle; ?></p>
                        <p class="product-price"><?php echo number_format($product->Price, 0, ",", "."); ?> VND</p>

                        <!-- Star Rating -->
                        <div class="star-rating mb-3">
                            <?php
                            $fullStars = floor($product->Rate);
                            $halfStar = ($product->Rate - $fullStars) >= 0.5 ? 1 : 0;
                            $emptyStars = 5 - $fullStars - $halfStar;
                            for ($i = 0; $i < $fullStars; $i++) echo '<i class="fa fa-star"></i>';
                            if ($halfStar) echo '<i class="fa fa-star-half-alt"></i>';
                            for ($i = 0; $i < $emptyStars; $i++) echo '<i class="fa fa-star-o"></i>';
                            ?>
                        </div>

                        <p><?php echo $product->Content; ?></p>

                        <!-- Quantity -->
                        <div class="input-group quantity mb-4" style="width:140px;">
                            <button class="btn btn-light border rounded-circle" id="decreaseQuantity"><i class="fa fa-minus"></i></button>
                            <input type="number" id="quantity" class="form-control text-center" value="1" min="1" step="1">
                            <button class="btn btn-light border rounded-circle" id="increaseQuantity"><i class="fa fa-plus"></i></button>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const quantityInput = document.getElementById('quantity');
                                const decreaseBtn = document.getElementById('decreaseQuantity');
                                const increaseBtn = document.getElementById('increaseQuantity');



                                function enforceInteger() {
                                    let value = parseFloat(quantityInput.value);
                                    if (isNaN(value) || value < 1) {
                                        quantityInput.value = 1;
                                    } else {
                                        quantityInput.value = Math.floor(value);
                                    }
                                }

                                // Decrease button
                                decreaseBtn.addEventListener('click', function() {
                                    let value = parseInt(quantityInput.value, 10);
                                    if (value > 1) {
                                        quantityInput.value = value - 0;
                                    }
                                });

                                // Increase button
                                increaseBtn.addEventListener('click', function() {
                                    let value = parseInt(quantityInput.value, 10);
                                    quantityInput.value = value + 0;
                                });
                            });
                        </script>

                        <form class="add-to-cart-form">
                            <input type="hidden" name="product_id" value="<?= $product->Id ?>">
                            <input type="hidden" name="quantity" id="hiddenQuantity" value="1"> <!-- thêm id này -->
                            <button type="button" class="btn btn-warning rounded-pill px-4 py-2 add_to_cart" data-id="<?= $product->Id ?>">
                                <i class="fa fa-shopping-bag me-2"></i>Thêm vào giỏ
                            </button>
                        </form>

                        <script>
                            function showTopToast(message, duration = 3000) {
                                const container = document.getElementById('topToastContainer');
                                const toast = document.createElement('div');
                                toast.className = 'top-toast';
                                toast.textContent = message;
                                container.appendChild(toast);

                                setTimeout(() => toast.classList.add('show'), 100);
                                setTimeout(() => {
                                    toast.classList.remove('show');
                                    setTimeout(() => container.removeChild(toast), 500);
                                }, duration);
                            }

                            const decreaseBtn = document.getElementById('decreaseQuantity');
                            const increaseBtn = document.getElementById('increaseQuantity');
                            const quantityInput = document.getElementById('quantity');
                            const hiddenInput = document.getElementById('hiddenQuantity');

                            decreaseBtn.addEventListener('click', () => {
                                let q = parseInt(quantityInput.value);
                                if (q > 1) quantityInput.value = q - 1;
                                hiddenInput.value = quantityInput.value;
                            });
                            increaseBtn.addEventListener('click', () => {
                                quantityInput.value = parseInt(quantityInput.value) + 1;
                                hiddenInput.value = quantityInput.value;
                            });
                            quantityInput.addEventListener('input', () => hiddenInput.value = quantityInput.value);

                            let selectedStore = <?= $selectedStore ?? 0 ?>;

                            document.querySelectorAll('.add_to_cart').forEach(btn => {
                                btn.addEventListener('click', () => {
                                    const productId = btn.dataset.id;
                                    const quantity = parseInt(document.getElementById('hiddenQuantity').value) || 1;
                                    const storeId = selectedStore;

                                    if (storeId == 0) {
                                        alert("Vui lòng chọn chi nhánh");
                                        return;
                                    }

                                    const xhr = new XMLHttpRequest();
                                    xhr.open('POST', 'add_to_cart.php', true);
                                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                                    xhr.onload = function() {
                                        try {
                                            const res = JSON.parse(this.responseText);
                                            if (res.success) {
                                                showTopToast(`Bạn đã thêm ${res.productName} vào giỏ!`);
                                                const cartCountElem = document.getElementById('cartCount');
                                                let currentCount = parseInt(cartCountElem.textContent) || 0;
                                                cartCountElem.textContent = currentCount + quantity;
                                            } else {
                                                showTopToast(res.message, 3000);
                                            }
                                        } catch (e) {
                                            showTopToast('Hãy vào giỏ hàng kiểm tra!', 3000);
                                            console.error(e);
                                        }
                                    };
                                    xhr.send(`action=add_to_cart&product_id=${productId}&store_id=${storeId}&quantity=${quantity}`);
                                });
                            });
                        </script>


                        <!-- Tabs -->
                        <ul class="nav nav-tabs mt-4" id="productTab" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#desc">Mô tả sản phẩm</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#review">Nhận xét</button>
                            </li>
                        </ul>
                        <div class="tab-content p-3 border">
                            <div class="tab-pane fade show active" id="desc">
                                <p><?php echo $product->Content; ?></p>
                            </div>
<div class="tab-pane fade" id="review">
                                <!-- Reviews List -->
                                <?php if (empty($reviews)): ?>
                                    <p>Chưa có nhận xét nào.</p>
                                <?php else: ?>
                                    <?php foreach ($reviews as $review): ?>
                                        <div class="review-item mb-4 p-3 border rounded">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="star-rating mb-1">
                                                    <?php
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $review->Rating) {
                                                            echo '<i class="fa fa-star text-warning"></i>';
                                                        } else {
                                                            echo '<i class="fa fa-star-o text-muted"></i>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($review->CreatedAt)); ?></small>
                                            </div>
                                            <h6 class="fw-bold"><?php echo htmlspecialchars($review->CustomerName); ?></h6>
                                            <p class="mb-0"><?php echo htmlspecialchars($review->Comment); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <!-- Review Form (Login Required) -->
                                <?php if (isset($_SESSION['CustomerId'])): ?>
                                    <div class="review-form mt-4 p-4 border rounded">
                                        <h5>Đánh giá sản phẩm</h5>
                                        <form id="reviewForm">
                                            <input type="hidden" name="store_product_id" value="<?php echo $storeProductId; ?>">
                                            <div class="mb-3">
                                                <label>Đánh giá của bạn:</label>
                                                <div class="star-rating-input" id="starRating">
                                                    <i class="fa fa-star rating-star" data-rating="1"></i>
                                                    <i class="fa fa-star rating-star" data-rating="2"></i>
                                                    <i class="fa fa-star rating-star" data-rating="3"></i>
                                                    <i class="fa fa-star rating-star" data-rating="4"></i>
                                                    <i class="fa fa-star rating-star" data-rating="5"></i>
                                                </div>
                                                <input type="hidden" name="rating" id="ratingValue" value="0">
                                            </div>
                                            <div class="mb-3">
                                                <textarea name="comment" class="form-control" rows="4" placeholder="Chia sẻ cảm nhận của bạn về sản phẩm..." required minlength="10"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-warning">Gửi đánh giá</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center mt-4 p-4 border rounded bg-light">
                                        <i class="fa fa-lock fa-2x text-muted mb-3"></i>
                                        <p class="mb-3">Vui lòng <a href="../customer/sign_in.php">đăng nhập</a> để đánh giá sản phẩm</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4 sidebar">
                <h5>Danh mục</h5>
                <ul>
                    <li>
                        <a href="index.php<?php echo $storeId > 0 ? '?store=' . $storeId : ''; ?>"
                            <?php echo $categoryId == 0 ? 'class="active-category"' : ''; ?>>
                            Tất cả danh mục
                        </a>
                    </li>
                    <?php foreach ($categories as $cat):
                        $c_Id = is_object($cat) ? $cat->Id : $cat['Id'];
                        $c_Title = is_object($cat) ? $cat->Title : $cat['Title'];
                    ?>
                        <li>
                            <a href="index.php?category=<?php echo $c_Id; ?><?php echo $storeId > 0 ? '&store=' . $storeId : ''; ?>"
                                <?php echo $categoryId == $c_Id ? 'style="color:#e67e22;font-weight:bold;"' : ''; ?>>
                                <?php echo htmlspecialchars($c_Title); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <h5 class="mt-4">Sản phẩm nổi bật</h5>
                <?php foreach ($featuredProducts as $fp): ?>
                    <a href="detail.php?id=<?php echo $fp->Id; ?>" class="d-flex mb-3 featured-product text-dark text-decoration-none">
                        <img src="../../img/SanPham/<?php echo $fp->Img; ?>" alt="<?php echo $fp->Title; ?>" width="60" height="60" style="object-fit: cover;">
                        <div class="ms-2">
                            <h6 class="mb-1"><?php echo $fp->Title; ?></h6>
                            <small><?php echo number_format($fp->Price, 0, ",", "."); ?> VND</small>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Related Products -->
            <h4 class="fw-bold mb-3 mt-5">Sản phẩm liên quan</h4>
            <div class="row g-5">
                <?php foreach ($relatedProducts as $rp): ?>
                    <div class="col-md-4 mb-4">
                        <a href="detail.php?id=<?php echo $rp->Id; ?>" class="text-decoration-none text-dark">
                            <div class="border p-2 related-product text-center">
                                <div style="width:100%;padding-top:100%;position:relative;overflow:hidden;border-radius:5px;">
                                    <img src="../../img/SanPham/<?php echo $rp->Img; ?>" alt="<?php echo $rp->Title; ?>"
                                        style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;">
                                </div>
                                <h6 class="mt-2"><?php echo $rp->Title; ?></h6>
                                <p class="text-warning fw-bold"><?php echo number_format($rp->Price, 0, ",", "."); ?> VND</p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php include '../footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Star rating interactive
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.rating-star');
            const ratingValue = document.getElementById('ratingValue');
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = this.dataset.rating;
                    ratingValue.value = rating;
                    stars.forEach(s => {
                        if (s.dataset.rating <= rating) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });
                
                star.addEventListener('mouseover', function() {
                    const rating = this.dataset.rating;
                    stars.forEach(s => {
                        if (s.dataset.rating <= rating) {
                            s.style.color = '#ffc107';
                        } else {
                            s.style.color = '#ddd';
                        }
                    });
                });
            });
            
            document.querySelectorAll('.star-rating-input').forEach(container => {
                container.addEventListener('mouseleave', function() {
                    const rating = ratingValue.value;
                    stars.forEach(s => {
                        if (s.dataset.rating <= rating && rating > 0) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });
            });
            
            // Review form submit
            const reviewForm = document.getElementById('reviewForm');
            if (reviewForm) {
                reviewForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    if (parseInt(ratingValue.value) === 0) {
                        alert('Vui lòng chọn số sao đánh giá');
                        return;
                    }
                    
                    const formData = new FormData(this);
                    
                    fetch('process_review.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showTopToast(data.message);
                            reviewForm.reset();
                            ratingValue.value = '0';
                            stars.forEach(s => s.classList.remove('active'));
                            // Reload page to show new review
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showTopToast(data.message, 4000);
                        }
                    })
                    .catch(error => {
                        showTopToast('Có lỗi xảy ra. Vui lòng thử lại!');
                        console.error('Error:', error);
                    });
                });
            }
        });
    </script>

</body>

</html>

