<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$success_msg = $_SESSION['success_message'] ?? '';
$error_msg = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

if (!isset($_SESSION['CustomerId'])) {
    header('Location: ../../../views/home/index.php');
    exit();
}

require_once __DIR__ . '/../../../controllers/CustomerController.php';
require_once __DIR__ . '/../../controllers/EmployeeAdminController.php';
require_once __DIR__ . '/../../controllers/RoleAdminController.php';
require_once __DIR__ . '/../../controllers/StoreAdminController.php';

$customerController = new CustomerController();
$customer = $customerController->getCustomerById($_SESSION['CustomerId']);

if (!$customer || !$customer->Role) {
    header('Location: ../../../views/home/index.php');
    exit();
}

$employeeController = new EmployeeAdminController();
$employeeAdmins = $employeeController->getAllEmployees();

$roleController = new RoleAdminController();
$roles = $roleController->getAllRoles();

$storeController = new StoreAdminController();
$stores = $storeController->getAllStores();

$roleMap = [];
foreach ($roles as $r) { $roleMap[$r->Id] = $r->RoleName; }

$storeMap = [];
foreach ($stores as $s) { $storeMap[$s->Id] = $s->StoreName; }

$empArrayForJs = [];
$uniqueStores = [];
foreach ($employeeAdmins as $emp) {
    $rName = isset($roleMap[$emp->RoleId]) ? $roleMap[$emp->RoleId] : 'Chưa phân công';
    $sName = isset($storeMap[$emp->StoreId]) ? $storeMap[$emp->StoreId] : 'Chi nhánh #' . $emp->StoreId;

    $empArrayForJs[] = [
        'Id' => $emp->Id,
        'FullName' => $emp->FullName,
        'StoreId' => $emp->StoreId,
        'StoreName' => $sName,
        'RoleId' => $emp->RoleId,
        'RoleName' => $rName,
        'Salary' => (float)$emp->Salary
    ];
    
    if ($emp->StoreId && !in_array($emp->StoreId, $uniqueStores)) {
        $uniqueStores[] = $emp->StoreId;
    }
}
sort($uniqueStores);
$employeesJson = htmlspecialchars(json_encode($empArrayForJs, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý nhân viên - Cafe Trung Nguyên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .table-wrapper { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .salary-result { font-size: 22px; font-weight: bold; color: #d35400; text-align: center; background: #fdf2e9; padding: 10px; border-radius: 8px; border: 1px dashed #e67e22; }
        .modal-header.bg-info { background-color: #0dcaf0 !important; }
        .employee-row td { vertical-align: middle; }
    </style>
</head>
<body>
    <div id="employees-data" data-json="<?= $employeesJson ?>" style="display: none;"></div>

    <div class="container my-5">
        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <?= htmlspecialchars($success_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <h1 class="text-center mb-4"><i class="fas fa-users-cog"></i> Quản lý Nhân sự & Tiền lương</h1>
        
        <div class="table-wrapper">
            <div class="row mb-3 align-items-center">
                <div class="col-md-4">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal">
                        <i class="fa fa-plus"></i> Thêm nhân viên
                    </button>
                </div>
                
                <div class="col-md-8 d-flex justify-content-end gap-2">
                    <select id="storeFilter" class="form-select w-auto fw-bold" onchange="filterEmployees()">
                        <option value="all">Tất cả chi nhánh</option>
                        <?php foreach ($uniqueStores as $sId): ?>
                            <option value="<?= $sId ?>">Chi nhánh #<?= $sId ?> - <?= htmlspecialchars($storeMap[$sId] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" class="form-control w-50" id="searchInput" placeholder="Tìm tên nhân viên..." onkeyup="filterEmployees()">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Mã NV</th>
                            <th>Tên nhân viên</th>
                            <th>Chi nhánh</th>
                            <th>Chức vụ</th>
                            <th>Lương cơ bản</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="employeeTableBody">
                        <?php foreach ($empArrayForJs as $emp): ?>
                            <tr class="employee-row" data-store="<?= $emp['StoreId'] ?>" data-name="<?= htmlspecialchars(mb_strtolower($emp['FullName'], 'UTF-8'), ENT_QUOTES) ?>">
                                <td><strong>#<?= $emp['Id'] ?></strong></td>
                                <td class="text-start fw-bold text-primary"><?= htmlspecialchars($emp['FullName']) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($emp['StoreName']) ?></span></td>
                                <td><strong class="text-success"><?= htmlspecialchars($emp['RoleName']) ?></strong></td>
                                <td class="text-end"><?= number_format($emp['Salary'], 0, ',', '.') ?> ₫</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-info text-white" title="Xem" 
                                            onclick="viewEmployeeDetail(
                                                '<?= $emp['Id'] ?>', 
                                                '<?= htmlspecialchars($emp['FullName'], ENT_QUOTES) ?>', 
                                                '<?= htmlspecialchars($emp['StoreName'], ENT_QUOTES) ?>', 
                                                '<?= htmlspecialchars($emp['RoleName'], ENT_QUOTES) ?>', 
                                                '<?= $emp['Salary'] ?>'
                                            )">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-warning" title="Sửa" data-bs-toggle="modal" data-bs-target="#editModal" data-bs-id="<?= $emp['Id'] ?>">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger" title="Xóa" onclick="deleteEmployee(<?= $emp['Id'] ?>, '<?= htmlspecialchars($emp['FullName'], ENT_QUOTES) ?>')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-id-card"></i> Hồ sơ nhân viên</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-borderless">
                        <tr><td width="40%"><strong>Mã nhân viên:</strong></td><td><span id="view-id" class="badge bg-dark"></span></td></tr>
                        <tr><td><strong>Họ tên:</strong></td><td id="view-name" class="fw-bold text-primary"></td></tr>
                        <tr><td><strong>Chi nhánh:</strong></td><td id="view-store-id"></td></tr>
                        <tr><td><strong>Chức vụ:</strong></td><td id="view-role-id" class="text-success fw-bold"></td></tr>
                        <tr><td><strong>Lương cơ bản:</strong></td><td id="view-salary" class="fw-bold text-danger"></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="employee/process_edit.php">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fa fa-edit"></i> Sửa thông tin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="employeeId" id="edit-employee-id">
                    <div class="mb-3"><label class="fw-bold">Tên nhân viên</label><input type="text" class="form-control" id="edit-name" name="name" required></div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Chi nhánh</label>
                            <select class="form-select" id="edit-store-id" name="store_id" required>
                                <?php foreach ($stores as $s): ?>
                                    <option value="<?= $s->Id ?>"><?= htmlspecialchars($s->StoreName) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Chức vụ</label>
                            <select class="form-select" id="edit-role-id" name="role_id" required>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r->Id ?>"><?= htmlspecialchars($r->RoleName) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="fw-bold">Lương cơ bản</label><input type="number" class="form-control" id="edit-salary" name="salary" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="employee/process_create.php">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Thêm nhân viên mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="fw-bold">Tên nhân viên</label><input type="text" class="form-control" name="name" required></div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Chi nhánh</label>
                            <select class="form-select" name="store_id" required>
                                <?php foreach ($stores as $s): ?>
                                    <option value="<?= $s->Id ?>"><?= htmlspecialchars($s->StoreName) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Chức vụ</label>
                            <select class="form-select" name="role_id" required>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r->Id ?>"><?= htmlspecialchars($r->RoleName) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="fw-bold">Lương cơ bản</label><input type="number" class="form-control" name="salary" required></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-success">Thêm mới</button></div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let employeesData = [];

        // HÀM MỚI: Bơm thẳng dữ liệu vào form Xem Chi Tiết
        function viewEmployeeDetail(id, name, store, role, salary) {
            document.getElementById('view-id').innerText = id;
            document.getElementById('view-name').innerText = name;
            document.getElementById('view-store-id').innerText = store;
            document.getElementById('view-role-id').innerText = role;
            document.getElementById('view-salary').innerText = new Intl.NumberFormat('vi-VN').format(salary) + " ₫";
            
            // Kích hoạt Modal lên
            var viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
            viewModal.show();
        }

        function deleteEmployee(id, name) {
            if (confirm(`Bạn có chắc muốn xóa nhân viên này?`)) {
                const formData = new FormData();
                formData.append('employeeId', id);
                
                fetch('employee/process_delete.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) { 
                        alert('✅ Đã xóa thành công!'); location.reload(); 
                    } else { 
                        alert('❌ Lỗi: ' + data.message); 
                    }
                }).catch(err => alert('❌ Lỗi kết nối máy chủ!'));
            }
        }

        function removeAccents(str) {
            if (!str) return "";
            return str.normalize('NFD').replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();
        }

        function filterEmployees() {
            const storeId = document.getElementById('storeFilter').value;
            const searchKeyword = removeAccents(document.getElementById('searchInput').value);
            const rows = document.querySelectorAll('.employee-row');
            
            rows.forEach(row => {
                const rowStore = row.getAttribute('data-store');
                const rowName = removeAccents(row.getAttribute('data-name') || "");
                const matchStore = (storeId === 'all' || rowStore === storeId);
                const matchName = rowName.includes(searchKeyword);
                row.style.display = (matchStore && matchName) ? '' : 'none';
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            try {
                const dataElement = document.getElementById('employees-data');
                if (dataElement) { employeesData = JSON.parse(dataElement.getAttribute('data-json')); }
            } catch(e) { console.error("Lỗi JSON: ", e); }

            // Giữ lại logic cũ cho Modal Edit vì nó đang hoạt động bình thường
            const editModal = document.getElementById('editModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget.closest('button');
                    if (!button) return;
                    const id = button.getAttribute('data-bs-id');
                    const emp = employeesData.find(e => e.Id == id);
                    if (!emp) return;

                    document.getElementById('edit-employee-id').value = emp.Id;
                    document.getElementById('edit-name').value = emp.FullName;
                    document.getElementById('edit-store-id').value = emp.StoreId;
                    document.getElementById('edit-role-id').value = emp.RoleId;
                    document.getElementById('edit-salary').value = emp.Salary;
                });
            }
        });
    </script>
</body>
</html>