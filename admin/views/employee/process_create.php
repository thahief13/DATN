<?php
session_start();
require_once __DIR__ . '/../../controllers/EmployeeAdminController.php';
require_once __DIR__ . '/../../models/EmployeeAdmin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $store_id = intval($_POST['store_id'] ?? 0);
    $role_id = intval($_POST['role_id'] ?? 0);
    $salary = floatval($_POST['salary'] ?? 0);

    // Validate dữ liệu cơ bản
    if (!empty($name) && $store_id > 0) {
        $emp = new EmployeeAdmin();
        $emp->FullName = $name;
        $emp->StoreId = $store_id;
        $emp->RoleId = $role_id;
        $emp->Salary = $salary;

        $controller = new EmployeeAdminController();
        $controller->addEmployee($emp);
    }
    
    // Luôn redirect
    header("Location: ../index.php?page=employee");
    exit();
}

// Nếu truy cập bằng GET, đẩy về
header("Location: ../index.php?page=employee");
exit();
?>