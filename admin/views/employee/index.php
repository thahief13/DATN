<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['CustomerId'])) {
    header('Location: ../../../views/home/index.php');
    exit();
}

require_once __DIR__ . '/../../../controllers/CustomerController.php';
require_once __DIR__ . '/../../controllers/EmployeeAdminController.php';
require_once __DIR__ . '/../../controllers/RoleAdminController.php';
require_once __DIR__ . '/../../controllers/StoreAdminController.php';

$employeeController = new EmployeeAdminController();
$employeeAdmins = $employeeController->getAllEmployees();

$roleController = new RoleAdminController();
$roles = $roleController->getAllRoles();

$storeController = new StoreAdminController();
$stores = $storeController->getAllStores();

// --- BƯỚC 1: CHUẨN BỊ BẢN ĐỒ DỮ LIỆU (FIX LỖI OBJECT AS ARRAY) ---
$roleMap = [];
foreach ($roles as $r) {
    $roleMap[$r->Id] = $r->RoleName;
}

$storeMap = [];
foreach ($stores as $s) {
    // SỬA TẠI ĐÂY: Dùng ->Id thay vì ['Id'] vì $s là Object StoreAdmin
    $storeMap[$s->Id] = $s->StoreName; 
}

// --- BƯỚC 2: CHUẨN BỊ DỮ LIỆU CHO JAVASCRIPT ---
$empArrayForJs = [];
$uniqueStores = [];
foreach ($employeeAdmins as $emp) {
    $rName = isset($roleMap[$emp->RoleId]) ? $roleMap[$emp->RoleId] : 'Chưa phân công';
    $sName = isset($storeMap[$emp->StoreId]) ? $storeMap[$emp->StoreId] : 'Chi nhánh ' . $emp->StoreId;

    $empArrayForJs[] = [
        'Id' => $emp->Id,
        'FullName' => $emp->FullName,
        'StoreId' => $emp->StoreId,
        'StoreName' => $sName,
        'RoleId' => $emp->RoleId,
        'RoleName' => $rName,
        'Salary' => $emp->Salary
    ];
    if ($emp->StoreId && !in_array($emp->StoreId, $uniqueStores)) {
        $uniqueStores[] = $emp->StoreId;
    }
}
sort($uniqueStores);

