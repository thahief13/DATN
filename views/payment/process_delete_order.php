<?php
session_start();
// Gọi env.php để kết nối DB kiểm tra thời gian
require_once '../../env.php'; 
require_once '../../controllers/CustomerPaymentController.php';

if (!isset($_SESSION['CustomerId'])) {
    die(json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập lại']));
}

$customerId = $_SESSION['CustomerId'];
$paymentId = (int)$_POST['paymentId'] ?? 0;

if ($paymentId <= 0) {
    die(json_encode(['success' => false, 'message' => 'ID đơn hàng không hợp lệ']));
}

date_default_timezone_set('Asia/Ho_Chi_Minh');

// --- BƯỚC 1: KIỂM TRA BẢO MẬT THỜI GIAN TRONG DATABASE ---
global $hostname, $username, $password, $dbname, $port;
$conn = new mysqli($hostname, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu']));
}

$stmt = $conn->prepare("SELECT CreatedAt, Status FROM payment WHERE Id = ? AND CustomerId = ?");
$stmt->bind_param("ii", $paymentId, $customerId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die(json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']));
}

$order = $res->fetch_assoc();
$stmt->close();
$conn->close();

// Kiểm tra trạng thái
if ($order['Status'] !== 'pending') {
    die(json_encode(['success' => false, 'message' => 'Chỉ có thể hủy đơn hàng đang chờ xử lý']));
}


$createdAt = strtotime($order['CreatedAt']);
$now = time();
$diffMinutes = floor(($now - $createdAt) / 60);

if ($diffMinutes > 15) {
    die(json_encode(['success' => false, 'message' => 'Đã quá 15 phút kể từ lúc đặt. Bạn không thể tự hủy đơn hàng này nữa!']));
}


$paymentController = new CustomerPaymentController();
$success = $paymentController->deletePayment($paymentId, $customerId);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Đã hủy đơn hàng thành công!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể xóa đơn hàng từ máy chủ']);
}
?>