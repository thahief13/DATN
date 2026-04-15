<?php
session_start();
require_once '../../../../env.php';

$paymentId = intval($_GET['id'] ?? 0);
if ($paymentId <= 0) {
    die('Invalid invoice ID');
}

require_once '../../../admin/controllers/PaymentAdminController.php';
$paymentController = new PaymentAdminController();
$paymentInfo = (new PaymentAdmin())->getPaymentById($paymentId);
$paymentDetails = $paymentController->getPaymentDetail($paymentId);

if (!$paymentInfo) {
    die('Invoice not found');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hóa đơn #<?php echo $paymentId; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .invoice-details { margin-bottom: 30px; }
        .products-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .products-table th, .products-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .products-table th { background-color: #f2f2f2; }
        .total { text-align: right; font-size: 1.2em; font-weight: bold; }
        @media print { body { margin: 0; } }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h1>TRUNG NGUYÊN CÀ PHÊ</h1>
        <p>HÓA ĐƠN THANH TOÁN</p>
        <h2>#<?php echo $paymentId; ?></h2>
    </div>

    <div class="invoice-details">
        <p><strong>Khách hàng:</strong> <?php echo htmlspecialchars($paymentInfo->CustomerName ?? 'N/A'); ?></p>
        <p><strong>Cửa hàng:</strong> <?php echo htmlspecialchars($paymentInfo->StoreName ?? 'N/A'); ?></p>
        <p><strong>Thời gian:</strong> <?php echo date('d/m/Y H:i', strtotime($paymentInfo->CreatedAt ?? 'now')); ?></p>
        <p><strong>Trạng thái:</strong> <?php echo htmlspecialchars($paymentInfo->Status ?? 'N/A'); ?></p>
    </div>

    <table class="products-table">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Số lượng</th>
                <th>Đơn giá</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($paymentDetails as $detail): ?>
            <tr>
                <td><?php echo htmlspecialchars($detail['Title']); ?></td>
                <td><?php echo intval($detail['Quantity']); ?></td>
                <td><?php echo number_format(floatval($detail['Price']), 0, ',', '.'); ?> ₫</td>
                <td><?php echo number_format(floatval($detail['Price']) * intval($detail['Quantity']), 0, ',', '.'); ?> ₫</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total">
        <strong>Tổng tiền: <?php echo number_format(floatval($paymentInfo->Total ?? 0), 0, ',', '.'); ?> ₫</strong>
    </div>

    <div style="text-align: center; margin-top: 50px;">
        <p>Cảm ơn quý khách!</p>
        <p>TRUNG NGUYÊN CÀ PHÊ</p>
    </div>
</body>
</html>

