<?php
    session_start();
    require_once '../../controllers/CustomerController.php';
    $customerController = new CustomerController();
    if($customerController->getCustomerById($_SESSION['CustomerId'])->Role){
        $adminId = $_SESSION['CustomerId'];
        $adminName = $_SESSION['CustomerName']; 
        $title = "Trang Quản Trị - Trung Nguyên Coffee";
        $page = $_GET['page'] ?? 'dashboard';
        $allowed_pages = ['dashboard', 'category', 'product', 'store', 'employee', 'role', 'customer', 'payment', 'revenue',];
        if (!in_array($page, $allowed_pages)) {
            $page = 'dashboard';
        }

        // Load category data if page is category
        $categoriesJson = '[]';
        if ($page === 'category') {
            require_once '../controllers/CategoryAdminController.php';
            $categoryAdminController = new CategoryAdminController();
            $categoryAdmins = $categoryAdminController->getAllCategories();
            $categoriesJson = json_encode($categoryAdmins, JSON_UNESCAPED_UNICODE);
        }
    }
    else{
        header('Location: ../../views/home/index.php');
        exit();
    }
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .vertical-navbar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background: #333;
            padding-top: 20px;
            transition: width 0.3s;
            overflow: hidden;
            z-index: 1000;
            overflow-y: auto;
        }

        .vertical-navbar.collapsed {
            width: 70px;
        }

        .vertical-navbar a {
            padding: 14px 20px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #444;
        }

        .vertical-navbar a i {
            margin-right: 10px;
            width: 25px;
            text-align: center;
        }

        .vertical-navbar a:hover {
            background: #575757;
        }

        .navbar-logo {
            max-width: 80%;
            height: auto;
            margin-bottom: 10px;
        }

        .navbar-header {
            color: #ffc107;
            text-align: center;
        }

        .vertical-navbar.collapsed a span,
        .vertical-navbar.collapsed .navbar-title {
            display: none;
        }

        /* Container content */
        .container-wrapper {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s;
        }

        .container-wrapper.collapsed {
            margin-left: 70px;
        }

        /* Table style */
        .table-wrapper {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .table thead {
            background: #343a40;
            color: #fff;
        }

        .table-hover tbody tr:hover {
            background: #f1f1f1;
        }

        .btn i {
            margin-right: 5px;
        }

        .btn-add {
            margin-bottom: 15px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #343a40;
            font-weight: 700;
        }

        .pagination .btn {
            min-width: 40px;
        }

        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
            }
        }

        /* Product specific styles */
        td img {
            width: 100px;
            height: 60px;
            object-fit: contain;
        }

        td span.content-clamp {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.5rem;
            height: 4.5rem;
        }

        .modal-backdrop-white {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1050;
            width: 100vw;
            height: 100vh;
            background-color: #ffffff;
            opacity: 0.8;
            transition: opacity 0.15s linear;
        }

        .navbar-toggler {
            display: block;
            width: 40px;
            height: 40px;
            border-radius: 5px;
            color: wheat;
        }

        @media (max-width: 991.98px) {
            .vertical-navbar {
                width: 70px;
            }

            .vertical-navbar a span,
            .vertical-navbar .navbar-title {
                display: none;
            }

            .container-wrapper {
                margin-left: 70px;
            }
        }
    </style>
</head>

<body>
    <nav class="vertical-navbar">
        <button class="btn btn-dark navbar-toggler"><i class="fas fa-bars"></i></button>

        <div class="navbar-header mb-3">
            <img src="../../img/Logo/logo.jpg" alt="Logo" class="navbar-logo">
            <div class="navbar-title fw-bolder">Xin chào, <?php echo $adminName ?></div>
        </div>
        <a href="../../views/home/index.php" class="no-ajax"><i class="fas fa-arrow-left"></i></i><span> Trang khách</span></a>
        <a href="?page=dashboard"><i class="fas fa-home"></i><span> Trang quản trị</span></a>
        <a href="?page=category"><i class="fas fa-list-alt"></i><span> Quản lý danh mục</span></a>
        <a href="?page=product"><i class="fas fa-glass-martini-alt"></i><span> Quản lý sản phẩm</span></a>
        <a href="?page=store"><i class="fas fa-store"></i><span> Quản lý cửa hàng</span></a>
        <a href="?page=employee"><i class="fas fa-users"></i><span> Quản lý nhân viên</span></a>
        <a href="?page=role"><i class="fas fa-user-tag"></i></i><span> Quản lý chức vụ</span></a>
        <a href="?page=customer"><i class="fas fa-user-tie"></i><span> Quản lý khách hàng</span></a>
        <a href="?page=payment"><i class="fas fa-file-invoice"></i><span> Quản lý đơn hàng</span></a>
        <a href="?page=revenue"><i class="fas fa-chart-line"></i><span> Quản lý doanh thu</span></a>
        
        
    </nav>

    <div class="container-wrapper">
        <main role="main" class="pb-3" id="main-content">
