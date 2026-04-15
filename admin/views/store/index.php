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
$storesJson = json_encode($stores, JSON_UNESCAPED_UNICODE);
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
        body {
            background-color: #f8f9fa;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #343a40;
            font-weight: 700;
        }

        .table-wrapper {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .table thead {
            background-color: #343a40;
            color: #fff;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
        }

        .btn i {
            margin-right: 5px;
        }

        .btn-add {
            margin-bottom: 15px;
        }

        .pagination .btn {
            min-width: 40px;
        }

        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
            }
        }

        .modal-backdrop-white {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1050;
            width: 100vw;
            height: 100vh;
            background-color: #fff;
            opacity: 0.8;
            transition: opacity 0.15s linear;
        }
    </style>
</head>

<body>
    <div class="container my-5">
        <h1>Quản lý cửa hàng</h1>

        <div class="table-wrapper">
            <div class="d-flex justify-content-between mb-3">
                <button class="btn btn-success btn-add" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="fa fa-plus"></i> Thêm cửa hàng
                </button>
                <div class="d-flex">
                    <input type="text" class="form-control me-2" placeholder="Tìm kiếm cửa hàng...">
                    <button class="btn btn-primary">Tìm kiếm</button>
                    <button class="btn btn-outline-dark ms-2">Quay lại danh sách</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Mã cửa hàng</th>
                            <th>Tên cửa hàng</th>
                            <th>Địa chỉ</th>
                            <th>Điện thoại</th>
                            <th>Giờ mở cửa</th>
                            <th>Giờ đóng cửa</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($stores)): ?>
                            <?php foreach ($stores as $store): ?>
                                <tr>
                                    <td><?= htmlspecialchars($store->Id) ?></td>
                                    <td><?= htmlspecialchars($store->StoreName) ?></td>
                                    <td><?= htmlspecialchars($store->Address) ?></td>
                                    <td><?= htmlspecialchars($store->Phone) ?></td>
                                    <td><?= htmlspecialchars($store->OpenTime) ?></td>
                                    <td><?= htmlspecialchars($store->CloseTime) ?></td>
                                    <td>
                                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal" data-bs-id="<?= $store->Id ?>"><i class="fa fa-eye"></i></button>
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" data-bs-id="<?= $store->Id ?>"><i class="fa fa-edit"></i></button>
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-bs-id="<?= $store->Id ?>"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Không có cửa hàng nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="process_create.php" id="createForm">

                <div class="modal-header">
                    <h5 class="modal-title">Thêm cửa hàng mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label>Tên cửa hàng</label><input type="text" class="form-control" name="StoreName"></div>
                    <div class="mb-3"><label>Địa chỉ</label><input type="text" class="form-control" name="Address"></div>
                    <div class="mb-3"><label>Điện thoại</label><input type="text" class="form-control" name="Phone"></div>
                    <div class="mb-3"><label>Giờ mở cửa</label><input type="text" class="form-control" name="OpenTime"></div>
                    <div class="mb-3"><label>Giờ đóng cửa</label><input type="text" class="form-control" name="CloseTime"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Thêm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="process_edit.php" id="editForm">

                <div class="modal-header">
                    <h5 class="modal-title">Sửa cửa hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="Id" id="edit-store-id">
                    <div class="mb-3"><label>Tên cửa hàng</label><input type="text" class="form-control" name="StoreName" id="edit-store-name"></div>
                    <div class="mb-3"><label>Địa chỉ</label><input type="text" class="form-control" name="Address" id="edit-store-address"></div>
                    <div class="mb-3"><label>Điện thoại</label><input type="text" class="form-control" name="Phone" id="edit-store-phone"></div>
                    <div class="mb-3"><label>Giờ mở cửa</label><input type="text" class="form-control" name="OpenTime" id="edit-store-open"></div>
                    <div class="mb-3"><label>Giờ đóng cửa</label><input type="text" class="form-control" name="CloseTime" id="edit-store-close"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chi tiết cửa hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Mã cửa hàng:</strong> <span id="view-id"></span></p>
                    <p><strong>Tên cửa hàng:</strong> <span id="view-name"></span></p>
                    <p><strong>Địa chỉ:</strong> <span id="view-address"></span></p>
                    <p><strong>Điện thoại:</strong> <span id="view-phone"></span></p>
                    <p><strong>Giờ mở cửa:</strong> <span id="view-open"></span></p>
                    <p><strong>Giờ đóng cửa:</strong> <span id="view-close"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="process_delete.php" id="deleteForm">

                <div class="modal-header">
                    <h5 class="modal-title text-danger">Xóa cửa hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn xóa cửa hàng này?
                    <input type="hidden" name="Id" id="delete-store-id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const storesData = <?= $storesJson ?>;

        function fillViewModal(store) {
            document.getElementById('view-id').innerText = store.Id;
            document.getElementById('view-name').innerText = store.StoreName;
            document.getElementById('view-address').innerText = store.Address;
            document.getElementById('view-phone').innerText = store.Phone;
            document.getElementById('view-open').innerText = store.OpenTime;
            document.getElementById('view-close').innerText = store.CloseTime;
        }

        function fillEditModal(store) {
            document.getElementById('edit-store-id').value = store.Id;
            document.getElementById('edit-store-name').value = store.StoreName;
            document.getElementById('edit-store-address').value = store.Address;
            document.getElementById('edit-store-phone').value = store.Phone;
            document.getElementById('edit-store-open').value = store.OpenTime;
            document.getElementById('edit-store-close').value = store.CloseTime;
        }

        // Reload page after CRUD
    document.addEventListener('DOMContentLoaded', function() {
            // Auto init modals
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
                        fillViewModal(store);
                    }
                    if (id === 'editModal') {
                        fillEditModal(store);
                    }
                    if (id === 'deleteModal') {
                        document.getElementById('delete-store-id').value = storeId;
                    }
                });
            });
        });

        function fillViewModal(store) {
            document.getElementById('view-id').innerText = store.Id;
            document.getElementById('view-name').innerText = store.StoreName;
            document.getElementById('view-address').innerText = store.Address;
            document.getElementById('view-phone').innerText = store.Phone;
            document.getElementById('view-open').innerText = store.OpenTime;
            document.getElementById('view-close').innerText = store.CloseTime;
        }

        function fillEditModal(store) {
            document.getElementById('edit-store-id').value = store.Id;
            document.getElementById('edit-store-name').value = store.StoreName;
            document.getElementById('edit-store-address').value = store.Address;
            document.getElementById('edit-store-phone').value = store.Phone;
            document.getElementById('edit-store-open').value = store.OpenTime;
            document.getElementById('edit-store-close').value = store.CloseTime;
        }

        // Reload parent after CRUD
        document.getElementById('createForm').addEventListener('submit', function() {
            setTimeout(() => window.parent.location.reload(), 1000);
        });
        document.getElementById('editForm').addEventListener('submit', function() {
            setTimeout(() => window.parent.location.reload(), 1000);
        });
        document.getElementById('deleteForm').addEventListener('submit', function() {
            setTimeout(() => window.parent.location.reload(), 1000);
        });


        const modals = ['viewModal', 'editModal', 'deleteModal'];
        modals.forEach(id => {
            const modalEl = document.getElementById(id);
            modalEl.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const storeId = button.getAttribute('data-bs-id');
                const store = storesData.find(s => s.Id == storeId);
                if (!store) return;

                if (id === 'viewModal') fillViewModal(store);
                if (id === 'editModal') fillEditModal(store);
                if (id === 'deleteModal') document.getElementById('delete-store-id').value = store.Id;

                // White backdrop
                const backdrop = document.createElement('div');
                backdrop.classList.add('modal-backdrop-white');
                document.body.appendChild(backdrop);
            });

            modalEl.addEventListener('hidden.bs.modal', () => {
                const backdrop = document.querySelector('.modal-backdrop-white');
                if (backdrop) backdrop.remove();
            });
        });
    </script>
 </body>

</html>

