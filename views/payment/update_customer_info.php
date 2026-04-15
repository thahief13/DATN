<?php
// Simple debug version
session_start();
header('Content-Type: application/json');

try {
    // Include config
    require_once '../../env.php';
    
    // Check session
    if (!isset($_SESSION['CustomerId'])) {
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
        exit();
    }

    // Get input
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'JSON decode error: ' . json_last_error_msg()]);
        exit();
    }

    $customerId = $_SESSION['CustomerId'];
    $paymentId = (int)($input['paymentId'] ?? 0);

    if ($paymentId <= 0 || $customerId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid data: paymentId=' . $paymentId . ', customerId=' . $customerId]);
        exit();
    }

    // Database connection
    $db = new mysqli($hostname, $username, $password, $dbname, $port);
    if ($db->connect_error) {
        echo json_encode(['success' => false, 'message' => 'DB connect error: ' . $db->connect_error]);
        exit();
    }

    // Check ownership
    $stmt = $db->prepare("SELECT Id FROM payment WHERE Id = ? AND CustomerId = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare check failed: ' . $db->error]);
        exit();
    }
    
    $stmt->bind_param("ii", $paymentId, $customerId);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Execute check failed: ' . $stmt->error]);
        exit();
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No permission']);
        $stmt->close();
        $db->close();
        exit();
    }
    $stmt->close();

    // Update customer
    $stmt = $db->prepare("UPDATE customer SET FirstName = ?, LastName = ?, Phone = ?, Email = ? WHERE Id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare customer update failed: ' . $db->error]);
        exit();
    }
    
    $stmt->bind_param("sssii", $input['firstName'], $input['lastName'], $input['phone'], $input['email'], $customerId);
    $success = $stmt->execute();
    if (!$success) {
        echo json_encode(['success' => false, 'message' => 'Execute customer update failed: ' . $stmt->error]);
        exit();
    }
    $stmt->close();

    // Update payment
    $stmt = $db->prepare("UPDATE payment SET StoreAddress = ?, DeliveryAddress = ? WHERE Id = ? AND CustomerId = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare payment update failed: ' . $db->error]);
        exit();
    }
    
    $stmt->bind_param("ssii", $input['storeAddress'], $input['deliveryAddress'], $paymentId, $customerId);
    $success2 = $stmt->execute();
    if (!$success2) {
        echo json_encode(['success' => false, 'message' => 'Execute payment update failed: ' . $stmt->error]);
        exit();
    }
    $stmt->close();

    $db->close();
    
    echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Exception: ' . $e->getMessage()]);
}
?>