$employeesJson = json_encode($empArrayForJs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?: '[]';
$rolesJson = json_encode($roles, JSON_UNESCAPED_UNICODE) ?: '[]';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý nhân viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .table-wrapper { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); }
        .salary-result { font-size: 24px; font-weight: bold; color: #d35400; text-align: center; background: #fdf2e9; padding: 15px; border-radius: 8px; border: 1px dashed #e67e22; }
        .modal-header.bg-info { background-color: #0dcaf0 !important; }
    </style>
</head>
<body>
    <script id="employees-data" type="application/json"><?= $employeesJson ?></script>
    <script id="roles-data" type="application/json"><?= $rolesJson ?></script>

    <div class="container my-5">
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
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead>
                        <tr class="table-dark">
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
                            <tr class="employee-row" data-store="<?= $emp['StoreId'] ?>" data-name="<?= htmlspecialchars($emp['FullName'], ENT_QUOTES) ?>">
                                <td><strong>#<?= $emp['Id'] ?></strong></td>
                                <td class="text-start fw-bold text-primary"><?= htmlspecialchars($emp['FullName']) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($emp['StoreName']) ?></span></td>
                                <td><strong class="<?= $emp['RoleId'] == 0 ? 'text-danger' : 'text-success' ?>"><?= htmlspecialchars($emp['RoleName']) ?></strong></td>
                                <td class="text-end"><?= number_format($emp['Salary'], 0, ',', '.') ?> ₫</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-info text-white" title="Xem" data-bs-toggle="modal" data-bs-target="#viewModal" data-bs-id="<?= $emp['Id'] ?>"><i class="fa fa-eye"></i></button>
                                        <button class="btn btn-warning" title="Sửa" data-bs-toggle="modal" data-bs-target="#editModal" data-bs-id="<?= $emp['Id'] ?>"><i class="fa fa-edit"></i></button>
                                        <button class="btn btn-success" title="Tính lương" data-bs-toggle="modal" data-bs-target="#salaryModal" data-bs-id="<?= $emp['Id'] ?>"><i class="fas fa-money-check-alt"></i></button>
                                        <button class="btn btn-danger" title="Xóa" onclick="deleteEmployee(<?= $emp['Id'] ?>, '<?= htmlspecialchars($emp['FullName'], ENT_QUOTES) ?>')"><i class="fa fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="employee/process_create.php">
                <div class="modal-header bg-success text-white"><h5>Thêm nhân viên mới</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="fw-bold">Tên nhân viên</label><input type="text" class="form-control" name="name" required></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="fw-bold">Mã chi nhánh</label><input type="number" class="form-control" name="store_id" required></div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Chức vụ</label>
                            <select class="form-select" name="role_id" required>
                                <option value="">-- Chọn --</option>
                                <?php foreach ($roles as $r): ?><option value="<?= $r->Id ?>"><?= htmlspecialchars($r->RoleName) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="fw-bold">Lương cơ bản</label><input type="number" class="form-control" name="salary" min="0" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-success">Xác nhận</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="employee/process_edit.php">
                <div class="modal-header bg-warning text-dark"><h5>Sửa thông tin</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="employeeId" id="edit-employee-id">
                    <div class="mb-3"><label class="fw-bold">Tên nhân viên</label><input type="text" class="form-control" id="edit-name" name="name" required></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="fw-bold">Chi nhánh</label><input type="number" class="form-control" id="edit-store-id" name="store_id" required></div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Chức vụ</label>
                            <select class="form-select" id="edit-role-id" name="role_id" required>
                                <option value="0">Chưa phân công</option>
                                <?php foreach ($roles as $r): ?><option value="<?= $r->Id ?>"><?= htmlspecialchars($r->RoleName) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="fw-bold">Lương cơ bản</label><input type="number" class="form-control" id="edit-salary" name="salary" min="0" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-warning">Lưu thay đổi</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white"><h5><i class="fas fa-id-card"></i> Hồ sơ nhân viên</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
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

    <div class="modal fade" id="salaryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white"><h5>Bảng tính lương tháng</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label>Nhân viên:</label><input type="text" class="form-control fw-bold" id="calc-name" readonly></div>
                    <div class="mb-3"><label>Lương cơ bản:</label><input type="number" class="form-control" id="calc-base" readonly></div>
                    <div class="row">
                        <div class="col-6 mb-3"><label>Ngày công:</label><input type="number" class="form-control" id="calc-days" value="26" min="0" oninput="calculatePayroll()"></div>
                        <div class="col-6 mb-3"><label>Thưởng:</label><input type="number" class="form-control" id="calc-bonus" value="0" min="0" oninput="calculatePayroll()"></div>
                    </div>
                    <div class="salary-result mt-3" id="calc-result">0 ₫</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Parser dữ liệu từ script tag ẩn
            const employeesData = JSON.parse(document.getElementById('employees-data').textContent);

            // Hàm xóa dấu tiếng Việt để tìm kiếm
            function removeAccents(str) {
                return str.normalize('NFD').replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();
            }

            // --- CHỨC NĂNG LỌC & TÌM KIẾM ---
            window.filterEmployees = function() {
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

            // --- CHỨC NĂNG XÓA ---
            window.deleteEmployee = function(id, name) {
                if (confirm(`Bạn có chắc muốn xóa nhân viên: ${name}?`)) {
                    const formData = new FormData();
                    formData.append('employeeId', id);
                    fetch('employee/process_delete.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) { alert('Đã xóa thành công!'); location.reload(); }
                        else { alert('Lỗi: ' + data.message); }
                    });
                }
            }

            // --- TÍNH LƯƠNG ---
            window.calculatePayroll = function() {
                const base = parseFloat(document.getElementById('calc-base').value) || 0;
                const days = parseFloat(document.getElementById('calc-days').value) || 0;
                const bonus = parseFloat(document.getElementById('calc-bonus').value) || 0;
                const total = Math.round((base / 26) * days + bonus);
                document.getElementById('calc-result').innerText = new Intl.NumberFormat('vi-VN').format(total) + " ₫";
            }

            // --- ĐIỀN DỮ LIỆU VÀO MODAL ---
            const modals = ['viewModal', 'editModal', 'salaryModal'].map(id => document.getElementById(id));
            modals.forEach(modal => {
                if(!modal) return;
                modal.addEventListener('show.bs.modal', function(event) {
                    const id = event.relatedTarget.getAttribute('data-bs-id');
                    const emp = employeesData.find(e => e.Id == id);
                    if (!emp) return;

                    if (modal.id === 'viewModal') {
                        document.getElementById('view-id').innerText = emp.Id;
                        document.getElementById('view-name').innerText = emp.FullName;
                        document.getElementById('view-store-id').innerText = emp.StoreName;
                        document.getElementById('view-role-id').innerText = emp.RoleName;
                        document.getElementById('view-salary').innerText = new Intl.NumberFormat('vi-VN').format(emp.Salary) + " ₫";
                    }
                    if (modal.id === 'editModal') {
                        document.getElementById('edit-employee-id').value = emp.Id;
                        document.getElementById('edit-name').value = emp.FullName;
                        document.getElementById('edit-store-id').value = emp.StoreId;
                        document.getElementById('edit-role-id').value = emp.RoleId;
                        document.getElementById('edit-salary').value = emp.Salary;
                    }
                    if (modal.id === 'salaryModal') {
                        document.getElementById('calc-name').value = emp.FullName;
                        document.getElementById('calc-base').value = emp.Salary;
                        document.getElementById('calc-days').value = 26;
                        document.getElementById('calc-bonus').value = 0;
                        calculatePayroll();
                    }
                });
            });
        });
    </script>
</body>
</html>