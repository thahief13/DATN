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
$employeeAdmins = [];

if ($customer && $customer->Role) {
    require_once __DIR__ . '/../../controllers/EmployeeAdminController.php';

    $employeeController = new EmployeeAdminController();
    $employeeAdmins = $employeeController->getAllEmployees();
} else {
    header('Location: ../../../views/home/index.php');
    exit();
}

$employeesJson = json_encode($employeeAdmins, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý nhân viên</title>
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
        <h1>Quản lý nhân viên</h1>
        <div class="table-wrapper">
            <div class="d-flex justify-content-between mb-3">
                <button class="btn btn-success btn-add" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="fa fa-plus"></i> Thêm nhân viên mới
                </button>
                <div class="d-flex">
                    <input type="text" class="form-control me-2" placeholder="Tìm kiếm nhân viên...">
                    <button class="btn btn-primary">Tìm kiếm</button>
                    <button class="btn btn-outline-dark ms-2">Quay lại danh sách</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Mã NV</th>
                            <th>Tên nhân viên</th>
                            <th>Mã cửa hàng</th>
                            <th>Mã vai trò</th>
                            <th>Lương</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($employeeAdmins)): ?>
                            <?php foreach ($employeeAdmins as $employee): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($employee->Id); ?></td>
                                    <td><?php echo htmlspecialchars($employee->FullName); ?></td>
                                    <td><?php echo htmlspecialchars($employee->StoreId); ?></td>
                                    <td><?php echo htmlspecialchars($employee->RoleId); ?></td>
                                    <td><?php echo number_format($employee->Salary, 0, ",", "."); ?></td>
                                    <td>
                                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal" data-bs-id="<?php echo $employee->Id; ?>"><i class="fa fa-eye"></i></button>
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" data-bs-id="<?php echo $employee->Id; ?>"><i class="fa fa-edit"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Không có nhân viên nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-4">
                <button class="btn btn-outline-dark mx-1">&laquo;</button>
                <button class="btn btn-outline-dark active mx-1">1</button>
                <button class="btn btn-outline-dark mx-1">2</button>
                <button class="btn btn-outline-dark mx-1">3</button>
                <button class="btn btn-outline-dark mx-1">&raquo;</button>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="process_create.php">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm nhân viên mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label>Tên nhân viên</label><input type="text" class="form-control" name="name" required></div>
                    <div class="mb-3"><label>Mã cửa hàng</label><input type="number" class="form-control" name="store_id" required></div>
                    <div class="mb-3"><label>Mã vai trò</label><input type="number" class="form-control" name="role_id" required></div>
                    <div class="mb-3"><label>Lương</label><input type="number" class="form-control" name="salary" min="0" required></div>
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
            <form class="modal-content" method="POST" action="process_edit.php">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa nhân viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="employeeId" id="edit-employee-id">
                    <div class="mb-3"><label>Tên nhân viên</label><input type="text" class="form-control" id="edit-name" name="name" required></div>
                    <div class="mb-3"><label>Mã cửa hàng</label><input type="number" class="form-control" id="edit-store-id" name="store_id" required></div>
                    <div class="mb-3"><label>Mã vai trò</label><input type="number" class="form-control" id="edit-role-id" name="role_id" required></div>
                    <div class="mb-3"><label>Lương</label><input type="number" class="form-control" id="edit-salary" name="salary" min="0" required></div>
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
                    <h5 class="modal-title">Chi tiết nhân viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Mã NV:</strong> <span id="view-id"></span></p>
                    <p><strong>Tên nhân viên:</strong> <span id="view-name"></span></p>
                    <p><strong>Mã cửa hàng:</strong> <span id="view-store-id"></span></p>
                    <p><strong>Mã vai trò:</strong> <span id="view-role-id"></span></p>
                    <p><strong>Lương:</strong> <span id="view-salary"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const employeesData = <?php echo $employeesJson; ?>;

        const viewModal = document.getElementById('viewModal');
        const editModal = document.getElementById('editModal');

        [viewModal, editModal].forEach(modal => {
            modal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                if (!button) return;
                const id = button.getAttribute('data-bs-id');
                const employee = employeesData.find(e => e.Id == id);
                if (!employee) return;

                if (modal.id === 'viewModal') {
                    document.getElementById('view-id').innerText = employee.Id;
                    document.getElementById('view-name').innerText = employee.Name;
                    document.getElementById('view-store-id').innerText = employee.StoreId;
                    document.getElementById('view-role-id').innerText = employee.RoleId;
                    document.getElementById('view-salary').innerText = new Intl.NumberFormat('vi-VN', {
                        style: 'currency',
                        currency: 'VND'
                    }).format(employee.Salary);
                }

                if (modal.id === 'editModal') {
                    document.getElementById('edit-employee-id').value = employee.Id;
                    document.getElementById('edit-name').value = employee.Name;
                    document.getElementById('edit-store-id').value = employee.StoreId;
                    document.getElementById('edit-role-id').value = employee.RoleId;
                    document.getElementById('edit-salary').value = employee.Salary;
                }
            });
        });
    </script>
</body>

</html>