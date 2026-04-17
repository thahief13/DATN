<?php
session_start();

$paymentId = intval($_GET['id'] ?? 0);

if ($paymentId <= 0) {
    die('Mã đơn hàng không hợp lệ.');
}

require_once '../../../admin/controllers/PaymentAdminController.php';
require_once '../../../admin/models/PaymentAdmin.php';

$paymentController = new PaymentAdminController();
$paymentDetails = $paymentController->getPaymentDetail($paymentId);

if (!is_array($paymentDetails)) {
    $paymentDetails = []; 
}

$paymentInfo = (new PaymentAdmin())->getPaymentById($paymentId);

if (!$paymentInfo) {
    die('<div style="color:red; text-align:center; margin-top:50px;"><h3>Không tìm thấy thông tin đơn hàng này trong hệ thống!</h3></div>');
}

// Biến cờ: Kiểm tra xem có sản phẩm nào trong đơn bị hết hàng không
$canFulfill = true; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?php echo $paymentId; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-dark text-white">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-0">
                                    <i class="fas fa-receipt me-2"></i>
                                    Đơn hàng #<?php echo $paymentId; ?>
                                </h3>
                                <small class="opacity-75">Ngày tạo: <?php echo date('d/m/Y H:i', strtotime($paymentInfo->CreatedAt ?? 'now')); ?></small>
                            </div>
                            <div class="col-auto">
                                <?php 
                                    $status = mb_strtolower($paymentInfo->Status ?? '');
                                    $badgeClass = 'warning';
                                    if ($status === 'đã giao' || $status === 'thành công') {
                                        $badgeClass = 'success';
                                    } elseif ($status === 'hủy' || $status === 'đã hủy') {
                                        $badgeClass = 'danger';
                                    }
                                ?>
                                <span class="badge fs-5 bg-<?php echo $badgeClass; ?>">
                                    <?php echo mb_strtoupper($paymentInfo->Status ?? 'Chưa xác định', 'UTF-8'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <div class="col-md-6 p-4 border-end">
                                <h5><i class="fas fa-user text-primary me-2"></i>Thông tin khách hàng</h5>
                                <p class="mb-1"><strong>Tên:</strong> <?php echo htmlspecialchars($paymentInfo->CustomerName ?? 'N/A'); ?></p>
                                <p class="mb-1"><strong>SĐT:</strong> <?php echo htmlspecialchars($paymentInfo->CustomerPhone ?? 'N/A'); ?></p>
                                <p class="mb-0"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($paymentInfo->CustomerAddress ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-md-6 p-4">
                                <h5><i class="fas fa-store text-success me-2"></i>Chi nhánh phục vụ</h5>
                                <p class="mb-1"><strong>Tên:</strong> <?php echo htmlspecialchars($paymentInfo->StoreName ?? 'N/A'); ?></p>
                                <p class="mb-1"><strong>SĐT:</strong> <?php echo htmlspecialchars($paymentInfo->StorePhone ?? 'N/A'); ?></p>
                                <p class="mb-0"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($paymentInfo->StoreAddress ?? 'N/A'); ?></p>
                            </div>
                        </div>

                        <div class="p-4 border-top">
                            <h5 class="mb-3">
                                <i class="fas fa-boxes me-2"></i>Sản phẩm yêu cầu (<?php echo count($paymentDetails); ?>)
                            </h5>
                            <div class="row g-3">
                                <?php if(count($paymentDetails) > 0): ?>
                                    <?php foreach ($paymentDetails as $detail): ?>
                                    <?php 
                                        // Kiểm tra món này cửa hàng còn không
                                        $isAvailable = isset($detail['IsAvailable']) ? (int)$detail['IsAvailable'] : 1;
                                        if ($isAvailable === 0) {
                                            $canFulfill = false; // Đánh cờ: Có món hết hàng
                                        }
                                    ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card h-100 shadow-sm <?php echo ($isAvailable === 0) ? 'border-danger bg-danger-subtle' : ''; ?>">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-4">
                                                        <img src="/app/img/SanPham/<?php echo htmlspecialchars($detail['Img'] ?? 'default.jpg'); ?>" 
                                                             class="img-fluid rounded" alt="<?php echo htmlspecialchars($detail['Title'] ?? 'Sản phẩm'); ?>">
                                                    </div>
                                                    <div class="col-8">
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($detail['Title'] ?? 'Tên sản phẩm trống'); ?></h6>
                                                        <p class="small text-dark fw-bold mb-1">Yêu cầu: <?php echo intval($detail['OrderQty'] ?? 0); ?> ly</p>
                                                        
                                                        <?php if ($isAvailable === 1): ?>
                                                            <span class="badge bg-success mb-2">Cửa hàng: Còn món</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger mb-2">Cửa hàng: HẾT MÓN</span>
                                                        <?php endif; ?>

                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="fw-bold text-success">
                                                                <?php 
                                                                    $price = floatval($detail['Price'] ?? 0);
                                                                    $qty = intval($detail['OrderQty'] ?? 0);
                                                                    echo number_format($price * $qty, 0, ',', '.'); 
                                                                ?> ₫
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12 text-center text-muted">
                                        <p>Không có sản phẩm nào trong đơn hàng này.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (!$canFulfill && $status === 'đang xử lý'): ?>
                                <div class="alert alert-danger mt-4 text-center">
                                    <i class="fas fa-exclamation-triangle"></i> <strong>CẢNH BÁO:</strong> Có sản phẩm trong đơn hàng hiện đang <strong>HẾT MÓN</strong> tại chi nhánh này. Vui lòng liên hệ khách hoặc Hủy đơn!
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="p-4 bg-light border-top">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5><i class="fas fa-info-circle me-2"></i>Tổng kết</h5>
                                    <p class="mb-0"><strong>Thanh toán:</strong> <?php echo mb_strtoupper($paymentInfo->PaymentMethod ?? 'COD', 'UTF-8'); ?></p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <h3 class="text-danger mb-0">
                                        <strong><?php echo number_format(floatval($paymentInfo->Total ?? 0), 0, ',', '.'); ?> ₫</strong>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-white border-0 py-3">
                        <div class="d-flex justify-content-between">
                            <a href="../?page=payment" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Trở lại
                            </a>

                            <?php 
                            // Đã bổ sung thêm trạng thái 'chờ thanh toán'
                            if ($status === 'đang xử lý' || $status === 'pending' || $status === 'chờ thanh toán'): 
                            ?>
                            <div>
                                <button class="btn btn-outline-danger me-2" onclick="cancelOrder(<?php echo $paymentId; ?>)">
                                    <i class="fas fa-times-circle me-1"></i>Từ chối / Hủy đơn
                                </button>
                                
                                <button class="btn btn-success" 
                                        onclick="confirmDelivery(<?php echo $paymentId; ?>)"
                                        <?php echo (!$canFulfill) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>
                                        title="<?php echo (!$canFulfill) ? 'Không thể giao vì thiếu đồ' : ''; ?>">
                                    <i class="fas fa-motorcycle me-1"></i>Giao cho Shipper
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelivery(paymentId) {
            if (confirm('Xác nhận CÓ ĐỦ MÓN và bắt đầu giao đơn hàng này cho khách?')) {
                updateStatus(paymentId, 'đang giao');
            }
        }

        function cancelOrder(paymentId) {
            if (confirm('Bạn có chắc chắn muốn HỦY đơn hàng này (VD: Do hết món, khách bom hàng)?')) {
                updateStatus(paymentId, 'hủy');
            }
        }

        function updateStatus(paymentId, newStatus) {
            fetch('process_update_status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `paymentId=${paymentId}&status=${encodeURIComponent(newStatus)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Thao tác thành công!');
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(err => {
                alert('❌ Lỗi kết nối máy chủ!');
            });
        }
    </script>
</body>
</html>