<?php
session_start();
require_once '../../env.php';
require_once '../../controllers/CheckoutController.php';

$conn = new mysqli($hostname, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$customerId = $_SESSION['CustomerId'] ?? null;
$storeId = $_POST['storeId'] ?? $_SESSION['CheckoutStoreId'] ?? 0;
$shippingFee = floatval($_POST['shippingFee'] ?? 0);
$totalAmount = floatval($_POST['grandTotal'] ?? 0);

$paymentMethod = $_POST['paymentMethod'] ?? 'cod';


$checkout = new CheckoutController($conn);

$selectedProductIds = $_SESSION['SelectedProductIds'] ?? '';

try {

$result = $checkout->processOrder($customerId, $storeId, $paymentMethod, true, $shippingFee, $totalAmount, $selectedProductIds);
    
    // Sau khi đặt hàng thành công thì xóa session này đi
    unset($_SESSION['SelectedProductIds']);






    if (isset($result['vnp_url'])) {
        // Redirect VNPay URL
        header("Location: " . $result['vnp_url']);
        exit();
    } else {
        $_SESSION['paymentId'] = $result['paymentId'];
        header("Location: ../payment/index.php");
        exit();
    }

} catch (Exception $e) {
    die('<div class="alert alert-danger text-center">' . $e->getMessage() . '</div>');
}

$conn->close();
exit();
