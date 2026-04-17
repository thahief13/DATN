<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['CustomerId'])) {
    header('Location: ../../../views/home/index.php');
    exit();
}

require_once __DIR__ . '/../../../controllers/CustomerController.php';
$customerController = new CustomerController();

$customer = $customerController->getCustomerById($_SESSION['CustomerId']);
$productAdmins = [];

$selectedStore = isset($_GET['store_id']) ? (int)$_GET['store_id'] : 0;

if ($customer && $customer->Role) {
    require_once __DIR__ . '/../../controllers/ProductAdminController.php';

    $productAdminController = new ProductAdminController();
    $allProductAdmins = $productAdminController->getAllProducts(0, $selectedStore);
    $stores = $productAdminController->getAllStores();
} else {
    header('Location: ../../../views/home/index.php');
    exit();
}

// Pagination
$itemsPerPage = 15;
$currentPage = isset($_GET['product_page']) ? (int)$_GET['product_page'] : 1;
if ($currentPage < 1) $currentPage = 1;

$totalItems = count($allProductAdmins);
$totalPages = ceil($totalItems / $itemsPerPage);
if ($currentPage > $totalPages && $totalPages > 0) $currentPage = $totalPages;

$startIndex = ($currentPage - 1) * $itemsPerPage;
$productAdmins = array_slice($allProductAdmins, $startIndex, $itemsPerPage);

$productsJson = json_encode($allProductAdmins, JSON_UNESCAPED_UNICODE);
?>

<link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet" />
<style>
    .choices__inner {
        border-radius: 0.375rem !important;
        border: 1px solid #ced4da !important;
        background-color: #fff !important;
        min-height: 38px !important;
    }
    .choices__list--multiple .choices__item {
        background-color: #198754 !important; /* Màu xanh của nút Success Bootstrap */
        border: 1px solid #146c43 !important;
        border-radius: 4px !important;
    }
    .choices[data-type*="select-multiple"] .choices__button {
        border-left: 1px solid rgba(255,255,255,.5);
    }
</style>

<div class="container my-5">
    <h1>Quản lý sản phẩm</h1>

    <div class="table-wrapper">
        <div class="card shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="row gx-3 gy-2 align-items-center">
                    <div class="col-auto">
                        <button class="btn btn-success btn-add" data-bs-toggle="modal" data-bs-target="#createModal">
                            <i class="fa fa-plus me-1"></i> Thêm sản phẩm
                        </button>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label mb-1" for="storeFilter">Chọn cửa hàng</label>
                        <select class="form-select" id="storeFilter">
                            <option value="0">Tất cả cửa hàng</option>
                            <?php foreach ($stores as $store): ?>
                                <option value="<?php echo $store['Id']; ?>" <?php echo $selectedStore === (int)$store['Id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($store['StoreName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label mb-1" for="searchInput">Tìm kiếm sản phẩm</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fa fa-search"></i></span>
                            <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Tên, mô tả, danh mục...">
                        </div>
                    </div>
                    <div class="col-12 col-md-auto d-flex gap-2 align-items-end">
                        <button class="btn btn-primary w-100" id="searchBtn">Tìm kiếm</button>
                        <button class="btn btn-outline-secondary w-100" id="resetBtn">Quay lại</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>Mã SP</th>
                        <th>Tên sản phẩm</th>
                        <th>Nội dung</th>
                        <th>Hình ảnh</th>
                        <th>Giá</th>
                        <th>Đánh giá</th>
                        <th>Ngày tạo</th>
                        <th>Ngày cập nhật</th>
                        <th>Mã danh mục</th>
                        <th>Tên danh mục</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($productAdmins)): ?>
                        <?php foreach ($productAdmins as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product->Id); ?></td>
                                <td><?php echo htmlspecialchars($product->Title); ?></td>
                                <td>
                                    <span class="content-clamp"><?php echo htmlspecialchars($product->Content); ?></span>
                                </td>
                                <td>
                                    <img src="/app/img/SanPham/<?php echo htmlspecialchars($product->Img); ?>" alt="<?php echo htmlspecialchars($product->Title); ?>" style="width: 100px; height: 60px; object-fit: contain;">
                                </td>
                                <td><?php echo number_format($product->Price, 0, ",", "."); ?></td>
                                <td><?php echo htmlspecialchars($product->Rate); ?></td>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($product->CreateAt)); ?></td>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($product->UpdateAt)); ?></td>
                                <td><?php echo htmlspecialchars($product->CategoryId); ?></td>
                                <td><?php echo htmlspecialchars($product->CategoryTitle); ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal" data-id="<?php echo $product->Id; ?>"><i class="fa fa-eye"></i></button>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $product->Id; ?>"><i class="fa fa-edit"></i></button>
                                    <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $product->Id; ?>"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center">Không có sản phẩm nào được tìm thấy.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-4 flex-wrap">
            <?php
                $storeParam = $selectedStore > 0 ? '&store_id=' . $selectedStore : '';
            ?>
            <?php if ($currentPage > 1): ?>
                <a href="?page=product&product_page=1<?php echo $storeParam; ?>" class="btn btn-outline-dark mx-1">&laquo;</a>
                <a href="?page=product&product_page=<?php echo $currentPage - 1; ?><?php echo $storeParam; ?>" class="btn btn-outline-dark mx-1">&lsaquo;</a>
            <?php else: ?>
                <button class="btn btn-outline-dark mx-1" disabled>&laquo;</button>
                <button class="btn btn-outline-dark mx-1" disabled>&lsaquo;</button>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i == $currentPage): ?>
                    <button class="btn btn-dark mx-1" disabled><?php echo $i; ?></button>
                <?php else: ?>
                    <a href="?page=product&product_page=<?php echo $i; ?><?php echo $storeParam; ?>" class="btn btn-outline-dark mx-1"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=product&product_page=<?php echo $currentPage + 1; ?><?php echo $storeParam; ?>" class="btn btn-outline-dark mx-1">&rsaquo;</a>
                <a href="?page=product&product_page=<?php echo $totalPages; ?><?php echo $storeParam; ?>" class="btn btn-outline-dark mx-1">&raquo;</a>
            <?php else: ?>
                <button class="btn btn-outline-dark mx-1" disabled>&rsaquo;</button>
                <button class="btn btn-outline-dark mx-1" disabled>&raquo;</button>
            <?php endif; ?>
        </div>
        <div class="text-center mt-3">
            <small class="text-muted">Trang <?php echo $currentPage; ?> / <?php echo $totalPages; ?> (Tổng: <?php echo $totalItems; ?> sản phẩm)</small>
        </div>
    </div>
