<?php
session_start();

// 1. KIỂM TRA QUYỀN TRUY CẬP (Rất quan trọng)
// Dù có comment "parent index.php already checked", nhưng nếu ai đó truy cập trực tiếp file này (VD: domain.com/.../detail.php?id=1) 
// thì họ sẽ vượt qua được. Tốt nhất nên có 1 dòng check session ở đây.
// Ví dụ: if (!isset($_SESSION['admin_logged_in'])) { die('Truy cập bị từ chối'); }

$paymentId = intval($_GET['id'] ?? 0);

if ($paymentId <= 0) {
    die('Mã đơn hàng không hợp lệ.');
}

// 2. NẠP FILE REQUIRE MỘT LẦN TRÊN CÙNG
require_once '../../../admin/controllers/PaymentAdminController.php';
require_once '../../../admin/models/PaymentAdmin.php';

// 3. LẤY DỮ LIỆU
$paymentController = new PaymentAdminController();
$paymentDetails = $paymentController->getPaymentDetail($paymentId);
// Đảm bảo $paymentDetails luôn là một mảng để hàm count() hoặc foreach bên dưới không bị lỗi
if (!is_array($paymentDetails)) {
    $paymentDetails = []; 
}

$paymentInfo = (new PaymentAdmin())->getPaymentById($paymentId);

// 4. KIỂM TRA TỒN TẠI DỮ LIỆU (Logic quan trọng nhất bị thiếu)
// Nếu truyền ID đúng định dạng số nhưng không có trong Database, $paymentInfo sẽ trả về null/false.
// Việc gọi $paymentInfo->Status ở dưới HTML sẽ gây lỗi Fatal Error (Attempt to read property on bool/null).
if (!$paymentInfo) {
    die('<div style="color:red; text-align:center; margin-top:50px;"><h3>Không tìm thấy thông tin đơn hàng này trong hệ thống!</h3></div>');
}

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
                    <div class="card-header bg-primary text-white">
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
                                    // Chuyển status về chữ thường để so sánh chính xác hơn, tránh lỗi gõ hoa/thường trong DB
                                    $status = mb_strtolower($paymentInfo->Status ?? '');
                                    $badgeClass = 'warning';
                                    if ($status === 'đã giao' || $status === 'thành công') {
                                        $badgeClass = 'success';
                                    } elseif ($status === 'hủy' || $status === 'đã hủy') {
                                        $badgeClass = 'danger';
                                    }
                                ?>
                                <span class="badge fs-6 bg-<?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($paymentInfo->Status ?? 'Chưa xác định'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <div class="col-md-6 p-4 border-end">
                                <h5><i class="fas fa-user text-primary me-2"></i>Thông tin khách hàng</h5>
                                <p class="mb-1"><strong>ID:</strong> <?php echo htmlspecialchars($paymentInfo->CustomerId ?? 'N/A'); ?></p>
                                <p class="mb-1"><strong>Tên:</strong> <?php echo htmlspecialchars($paymentInfo->CustomerName ?? 'N/A'); ?></p>
                                <p class="mb-1"><strong>SĐT:</strong> <?php echo htmlspecialchars($paymentInfo->CustomerPhone ?? 'N/A'); ?></p>
                                <p class="mb-0"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($paymentInfo->CustomerAddress ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-md-6 p-4">
                                <h5><i class="fas fa-store text-success me-2"></i>Thông tin cửa hàng</h5>
                                <p class="mb-1"><strong>ID:</strong> <?php echo htmlspecialchars($paymentInfo->StoreId ?? 'N/A'); ?></p>
                                <p class="mb-1"><strong>Tên:</strong> <?php echo htmlspecialchars($paymentInfo->StoreName ?? 'N/A'); ?></p>
                                <p class="mb-1"><strong>SĐT:</strong> <?php echo htmlspecialchars($paymentInfo->StorePhone ?? 'N/A'); ?></p>
                                <p class="mb-0"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($paymentInfo->StoreAddress ?? 'N/A'); ?></p>
                            </div>
                        </div>

                        <div class="p-4 border-top">
                            <h5 class="mb-3">
                                <i class="fas fa-boxes me-2"></i>Sản phẩm trong đơn (<?php echo count($paymentDetails); ?>)
                            </h5>
                            <div class="row g-3">
                                <?php if(count($paymentDetails) > 0): ?>
                                    <?php foreach ($paymentDetails as $detail): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card h-100 shadow-sm">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-3">
                                                        <img src="/app/img/SanPham/<?php echo htmlspecialchars($detail['Img'] ?? 'default.jpg'); ?>" 
                                                             class="img-fluid rounded" alt="<?php echo htmlspecialchars($detail['Title'] ?? 'Sản phẩm'); ?>">

                                                    </div>
                                                    <div class="col-9">
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($detail['Title'] ?? 'Tên sản phẩm trống'); ?></h6>
                                                        <p class="small text-muted mb-1">SL: <?php echo intval($detail['Quantity'] ?? 0); ?></p>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="fw-bold text-success">
                                                                <?php 
                                                                    $price = floatval($detail['Price'] ?? 0);
                                                                    $qty = intval($detail['Quantity'] ?? 0);
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
                        </div>

                        <div class="p-4 bg-light border-top">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5><i class="fas fa-info-circle me-2"></i>Tổng kết</h5>
                                    <p class="mb-0"><strong>Phương thức thanh toán:</strong> <?php echo htmlspecialchars($paymentInfo->PaymentMethod ?? 'COD'); ?></p>
                                    <p class="mb-0"><strong>Mã vận đơn:</strong> <?php echo htmlspecialchars($paymentInfo->TrackingCode ?? 'N/A'); ?></p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <h4 class="text-primary mb-0">
                                        <strong><?php echo number_format(floatval($paymentInfo->Total ?? 0), 0, ',', '.'); ?> ₫</strong>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-white border-0">
                        <div class="d-flex justify-content-between">
                            <a href="../?page=payment" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Quay lại danh sách
                            </a>

                            <button class="btn btn-success" onclick="confirmDelivery(<?php echo $paymentId; ?>)">
                                <i class="fas fa-check-circle me-1"></i>Xác nhận đã giao
                            </button>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelivery(paymentId) {
            if (confirm('Xác nhận đơn hàng này đã được giao thành công? Trạng thái sẽ cập nhật thành "Đã giao"')) {
                fetch('process_update_status.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `paymentId=${paymentId}&status=đã giao`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Xác nhận thành công! Trở về danh sách...');
                        window.location.href = '../?page=payment';
                    } else {
                        alert('❌ ' + data.message);
                    }
                })
                .catch(err => {
                    alert('❌ Lỗi kết nối, vui lòng thử lại');
                });
            }
        }
    </script>
</body>
</html>

