<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền Admin
if (!isset($_SESSION['CustomerId'])) {
    header('Location: ../../../views/home/index.php');
    exit();
}

require_once __DIR__ . '/../../../controllers/CustomerController.php';
require_once __DIR__ . '/../../controllers/CustomerAdminController.php';

$customerController = new CustomerController();
$customerAdminController = new CustomerAdminController();

$me = $customerController->getCustomerById($_SESSION['CustomerId']);
if (!$me || !$me->Role) {
    header('Location: ../../../views/home/index.php');
    exit();
}

// Xử lý Tìm kiếm
$search = isset($_GET['search']) ? $_GET['search'] : '';
// Bạn cần cập nhật hàm getAllCustomers($search) trong Controller như tôi đã hướng dẫn ở câu trước
$customerAdmins = $customerAdminController->getAllCustomers($search);
$customersJson = json_encode($customerAdmins, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .table-wrapper { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .img-avatar { width: 50px; height: 50px; object-fit: cover; border-radius: 50%; border: 1px solid #ddd; }
        .modal-backdrop-white { position: fixed; top: 0; left: 0; z-index: 1040; width: 100vw; height: 100vh; background-color: rgba(255,255,255,0.7); }
    </style>
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center mb-4">Quản lý khách hàng</h1>

        <div class="table-wrapper">
            <div class="d-flex justify-content-between mb-3">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="fa fa-plus"></i> Thêm khách hàng
                </button>
                <form action="" method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Tên, Email hoặc SĐT..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary">Tìm</button>
                    <?php if($search): ?>
                        <a href="index.php" class="btn btn-secondary ms-1">Xóa</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Mã KH</th>
                            <th>Ảnh</th>
                            <th>Họ và Tên</th>
                            <th>Liên hệ</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($customerAdmins)): ?>
                            <?php foreach ($customerAdmins as $cust): ?>
                                <tr>
                                    <td><?= $cust->Id ?></td>
                                    <td>
                                        <img src="/oss_trung_nguyen_coffee/app/img/Avatar/<?= htmlspecialchars($cust->Img ?: 'default.png') ?>" class="img-avatar">
                                    </td>
                                    <td><?= htmlspecialchars($cust->LastName . ' ' . $cust->FirstName) ?></td>
                                    <td>
                                        <small>Email: <?= htmlspecialchars($cust->Email) ?></small><br>
                                        <small>SĐT: <?= htmlspecialchars($cust->Phone) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge <?= $cust->IsActive ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $cust->IsActive ? 'Hoạt động' : 'Đã khóa' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal" data-bs-id="<?= $cust->Id ?>"><i class="fa fa-eye"></i></button>
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" data-bs-id="<?= $cust->Id ?>"><i class="fa fa-edit"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center">Không tìm thấy dữ liệu.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="process_edit_customer.php">
                <div class="modal-header">
                    <h5 class="modal-title">Cập nhật trạng thái khách hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="CustomerId" id="edit-customer-id">
                    <div class="mb-3">
                        <label class="form-label">Họ và tên</label>
                        <input type="text" id="edit-full-name" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trạng thái hoạt động</label>
                        <select class="form-select" name="IsActive" id="edit-is-active">
                            <option value="1">Kích hoạt (Hoạt động)</option>
                            <option value="0">Khóa tài khoản</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const customersData = <?= $customersJson ?>;
        const editModal = document.getElementById('editModal');

        editModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-bs-id');
            const customer = customersData.find(c => c.Id == id);
            
            if (customer) {
                document.getElementById('edit-customer-id').value = customer.Id;
                document.getElementById('edit-full-name').value = customer.LastName + ' ' + customer.FirstName;
                document.getElementById('edit-is-active').value = customer.IsActive;
            }
        });
    </script>
</body>
</html>