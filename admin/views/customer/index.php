<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../controllers/CustomerAdminController.php';

$customerAdminController = new CustomerAdminController();
$search = $_GET['search'] ?? '';
$customerAdmins = $customerAdminController->getAllCustomers($search);
$customersJson = json_encode($customerAdmins, JSON_UNESCAPED_UNICODE);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-users text-primary"></i> Danh sách khách hàng</h3>
        <form method="GET" class="d-flex w-50">
            <input type="hidden" name="page" value="customer">
            <input type="text" name="search" class="form-control me-2" placeholder="Tìm tên, email hoặc SĐT..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
        </form>
    </div>

    <div class="table-responsive bg-white p-3 rounded shadow-sm">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>SĐT</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customerAdmins as $c): ?>
                <tr>
                    <td>#<?= $c->Id ?></td>
                    <td class="fw-bold"><?= $c->LastName ?> <?= $c->FirstName ?></td>
                    <td><?= $c->Email ?></td>
                    <td><?= $c->Phone ?></td>
                    <td class="text-center">
                        <span class="badge <?= $c->IsActive ? 'bg-success' : 'bg-danger' ?>">
                            <?= $c->IsActive ? 'Hoạt động' : 'Đang khóa' ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewModal" data-bs-id="<?= $c->Id ?>"><i class="fa fa-eye"></i></button>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal" data-bs-id="<?= $c->Id ?>"><i class="fa fa-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-bs-id="<?= $c->Id ?>"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white"><h5 class="modal-title">Chi tiết khách hàng</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="view-content"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="process_edit_customer.php" method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white"><h5 class="modal-title">Cập nhật trạng thái</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="CustomerId" id="edit-id">
                <div class="mb-3"><label class="form-label">Khách hàng:</label><input type="text" id="edit-name" class="form-control" readonly></div>
                <div class="mb-3"><label class="form-label">Trạng thái:</label>
                    <select name="IsActive" id="edit-status" class="form-select">
                        <option value="1">Kích hoạt (Hoạt động)</option>
                        <option value="0">Khóa tài khoản</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Lưu</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="process_delete_customer.php" method="POST" class="modal-content">
            <div class="modal-header bg-danger text-white"><h5 class="modal-title">Xác nhận xóa</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">Bạn có chắc chắn muốn xóa khách hàng này? <input type="hidden" name="CustomerId" id="delete-id"></div>
            <div class="modal-footer"><button type="submit" class="btn btn-danger">Xóa ngay</button></div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const customers = <?= $customersJson ?>;
    document.querySelectorAll('.modal').forEach(m => {
        m.addEventListener('show.bs.modal', e => {
            const id = e.relatedTarget.getAttribute('data-bs-id');
            const c = customers.find(item => item.Id == id);
            if(!c) return;

            if(m.id === 'viewModal') {
                document.getElementById('view-content').innerHTML = `
                    <p><strong>Họ tên:</strong> ${c.LastName} ${c.FirstName}</p>
                    <p><strong>Email:</strong> ${c.Email}</p>
                    <p><strong>SĐT:</strong> ${c.Phone}</p>
                    <p><strong>Địa chỉ:</strong> ${c.Address}</p>
                    <p><strong>Ngày ĐK:</strong> ${c.RegisteredAt}</p>
                `;
            } else if(m.id === 'editModal') {
                document.getElementById('edit-id').value = c.Id;
                document.getElementById('edit-name').value = c.LastName + ' ' + c.FirstName;
                document.getElementById('edit-status').value = c.IsActive;
            } else if(m.id === 'deleteModal') {
                document.getElementById('delete-id').value = c.Id;
            }
        });
    });
</script>
</body>
</html>