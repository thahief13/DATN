<?php session_start(); ?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Trang chủ - Trung Nguyên Cà Phê</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-color: #fff1e0;
            padding-top: 150px;
            font-family: 'Segoe UI', sans-serif;
        }

        .hero {
            text-align: center;
            padding: 90px 20px 10px;
        }

        .hero h1 {
            font-size: 62px;
            font-weight: 800;
            color: #37474f;
            letter-spacing: 3px;
            margin-bottom: 10px;
        }

        .hero h2 {
            font-size: 26px;
            color: #ffb300;
            font-weight: 600;
            letter-spacing: 4px;
            margin-bottom: 40px;
        }

        .hero p {
            max-width: 960px;
            margin: 0 auto;
            font-size: 16px;
            color: #444;
            line-height: 1.8;
        }

        .banner-slider {
            max-width: 1200px;
            height: 600px;
            margin: 40px auto 100px;
            overflow: hidden;
            border-radius: 18px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            position: relative;
        }

        .slider-container {
            display: flex;
            height: 100%;
            transition: transform 0.5s ease-in-out;
        }

        .slide {
            min-width: 100%;
            height: 100%;
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .prev,
        .next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            padding: 15px;
            font-size: 24px;
            border-radius: 50%;
            cursor: pointer;
            transition: 0.3s;
            z-index: 20;
        }

        .prev:hover,
        .next:hover {
            background: rgba(0, 0, 0, 0.8);
        }

        .prev {
            left: 20px;
        }

        .next {
            right: 20px;
        }

        @media (max-width: 768px) {
            .banner-slider {
                height: 300px;
            }

            .hero h1 {
                font-size: 42px;
            }

            .hero h2 {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <?php include "../header.php"; ?>

    <section class="hero">
        <h1>CHUỖI CỬA HÀNG TRUNG NGUYÊN CÀ PHÊ</h1>
        <h2>CÀ PHÊ NĂNG LƯỢNG - CÀ PHÊ ĐỔI ĐỜI</h2>

        <p>
            Trung Nguyên Cà Phê là thương hiệu cà phê hàng đầu Việt Nam, nổi tiếng với sứ mệnh lan tỏa
            văn hóa thưởng thức cà phê Việt đến toàn cầu. Được thành lập vào năm 1996, Trung Nguyên
            cung cấp sản phẩm chất lượng cao với hương vị đậm đà đặc trưng.
            <br><br>
            Chuỗi hệ thống có hơn 1.000 cửa hàng trên toàn quốc, mang đến trải nghiệm cà phê chuẩn mực
            dành cho mọi khách hàng yêu thích văn hóa cà phê Việt.
        </p>
    </section>

    <div class="banner-slider">
        <button class="prev">&#10094;</button>
        <button class="next">&#10095;</button>

        <div class="slider-container">
            <div class="slide"><img src="../../img/ChiNhanh/chinhanh1.jpg"></div>
            <div class="slide"><img src="../../img/ChiNhanh/chinhanh2.jpg"></div>
            <div class="slide"><img src="../../img/ChiNhanh/chinhanh3.jpg"></div>
            <div class="slide"><img src="../../img/ChiNhanh/chinhanh4.jpg"></div>
            <div class="slide"><img src="../../img/ChiNhanh/chinhanh5.jpg"></div>
            <div class="slide"><img src="../../img/ChiNhanh/chinhanh6.jpg"></div>
        </div>
    </div>

   <script>
        const slider = document.querySelector('.slider-container');
        const slides = document.querySelectorAll('.slide');
        const prevBtn = document.querySelector('.prev');
        const nextBtn = document.querySelector('.next');

        const originalTotal = slides.length;

        // Clone first và last
        const firstClone = slides[0].cloneNode(true);
        const lastClone = slides[originalTotal - 1].cloneNode(true);

        slider.appendChild(firstClone);
        slider.insertBefore(lastClone, slider.firstChild);

        // Lấy lại danh sách slide sau khi clone
        const updatedSlides = document.querySelectorAll('.slide');
        const totalSlides = updatedSlides.length;

        let currentIndex = 1; 
        let isTransitioning = false; // BỔ SUNG: Biến khóa để ngăn lỗi click liên tục
        
        slider.style.transform = `translateX(-${currentIndex * 100}%)`;

        let interval = setInterval(() => moveSlide(1), 5000);

        function moveSlide(direction) {
            // BỔ SUNG: Nếu đang chạy hiệu ứng thì không nhận lệnh mới
            if (isTransitioning) return; 
            isTransitioning = true;

            currentIndex += direction;
            slider.style.transition = 'transform 0.5s ease-in-out';
            slider.style.transform = `translateX(-${currentIndex * 100}%)`;
        }

        // Khi chuyển slide xong, reset position nếu cần
        slider.addEventListener('transitionend', () => {
            isTransitioning = false; // Mở khóa

            // BỔ SUNG: Dùng <= và >= thay cho === để bắt lỗi vượt quá giới hạn an toàn
            if (currentIndex <= 0) { 
                slider.style.transition = 'none';
                currentIndex = originalTotal;
                slider.style.transform = `translateX(-${currentIndex * 100}%)`;
            } else if (currentIndex >= totalSlides - 1) { 
                slider.style.transition = 'none';
                currentIndex = 1;
                slider.style.transform = `translateX(-${currentIndex * 100}%)`;
            }
        });

        prevBtn.addEventListener('click', () => {
            moveSlide(-1);
            resetInterval();
        });
        nextBtn.addEventListener('click', () => {
            moveSlide(1);
            resetInterval();
        });

        function resetInterval() {
            clearInterval(interval);
            interval = setInterval(() => moveSlide(1), 5000);
        }

        // BỔ SUNG QUAN TRỌNG: Dừng tự động lướt khi người dùng chuyển sang tab khác
        document.addEventListener("visibilitychange", function() {
            if (document.hidden) {
                clearInterval(interval);
            } else {
                resetInterval();
            }
        });
    </script>


    <?php 
    // Featured Products Section - Use absolute paths from root
    require_once $_SERVER['DOCUMENT_ROOT'] . '/app/controllers/ProductController.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/Product.php';
    $productController = new ProductController();
    $featuredProducts = $productController->getFeaturedProducts(0, 8); // All stores, top 8 by rate
    ?>

    <!-- Sản phẩm nổi bật -->
    <section class="featured-products" style="max-width: 1200px; margin: 60px auto; padding: 0 20px;">
        <div class="section-header" style="text-align: center; margin-bottom: 40px;">
            <h2 style="font-size: 36px; font-weight: 800; color: #37474f; margin-bottom: 10px;">SẢN PHẨM NỔI BẬT</h2>
            <p style="font-size: 18px; color: #ffb300; font-weight: 600;">Khám phá những sản phẩm được yêu thích nhất</p>
        </div>
        <div class="products-grid">
            <?php if (!empty($featuredProducts)): ?>
                <?php foreach ($featuredProducts as $product): ?>
                    <div class="card" style="background: white; border-radius: 15px; box-shadow: 0 6px 12px rgba(0,0,0,0.1); overflow: hidden; transition: transform 0.3s; max-width: 280px; margin: 0 auto;">
                        <img src="../../img/SanPham/<?php echo htmlspecialchars($product->Img); ?>" alt="<?php echo htmlspecialchars($product->Title); ?>" style="width: 100%; height: 220px; object-fit: cover;">


                        <div class="card-body" style="padding: 20px;">
                            <h4 style="font-weight: 700; font-size: 18px; margin-bottom: 10px; color: #37474f;"><?php echo htmlspecialchars($product->Title); ?></h4>
                            <p style="font-size: 14px; color: #666; margin-bottom: 15px; height: 3.6em; overflow: hidden;"><?php echo htmlspecialchars(substr($product->Content, 0, 100)); ?>...</p>
                            <div style="font-weight: 700; font-size: 20px; color: #ffb300; margin-bottom: 15px;"><?php echo number_format($product->Price, 0, ',', '.'); ?> ₫</div>
                            <button class="btn add_to_cart" data-id="<?php echo $product->Id; ?>" style="font-size: 14px; border-radius: 25px; padding: 12px 24px; background: #ffb300; color: white; border: none; cursor: pointer; width: 100%; transition: background 0.3s;">Thêm vào giỏ</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; color: #666; grid-column: 1/-1;">Chưa có sản phẩm nổi bật.</p>
            <?php endif; ?>
        </div>
        <style>
            .featured-products .products-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
                gap: 30px;
                justify-items: center;
            }
            .featured-products .card:hover {
                transform: translateY(-8px);
                box-shadow: 0 12px 24px rgba(0,0,0,0.15);
            }
            .featured-products .card img:hover {
                transform: scale(1.05);
            }
            @media (max-width: 768px) {
                .featured-products .products-grid {
                    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                }
            }
        </style>
    </section>

    <div id="topToastContainer" style="position: fixed; top: 0; left: 50%; transform: translateX(-50%); z-index: 9999;"></div>

    <script>
        // Add to cart functionality (requires store selection - redirect to product page if no store)
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('add_to_cart')) {
                const productId = e.target.dataset.id;
                alert('Vui lòng chọn chi nhánh tại trang sản phẩm để thêm vào giỏ!');
window.location.href = 'http://localhost/app/views/product/index.php';
                return;
            }
        });



        // Optional: Later integrate store selector from product/index.php
    </script>

    <?php include "../footer.php"; ?>

</body>

</html>
