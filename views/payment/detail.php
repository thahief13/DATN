<?php
session_start();
require_once '../../controllers/PaymentController.php';
if (!isset($_SESSION['CustomerId'])) {
    header("Location: ../customer/sign_in.php");
    exit();
}
$id = $_GET['id'] ?? $_GET['paymentId'] ?? 0;
if (!$id) die('<div class="alert alert-danger text-center">ID đơn hàng không hợp lệ!</div>');
$paymentController = new PaymentController();
$payment = $paymentController->getPaymentById($id);
if (!$payment) die('<div class="alert alert-danger text-center">Đơn hàng không tồn tại!</div>');
$payment['PaymentDetail'] = $payment['PaymentDetail'] ?? [];
include '../header.php';
?>
<style>
    body,
    html {
        font-family: 'Poppins', sans-serif;
        background: #f4f6f9;
        margin: 0;
        padding: 0;
    }

    .page-header {
        padding: 80px 0;
        background: linear-gradient(135deg, #ffc107, #ff9800);
        color: #fff;
        text-align: center;
        position: relative;
        border-radius: 0 0 40px 40px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    .page-header h2 {
        font-size: 48px;
        font-weight: 800;
        margin: 0;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
    }

    .invoice-card {
        background: #fff;
        border-radius: 25px;
        padding: 40px;
        margin: 40px 0;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .invoice-card:hover {
        transform: translateY(-5px);
    }

    .section-title {
        font-size: 30px;
        font-weight: 700;
        margin-bottom: 30px;
        border-left: 5px solid #ffb300;
        padding-left: 15px;
        color: #333;
        letter-spacing: 0.5px;
    }

    /* Style cho bảng thông tin hóa đơn (Label/Value) */
    .invoice-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 15px;
    }

    .invoice-table th,
    .invoice-table td {
        padding: 18px 20px;
        border: none;
        vertical-align: middle;
    }

    .invoice-table tr {
        background: #ffffff;
        border: 1px solid #eee;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease-in-out;
    }

    .invoice-table tr:hover {
        background: #fffcf5;
    }

    .invoice-table th {
        background: #f8f9fa;
        color: #555;
        font-weight: 600;
        width: 35%;
        border-radius: 12px 0 0 12px;
    }

    .invoice-table td {
        font-weight: 500;
        color: #333;
        border-radius: 0 12px 12px 0;
    }

    .text-warning-custom {
        color: #ff9800 !important;
        font-size: 1.25rem;
        font-weight: 700 !important;
    }

    .form-control.edit-field {
        border: 2px solid #ddd !important;
        border-radius: 8px !important;
        padding: 12px 15px !important;
        font-size: 16px;
        transition: border-color 0.3s;
        box-sizing: border-box;
        width: 100% !important;
    }
    .form-control.edit-field:focus {
        border-color: #ffb300 !important;
        box-shadow: 0 0 0 0.2rem rgba(255, 179, 0, 0.25);
        outline: none;
    }
    .edit-section {
        background: #fff8e1 !important;
        border-left: 4px solid #ffb300;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
    }

    /* Style cho bảng chi tiết sản phẩm */
    .products-table th {
        background: #343a40;
        color: #ffc107;
        font-weight: 600;
        padding: 15px;
        text-align: center;
    }

    .products-table td {
        vertical-align: middle;
        padding: 15px 8px;
        text-align: center;
    }

    .products-table img.product-img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        border: 3px solid #f0f0f0;
    }

    .btn-custom {
        padding: 12px 35px;
        border-radius: 50px;
        background: #ffb300;
        color: #fff !important;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
        margin-top: 30px;
        letter-spacing: 0.5px;
        border: none;
    }

    .btn-custom:hover {
        background: #ff9800;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(255, 165, 0, 0.4);
    }

    .btn-sm-view {
        background: #3498db;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 14px;
        color: #fff !important;
        text-decoration: none;
        transition: background 0.3s;
    }

    .btn-sm-view:hover {
        background: #2980b9;
    }

    @media(max-width:768px) {
        .page-header {
            padding: 40px 0;
            border-radius: 0 0 20px 20px;
        }

        .page-header h2 {
            font-size: 32px;
        }

        .invoice-card {
            padding: 20px;
            margin: 20px 0;
            border-radius: 15px;
        }

        .section-title {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .invoice-table th,
        .invoice-table td {
            padding: 10px;
            font-size: 14px;
            display: block;
            width: 100%;
            border-radius: 0;
        }

        .invoice-table tr {
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            margin-bottom: 15px;
            display: block;
        }

        .products-table th,
        .products-table td {
            padding: 10px;
            font-size: 13px;
        }

        .products-table img.product-img {
            width: 60px;
            height: 60px;
            border-radius: 10px;
        }

        .btn-custom,
        .btn {
            padding: 10px 20px !important;
            font-size: 14px !important;
            width: 100%;
            margin-bottom: 10px;
        }

        .d-flex.flex-column.flex-md-row {
            flex-direction: column !important;
        }
    }
</style>

<script>
function saveCustomerInfo(paymentId, event) {
    console.log('Starting save for payment:', paymentId);
    
    // Check if all required elements exist
    const requiredFields = ['firstName', 'lastName', 'phone', 'email', 'storeAddress', 'deliveryAddress'];
    for (const fieldId of requiredFields) {
        const element = document.getElementById(fieldId);
        if (!element) {
            console.error('Element not found:', fieldId);
            alert('❌ Lỗi: Không tìm thấy trường ' + fieldId);
            return;
        }
    }
    
    const data = {
        firstName: document.getElementById('firstName').value,
        lastName: document.getElementById('lastName').value,
        phone: document.getElementById('phone').value,
        email: document.getElementById('email').value,
        storeAddress: document.getElementById('storeAddress').value,
        deliveryAddress: document.getElementById('deliveryAddress').value,
        paymentId: paymentId
    };
    
    console.log('Data to send:', data);
    
    // Show loading
    const btn = event ? event.target : document.querySelector('.btn-success');
    console.log('Button found:', btn);
    const originalText = btn.textContent;
    btn.textContent = 'Đang lưu...';
    btn.disabled = true;
    
    fetch('update_customer_info.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    }).then(res => {
        console.log('Response status:', res.status);
        console.log('Response headers:', res.headers);
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
    }).then(result => {
        console.log('Result:', result);
        if (result.success) {
            alert('✅ Lưu thông tin thành công!');
            // Chuyển về chế độ xem chi tiết (không có edit mode)
            location.href = 'detail.php?id=' + paymentId;
        } else {
            alert('❌ ' + result.message);
            btn.textContent = originalText;
            btn.disabled = false;
        }
    }).catch(err => {
        console.error('Error:', err);
        alert('❌ Lỗi kết nối: ' + err.message);
        btn.textContent = originalText;
        btn.disabled = false;
    });
}

// Auto focus edit fields
document.addEventListener('DOMContentLoaded', function() {
    const editMode = (new URLSearchParams(window.location.search).get('edit') === '1') || window.location.hash === '#edit';
    if (editMode) {
        setTimeout(() => {
            const firstField = document.getElementById('firstName');
            if (firstField) firstField.focus();
        }, 500);
    }
});

function deleteOrder(paymentId) {
    if (!confirm('Bạn có chắc muốn xóa đơn hàng này?\n\n⚠️ Hành động này không thể hoàn tác và sẽ xóa vĩnh viễn đơn hàng.')) {
        return;
    }

    // Show loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang xóa...';
    btn.disabled = true;

    fetch('process_delete_order.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `paymentId=${paymentId}`
    }).then(res => res.json()).then(data => {
        if (data.success) {
            alert('✅ Đã xóa đơn hàng thành công!');
            window.location.href = 'index.php';
        } else {
            alert('❌ ' + data.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }).catch(err => {
        alert('❌ Lỗi kết nối!');
        console.error(err);
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>

<main class="container my-5">
    <div class="page-header">
        <h2>Chi tiết Đơn Hàng</h2>
    </div>
    <div class="invoice-card">
        <h3 class="section-title">📝 Thông tin đơn hàng</h3>
        <?php $isEdit = ($_GET['edit'] ?? '') == '1' || (parse_url($_SERVER['REQUEST_URI'], PHP_URL_FRAGMENT) ?? '') == 'edit'; ?>
        <table class="invoice-table">
            <tr>
                <th>Mã hóa đơn</th>
                <td><?= htmlspecialchars($payment['Id']) ?></td>
            </tr>
            <tr>
                <th>Họ</th>
                <td>
                    <?php if ($isEdit): ?>
                        <input type="text" id="firstName" value="<?= htmlspecialchars($payment['FirstName']) ?>" class="form-control edit-field" required>
                    <?php else: ?>
                        <?= htmlspecialchars($payment['FirstName']) ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Tên</th>
                <td>
                    <?php if ($isEdit): ?>
                        <input type="text" id="lastName" value="<?= htmlspecialchars($payment['LastName']) ?>" class="form-control edit-field" required>
                    <?php else: ?>
                        <?= htmlspecialchars($payment['LastName']) ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Điện thoại</th>
                <td>
                    <?php if ($isEdit): ?>
                        <input type="tel" id="phone" value="<?= htmlspecialchars($payment['Phone']) ?>" class="form-control edit-field" pattern="[0-9]{10,11}" required>
                    <?php else: ?>
                        <?= htmlspecialchars($payment['Phone']) ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Email</th>
                <td>
                    <?php if ($isEdit): ?>
                        <input type="email" id="email" value="<?= htmlspecialchars($payment['Email'] ?? '') ?>" class="form-control edit-field" placeholder="Nhập email">
                    <?php else: ?>
                        <?= htmlspecialchars($payment['Email'] ?? 'Chưa cập nhật') ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Địa chỉ cửa hàng</th>
                <td>
                    <?php if ($isEdit): ?>
                        <textarea id="storeAddress" class="form-control edit-field" rows="3" placeholder="Nhập địa chỉ cửa hàng"><?php echo htmlspecialchars($payment['StoreAddress'] ?? $payment['StoreName'] . ' - ' . ($payment['StoreAddress'] ?? '')); ?></textarea>
                    <?php else: ?>
                        <?php echo htmlspecialchars($payment['StoreAddress'] ?? $payment['StoreName'] . ' - ' . ($payment['StoreAddress'] ?? 'Chưa cập nhật')); ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Nơi giao hàng</th>
                <td>
                    <?php if ($isEdit): ?>
                        <textarea id="deliveryAddress" class="form-control edit-field" rows="3" placeholder="Nhập địa chỉ giao hàng"><?php echo htmlspecialchars($payment['DeliveryAddress'] ?? $payment['CustomerAddress'] ?? ''); ?></textarea>
                    <?php else: ?>
                        <?php echo htmlspecialchars($payment['DeliveryAddress'] ?? $payment['CustomerAddress'] ?? 'Chưa cập nhật'); ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Ngày tạo</th>
                <td><?= date("H:i, d/m/Y", strtotime($payment['CreatedAt'])) ?></td>
            </tr>
            <tr>

                <th>Tổng tiền thanh toán</th>
                <td class="text-warning-custom"><?= number_format($payment['Total'], 0, ',', '.') ?> VNĐ</td>
            </tr>

        </table>


        <?php if ($isEdit): ?>
            <div class="text-center mt-4 edit-section">
                <button onclick="saveCustomerInfo(<?= $payment['Id'] ?>, event)" class="btn btn-success me-2" style="padding: 12px 30px; font-size: 16px;">
                    <i class="fas fa-save me-1"></i> Lưu thông tin
                </button>
                <a href="detail.php?id=<?= $payment['Id'] ?>" class="btn btn-secondary" style="padding: 12px 30px; font-size: 16px;">
                    <i class="fas fa-times me-1"></i> Hủy
                </a>
            </div>
        <?php else: ?>
            <div class="text-center mt-4 d-flex flex-column flex-md-row justify-content-center gap-2">
                
                <a href="../payment/index.php" class="btn btn-secondary" style="padding: 12px 30px; font-size: 16px;">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="invoice-card">
        <h3 class="section-title">🛒 Chi tiết sản phẩm</h3>
        <div class="table-responsive">
            <table class="products-table table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th style="width: 25%;">Tên sản phẩm</th>
                        <th style="width: 15%;">Hình ảnh</th>
                        <th style="width: 15%;">Giá</th>
                        <th style="width: 10%;">Số lượng</th>
                        <th style="width: 20%;">Thành tiền</th>
                        <th style="width: 15%;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payment['PaymentDetail'] as $detail):
                        $totalDetail = $detail['Price'] * $detail['Quantity'];
                    ?>
                        <tr>
                            <td class="text-start"><?= htmlspecialchars($detail['ProductName']) ?></td>
                            <td>
                                <?php if (!empty($detail['ImageUrl'])): ?>
                                    <img src="../../img/SanPham/<?= htmlspecialchars($detail['ImageUrl']) ?>" class="product-img" alt="<?= htmlspecialchars($detail['ProductName']) ?>">
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($detail['Price'], 0, ',', '.') ?> VNĐ</td>
                            <td><?= $detail['Quantity'] ?></td>
                            <td class="fw-bold text-success"><?= number_format($totalDetail, 0, ',', '.') ?> VNĐ</td>
                            <td><a href="../product/detail.php?id=<?= $detail['ProductId'] ?>" class="btn-sm-view">Xem</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="text-center">
            <button class="btn btn-danger" onclick="deleteOrder(<?= $payment['Id'] ?>)" style="padding: 12px 30px; font-size: 16px;">
                <i class="fas fa-trash me-1"></i> Xóa đơn hàng
            </button>
        </div>
    </div>
</main>
<?php include '../footer.php'; ?>

