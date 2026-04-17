<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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
        body { background-color: #f8f9fa; }
        h1 { text-align: center; margin-bottom: 30px; color: #343a40; font-weight: 700; }
        .table-wrapper { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); }
        .table thead { background-color: #343a40; color: #fff; }
        .table-hover tbody tr:hover { background-color: #f1f1f1; }
        .btn i { margin-right: 3px; }
        .modal-backdrop-white { position: fixed; top: 0; left: 0; z-index: 1050; width: 100vw; height: 100vh; background-color: #fff; opacity: 0.8; }
    </style>
</head>

<body>
    <div class="container my-5">
        <h1><i class="fas fa-user-tag"></i> Quản lý Chức vụ Hệ thống</h1>

        <div class="table-wrapper">
            <div class="row mb-3 align-items-center">
                <div class="col-md-6">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal">
                        <i class="fa fa-plus"></i> Thêm chức vụ mới
                    </button>
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" id="searchInput" placeholder="Tìm kiếm tên chức vụ..." onkeyup="filterRoles()">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Mã chức vụ</th>
                            <th style="width: 50%;">Tên chức vụ</th>
                            <th style="width: 30%;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="roleTableBody">
                        <?php if (!empty($roles)): ?>
                            <?php foreach ($roles as $role): ?>
                                <tr class="role-row" data-name="<?php echo mb_strtolower($role->RoleName, 'UTF-8'); ?>">
                                    <td><strong>#<?= htmlspecialchars($role->Id) ?></strong></td>
                                    <td class="text-start fw-bold text-primary"><?= htmlspecialchars($role->RoleName) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-info text-white" title="Xem" data-bs-toggle="modal" data-bs-target="#viewModal" data-bs-id="<?= $role->Id ?>"><i class="fa fa-eye"></i></button>
                                            <button class="btn btn-warning text-dark" title="Sửa" data-bs-toggle="modal" data-bs-target="#editModal" data-bs-id="<?= $role->Id ?>"><i class="fa fa-edit"></i></button>
                                            <button class="btn btn-danger" title="Xóa" onclick="deleteRole(<?= $role->Id ?>, '<?= htmlspecialchars($role->RoleName) ?>')"><i class="fa fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted">Không có chức vụ nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-info-circle"></i> Chi tiết chức vụ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-borderless">
                        <tr><td style="width: 40%;"><strong>Mã chức vụ:</strong></td><td><span id="view-id" class="badge bg-dark"></span></td></tr>
                        <tr><td><strong>Tên chức vụ:</strong></td><td id="view-name" class="fw-bold text-primary"></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="role/process_edit.php">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fa fa-edit"></i> Cập nhật chức vụ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="Id" id="edit-role-id">
                    <div class="mb-3">
                        <label class="form-label">Tên chức vụ mới</label>
                        <input type="text" class="form-control" name="RoleName" id="edit-role-name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning shadow-sm">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="role/process_create.php">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fa fa-plus-circle"></i> Tạo chức vụ mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên chức vụ</label>
                        <input type="text" class="form-control" name="RoleName" placeholder="VD: Bảo vệ, Tạp vụ..." required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success shadow-sm">Xác nhận tạo</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const rolesData = <?= $rolesJson ?>;

        function filterRoles() {
            const keyword = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.role-row');
            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                row.style.display = name.includes(keyword) ? '' : 'none';
            });
        }

        // ĐÃ SỬA ĐƯỜNG DẪN FETCH API
        function deleteRole(id, name) {
            if (confirm(`⚠️ CẢNH BÁO: Xóa vai trò "${name}"?\n\nĐiều này sẽ khiến tất cả nhân viên đang giữ vai trò này bị chuyển về "Không xác định".`)) {
                fetch('role/process_delete.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `roleId=${id}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Đã xóa chức vụ thành công!');
                        location.reload();
                    } else {
                        alert('❌ Lỗi: ' + data.message);
                    }
                });
            }
        }

        const modals = [document.getElementById('viewModal'), document.getElementById('editModal')];
        modals.forEach(modal => {
            if (!modal) return;
            modal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-bs-id');
                const role = rolesData.find(r => r.Id == id);
                if (!role) return;

                if (modal.id === 'viewModal') {
                    document.getElementById('view-id').innerText = role.Id;
                    document.getElementById('view-name').innerText = role.RoleName;
                }
                if (modal.id === 'editModal') {
                    document.getElementById('edit-role-id').value = role.Id;
                    document.getElementById('edit-role-name').value = role.RoleName;
                }
            });
        });
    </script>
</body>
</html>