<?php 
// Check session messages
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
    echo $_SESSION['success_message'];
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
    echo $_SESSION['error_message'];
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
    unset($_SESSION['error_message']);
}
include $page . '/index.php'; 
?>

        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const navbar = document.querySelector('.vertical-navbar');
        const container = document.querySelector('.container-wrapper');
        const toggleBtn = document.querySelector('.navbar-toggler');

        toggleBtn.addEventListener('click', () => {
            navbar.classList.toggle('collapsed');
            container.classList.toggle('collapsed');
        });

        function checkWindowSize() {
            if (window.innerWidth < 992) {
                navbar.classList.add('collapsed');
                container.classList.add('collapsed');
            } else {
                navbar.classList.remove('collapsed');
                container.classList.remove('collapsed');
            }
        }

        window.addEventListener('resize', checkWindowSize);
        checkWindowSize();

        // --------- AJAX Load Page ---------
        function loadPage(page) {
            $.ajax({
                url: page + '/index.php',
                method: 'GET',
                success: function(data) {
                    $('#main-content').html(data);
                    if (page === 'category') {
                        initCategoryPage();
                    }
                },
                error: function() {
                    $('#main-content').html('<p class="text-danger">Không thể load dữ liệu.</p>');
                }
            });
        }

        function initCategoryPage() {
            const categoriesTextarea = document.getElementById('categoriesDataJson');
            if (!categoriesTextarea) {
                return;
            }

            let categoriesData = [];
            try {
                categoriesData = JSON.parse(categoriesTextarea.value || '[]');
            } catch (error) {
                console.error('Lỗi phân tích JSON danh mục:', error);
            }

            function formatDate(dateString) {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) {
                    return '';
                }
                return date.toLocaleDateString('vi-VN', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            }

            function findCategory(id) {
                return categoriesData.find(c => String(c.Id) === String(id));
            }

            function openCategoryModal(button, modalId) {
                const categoryId = button.getAttribute('data-bs-id');
                const category = findCategory(categoryId);
                if (!category) {
                    console.error('Không tìm thấy danh mục với ID:', categoryId);
                    return;
                }

                if (modalId === 'viewModal') {
                    document.getElementById('view-id').innerText = category.Id;
                    document.getElementById('view-title').innerText = category.Title;
                    document.getElementById('view-content').innerText = category.Content;
                    document.getElementById('view-create-at').innerText = formatDate(category.CreateAt);
                    document.getElementById('view-update-at').innerText = formatDate(category.UpdateAt);
                }

                if (modalId === 'editModal') {
                    document.getElementById('edit-category-id').value = category.Id;
                    document.getElementById('edit-title').value = category.Title;
                    document.getElementById('edit-content').value = category.Content;
                }

                if (modalId === 'deleteModal') {
                    document.getElementById('delete-category-id-display').innerText = category.Id;
                    document.getElementById('delete-category-title-display').innerText = category.Title;
                    document.getElementById('confirmDeleteLink').href = '/app/admin/views/category/process_delete_category.php?categoryId=' + category.Id;
                }
            }

            document.querySelectorAll('button[data-bs-target="#viewModal"]').forEach(button => {
                button.addEventListener('click', function() {
                    openCategoryModal(this, 'viewModal');
                });
            });

            document.querySelectorAll('button[data-bs-target="#editModal"]').forEach(button => {
                button.addEventListener('click', function() {
                    openCategoryModal(this, 'editModal');
                });
            });

            document.querySelectorAll('button[data-bs-target="#deleteModal"]').forEach(button => {
                button.addEventListener('click', function() {
                    openCategoryModal(this, 'deleteModal');
                });
            });

            const confirmDeleteLink = document.getElementById('confirmDeleteLink');
            if (confirmDeleteLink) {
                confirmDeleteLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    const categoryId = this.href.split('categoryId=')[1];
                    if (!categoryId) {
                        return;
                    }

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/app/admin/views/category/process_delete_category.php';

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'categoryId';
                    input.value = categoryId;
                    form.appendChild(input);

                    document.body.appendChild(form);
                    form.submit();
                });
            }

            ['#createModal form', '#editModal form'].forEach(formSelector => {
                const form = document.querySelector(formSelector);
                if (form) {
                    form.addEventListener('submit', function() {
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    });
                }
            });
        }

        // Click menu
        $('.vertical-navbar a').click(function(e) {
            if ($(this).hasClass('no-ajax')) {
                // cho phép link load bình thường
                return;
            }

            e.preventDefault();
            let url = $(this).attr('href');
            let page = url.split('=')[1];

            // Load nội dung
            loadPage(page);

            // Cập nhật URL mà không reload trang
            history.pushState({
                page: page
            }, '', '?page=' + page);
        });

        if ('<?php echo $page; ?>' === 'category') {
            initCategoryPage();
        }

    </script>
</body>

</html>