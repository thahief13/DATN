<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../controllers/EmployeeAdminController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ']);
    exit();
}

$employeeId = intval($_POST['employeeId'] ?? 0);
if ($employeeId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Mã nhân viên không hợp lệ']);
    exit();
}

$controller = new EmployeeAdminController();
$result = $controller->deleteEmployeeById($employeeId);

if ($result === true) {
    echo json_encode(['success' => true, 'message' => 'Đã xóa thành công']);
} else {
    // Trả về lỗi CSDL trực tiếp cho màn hình Javascript
    echo json_encode(['success' => false, 'message' => $result]);
}
?>