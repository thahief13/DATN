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
require_once __DIR__ . '/../../controllers/RoleAdminController.php';

$customerController = new CustomerController();
$customer = $customerController->getCustomerById($_SESSION['CustomerId']);

if (!$customer || !$customer->Role) {
    header('Location: ../../../views/home/index.php');
    exit();
}

$roleController = new RoleAdminController();
$roles = $roleController->getAllRoles();
$rolesJson = json_encode($roles, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý chức vụ</title>
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

        @media (max-width:768px) {
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
        <h1>Quản lý chức vụ</h1>

        <div class="table-wrapper">
            <div class="d-flex justify-content-between mb-3">
                <button class="btn btn-success btn-add" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="fa fa-plus"></i> Thêm chức vụ
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Mã chức vụ</th>
                            <th>Tên chức vụ</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($roles)): ?>
                            <?php foreach ($roles as $role): ?>
                                <tr>
                                    <td><?= htmlspecialchars($role->Id) ?></td>
                                    <td><?= htmlspecialchars($role->RoleName) ?></td>
                                    <td>
                                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal" data-bs-id="<?= $role->Id ?>"><i class="fa fa-eye"></i></button>
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" data-bs-id="<?= $role->Id ?>"><i class="fa fa-edit"></i></button>
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-bs-id="<?= $role->Id ?>"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center">Không có chức vụ nào.</td>
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
            <form class="modal-content" method="POST" action="process_create.php">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm chức vụ mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label>Tên chức vụ</label><input type="text" class="form-control" name="RoleName" required></div>
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
            <form class="modal-content" method="POST" action="process_edit.php">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa chức vụ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="Id" id="edit-role-id">
                    <div class="mb-3"><label>Tên chức vụ</label><input type="text" class="form-control" name="RoleName" id="edit-role-name" required></div>
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
                    <h5 class="modal-title">Chi tiết chức vụ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Mã chức vụ:</strong> <span id="view-id"></span></p>
                    <p><strong>Tên chức vụ:</strong> <span id="view-name"></span></p>
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
            <form class="modal-content" method="POST" action="process_delete.php">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Xóa chức vụ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn xóa chức vụ này?
                    <input type="hidden" name="Id" id="delete-role-id">
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
        const rolesData = <?= $rolesJson ?>;

        function fillViewModal(role) {
            document.getElementById('view-id').innerText = role.Id;
            document.getElementById('view-name').innerText = role.RoleName;
        }

        function fillEditModal(role) {
            document.getElementById('edit-role-id').value = role.Id;
            document.getElementById('edit-role-name').value = role.RoleName;
        }

        const modals = ['viewModal', 'editModal', 'deleteModal'];
        modals.forEach(id => {
            const modalEl = document.getElementById(id);
            modalEl.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const roleId = button.getAttribute('data-bs-id');
                const role = rolesData.find(r => r.Id == roleId);
                if (!role) return;

                if (id === 'viewModal') fillViewModal(role);
                if (id === 'editModal') fillEditModal(role);
                if (id === 'deleteModal') document.getElementById('delete-role-id').value = role.Id;

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