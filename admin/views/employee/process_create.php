<?php
session_start();
require_once __DIR__ . '/../../controllers/EmployeeAdminController.php';
require_once __DIR__ . '/../../models/EmployeeAdmin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp = new EmployeeAdmin();
    $emp->FullName = trim($_POST['name'] ?? '');
    $emp->StoreId = intval($_POST['store_id'] ?? 0);
    $emp->RoleId = intval($_POST['role_id'] ?? 0);
    $emp->Salary = floatval($_POST['salary'] ?? 0);

    if (empty($emp->FullName) || $emp->StoreId <= 0 || $emp->RoleId <= 0) {
        $_SESSION['error_message'] = "Vui lòng nhập đầy đủ Họ tên, Chi nhánh và Chức vụ hợp lệ!";
    } else {
        $controller = new EmployeeAdminController();
        $result = $controller->addEmployee($emp);
        
        if ($result === true) {
            $_SESSION['success_message'] = "🎉 Đã thêm nhân viên thành công!";
        } else {
            // Trả về lỗi CSDL nếu thất bại
            $_SESSION['error_message'] = $result; 
        }
    }
}

 header('Location: ../index.php?page=employee');
exit();
?>