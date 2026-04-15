<?php
session_start();
include '../header.php';

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ - Trung Nguyên Cà Phê</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fff1e0;
            color: #333;
            padding-top: 125px;
        }


        .page-header {
            position: relative;
            background-image: url('https://mmvietnam.com/wp-content/uploads/2020/12/lien-he-new-scaled.jpg');
            background-size: cover;
            background-position: center;
            height: 300px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
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
            color: white;
        }

        .page-header h1 {
            font-size: 48px;
            font-weight: 800;
            letter-spacing: 2px;
            margin-bottom: 15px;
        }

        .breadcrumb {
            list-style: none;
            display: flex;
            gap: 5px;
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
            font-size: 18px;
        }

        .breadcrumb a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
            font-weight: 400;
        }

        .breadcrumb a:hover {
            color: orange;
        }

        .breadcrumb .active {
            color: yellow;
            font-weight: bold;
        }

        .breadcrumb span.separator {
            color: white;
        }


        /* CONTACT FORM & INFO */
        .contact .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .contact .bg-light {
            padding: 40px;
            border-radius: 15px;
        }

        .contact h1 {
            color: #007bff;
            margin-bottom: 20px;
        }

        .contact p {
            margin-bottom: 30px;
        }

        .contact form input,
        .contact form textarea {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .contact form button {
            width: 100%;
            padding: 12px;
            background: #fff;
            color: #007bff;
            border: 1px solid #ccc;
            cursor: pointer;
            font-weight: bold;
        }

        .contact form button:hover {
            background: #007bff;
            color: #fff;
        }

        .contact .info-box {
            display: flex;
            align-items: flex-start;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .contact .info-box i {
            font-size: 28px;
            color: #007bff;
            margin-right: 15px;
        }

        .contact .info-box h4 {
            margin-bottom: 10px;
        }

        .contact iframe {
            width: 100%;
            height: 550px;
            border: none;
            border-radius: 10px;
        }



        .breadcrumb-item+.breadcrumb-item::before {
            content: "/";
            font-weight: bold;
            /* dấu / */
            color: blue;
            /* màu bạn muốn */
        }

        @media(max-width:992px) {
            .contact .row {
                flex-direction: column;
            }

            .contact iframe {
                height: 400px;
                margin-bottom: 30px;
            }
        }
    </style>
</head>

<body>
    <!-- PAGE HEADER -->
    <div class="container-fluid page-header py-4 bg-light">
        <h1 class="display-6 fw-bold font-monospace">Liên hệ</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../home/index.php">Trang chủ</a></li>
                <span class="separator">/</span>

                <li class="breadcrumb-item"><a href="../product/index.php">Cửa hàng</a></li>
                <span class="separator">/</span>

                <li class="breadcrumb-item active" aria-current="page">Liên hệ</li>
            </ol>
        </nav>
    </div>

    <!-- CONTACT -->
    <div class="contact">
        <div class="container">
            <div class="bg-light" style="padding:50px; border-radius:15px;">
                <div class="row">
                    <div class="col-12 text-center mb-4">
                        <h1>Liên hệ với chúng tôi</h1>
                        <p>Để biết thêm thông tin chi tiết về các sản phẩm và dịch vụ tại Trung Nguyên Cà phê, hãy để lại tin nhắn hoặc ghé thăm trực tiếp chúng tôi.</p>
                    </div>

                    <div class="col-lg-12 mb-4">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.482965274824!2d106.69859061526002!3d10.777529292331067!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3175272e5e8b1a3d%3A0xe0db62178d885ea6!2zODIgQi4gVGhpIFh1w6JuLCBQLiBC4bqjaSBUaMawbmgsIFF1YW5nIDEsIFRow6BjaCBIb8OgIE1pbmggQ2l0eQ!5e0!3m2!1sen!2s!4v1699999999999!5m2!1sen!2s"
                            allowfullscreen="" loading="lazy" style="border-radius:10px;">
                        </iframe>
                    </div>

                    <div class="col-lg-7 mb-4" style='margin-bottom: 20px;'>
                        <form action="" method="post" style="background:#fff; padding:30px; border-radius:10px; box-shadow:0 4px 15px rgba(0,0,0,0.1);">
                            <input type="text" name="name" placeholder="Tên của bạn" required style="margin-bottom:20px; padding:12px; border-radius:5px; border:1px solid #ccc;">
                            <input type="email" name="email" placeholder="Email của bạn" required style="margin-bottom:20px; padding:12px; border-radius:5px; border:1px solid #ccc;">
                            <textarea name="message" rows="5" placeholder="Tin nhắn của bạn" required style="margin-bottom:20px; padding:12px; border-radius:5px; border:1px solid #ccc;"></textarea>
                            <button type="submit" style="width:100%; padding:12px; background:#007bff; color:#fff; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">Gửi</button>
                            <!-- xử lý nút gửi -->
                        </form>
                    </div>

                    <div class="col-lg-5">
                        <div class="info-box" style="display:flex; align-items:flex-start; gap:15px; background:#fff; padding:20px; border-radius:10px; margin-bottom:20px; box-shadow:0 2px 10px rgba(0,0,0,0.05);">
                            <i class="fas fa-map-marker-alt" style="font-size:28px; color:#007bff;"></i>
                            <div>
                                <h4>Địa chỉ</h4>
                                <p>82-84 Bùi Thị Xuân, P. Bến Thành, Q.1, Tp Hồ Chí Minh</p>
                            </div>
                        </div>
                        <div class="info-box" style="display:flex; align-items:flex-start; gap:15px; background:#fff; padding:20px; border-radius:10px; margin-bottom:20px; box-shadow:0 2px 10px rgba(0,0,0,0.05);">
                            <i class="fas fa-envelope" style="font-size:28px; color:#007bff;"></i>
                            <div>
                                <h4>Email</h4>
                                <p>contact@trungnguyencoffee.com</p>
                            </div>
                        </div>
                        <div class="info-box" style="display:flex; align-items:flex-start; gap:15px; background:#fff; padding:20px; border-radius:10px; margin-bottom:20px; box-shadow:0 2px 10px rgba(0,0,0,0.05);">
                            <i class="fas fa-phone-alt" style="font-size:28px; color:#007bff;"></i>
                            <div>
                                <h4>Điện thoại</h4>
                                <p>(+84) 58 1234 5678</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <?php include '../footer.php'; ?>
</body>

</html>