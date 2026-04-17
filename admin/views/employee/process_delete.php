<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../controllers/EmployeeAdminController.php';

$employeeId = intval($_POST['employeeId'] ?? 0);
if ($employeeId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Mã không hợp lệ']);
    exit();
}

$controller = new EmployeeAdminController();
if ($controller->deleteEmployeeById($employeeId)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi xóa Database']);
}