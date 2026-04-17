<?php
session_start();
require_once __DIR__ . '/../../controllers/EmployeeAdminController.php';
require_once __DIR__ . '/../../models/EmployeeAdmin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['employeeId'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $store_id = intval($_POST['store_id'] ?? 0);
    $role_id = intval($_POST['role_id'] ?? 0);
    $salary = floatval($_POST['salary'] ?? 0);

    // Validate ID và Name
    if ($id > 0 && !empty($name)) {
        $emp = new EmployeeAdmin();
        $emp->Id = $id;
        $emp->FullName = $name;
        $emp->StoreId = $store_id;
        $emp->RoleId = $role_id;
        $emp->Salary = $salary;

        $controller = new EmployeeAdminController();
        $controller->updateEmployee($emp);
    }
    
    header("Location: ../index.php?page=employee");
    exit();
}

header("Location: ../index.php?page=employee");
exit();
?>