<footer class="footer">
    <style>
        .footer {
            background: #37474f;
            color: white;
            padding: 60px 80px 30px;
            font-size: 15px;
            margin-top: auto
        }

        .footer-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section {
            flex: 1;
            min-width: 200px;
        }

        .footer-section h3 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #ffb300;
        }

        .footer-section p,
        .footer-section a {
            color: white;
            text-decoration: none;
            margin-bottom: 10px;
            display: block;
        }

        .footer-section a:hover {
            color: #ffb300;
        }

        .footer-social {
            display: flex;
            gap: 15px;
        }

        .footer-social i {
            font-size: 24px;
            transition: 0.3s;
        }

        .footer-social i:hover {
            color: #ffb300;
        }

        .footer-bottom {
            text-align: center;
            border-top: 1px solid #455a64;
            padding-top: 20px;
            font-size: 14px;
        }

        @media(max-width: 768px) {
            .footer-container {
                flex-direction: column;
                gap: 30px;
            }
        }
    </style>

    <div class="footer-container">
        <div class="footer-section">
            <h3>TẬP ĐOÀN TRUNG NGUYÊN LEGEND</h3>
            <p>82-84 Bùi Thị Xuân, P. Bến Thành, Q.1, Tp Hồ Chí Minh</p>
            <p>Hotline: 1900 6011</p>
            <p>Tel: (84.28) 39251852</p>
            <p>Fax: (84.28) 39251848</p>
            <br>
            <h4>© 2026 TẬP ĐOÀN TRUNG NGUYÊN LEGEND.</h4>
        </div>
        <div class="footer-section">
            <h3>LIÊN KẾT NHANH</h3>
            <a href="../home/index.php">Trang chủ</a>
            <a href="../product/index.php">Cửa hàng</a>
            <a href="../contact/index.php">Liên hệ</a>
        </div>
        <div class="footer-section">
            <h3>LIÊN HỆ</h3>
            <p><i class="fas fa-phone-alt"></i> +84 123 456 789</p>
            <p><i class="fas fa-envelope"></i> contact@trungnguyencoffee.com</p>
            <div class="footer-social">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        &copy; 2026 Trung Nguyên Cà Phê. All Rights Reserved.
    </div>
</footer>