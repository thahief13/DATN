<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền admin
if (!isset($_SESSION['CustomerId'])) {
    header('Location: ../../../views/home/index.php');
    exit();
}

require_once __DIR__ . '/../../../controllers/CustomerController.php';
require_once __DIR__ . '/../../models/StoreAdmin.php';
require_once __DIR__ . '/../../controllers/StoreAdminController.php';

$customerController = new CustomerController();
$customer = $customerController->getCustomerById($_SESSION['CustomerId']);

if (!$customer || !$customer->Role) {
    header('Location: ../../../views/home/index.php');
    exit();
}

$storeController = new StoreAdminController();
$stores = $storeController->getAllStores();
$storesJson = json_encode($stores, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?: '[]';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý cửa hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        h1 { text-align: center; margin-bottom: 30px; color: #343a40; font-weight: 700; }
        .table-wrapper { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); }
        .table thead { background-color: #343a40; color: #fff; }
        .table-hover tbody tr:hover { background-color: #f1f1f1; }
        .btn i { margin-right: 5px; }
    </style>
</head>

<body>
    <script id="stores-data" type="application/json"><?= $storesJson ?></script>

    <div class="container my-5">
        <h1><i class="fas fa-store"></i> Quản lý cửa hàng</h1>

        <div class="table-wrapper">
            <div class="d-flex justify-content-between mb-3 align-items-center">
                <button class="btn btn-success btn-add" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="fa fa-plus"></i> Thêm cửa hàng
                </button>
                <div class="d-flex w-50">
                    <input type="text" id="searchInput" class="form-control me-2" placeholder="Tìm kiếm tên cửa hàng..." onkeyup="filterStores()">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th class="text-start">Tên cửa hàng</th>
                            <th class="text-start">Địa chỉ</th>
                            <th>Điện thoại</th>
                            <th>Giờ mở</th>
                            <th>Giờ đóng</th>
                            <th style="min-width: 120px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="storeTableBody">
                        <?php if (!empty($stores)): ?>
                            <?php foreach ($stores as $store): ?>
                                <tr class="store-row" data-name="<?= htmlspecialchars(mb_strtolower($store->StoreName, 'UTF-8')) ?>">
                                    <td><strong>#<?= htmlspecialchars($store->Id) ?></strong></td>
                                    <td class="text-start fw-bold text-primary"><?= htmlspecialchars($store->StoreName) ?></td>
                                    <td class="text-start"><?= htmlspecialchars($store->Address) ?></td>
                                    <td><?= htmlspecialchars($store->Phone) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($store->OpenTime) ?></span></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($store->CloseTime) ?></span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-info text-white" title="Xem" data-bs-toggle="modal" data-bs-target="#viewModal" data-bs-id="<?= $store->Id ?>"><i class="fa fa-eye m-0"></i></button>
                                            <button class="btn btn-warning" title="Sửa" data-bs-toggle="modal" data-bs-target="#editModal" data-bs-id="<?= $store->Id ?>"><i class="fa fa-edit m-0"></i></button>
                                            <button class="btn btn-danger" title="Xóa" data-bs-toggle="modal" data-bs-target="#deleteModal" data-bs-id="<?= $store->Id ?>"><i class="fa fa-trash m-0"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Không có cửa hàng nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="store/process_create.php">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Thêm cửa hàng mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="fw-bold">Tên cửa hàng</label><input type="text" class="form-control" name="StoreName" required></div>
                    <div class="mb-3"><label class="fw-bold">Địa chỉ</label><input type="text" class="form-control" name="Address" required></div>
                    <div class="mb-3"><label class="fw-bold">Điện thoại</label><input type="text" class="form-control" name="Phone"></div>
                    <div class="row">
    <div class="col-6 mb-3">
        <label class="fw-bold text-success"><i class="far fa-clock me-1"></i>Giờ mở cửa</label>
        <input type="time" class="form-control border-success" name="OpenTime" value="06:30" required>
        <small class="text-muted">Chọn giờ mở cửa</small>
    </div>
    <div class="col-6 mb-3">
        <label class="fw-bold text-danger"><i class="fas fa-history me-1"></i>Giờ đóng cửa</label>
        <input type="time" class="form-control border-danger" name="CloseTime" value="22:00" required>
        <small class="text-muted">Chọn giờ đóng cửa</small>
    </div>
</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Thêm mới</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="store/process_edit.php">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Sửa thông tin cửa hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="Id" id="edit-store-id">
                    <div class="mb-3"><label class="fw-bold">Tên cửa hàng</label><input type="text" class="form-control" name="StoreName" id="edit-store-name" required></div>
                    <div class="mb-3"><label class="fw-bold">Địa chỉ</label><input type="text" class="form-control" name="Address" id="edit-store-address" required></div>
                    <div class="mb-3"><label class="fw-bold">Điện thoại</label><input type="text" class="form-control" name="Phone" id="edit-store-phone"></div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="fw-bold">Giờ mở cửa</label><input type="time" class="form-control" name="OpenTime" id="edit-store-open"></div>
                        <div class="col-6 mb-3"><label class="fw-bold">Giờ đóng cửa</label><input type="time" class="form-control" name="CloseTime" id="edit-store-close"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="store/process_delete.php">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Xóa cửa hàng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i>
                    <p class="fs-5">Bạn có chắc chắn muốn xóa cửa hàng này?</p>
                    <p class="text-muted small">Cảnh báo: Hành động này có thể ảnh hưởng đến nhân viên và đơn hàng liên quan.</p>
                    <input type="hidden" name="Id" id="delete-store-id">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger px-4">Xác nhận xóa</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-info-circle"></i> Chi tiết cửa hàng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-borderless">
                        <tr><td style="width: 35%;"><strong>Mã cửa hàng:</strong></td><td><span id="view-id" class="badge bg-dark"></span></td></tr>
                        <tr><td><strong>Tên cửa hàng:</strong></td><td id="view-name" class="fw-bold text-primary"></td></tr>
                        <tr><td><strong>Địa chỉ:</strong></td><td id="view-address"></td></tr>
                        <tr><td><strong>Điện thoại:</strong></td><td id="view-phone"></td></tr>
                        <tr><td><strong>Giờ mở cửa:</strong></td><td id="view-open"></td></tr>
                        <tr><td><strong>Giờ đóng cửa:</strong></td><td id="view-close"></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let storesData = [];
            try {
                storesData = JSON.parse(document.getElementById('stores-data').textContent);
            } catch(e) {
                console.error("Lỗi JSON: ", e);
            }

            // TÌM KIẾM
            window.filterStores = function() {
                const keyword = document.getElementById('searchInput').value.toLowerCase().trim();
                const rows = document.querySelectorAll('.store-row');
                rows.forEach(row => {
                    const name = row.getAttribute('data-name');
                    row.style.display = name.includes(keyword) ? '' : 'none';
                });
            }

            // NẠP DỮ LIỆU VÀO MODAL
            const modals = ['viewModal', 'editModal', 'deleteModal'];
            modals.forEach(id => {
                const modalEl = document.getElementById(id);
                if (!modalEl) return;
                
                modalEl.addEventListener('show.bs.modal', event => {
                    const button = event.relatedTarget;
                    const storeId = button.getAttribute('data-bs-id');
                    const store = storesData.find(s => s.Id == storeId);
                    if (!store) return;

                    if (id === 'viewModal') {
                        document.getElementById('view-id').innerText = store.Id;
                        document.getElementById('view-name').innerText = store.StoreName;
                        document.getElementById('view-address').innerText = store.Address;
                        document.getElementById('view-phone').innerText = store.Phone;
                        document.getElementById('view-open').innerText = store.OpenTime;
                        document.getElementById('view-close').innerText = store.CloseTime;
                    }
                    if (id === 'editModal') {
                        document.getElementById('edit-store-id').value = store.Id;
                        document.getElementById('edit-store-name').value = store.StoreName;
                        document.getElementById('edit-store-address').value = store.Address;
                        document.getElementById('edit-store-phone').value = store.Phone;
                        document.getElementById('edit-store-open').value = store.OpenTime;
                        document.getElementById('edit-store-close').value = store.CloseTime;
                    }
                    if (id === 'deleteModal') {
                        document.getElementById('delete-store-id').value = store.Id;
                    }
                });
            });
        });
    </script>
</body>
</html>