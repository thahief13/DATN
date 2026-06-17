<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['CustomerId'])) {
    header('Location: ../../../views/home/index.php');
    exit();
}

require_once __DIR__ . '/../../../controllers/CustomerController.php';
require_once __DIR__ . '/../../controllers/ProductAdminController.php';

$customerController = new CustomerController();
$customer = $customerController->getCustomerById($_SESSION['CustomerId']);

if (!$customer || !$customer->Role) {
    header('Location: ../../../views/home/index.php');
    exit();
}
$success_msg = $_SESSION['success_message'] ?? '';
$error_msg = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
// LẤY thông tin TỪ URL
$selectedStore = isset($_GET['store_id']) ? (int)$_GET['store_id'] : 0;
$searchKeyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

$productAdminController = new ProductAdminController();
// GỌI HÀM CÓ TRUYỀN KEYWORD ĐỂ LỌC BẰNG SQL
$allProductAdmins = $productAdminController->getAllProducts(0, $selectedStore, $searchKeyword);
$stores = $productAdminController->getAllStores();

// phân trang
$itemsPerPage = 15;
$currentPage = isset($_GET['product_page']) ? (int)$_GET['product_page'] : 1;
if ($currentPage < 1) $currentPage = 1;

$totalItems = count($allProductAdmins);
$totalPages = ceil($totalItems / $itemsPerPage);
if ($currentPage > $totalPages && $totalPages > 0) $currentPage = $totalPages;

$startIndex = ($currentPage - 1) * $itemsPerPage;
$productAdmins = array_slice($allProductAdmins, $startIndex, $itemsPerPage);

