<?php
session_start();
require_once __DIR__ . '/../../controllers/EmployeeAdminController.php';
require_once __DIR__ . '/../../models/EmployeeAdmin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp = new EmployeeAdmin();
    $emp->Id = intval($_POST['employeeId'] ?? 0);
    $emp->FullName = trim($_POST['name'] ?? '');
    $emp->StoreId = intval($_POST['store_id'] ?? 0);
    $emp->RoleId = intval($_POST['role_id'] ?? 0);
    $emp->Salary = floatval($_POST['salary'] ?? 0);

    if ($emp->Id > 0 && !empty($emp->FullName)) {
        $controller = new EmployeeAdminController();
        $result = $controller->updateEmployee($emp);
        
        if ($result === true) {
            $_SESSION['success_message'] = "🎉 Cập nhật thông tin thành công!";
        } else {
            $_SESSION['error_message'] = $result;
        }
    } else {
        $_SESSION['error_message'] = "Dữ liệu không hợp lệ!";
    }
}

header('Location: ../index.php?page=employee');
exit();
?>