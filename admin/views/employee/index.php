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

require_once __DIR__ . '/../../../env.php';
global $hostname, $username, $password, $dbname, $port;
$dbAuth = new mysqli($hostname, $username, $password, $dbname, $port);
$authQuery = "SELECT Role, StoreId FROM customer WHERE Id = " . (int)$_SESSION['CustomerId'];
$authResult = $dbAuth->query($authQuery);
$authData = $authResult->fetch_assoc();

$userRole = (int)($authData['Role'] ?? 1);
$userStoreId = (int)($authData['StoreId'] ?? 0);
$dbAuth->close();

if ($userRole == 0) { // Chặn người dùng bình thường
    header('Location: ../../../views/home/index.php');
    exit();
}

require_once __DIR__ . '/../../controllers/EmployeeAdminController.php';
require_once __DIR__ . '/../../controllers/RoleAdminController.php';
require_once __DIR__ . '/../../controllers/StoreAdminController.php';

$employeeController = new EmployeeAdminController();
$employeeAdmins = $employeeController->getAllEmployees(); // Hàm trong Controller đã tự chặn quyền

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

// --- BIẾN CHỨA DỮ LIỆU CHO BIỂU ĐỒ ---
$chartRoleCount = [];
$chartRoleSalary = [];
$totalEmployees = 0;
$totalSalary = 0;
// -------------------------------------

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

    // TÍNH TOÁN DỮ LIỆU BIỂU ĐỒ
    if (!isset($chartRoleCount[$rName])) {
        $chartRoleCount[$rName] = 0;
        $chartRoleSalary[$rName] = 0;
    }
    $chartRoleCount[$rName]++;
    $chartRoleSalary[$rName] += (float)$emp->Salary;
    $totalEmployees++;
    $totalSalary += (float)$emp->Salary;
}
sort($uniqueStores);
$employeesJson = htmlspecialchars(json_encode($empArrayForJs, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

// Định dạng dữ liệu cho Javascript
$chartLabels = array_keys($chartRoleCount);
$chartDataCount = array_values($chartRoleCount);
$chartDataSalary = array_values($chartRoleSalary);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý nhân viên - Cafe Trung Nguyên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .table-wrapper { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.05); }
        .salary-result { font-size: 22px; font-weight: bold; color: #d35400; text-align: center; background: #fdf2e9; padding: 10px; border-radius: 8px; border: 1px dashed #e67e22; }
        .modal-header.bg-info { background-color: #0dcaf0 !important; }
        .employee-row td { vertical-align: middle; }
        .stat-card { border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
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

        <h2 class="text-center mb-4 fw-bold text-dark"><i class="fas fa-users-cog text-primary"></i> Tổng quan Nhân sự</h2>

        <div class="row mb-4 g-4">
            <div class="col-md-3">
                <div class="stat-card bg-primary text-white p-4 h-100 d-flex flex-column justify-content-center align-items-center">
                    <i class="fas fa-users fa-3x mb-3 opacity-75"></i>
                    <h5 class="fw-light">Tổng nhân viên</h5>
                    <h2 class="fw-bold mb-0"><?= $totalEmployees ?></h2>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stat-card bg-white p-3 h-100">
                    <h6 class="text-center fw-bold text-secondary mb-3">Cơ cấu theo Chức vụ</h6>
                    <div style="height: 200px; display: flex; justify-content: center;">
                        <canvas id="roleCountChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="stat-card bg-white p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-secondary mb-0">Quỹ lương theo Chức vụ</h6>
                        <span class="badge bg-danger fs-6"><?= number_format($totalSalary, 0, ',', '.') ?> ₫</span>
                    </div>
                    <div style="height: 200px;">
                        <canvas id="salaryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-wrapper">
            <div class="row mb-3 align-items-center">
                <div class="col-md-4">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal">
                        <i class="fa fa-plus"></i> Thêm nhân viên
                    </button>
                </div>
                
                <div class="col-md-8 d-flex justify-content-end gap-2">
                    <select id="storeFilter" class="form-select w-auto fw-bold" onchange="filterEmployees()" <?= ($userRole == 2) ? 'disabled' : '' ?>>
                        <?php if ($userRole != 2): ?>
                            <option value="all">Tất cả chi nhánh</option>
                        <?php endif; ?>
                        
                        <?php 
                        $hasMyStore = false;
                        foreach ($uniqueStores as $sId): 
                            if ($userRole == 2 && $sId != $userStoreId) continue;
                            $hasMyStore = true;
                        ?>
                            <option value="<?= $sId ?>" <?= ($userRole == 2 && $sId == $userStoreId) ? 'selected' : '' ?>>
                                Chi nhánh #<?= $sId ?> - <?= htmlspecialchars($storeMap[$sId] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                        
                        <?php if ($userRole == 2 && !$hasMyStore): ?>
                            <option value="<?= $userStoreId ?>" selected><?= htmlspecialchars($storeMap[$userStoreId] ?? 'Chi nhánh của bạn') ?></option>
                        <?php endif; ?>
                    </select>

                    <input type="text" class="form-control w-50" id="searchInput" placeholder="Tìm tên nhân viên..." onkeyup="filterEmployees()">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center align-middle">
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
                        <?php if (empty($empArrayForJs)): ?>
                            <tr><td colspan="6" class="text-center py-4">Chưa có nhân viên nào trong chi nhánh này.</td></tr>
                        <?php else: ?>
                            <?php foreach ($empArrayForJs as $emp): ?>
                                <tr class="employee-row" data-store="<?= $emp['StoreId'] ?>" data-name="<?= htmlspecialchars(mb_strtolower($emp['FullName'], 'UTF-8'), ENT_QUOTES) ?>">
                                    <td><strong>#<?= $emp['Id'] ?></strong></td>
                                    <td class="text-start fw-bold text-primary"><?= htmlspecialchars($emp['FullName']) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($emp['StoreName']) ?></span></td>
                                    <td><strong class="text-success"><?= htmlspecialchars($emp['RoleName']) ?></strong></td>
                                    <td class="text-end fw-bold"><?= number_format($emp['Salary'], 0, ',', '.') ?> ₫</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-info" title="Xem" 
                                                onclick="viewEmployeeDetail(
                                                    '<?= $emp['Id'] ?>', 
                                                    '<?= htmlspecialchars($emp['FullName'], ENT_QUOTES) ?>', 
                                                    '<?= htmlspecialchars($emp['StoreName'], ENT_QUOTES) ?>', 
                                                    '<?= htmlspecialchars($emp['RoleName'], ENT_QUOTES) ?>', 
                                                    '<?= $emp['Salary'] ?>'
                                                )">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            
                                            <button type="button" class="btn btn-outline-warning" title="Sửa" data-bs-toggle="modal" data-bs-target="#editModal" data-bs-id="<?= $emp['Id'] ?>">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" title="Xóa" onclick="deleteEmployee(<?= $emp['Id'] ?>, '<?= htmlspecialchars($emp['FullName'], ENT_QUOTES) ?>')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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
                                    <?php if ($userRole == 2 && $s->Id != $userStoreId) continue; ?>
                                    <option value="<?= $s->Id ?>" <?= ($userRole == 2 && $s->Id == $userStoreId) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s->StoreName) ?>
                                    </option>
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
                                    <?php if ($userRole == 2 && $s->Id != $userStoreId) continue; ?>
                                    <option value="<?= $s->Id ?>" <?= ($userRole == 2 && $s->Id == $userStoreId) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s->StoreName) ?>
                                    </option>
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
        // --- KHỞI TẠO BIỂU ĐỒ THÔNG MINH (CHỐNG LỖI AJAX) ---
        function initEmployeeCharts() {
            const bgColors = ['#0dcaf0', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14'];
            
            // 1. Biểu đồ tròn: Cơ cấu nhân sự
            const roleCanvas = document.getElementById('roleCountChart');
            if (roleCanvas) {
                if (window.roleChartInstance) window.roleChartInstance.destroy();
                window.roleChartInstance = new Chart(roleCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: <?= json_encode($chartLabels) ?>,
                        datasets: [{
                            data: <?= json_encode($chartDataCount) ?>,
                            backgroundColor: bgColors,
                            borderWidth: 1
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
                });
            }

            // 2. Biểu đồ cột: Quỹ lương
            const salaryCanvas = document.getElementById('salaryChart');
            if (salaryCanvas) {
                if (window.salaryChartInstance) window.salaryChartInstance.destroy();
                window.salaryChartInstance = new Chart(salaryCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: <?= json_encode($chartLabels) ?>,
                        datasets: [{
                            label: 'Tổng lương (VNĐ)',
                            data: <?= json_encode($chartDataSalary) ?>,
                            backgroundColor: 'rgba(220, 53, 69, 0.8)',
                            borderRadius: 5
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }
        }

        // Tự động kiểm tra và tải thư viện Chart.js nếu tải trang qua AJAX
        if (typeof Chart === 'undefined') {
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.onload = initEmployeeCharts; // Tải xong mới vẽ
            document.head.appendChild(script);
        } else {
            initEmployeeCharts(); // Đã có thư viện thì vẽ luôn
        }
        // -------------------------

        var employeesData = [];
        try {
            var dataElement = document.getElementById('employees-data');
            if (dataElement) { employeesData = JSON.parse(dataElement.getAttribute('data-json')); }
        } catch(e) { console.error("Lỗi JSON: ", e); }

        function viewEmployeeDetail(id, name, store, role, salary) {
            document.getElementById('view-id').innerText = id;
            document.getElementById('view-name').innerText = name;
            document.getElementById('view-store-id').innerText = store;
            document.getElementById('view-role-id').innerText = role;
            document.getElementById('view-salary').innerText = new Intl.NumberFormat('vi-VN').format(salary) + " ₫";
            new bootstrap.Modal(document.getElementById('viewModal')).show();
        }

        function deleteEmployee(id, name) {
            if (confirm(`Bạn có chắc muốn xóa nhân viên này?`)) {
                var formData = new FormData();
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
            var storeId = document.getElementById('storeFilter').value;
            var searchKeyword = removeAccents(document.getElementById('searchInput').value);
            var rows = document.querySelectorAll('.employee-row');
            
            rows.forEach(row => {
                var rowStore = row.getAttribute('data-store');
                var rowName = removeAccents(row.getAttribute('data-name') || "");
                var matchStore = (storeId === 'all' || rowStore === storeId);
                var matchName = rowName.includes(searchKeyword);
                row.style.display = (matchStore && matchName) ? '' : 'none';
            });
        }

        if (window.editEmployeeHandler) { document.removeEventListener('show.bs.modal', window.editEmployeeHandler); }

        window.editEmployeeHandler = function(event) {
            var modal = event.target;
            if (modal.id !== 'editModal') return;

            var button = event.relatedTarget;
            if (!button) return;
            
            var buttonClosest = button.closest('button');
            if (!buttonClosest || !buttonClosest.hasAttribute('data-bs-id')) return;

            var id = buttonClosest.getAttribute('data-bs-id');
            var emp = employeesData.find(e => e.Id == id);
            if (!emp) return;

            document.getElementById('edit-employee-id').value = emp.Id;
            document.getElementById('edit-name').value = emp.FullName;
            document.getElementById('edit-store-id').value = emp.StoreId;
            document.getElementById('edit-role-id').value = emp.RoleId;
            document.getElementById('edit-salary').value = emp.Salary;
        };

        document.addEventListener('show.bs.modal', window.editEmployeeHandler);
    </script>
</body>
</html>