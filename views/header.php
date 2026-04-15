<?php
    $customerId = isset($_SESSION['CustomerId']) ? $_SESSION['CustomerId'] : null;
    $customerName = isset($_SESSION['CustomerName']) ? $_SESSION['CustomerName'] : null;
    $role = 0;

    if ($customerId) {
        require_once '../../controllers/CartController.php';
        require_once '../../controllers/CustomerController.php';

        $cartController = new CartController();
        $customerController = new CustomerController();
        $totalCartItems = $cartController->getTotal($customerId);
        $role = $customerController->getCustomerById($customerId)->Role;
    } else {
        $totalCartItems = 0;
    }
?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .top-bar {
        background-color: #37474f;
        color: white;
        padding: 20px 60px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 14px;
        border-radius: 0 0 30px 30px;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1000;
    }

    .top-bar .left {
        display: flex;
        gap: 25px;
    }

    .top-bar .left span {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .top-bar .right a {
        color: white;
        text-decoration: none;
        padding: 5px 12px;
        background-color: #455a64;
        border-radius: 25px;
        font-weight: 500;
    }

    .top-bar .right a:hover {
        background-color: #546e7a;
    }

    .menu {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 80px;
        background-color: white;
        position: fixed;
        top: 59px;
        left: 0;
        width: 100%;
        z-index: 999;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .menu-nav a {
        margin-right: 35px;
        text-decoration: none;
        color: #37474f;
        font-weight: 600;
        font-size: 20px;
        transition: color 0.3s, transform 0.3s;
        display: inline-block;
    }

    .menu-nav a:hover {
        color: #ffb300;
        transform: scale(1.1);
    }

    .menu-nav a.dropdown::after {
        content: " ▼";
        font-size: 10px;
    }

    .logo img {
        height: 65px;
    }

    .menu-icons {
        display: flex;
        align-items: center;
        gap: 22px;
        font-size: 23px;
        color: #37474f;
    }

    .menu-icons a {
        color: #333;
        text-decoration: none;
        font-size: 20px;
    }

    .menu-icons a:hover {
        color: brown;
    }


    .menu-icons .cart {
        position: relative;
    }

    .menu-icons .cart sup {
        position: absolute;
        top: -8px;
        right: -10px;
        background: #ffb300;
        color: white;
        font-size: 12px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    @media(max-width:992px) {
        .top-bar {
            padding: 10px 30px;
            border-radius: 0 0 20px 20px;
            font-size: 12px;
        }

        .menu {
            padding: 20px 40px;
            flex-direction: column;
            gap: 20px;
        }

        .menu-nav a {
            font-size: 18px;
            margin-right: 20px;
        }
    }

    @media(max-width:576px) {
        .menu-nav a {
            font-size: 16px;
            margin-right: 15px;
        }

        .logo img {
            height: 50px;
        }
    }

    .dropdown-menu {
        position: relative;
        display: inline-block;
        padding-bottom: 10px;
    }

    .dropdown-btn {
        cursor: pointer;
        font-weight: 600;
        font-size: 20px;
        color: #37474f;
        display: inline-block;
    }

    .dropdown-btn:hover {
        color: #ffb300;
    }

    .dropdown-list {
        list-style: none;
        position: absolute;
        top: 28px;
        left: 0;
        background: white;
        padding: 12px 0;
        width: 220px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: none;
        z-index: 9999;
    }

    .dropdown-menu:hover .dropdown-list {
        display: block;
    }

    .dropdown-list li a {
        display: block;
        padding: 10px 18px;
        font-size: 17px;
        color: #37474f;
        text-decoration: none;
    }

    .dropdown-list li a:hover {
        background-color: #ffb300;
        color: white;
    }

    .dropdown-menu,
    .dropdown-menu a,
    .dropdown-menu div,
    .dropdown-btn {
        border: none !important;
        box-shadow: none !important;
    }
</style>

<header>

    <div class="top-bar">
        <div class="left">
            <span><i class="fas fa-map-marker-alt"></i> Trung Nguyên Coffee</span>
            <span><i class="fas fa-envelope"></i> contact@trungnguyencoffee.com</span>
        </div>
        <?php if ($customerId): ?>
            <div class="right">
                <a href="../customer/profile.php">Xin chào, <?php echo htmlspecialchars($customerName); ?></a>
                <a href="../customer/log_out.php">Đăng xuất</a>
            </div>
        <?php else: ?>
            <div class="right">
                <a href="../customer/sign_in.php">Đăng nhập</a> / <a href="../customer/sign_up.php">Đăng ký</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="menu">
        <div class="logo">
            <img src="../../img/Logo/logo.jpg" alt="Trung Nguyên Legend">
        </div>
        <div class="menu-nav">
            <a href="../home/index.php">Trang chủ</a>
            <a href="../product/index.php">Cửa hàng</a>

            <div class="dropdown-menu">
                <a class="dropdown-btn">Danh mục</a>

                <ul class="dropdown-list">
                    <?php
                        include '../../controllers/CategoryController.php';
                        $categoryController = new CategoryController();
                        $categories = $categoryController->getAllCategories();
                    ?>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <?php
                                $c_Id = is_object($cat) ? $cat->Id : (isset($cat['Id']) ? $cat['Id'] : 0);
                                $c_Title = is_object($cat) ? $cat->Title : (isset($cat['Title']) ? $cat['Title'] : '');
                            ?>
                            <li><a href="../product/index.php?category=<?php echo $c_Id; ?>">
                                <?php echo htmlspecialchars($c_Title); ?>
                            </a></li>
                        <?php endforeach; ?>
                        <?php else: ?>
                            <span class="dropdown-item disabled">Không có loại sản phẩm</span>
                        <?php endif; ?>
                </ul>
            </div>

            <a href="../contact/index.php">Liên hệ</a>
            <?php if($role): ?>
                <a href="../../admin/views/index.php">Trang quản trị</a>
            <?php endif; ?>
        </div>

        <div class="menu-icons">
            <a href="../contact/index.php"><i class="fas fa-phone-alt"></i></a>
            <?php if($customerId): ?>
                <a href="../cart/index.php" class="cart">
                    <i class="fas fa-shopping-cart"></i><sup><?php echo $totalCartItems; ?></sup>
                </a>
            <?php else: ?>
                <a href="../customer/sign_in.php" class="cart">
                    <i class="fas fa-shopping-cart"></i><sup><?php echo $totalCartItems; ?></sup>
                </a>
            <?php endif; ?>
            <?php if($customerId): ?>
                <a href="../customer/profile.php">
                    <i class="fas fa-user"></i>
                </a>
            <?php else: ?>
                <a href="../customer/sign_in.php">
                    <i class="fas fa-user"></i>
                </a>
            <?php endif; ?>
        </div>

    </div>

</header>