</div>

<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="product/process_create.php" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title">Thêm sản phẩm mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Tên sản phẩm</label>
                    <input type="text" class="form-control" name="title" placeholder="Nhập tên sản phẩm" required>
                </div>
                <div class="mb-3">
                    <label>Nội dung</label>
                    <textarea class="form-control" name="content" placeholder="Nhập mô tả sản phẩm" required></textarea>
                </div>
                <div class="mb-3">
                    <label>Giá</label>
                    <input type="number" class="form-control" name="price" placeholder="Nhập giá sản phẩm" min="0" required>
                </div>
                <div class="mb-3">
                    <label>Mã danh mục</label>
                    <input type="number" class="form-control" name="category_id" placeholder="Nhập mã danh mục" required>
                </div>
                <div class="mb-3">
                    <label>Chọn cửa hàng</label>
                    <select class="form-control" name="store_ids[]" id="create-store-ids" multiple required>
                        <?php foreach ($stores as $store): ?>
                            <option value="<?php echo $store['Id']; ?>"><?php echo htmlspecialchars($store['StoreName']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Hình ảnh</label>
                    <input type="file" class="form-control" name="image" accept="image/*" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-success">Thêm</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="product/process_edit.php" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title">Sửa sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="productId" id="edit-product-id">
                <input type="hidden" name="current_page" id="edit-current-page" value="<?php echo $currentPage; ?>">
                <div class="mb-3">
                    <label for="edit-title">Tên sản phẩm</label>
                    <input type="text" class="form-control" id="edit-title" name="title" required>
                </div>
                <div class="mb-3">
                    <label for="edit-content">Nội dung</label>
                    <textarea class="form-control" id="edit-content" name="content" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="edit-price">Giá</label>
                    <input type="number" class="form-control" id="edit-price" name="price" min="0" required>
                </div>
                <div class="mb-3">
                    <label for="edit-category-id">Mã danh mục</label>
                    <input type="number" class="form-control" id="edit-category-id" name="category_id" required>
                </div>
                <div class="mb-3">
                    <label for="edit-store-ids">Chọn cửa hàng</label>
                    <select class="form-control" name="store_ids[]" id="edit-store-ids" multiple required>
                        <?php foreach ($stores as $store): ?>
                            <option value="<?php echo $store['Id']; ?>"><?php echo htmlspecialchars($store['StoreName']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="edit-image-file">Hình ảnh (Để trống nếu không thay đổi)</label>
                    <input type="file" class="form-control" id="edit-image-file" name="image">
                    <p class="mt-2">Ảnh hiện tại: <img id="edit-current-img" src="" style="width: 50px; height: 30px; object-fit: cover;"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-warning">Lưu</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="view-modal-body">
                <p><strong>Mã SP:</strong> <span id="view-id"></span></p>
                <p><strong>Tên sản phẩm:</strong> <span id="view-title"></span></p>
                <p><strong>Nội dung:</strong> <span id="view-content"></span></p>
                <p><strong>Giá:</strong> <span id="view-price"></span></p>
                <p><strong>Đánh giá:</strong> <span id="view-rate"></span></p>
                <p><strong>Ngày tạo:</strong> <span id="view-create-at"></span></p>
                <p><strong>Ngày cập nhật:</strong> <span id="view-update-at"></span></p>
                <p><strong>Mã danh mục:</strong> <span id="view-category-id"></span></p>
                <p><strong>Tên danh mục:</strong> <span id="view-category-title"></span></p>
                <div class="text-center">
                    <img id="view-img" src="" alt="Hình ảnh sản phẩm" class="img-fluid rounded mt-3" style="max-height: 200px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const productsData = <?php echo $productsJson; ?>;
    const IMG_BASE_PATH = '/app/img/SanPham/';

    // Biến lưu trữ thư viện Choices.js
    let createStoreChoices;
    let editStoreChoices;

    function formatCurrency(number) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(number);
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('vi-VN');
    }

    function showWhiteBackdrop() {
        const backdrop = document.createElement('div');
        backdrop.classList.add('modal-backdrop-white');
        document.body.appendChild(backdrop);
    }

    function removeWhiteBackdrop() {
        const backdrop = document.querySelector('.modal-backdrop-white');
        if (backdrop) {
            backdrop.remove();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // 1. KHỞI TẠO CHOICES.JS CHO 2 Ô DROPDOWN
        const choicesOptions = {
            removeItemButton: true,
            searchEnabled: false,
            placeholder: true,
            placeholderValue: 'Nhấn để chọn cửa hàng...',
            itemSelectText: '', // Ẩn chữ tiếng anh mặc định
            noChoicesText: 'Đã chọn tất cả cửa hàng',
        };

        createStoreChoices = new Choices('#create-store-ids', choicesOptions);
        editStoreChoices = new Choices('#edit-store-ids', choicesOptions);

        const viewModal = document.getElementById('viewModal');
        const editModal = document.getElementById('editModal');
        const createModal = document.getElementById('createModal');
        const modalElements = [createModal, viewModal, editModal];

        modalElements.forEach(modalElement => {
            if (!modalElement) return;

            modalElement.addEventListener('show.bs.modal', function(event) {
                showWhiteBackdrop();

                const button = event.relatedTarget;
                if (button) {
                    const productId = button.getAttribute('data-id');
                    if (!productId) return;

                    const product = productsData.find(p => p.Id == productId);

                    if (!product) return;

                    if (modalElement.id === 'viewModal') {
                        document.getElementById('view-id').innerText = product.Id;
                        document.getElementById('view-title').innerText = product.Title;
                        document.getElementById('view-content').innerText = product.Content;
                        document.getElementById('view-price').innerText = formatCurrency(product.Price);
                        document.getElementById('view-rate').innerText = product.Rate;
                        document.getElementById('view-create-at').innerText = formatDate(product.CreateAt);
                        document.getElementById('view-update-at').innerText = formatDate(product.UpdateAt);
                        document.getElementById('view-category-id').innerText = product.CategoryId;
                        document.getElementById('view-category-title').innerText = product.CategoryTitle;
                        document.getElementById('view-img').src = IMG_BASE_PATH + product.Img;
                    }

                    if (modalElement.id === 'editModal') {
                        document.getElementById('edit-product-id').value = product.Id;
                        document.getElementById('edit-title').value = product.Title;
                        document.getElementById('edit-content').value = product.Content;
                        document.getElementById('edit-price').value = product.Price;
                        document.getElementById('edit-category-id').value = product.CategoryId;
                        document.getElementById('edit-current-img').src = IMG_BASE_PATH + product.Img;
                        const urlParams = new URLSearchParams(window.location.search);
                        document.getElementById('edit-current-page').value = urlParams.get('product_page') || '1';
                        
                        // 2. NẠP DỮ LIỆU ĐỘNG VÀO CHOICES.JS
                        if (product.StoreIds && Array.isArray(product.StoreIds)) {
                            editStoreChoices.removeActiveItems(); // Reset lại trước khi nạp
                            editStoreChoices.setChoiceByValue(product.StoreIds.map(String));
                        } else {
                            editStoreChoices.removeActiveItems();
                        }
                    }
                }
            });

            modalElement.addEventListener('hidden.bs.modal', function() {
                removeWhiteBackdrop();
                // Reset form khi tắt modal Thêm để lần sau mở lên trống trơn
                if (modalElement.id === 'createModal') {
                    document.querySelector('#createModal form').reset();
                    createStoreChoices.removeActiveItems();
                }
            });
        });

        // Xóa sản phẩm
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.id;
                if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'product/process_delete.php';
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'productId';
                    input.value = productId;
                    form.appendChild(input);
                    
                    const pageInput = document.createElement('input');
                    pageInput.type = 'hidden';
                    pageInput.name = 'current_page';
                    const urlParams = new URLSearchParams(window.location.search);
                    pageInput.value = urlParams.get('product_page') || '1';
                    form.appendChild(pageInput);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // Tìm kiếm
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        const resetBtn = document.getElementById('resetBtn');

        if (searchBtn) {
            searchBtn.addEventListener('click', function() {
                const keyword = searchInput.value.trim().toLowerCase();
                if (keyword === '') {
                    alert('Vui lòng nhập từ khóa tìm kiếm');
                    return;
                }
                
                const filteredProducts = productsData.filter(p => 
                    p.Id.toString().toLowerCase().includes(keyword) ||
                    p.Title.toLowerCase().includes(keyword) || 
                    p.Content.toLowerCase().includes(keyword) ||
                    (p.CategoryTitle && p.CategoryTitle.toLowerCase().includes(keyword))
                );
                
                displaySearchResults(filteredProducts);
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                document.getElementById('storeFilter').value = '0';
                searchInput.value = '';
                window.location.href = '?page=product&product_page=1';
            });
        }

        const storeFilter = document.getElementById('storeFilter');
        if (storeFilter) {
            storeFilter.addEventListener('change', function() {
                const storeId = this.value;
                window.location.href = '?page=product&product_page=1' + (storeId > 0 ? '&store_id=' + storeId : '');
            });
        }

        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchBtn.click();
                }
            });
        }

        function displaySearchResults(filteredProducts) {
            const tbody = document.querySelector('table tbody');
            tbody.innerHTML = '';

            if (filteredProducts.length === 0) {
                tbody.innerHTML = '<tr><td colspan="11" class="text-center">Không tìm thấy sản phẩm nào.</td></tr>';
                document.querySelector('.d-flex.justify-content-center.mt-4').style.display = 'none';
                document.querySelector('.text-center.mt-3').style.display = 'none';
                return;
            }

            filteredProducts.forEach(product => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${escapeHtml(product.Id)}</td>
                    <td>${escapeHtml(product.Title)}</td>
                    <td><span class="content-clamp">${escapeHtml(product.Content)}</span></td>
                    <td><img src="${IMG_BASE_PATH}${escapeHtml(product.Img)}" alt="${escapeHtml(product.Title)}" style="width: 100px; height: 60px; object-fit: contain;"></td>
                    <td>${formatNumberVN(product.Price)}</td>
                    <td>${escapeHtml(product.Rate)}</td>
                    <td>${formatDateVN(product.CreateAt)}</td>
                    <td>${formatDateVN(product.UpdateAt)}</td>
                    <td>${escapeHtml(product.CategoryId)}</td>
                    <td>${escapeHtml(product.CategoryTitle)}</td>
                    <td>
                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal" data-id="${product.Id}"><i class="fa fa-eye"></i></button>
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" data-id="${product.Id}"><i class="fa fa-edit"></i></button>
                        <button class="btn btn-danger btn-sm delete-btn" data-id="${product.Id}"><i class="fa fa-trash"></i></button>
                    </td>
                `;
                tbody.appendChild(row);
            });

            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.dataset.id;
                    if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'product/process_delete.php';
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'productId';
                        input.value = productId;
                        form.appendChild(input);
                        
                        const pageInput = document.createElement('input');
                        pageInput.type = 'hidden';
                        pageInput.name = 'current_page';
                        const urlParams = new URLSearchParams(window.location.search);
                        pageInput.value = urlParams.get('product_page') || '1';
                        form.appendChild(pageInput);
                        
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });

            document.querySelector('.d-flex.justify-content-center.mt-4').style.display = 'none';
            document.querySelector('.text-center.mt-3').style.display = 'none';
        }

        function escapeHtml(text) {
            if (text == null || text === undefined) {
                return '';
            }
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.toString().replace(/[&<>"']/g, m => map[m]);
        }

        function formatNumberVN(number) {
            if (number == null || number === undefined || isNaN(number)) {
                return '0';
            }
            return new Intl.NumberFormat('vi-VN', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number);
        }

        function formatDateVN(dateString) {
            if (!dateString) {
                return '';
            }
            const date = new Date(dateString);
            if (isNaN(date.getTime())) {
                return '';
            }
            return date.toLocaleString('vi-VN', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
    });
</script>