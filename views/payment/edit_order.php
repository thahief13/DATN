<?php
session_start();
require_once '../../controllers/CustomerPaymentController.php';
require_once '../../controllers/ProductController.php';
require_once '../../controllers/StoreController.php';

if (!isset($_SESSION['CustomerId'])) {
    header("Location: ../customer/sign_in.php");
    exit();
}

$customerId = $_SESSION['CustomerId'];
$paymentId = (int)$_GET['id'] ?? 0;
$customerPaymentController = new CustomerPaymentController();
$paymentController = new PaymentController();
$productController = new ProductController();
$storeController = new StoreController();

$payment = $customerPaymentController->getCustomerPayments($customerId);
$payment = array_filter($payment, fn($p) => $p['Id'] == $paymentId)[0] ?? null;
if (!$payment || !$payment['canEdit']) {
    die('<div class="alert alert-danger">Không thể chỉnh sửa đơn hàng này!</div>');
}

$details = $paymentController->getPaymentById($paymentId)['PaymentDetail'] ?? [];
$stores = $storeController->getAllStores();
$payment['StoreName'] = $payment['StoreName'] ?? 'N/A';

if ($_POST) {
    // Update store if changed
    if (isset($_POST['storeId']) && $_POST['storeId'] != $payment['StoreId']) {
        $stmt = $GLOBALS['db'] ?? new mysqli(...$GLOBALS); // Simplified, use global DB
        $stmt = $db->prepare("UPDATE payment SET StoreId = ? WHERE Id = ? AND CustomerId = ?");
        $stmt->bind_param("iii", $_POST['storeId'], $paymentId, $customerId);
        $stmt->execute();
    }
    header("Location: index.php?success=Updated");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa đơn hàng #<?= $paymentId ?></title>
</head>
<body>
    <h2>Sửa đơn hàng #<?= $paymentId ?></h2>
    <form method="POST">
        <label>Chi nhánh:</label>
        <select name="storeId">
            <?php foreach ($stores as $store): ?>
                <option value="<?= $store->Id ?>" <?= $store->Id == $payment['StoreId'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($store->StoreName) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>
        <table>
            <tr><th>Sản phẩm</th><th>Số lượng</th></tr>
            <?php foreach ($details as $detail): ?>
                <tr>
                    <td><?= htmlspecialchars($detail['ProductName']) ?></td>
                    <td>
                        <input type="number" min="1" value="<?= $detail['Quantity'] ?>" onchange="updateQty(<?= $paymentId ?>, <?= $detail['ProductId'] ?>, this.value)">
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <button type="submit">Cập nhật đơn hàng</button>
    </form>

    <script>
        function updateQty(paymentId, productId, qty) {
            fetch('process_update_qty.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `paymentId=${paymentId}&productId=${productId}&qty=${qty}`
            }).then(res => res.json()).then(data => {
                if (data.success) alert('Cập nhật số lượng OK');
            });
        }
    </script>
</body>
</html>

