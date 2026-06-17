<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../../env.php';
require_once __DIR__ . '/../../controllers/PaymentAdminController.php';
require_once __DIR__ . '/../../controllers/RevenueAdminController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận POST']);
    exit();
}

$paymentId = intval($_POST['paymentId'] ?? 0);
$newStatus = trim($_POST['status'] ?? '');

if ($paymentId <= 0 || empty($newStatus)) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit();
}

global $hostname, $username, $password, $dbname, $port;
$db = new mysqli($hostname, $username, $password, $dbname, $port);
$db->set_charset("utf8mb4");

$stmtInfo = $db->prepare("SELECT StoreId, Total, Status FROM payment WHERE Id = ?");
$stmtInfo->bind_param("i", $paymentId);
$stmtInfo->execute();
$order = $stmtInfo->get_result()->fetch_assoc();
$stmtInfo->close();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
    $db->close();
    exit();
}

$oldStatus = mb_strtolower(trim($order['Status']), 'UTF-8');
$newStatusLower = mb_strtolower($newStatus, 'UTF-8');

$db->begin_transaction();

try {
    $preDelivery = ['pending', 'đang xử lý', 'chờ thanh toán'];
    $postDelivery = ['đang giao', 'đã giao', 'thành công'];
    $cancelStates = ['hủy', 'đã hủy'];

   
    if (in_array($oldStatus, $preDelivery) && in_array($newStatusLower, $postDelivery)) {
        
       
        $stmtDetail = $db->prepare("SELECT pd.StoreProductId, pd.Quantity, sp.Stock, p.Title 
                                    FROM paymentdetail pd 
                                    JOIN storeproduct sp ON pd.StoreProductId = sp.Id 
                                    JOIN product p ON sp.ProductId = p.Id 
                                    WHERE pd.PaymentId = ?");
        $stmtDetail->bind_param("i", $paymentId);
        $stmtDetail->execute();
        $resDetail = $stmtDetail->get_result();

        $itemsToDeduct = [];
        
        // Vòng lặp check kho 
        while ($detail = $resDetail->fetch_assoc()) {
            $stock = (int)$detail['Stock'];
            $qty = (int)$detail['Quantity'];
            
          
            if ($stock < $qty) {
                throw new Exception("Sản phẩm '" . $detail['Title'] . "' chỉ còn " . $stock . " ly, không đủ để giao " . $qty . " ly! Vui lòng tải lại trang.");
            }
            $itemsToDeduct[] = $detail;
        }
        $stmtDetail->close();

        // Vượt qua được vòng check an toàn ở trên thì mới bắt đầu trừ kho
        $stmtDeductStock = $db->prepare("UPDATE storeproduct SET Stock = Stock - ? WHERE Id = ?");
        foreach ($itemsToDeduct as $item) {
            $stmtDeductStock->bind_param("ii", $item['Quantity'], $item['StoreProductId']);
            $stmtDeductStock->execute();
        }
        $stmtDeductStock->close();
    }

    
    if (in_array($oldStatus, $postDelivery) && in_array($newStatusLower, $cancelStates)) {
        $stmtDetail = $db->prepare("SELECT StoreProductId, Quantity FROM paymentdetail WHERE PaymentId = ?");
        $stmtDetail->bind_param("i", $paymentId);
        $stmtDetail->execute();
        $resDetail = $stmtDetail->get_result();

        $stmtRestoreStock = $db->prepare("UPDATE storeproduct SET Stock = Stock + ? WHERE Id = ?");
        while ($detail = $resDetail->fetch_assoc()) {
            $qty = (int)$detail['Quantity'];
            $spId = (int)$detail['StoreProductId'];
            $stmtRestoreStock->bind_param("ii", $qty, $spId);
            $stmtRestoreStock->execute();
        }
        $stmtRestoreStock->close();
        $stmtDetail->close();
    }

    $db->commit();
} catch (Exception $e) {
    // Nếu bị lỗi Thiếu hàng, hệ thống sẽ thông báo ra giao diện
    $db->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    $db->close();
    exit();
}


$paymentController = new PaymentAdminController();
$result = $paymentController->updatePaymentStatus($paymentId, $newStatus);

if ($result) {
    $stmtUpdateShip = $db->prepare("UPDATE shipment SET Status = ? WHERE PaymentId = ?");
    $stmtUpdateShip->bind_param("si", $newStatusLower, $paymentId);
    $stmtUpdateShip->execute();
    $stmtUpdateShip->close();

    if ($newStatusLower === 'đã giao' && $oldStatus !== 'đã giao') {
        $revenueController = new RevenueAdminController();
        $revenueController->syncRevenue($order['StoreId'], $order['Total'], date('Y-m-d'));
    }
    
    echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái và kho hàng thành công']);
} else {
    echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại']);
}
$db->close();
?>