$productsJson = json_encode($allProductAdmins, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?: '[]';

global $hostname, $username, $password, $dbname, $port;
$dbAuth = new mysqli($hostname, $username, $password, $dbname, $port);
$authQuery = "SELECT Role, StoreId FROM customer WHERE Id = " . (int)$_SESSION['CustomerId'];
$authResult = $dbAuth->query($authQuery);
$authData = $authResult->fetch_assoc();

$userRole = (int)($authData['Role'] ?? 1);
$userStoreId = (int)($authData['StoreId'] ?? 0);
$dbAuth->close();
?>

<link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet" />
<style>
    .choices__inner { border-radius: 0.375rem !important; border: 1px solid #ced4da !important; background-color: #fff !important; min-height: 38px !important; }
    .choices__list--multiple .choices__item { background-color: #198754 !important; border: 1px solid #146c43 !important; border-radius: 4px !important; }
</style>

<div class="container my-5">
    <?php if ($success_msg): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($success_msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> <?= htmlspecialchars($error_msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <h1>Quản lý sản phẩm</h1>

    <div class="table-wrapper">
        <div class="card shadow-sm mb-4">
            <div class="card-body py-3">
                <form method="GET" action="" class="row gx-3 gy-2 align-items-center">
                    <input type="hidden" name="page" value="product">
                    
                    <div class="col-auto">
                        <button type="button" class="btn btn-success btn-add" data-bs-toggle="modal" data-bs-target="#createModal">
                            <i class="fa fa-plus me-1"></i> Thêm sản phẩm
                        </button>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label mb-1">Chọn cửa hàng</label>
                        <select name="store_id" class="form-select" onchange="this.form.submit()" <?= ($userRole == 2) ? 'disabled' : '' ?>>
                            <?php if ($userRole != 2): ?>
                                <option value="0">Tất cả cửa hàng</option>
                            <?php endif; ?>

                            <?php foreach ($stores as $store): ?>
                                <?php 
                                    // Ẩn các cửa hàng khác nếu là quản lý chi nhánh
                                    if ($userRole == 2 && $store->Id != $userStoreId) continue; 
                                ?>
                                <option value="<?= $store->Id ?>" <?= ($selectedStore == $store->Id || ($userRole == 2 && $store->Id == $userStoreId)) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($store->StoreName) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label mb-1">Tìm kiếm sản phẩm</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fa fa-search"></i></span>
                            <input type="text" name="keyword" class="form-control border-start-0" placeholder="Tên, mô tả, danh mục..." value="<?= htmlspecialchars($searchKeyword) ?>">
                        </div>
                    </div>
                    <div class="col-12 col-md-auto d-flex gap-2 align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                        <a href="?page=product" class="btn btn-outline-secondary w-100">Quay lại</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>Mã SP</th> <th>Tên sản phẩm</th> <th>Nội dung</th> <th>Hình ảnh</th> <th>Giá</th>
                        <th>Đánh giá</th> <th>Ngày tạo</th> <th>Ngày cập nhật</th> <th>Mã danh mục</th> <th>Danh mục</th> <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($productAdmins)): ?>
                        <?php foreach ($productAdmins as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product->Id) ?></td>
                                <td><?= htmlspecialchars($product->Title) ?></td>
                                <td><span class="content-clamp"><?= htmlspecialchars($product->Content) ?></span></td>
                                <td><img src="/app/img/SanPham/<?= htmlspecialchars($product->Img) ?>" style="width: 100px; height: 60px; object-fit: contain;"></td>
                                <td class="text-danger fw-bold"><?= number_format($product->Price, 0, ",", ".") ?> đ</td>
                                <td><?= htmlspecialchars($product->Rate) ?> <i class="fa fa-star text-warning"></i></td>
                                <td><?= date('d/m/Y', strtotime($product->CreateAt)) ?></td>
                                <td><?= date('d/m/Y', strtotime($product->UpdateAt)) ?></td>
                                <td><?= htmlspecialchars($product->CategoryId) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($product->CategoryTitle) ?></span></td>
                                <td>
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal" onclick="fillViewModal(<?= $product->Id ?>)"><i class="fa fa-eye"></i></button>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" onclick="fillEditModal(<?= $product->Id ?>)"><i class="fa fa-edit"></i></button>
                                    <form method="POST" action="product/process_delete.php" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">
                                        <input type="hidden" name="productId" value="<?= $product->Id ?>">
                                        <input type="hidden" name="current_page" value="<?= $currentPage ?>">
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="11" class="text-center text-muted py-4">Không có sản phẩm nào được tìm thấy.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-4 flex-wrap">
            <?php 
                $urlParams = '';
                if ($selectedStore > 0) $urlParams .= '&store_id=' . $selectedStore;
                if ($searchKeyword !== '') $urlParams .= '&keyword=' . urlencode($searchKeyword);
            ?>
            <?php if ($currentPage > 1): ?>
                <a href="?page=product&product_page=1<?= $urlParams ?>" class="btn btn-outline-dark mx-1">&laquo;</a>
                <a href="?page=product&product_page=<?= $currentPage - 1 ?><?= $urlParams ?>" class="btn btn-outline-dark mx-1">&lsaquo;</a>
            <?php else: ?>
                <button class="btn btn-outline-dark mx-1" disabled>&laquo;</button>
                <button class="btn btn-outline-dark mx-1" disabled>&lsaquo;</button>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=product&product_page=<?= $i ?><?= $urlParams ?>" class="btn <?= ($i == $currentPage) ? 'btn-dark' : 'btn-outline-dark' ?> mx-1"><?= $i ?></a>
            <?php endfor; ?>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=product&product_page=<?= $currentPage + 1 ?><?= $urlParams ?>" class="btn btn-outline-dark mx-1">&rsaquo;</a>
                <a href="?page=product&product_page=<?= $totalPages ?><?= $urlParams ?>" class="btn btn-outline-dark mx-1">&raquo;</a>
            <?php else: ?>
                <button class="btn btn-outline-dark mx-1" disabled>&rsaquo;</button>
                <button class="btn btn-outline-dark mx-1" disabled>&raquo;</button>
            <?php endif; ?>
        </div>
        <div class="text-center mt-3"><small class="text-muted">Trang <?= $currentPage ?> / <?= $totalPages ?> (Tổng: <?= $totalItems ?> sản phẩm)</small></div>
    </div>
</div>

<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="product/process_create.php" enctype="multipart/form-data">
            <div class="modal-header"><h5 class="modal-title">Thêm sản phẩm mới</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label>Tên sản phẩm</label><input type="text" class="form-control" name="title" required></div>
                <div class="mb-3"><label>Nội dung</label><textarea class="form-control" name="content" required></textarea></div>
                <div class="mb-3"><label>Giá</label><input type="number" class="form-control" name="price" min="0" required></div>
                <div class="mb-3"><label>Mã danh mục</label><input type="number" class="form-control" name="category_id" required></div>
                <div class="mb-3">
                    <label>Chọn cửa hàng</label>
                    <select class="form-control" name="store_ids[]" id="create-store-ids" multiple required>
                        <?php foreach ($stores as $store): ?>
                            <?php if ($userRole == 2 && $store->Id != $userStoreId) continue; ?>
                            <option value="<?= $store->Id ?>"><?= htmlspecialchars($store->StoreName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3"><label>Hình ảnh</label><input type="file" class="form-control" name="image" accept="image/*" required></div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-success">Thêm</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="product/process_edit.php" enctype="multipart/form-data">
            <div class="modal-header"><h5 class="modal-title">Sửa sản phẩm</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="productId" id="edit-product-id">
                <input type="hidden" name="current_page" value="<?= $currentPage ?>">
                <div class="mb-3"><label>Tên sản phẩm</label><input type="text" class="form-control" id="edit-title" name="title" required></div>
                <div class="mb-3"><label>Nội dung</label><textarea class="form-control" id="edit-content" name="content" required></textarea></div>
                <div class="mb-3"><label>Giá</label><input type="number" class="form-control" id="edit-price" name="price" min="0" required></div>
                <div class="mb-3"><label>Mã danh mục</label><input type="number" class="form-control" id="edit-category-id" name="category_id" required></div>
                <div class="mb-3">
                    <label>Chọn cửa hàng</label>
                    <select class="form-control" name="store_ids[]" id="edit-store-ids" multiple required>
                        <?php foreach ($stores as $store): ?>
                            <?php if ($userRole == 2 && $store->Id != $userStoreId) continue; ?>
                            <option value="<?= $store->Id ?>"><?= htmlspecialchars($store->StoreName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Hình ảnh (Để trống nếu không thay đổi)</label>
                    <input type="file" class="form-control" name="image" accept="image/*">
                    <p class="mt-2">Ảnh hiện tại: <img id="edit-current-img" src="" style="width: 50px; height: 30px; object-fit: cover;"></p>
                </div>
               
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-warning">Lưu</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Chi tiết sản phẩm</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <p><strong>Mã SP:</strong> <span id="view-id"></span></p>
                <p><strong>Tên sản phẩm:</strong> <span id="view-title"></span></p>
                <p><strong>Nội dung:</strong> <span id="view-content"></span></p>
                <p><strong>Giá:</strong> <span id="view-price"></span></p>
                <p><strong>Đánh giá:</strong> <span id="view-rate"></span></p>
                <p><strong>Danh mục:</strong> <span id="view-category-title"></span></p>
                <div class="text-center"><img id="view-img" src="" class="img-fluid rounded mt-3" style="max-height: 200px;"></div>
            </div>
        </div>
    </div>
</div>

<textarea id="productsDataJson" style="display:none;"><?= htmlspecialchars($productsJson, ENT_QUOTES, 'UTF-8') ?></textarea>

<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    var productsData = [];
    try {
        var rawData = document.getElementById('productsDataJson').value;
        if (rawData) productsData = JSON.parse(rawData);
    } catch(e) { console.error("Lỗi JSON", e); }
    
    var IMG_BASE_PATH = '/app/img/SanPham/';
    
    document.addEventListener('DOMContentLoaded', function() {
        var choiceOpts = { 
            removeItemButton: true, 
            searchEnabled: true, // Bật tính năng gõ để tìm kiếm
            placeholder: true,
            placeholderValue: 'Nhấn để chọn cửa hàng...', 
            itemSelectText: 'Nhấn để chọn',
            noResultsText: 'Không tìm thấy kết quả',
            noChoicesText: 'Đã chọn hết cửa hàng'
        };
        
        var createSelect = document.getElementById('create-store-ids');
        if (createSelect) {
            window.cStoreChoices = new Choices(createSelect, choiceOpts);
        }

        var editSelect = document.getElementById('edit-store-ids');
        if (editSelect) {
            window.eStoreChoices = new Choices(editSelect, choiceOpts);
        }
    });

    // Hàm điền dữ liệu  XEM
    function fillViewModal(id) {
        var p = productsData.find(x => x.Id == id);
        if(!p) return;
        document.getElementById('view-id').innerText = p.Id;
        document.getElementById('view-title').innerText = p.Title;
        document.getElementById('view-content').innerText = p.Content;
        document.getElementById('view-price').innerText = new Intl.NumberFormat('vi-VN').format(p.Price) + ' đ';
        document.getElementById('view-rate').innerText = p.Rate;
        document.getElementById('view-category-title').innerText = p.CategoryTitle;
        var img = document.getElementById('view-img');
        if(p.Img) { img.src = IMG_BASE_PATH + p.Img; img.style.display = 'inline-block'; } 
        else { img.style.display = 'none'; }
    }

    // Hàm điền dữ liệu SỬA
    function fillEditModal(id) {
        var p = productsData.find(x => x.Id == id);
        if(!p) return;
        document.getElementById('edit-product-id').value = p.Id;
        document.getElementById('edit-title').value = p.Title;
        document.getElementById('edit-content').value = p.Content;
        document.getElementById('edit-price').value = p.Price;
        document.getElementById('edit-category-id').value = p.CategoryId;
        
        var img = document.getElementById('edit-current-img');
        if(p.Img) { img.src = IMG_BASE_PATH + p.Img; img.style.display = 'inline-block'; } 
        else { img.style.display = 'none'; }

        
        if (window.eStoreChoices && p.StoreIds) {
            window.eStoreChoices.removeActiveItems();
            window.eStoreChoices.setChoiceByValue(p.StoreIds.map(String));
        }
    }
</script>