<?php
session_start();
header('Content-Type: application/json');
require_once '../../env.php'; 

if (!isset($_SESSION['CustomerId'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập lại']);
    exit();
}

$customerId = $_SESSION['CustomerId'];
$paymentId = (int)($_POST['paymentId'] ?? 0);

if ($paymentId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID đơn hàng không hợp lệ']);
    exit();
}

date_default_timezone_set('Asia/Ho_Chi_Minh');

global $hostname, $username, $password, $dbname, $port;
$conn = new mysqli($hostname, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu']);
    exit();
}

$stmt = $conn->prepare("SELECT CreatedAt, Status FROM payment WHERE Id = ? AND CustomerId = ?");
$stmt->bind_param("ii", $paymentId, $customerId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng hoặc không có quyền xóa']);
    $stmt->close();
    $conn->close();
    exit();
}

$order = $res->fetch_assoc();
$stmt->close();

$statusStr = mb_strtolower($order['Status'], 'UTF-8');
if ($statusStr !== 'pending' && $statusStr !== 'đang xử lý' && $statusStr !== 'chờ thanh toán') {
    echo json_encode(['success' => false, 'message' => 'Chỉ có thể hủy đơn hàng đang chờ xử lý']);
    $conn->close();
    exit();
}

$createdAt = strtotime($order['CreatedAt']);
$now = time();
$diffMinutes = floor(($now - $createdAt) / 60);

if ($diffMinutes > 15) {
    echo json_encode(['success' => false, 'message' => 'Đã quá 15 phút kể từ lúc đặt. Bạn không thể tự hủy đơn hàng này nữa!']);
    $conn->close();
    exit();
}


$conn->begin_transaction();

try {
    
    $stmtShip = $conn->prepare("DELETE FROM shipment WHERE PaymentId = ?");
    $stmtShip->bind_param("i", $paymentId);
    $stmtShip->execute();
    $stmtShip->close();

    
    $stmtDetail = $conn->prepare("DELETE FROM paymentdetail WHERE PaymentId = ?");
    $stmtDetail->bind_param("i", $paymentId);
    $stmtDetail->execute();
    $stmtDetail->close();

    
    $stmtPay = $conn->prepare("DELETE FROM payment WHERE Id = ?");
    $stmtPay->bind_param("i", $paymentId);
    $stmtPay->execute();
    $stmtPay->close();

    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Đã hủy đơn hàng thành công!']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi xóa: ' . $e->getMessage()]);
}

$conn->close();
?>