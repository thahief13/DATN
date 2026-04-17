<?php
session_start();
require_once '../../env.php'; 
include '../header.php'; 

if (!isset($_SESSION['CustomerId'])) {
    header("Location: ../customer/sign_in.php");
    exit();
}

$currentCustomerId = $_SESSION['CustomerId'];

$conn = new mysqli($hostname, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

require_once '../../controllers/CustomerPaymentController.php';
$paymentController = new CustomerPaymentController();
$payments = $paymentController->getCustomerPayments($currentCustomerId);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách hóa đơn</title>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f6f9; }
        .page-header {
            padding: 80px 0; background: linear-gradient(135deg, #ffc107, #ff9800);
            color: #fff; text-align: center; border-radius: 0 0 40px 40px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2); margin-bottom: 30px;
        }
        .page-header h1 { font-size: 42px; font-weight: 800; margin: 0; text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1); }
        .payment-list-container { background: #fff; padding: 30px; border-radius: 20px; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08); }
        .payment-table { border-collapse: separate; border-spacing: 0; width: 100%; }
        .payment-table th { background: #343a40; color: #ffc107; font-weight: 600; padding: 15px 12px; text-align: center; border: none !important; }
        .payment-table tbody tr { background: #ffffff; border-bottom: 1px solid #eee; transition: background-color 0.2s ease; }
        .payment-table tbody tr:last-child { border-bottom: none; }
        .payment-table tbody tr:hover { background-color: #fffaf0; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
        .payment-table td { vertical-align: middle !important; padding: 15px 12px; color: #333; font-weight: 500; border: none; text-align: center; }
        .payment-table td:nth-child(2), .payment-table td:nth-child(3) { text-align: left; }
        .total-amount { color: #e74c3c; font-weight: 700; }
        .btn-detail { background: #3498db; color: #fff; padding: 8px 18px; border-radius: 20px; font-weight: 600; transition: all 0.3s ease; }
        .btn-detail:hover { background: #2980b9; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); color: #fff; }
        .btn-group .btn { border-radius: 0.375rem !important; margin: 0 1px; padding: 0.375rem 0.75rem; font-size: 0.875rem; }
        .btn-group .btn:first-child { border-top-left-radius: 0.375rem !important; border-bottom-left-radius: 0.375rem !important; }
        .btn-group .btn:last-child { border-top-right-radius: 0.375rem !important; border-bottom-right-radius: 0.375rem !important; }
        .btn-outline-info { color: #0dcaf0; border-color: #0dcaf0; }
        .btn-outline-info:hover { color: #fff; background-color: #0dcaf0; border-color: #0dcaf0; }
        .btn-outline-warning { color: #ffc107; border-color: #ffc107; }
        .btn-outline-warning:hover { color: #000; background-color: #ffc107; border-color: #ffc107; }
        .btn-outline-danger { color: #dc3545; border-color: #dc3545; }
        .btn-outline-danger:hover { color: #fff; background-color: #dc3545; border-color: #dc3545; }
        
        @media (max-width: 768px) {
            .page-header { padding: 60px 0 30px; border-radius: 0 0 20px 20px; }
            .page-header h1 { font-size: 30px; }
            .payment-list-container { padding: 15px; border-radius: 15px; }
            .payment-table thead { display: none; }
            .payment-table, .payment-table tbody, .payment-table tr, .payment-table td { display: block; width: 100%; }
            .payment-table tr { margin-bottom: 15px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); }
            .payment-table td { text-align: right; padding: 10px 15px; border: none; position: relative; }
            .payment-table td::before { content: attr(data-label); position: absolute; left: 15px; width: 50%; padding-right: 10px; font-weight: 600; text-align: left; color: #555; }
            .payment-table td[data-label="Thao tác"] .btn-group { justify-content: center !important; }
            .payment-table td[data-label="Thao tác"] .btn { width: 35px; height: 35px; padding: 0; font-size: 14px; margin: 0 2px; }
        }
        .tooltip { z-index: 9999 !important; }
        .btn-group .btn { position: relative !important; }
    </style>
</head>

<body>

    <div class="page-header">
        <h1 class="fw-bold">Lịch Sử Mua Hàng 🧾</h1>
    </div>
    <div class="container my-5">
        <div class="payment-list-container">
            <div class="table-responsive">
                <table class="payment-table table-hover">
    <thead>
        <tr>
            <th style="width: 10%;">Mã HĐ</th>
            <th style="width: 15%;">Ngày tạo</th>
            <th style="width: 15%;">Hình thức</th>
            <th style="width: 20%;">Trạng thái đơn</th>
            <th style="width: 20%;">Tổng tiền</th>
            <th style="width: 20%;">Thao tác</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($payments)): ?>
            <?php foreach ($payments as $payment): ?>
                <tr>
                    <td data-label="Mã HĐ"><?= $payment['Id'] ?></td>
                    <td data-label="Ngày tạo"><?= date("d/m/Y H:i", strtotime($payment['CreatedAt'])) ?></td>
                    
                    <td data-label="Hình thức">
                        <?php if (($payment['PaymentMethod'] ?? 'cod') == 'bank'): ?>
                            <span class="badge bg-info text-dark"><i class="fas fa-university"></i> Ngân hàng</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><i class="fas fa-truck"></i> Ship COD</span>
                        <?php endif; ?>
                    </td>

                    <td data-label="Trạng thái đơn">
                        <?php 
                            // Chuyển toàn bộ về chữ thường để dễ so sánh
                            $statusStr = mb_strtolower($payment['Status'] ?? 'pending', 'UTF-8');
                            
                            if ($statusStr == 'pending' || $statusStr == 'đang xử lý' || $statusStr == 'chờ thanh toán') {
                                echo '<span class="badge bg-warning text-dark"><i class="fas fa-spinner fa-spin"></i> Đang xử lý</span>';
                            } elseif ($statusStr == 'đang giao') {
                                echo '<span class="badge bg-primary"><i class="fas fa-motorcycle"></i> Đang giao hàng</span>';
                            } elseif ($statusStr == 'đã giao' || $statusStr == 'thành công' || $statusStr == 'paid') {
                                echo '<span class="badge bg-success"><i class="fas fa-check-circle"></i> Đã giao thành công</span>';
                            } elseif ($statusStr == 'hủy' || $statusStr == 'cancelled' || $statusStr == 'đã hủy') {
                                echo '<span class="badge bg-danger"><i class="fas fa-times-circle"></i> Đã hủy</span>';
                            } else {
                                echo '<span class="badge bg-secondary">' . htmlspecialchars(ucfirst($statusStr)) . '</span>';
                            }
                        ?>
                    </td>

                    <td data-label="Tổng tiền" class="total-amount"><?= number_format($payment['Total'], 0, ',', '.') ?> VND</td>
                    
                    <td data-label="Thao tác">
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="detail.php?id=<?= $payment['Id'] ?>" class="btn btn-outline-info" title="Xem chi tiết" data-bs-toggle="tooltip">
                                <i class="fas fa-eye"></i>
                            </a>

                            <?php 
                                // LOGIC GIỚI HẠN 15 PHÚT HỦY ĐƠN
                                date_default_timezone_set('Asia/Ho_Chi_Minh');
                                $createdAt = strtotime($payment['CreatedAt']);
                                $now = time();
                                $diffMinutes = floor(($now - $createdAt) / 60);
                                
                                // Khách chỉ được hủy khi đơn vẫn đang nằm ở bước "đang xử lý" và chưa qua 15p
                                $canCancel = (($statusStr == 'pending' || $statusStr == 'đang xử lý' || $statusStr == 'chờ thanh toán') && $diffMinutes <= 15);
                            ?>

                           <?php if ($canCancel): ?>
                                <a href="detail.php?id=<?= $payment['Id'] ?>&edit=1" class="btn btn-outline-warning" title="Sửa thông tin (Còn <?= 15 - $diffMinutes ?> phút)" data-bs-toggle="tooltip">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-outline-danger delete-btn" data-id="<?= $payment['Id'] ?>" title="Hủy đơn hàng" data-bs-toggle="tooltip">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php elseif ($statusStr == 'pending' || $statusStr == 'đang xử lý' || $statusStr == 'chờ thanh toán'): ?>
                                <button type="button" class="btn btn-outline-secondary" onclick="alert('Đã quá 15 phút kể từ lúc đặt.\nBạn không thể tự hủy đơn hàng này nữa!\n\nVui lòng liên hệ trực tiếp Hotline cửa hàng để được hỗ trợ.');" title="Không thể hủy" style="opacity: 0.65;">
                                    <i class="fas fa-ban"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" class="text-center">Bạn chưa có hóa đơn nào.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    container: 'body',
                    placement: 'bottom'
                });
            });
        });

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!confirm('Bạn có chắc muốn hủy đơn hàng này?\n\n⚠️ Hành động này không thể hoàn tác.')) {
                    return;
                }

                const paymentId = btn.dataset.id;
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btn.disabled = true;

                fetch('process_delete_order.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `paymentId=${paymentId}`
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        alert('✅ Đã hủy đơn hàng thành công!');
                        location.reload();
                    } else {
                        alert('❌ ' + data.message);
                        btn.innerHTML = originalHTML;
                        btn.disabled = false;
                    }
                }).catch(err => {
                    alert('❌ Lỗi kết nối!');
                    console.error(err);
                    btn.innerHTML = originalHTML;
                    btn.disabled = false;
                });
            });
        });
    </script>

    <?php include '../footer.php'; ?>
</body>
</html>