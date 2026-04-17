<?php
session_start();
error_reporting(0); // Tắt lỗi HTML rác để không làm hỏng chuỗi JSON
header('Content-Type: application/json');

require_once __DIR__ . '/../../controllers/RoleAdminController.php';

$roleId = intval($_POST['roleId'] ?? 0);

if ($roleId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Mã chức vụ không hợp lệ!']);
    exit();
}

try {
    $controller = new RoleAdminController();
    $result = $controller->deleteRoleById($roleId);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Đã xóa chức vụ thành công']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể xóa chức vụ này. Vui lòng thử lại.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>