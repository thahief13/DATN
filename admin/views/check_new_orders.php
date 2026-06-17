<?php
session_start();

require_once __DIR__ . '/../../env.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION['CustomerId'])) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: Chưa đăng nhập']);
    exit;
}

$customerId = (int)$_SESSION['CustomerId'];

global $hostname, $username, $password, $dbname, $port;
$db = new mysqli($hostname, $username, $password, $dbname, $port);
$db->set_charset("utf8mb4");

$sqlUser = "SELECT Role, StoreId FROM customer WHERE Id = $customerId";
$resUser = $db->query($sqlUser);
$role = 0;
$storeId = 0;
if ($resUser && $resUser->num_rows > 0) {
    $u = $resUser->fetch_assoc();
    $role = (int)$u['Role'];
    $storeId = (int)$u['StoreId'];
}

if ($role == 0) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: Không phải Admin']);
    $db->close();
    exit;
}

$sql = "SELECT Id, Status FROM payment";
if ($role == 2 && $storeId > 0) {
    $sql .= " WHERE StoreId = " . $storeId;
}

$res = $db->query($sql);
if (!$res) {
    echo json_encode(['success' => false, 'message' => 'Lỗi SQL: ' . $db->error]);
    exit;
}

$currentOrders = [];
while ($row = $res->fetch_assoc()) {
    $currentOrders[$row['Id']] = trim(mb_strtolower($row['Status'], 'UTF-8'));
}
$db->close();

$notifications = [];

if (!isset($_SESSION['AdminOrderStatuses'])) {
    $_SESSION['AdminOrderStatuses'] = $currentOrders;
    session_write_close();
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

$oldOrders = $_SESSION['AdminOrderStatuses'];

foreach ($currentOrders as $orderId => $status) {

    if (!isset($oldOrders[$orderId])) {
        $notifications[] = [
            'Type' => 'new',
            'Message' => 'Bạn vừa nhận được đơn hàng mới! (Mã đơn: #' . $orderId . ')',
            'OrderId' => $orderId
        ];
    } 
    else {
        $oldStatus = $oldOrders[$orderId];
        if ($oldStatus != $status) {
            if ($status === 'hủy' || $status === 'đã hủy') {
                $notifications[] = [
                    'Type' => 'cancel',
                    'Message' => 'Đơn hàng #' . $orderId . ' vừa bị khách HỦY!',
                    'OrderId' => $orderId
                ];
            }
        }
    }
}

$_SESSION['AdminOrderStatuses'] = $currentOrders;
session_write_close();

echo json_encode(['success' => true, 'data' => $notifications]);
?>