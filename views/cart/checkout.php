<?php
session_start();

require_once '../../env.php';
require_once '../../controllers/CartController.php';
require_once '../../controllers/ProductController.php';
require_once '../../controllers/CustomerController.php';
require_once '../../controllers/StoreController.php';

if (!isset($_SESSION['CustomerId'])) {
    header("Location: ../customer/sign_in.php");
    exit();
}

date_default_timezone_set('Asia/Ho_Chi_Minh');
$customerId = $_SESSION['CustomerId'];

$storeId = 0;
if (isset($_POST['storeId'])) {
    $storeId = (int)$_POST['storeId'];
    $_SESSION['CheckoutStoreId'] = $storeId;
} elseif (isset($_GET['storeId'])) {
    $storeId = (int)$_GET['storeId'];
    $_SESSION['CheckoutStoreId'] = $storeId;
} elseif (isset($_SESSION['CheckoutStoreId'])) {
    $storeId = (int)$_SESSION['CheckoutStoreId'];
}

if (!$storeId) die('<div class="alert alert-warning text-center">Chi nhánh không xác định!</div>');

$cartController = new CartController();
$productController = new ProductController();
$customerController = new CustomerController();
$storeController = new StoreController();

$storeCarts = $cartController->getCartByCustomerId($customerId, $storeId);

$selectedIdsStr = $_GET['selectedProducts'] ?? '';
if (!empty($selectedIdsStr)) {
    $selectedIds = explode(',', $selectedIdsStr);
    $storeCarts = array_filter($storeCarts, function($item) use ($selectedIds) {
        return in_array($item->ProductId, $selectedIds);
    });
    $_SESSION['SelectedProductIds'] = $selectedIdsStr;
}

if (empty($storeCarts)) die('<div class="alert alert-warning text-center">Vui lòng chọn ít nhất một sản phẩm để thanh toán!</div>');

$customer = $customerController->getCustomerById($customerId);
$customerName = trim(($customer->FirstName ?? '') . ' ' . ($customer->LastName ?? ''));
$customerPhone = preg_replace('/\D/', '', $customer->Phone ?? '');
$customerAddress = $customer->Address ?? '';
$toDistrict = (int)($customer->DistrictId ?? 0);
$toWard = trim((string)($customer->WardCode ?? ''));

$store = $storeController->getStoreById($storeId);
$storeName = $store->StoreName ?? 'Chi nhánh';
$storePhone = preg_replace('/\D/', '', $store->Phone ?? '');
$storeDistrict = (int)($store->DistrictId ?? 1548); 
$storeWard = trim((string)($store->WardCode ?? '410110')); 

$ghn_error = '';
$storeTotal = 0;
$totalWeight = 0;

// Khởi tạo DB tạm để lấy DiscountPercent
$dbTemp = new mysqli($hostname, $username, $password, $dbname, $port);

foreach ($storeCarts as $cart) {
    $product = $productController->getProductById($cart->ProductId);
    
    $discount = 0;
    $resSP = $dbTemp->query("SELECT DiscountPercent FROM storeproduct WHERE ProductId = " . (int)$cart->ProductId . " AND StoreId = " . (int)$storeId);
    if ($resSP && $rowSP = $resSP->fetch_assoc()) {
        $discount = (int)$rowSP['DiscountPercent'];
    }
    
    $basePrice = (int)($product->Price ?? 0);
    $finalPrice = (int)($basePrice * (1 - $discount / 100));
    
    $weight = (int)($product->Weight ?? 500);
    if ($weight <= 0) $weight = 500;

    $storeTotal += $finalPrice * $cart->Quantity;
    $totalWeight += $weight * $cart->Quantity;
}
$dbTemp->close();

if ($storeTotal <= 0) die('<div class="alert alert-danger text-center">Tổng giá trị đơn hàng không hợp lệ!</div>');

// ==========================================
// TÍCH HỢP API GIAO HÀNG NHANH (GHN) - ĐÃ CẬP NHẬT
// ==========================================
define('GHN_TOKEN', 'ee236453-32f7-11f1-83ac-625f4e0bad60'); 
define('GHN_SHOP_ID', 6372158); 

$storeShippingFee = 15000; 
$storeLeadtime = date('H:i d/m/Y', strtotime('+30 minutes'));
$ghn_error = '';

// BƯỚC CỨU HỘ ĐỊA CHỈ: Nếu DB của khách chưa có Mã Quận/Phường (bằng 0), tự động gán về Trung tâm Nha Trang để API chạy được!
if ($toDistrict <= 0 || empty($toWard)) {
    $toDistrict = 1548;   // Mã TP Nha Trang trên GHN
    $toWard = '410110';   // Mã Phường Lộc Thọ trên GHN
    $ghn_error = '';
}

// 1. GỌI API TÍNH PHÍ VẬN CHUYỂN (/fee)
$feePayload = [
    "service_type_id" => 2,
    "from_district_id" => $storeDistrict,
    "from_ward_code" => $storeWard,
    "to_district_id" => $toDistrict,
    "to_ward_code" => $toWard,
    "weight" => $totalWeight,
    "insurance_value" => $storeTotal
];

$chFee = curl_init('https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/fee');
curl_setopt($chFee, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chFee, CURLOPT_POST, true);
curl_setopt($chFee, CURLOPT_HTTPHEADER, [
    "Token: " . GHN_TOKEN,
    "ShopId: " . GHN_SHOP_ID,
    "Content-Type: application/json"
]);
curl_setopt($chFee, CURLOPT_POSTFIELDS, json_encode($feePayload));
$resFee = json_decode(curl_exec($chFee), true);
curl_close($chFee);

if (isset($resFee['code']) && $resFee['code'] == 200) {
    $storeShippingFee = $resFee['data']['total'] ?? 15000;
} else {
    // In lỗi chi tiết của GHN ra màn hình để dễ Debug
    $ghn_error .= '<div class="alert alert-danger text-center mb-2">Lỗi tính phí GHN: ' . htmlspecialchars($resFee['message'] ?? 'Thất bại') . '</div>';
}

// 2. GỌI API TÍNH THỜI GIAN GIAO HÀNG (/leadtime)
$timePayload = [
    "from_district_id" => $storeDistrict,
    "from_ward_code" => $storeWard,
    "to_district_id" => $toDistrict,
    "to_ward_code" => $toWard,
    "service_id" => 53320 
];

$chTime = curl_init('https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/leadtime');
curl_setopt($chTime, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chTime, CURLOPT_POST, true);
curl_setopt($chTime, CURLOPT_HTTPHEADER, [
    "Token: " . GHN_TOKEN,
    "ShopId: " . GHN_SHOP_ID,
    "Content-Type: application/json"
]);
curl_setopt($chTime, CURLOPT_POSTFIELDS, json_encode($timePayload));
$resTime = json_decode(curl_exec($chTime), true);
curl_close($chTime);

if (isset($resTime['code']) && $resTime['code'] == 200) {
    $timestamp = $resTime['data']['leadtime'] ?? time();
    $storeLeadtime = date('H:i d/m/Y', $timestamp);
}


$grandTotal = $storeTotal + $storeShippingFee;
?>

<?php include '../header.php'; ?>

<div class="container" style="padding: 60px 20px; max-width: 1200px; margin: 0 auto;">
    <div class="card" style="border-radius: 20px; box-shadow: 0 6px 20px rgba(0,0,0,0.15); padding: 40px; background: linear-gradient(to bottom, #ffffff, #f9f9f9);">
        <h1 style="text-align: center; color: #333; font-family: 'Arial', sans-serif; margin-bottom: 20px; font-weight: bold; letter-spacing: 1px;">Thanh toán - <?= htmlspecialchars($storeName) ?></h1>

        <?= $ghn_error ?>

        <div style="display: flex; flex-wrap: wrap; gap: 30px; margin-bottom: 40px; justify-content: space-between;">
            <div style="flex: 1; min-width: 300px; background: #f8f9fa; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h3 style="color: #ffb300; margin-bottom: 20px; font-size: 22px;">Chi nhánh gửi</h3>
                <p><strong>Tên:</strong> <?= htmlspecialchars($storeName) ?></p>
                <p><strong>Điện thoại:</strong> <?= htmlspecialchars($storePhone) ?></p>
                <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($store->Address ?? '') ?></p>
            </div>
            <div style="flex: 1; min-width: 300px; background: #f8f9fa; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h3 style="color: #ffb300; margin-bottom: 20px; font-size: 22px;">Người nhận</h3>
                <p><strong>Tên:</strong> <?= htmlspecialchars($customerName) ?></p>
                <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($customerAddress) ?></p>
                <p><strong>Điện thoại:</strong> <?= htmlspecialchars($customerPhone) ?></p>
            </div>
        </div>

        <div style="overflow-x: auto; margin-bottom: 40px; border-radius: 15px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <table style="width: 100%; border-collapse: collapse; text-align: center; min-width: 800px; background: #fff;">
                <thead style="background: #ffb300; color: white; font-weight: bold; font-size: 16px;">
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Đơn giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $dbTemp = new mysqli($hostname, $username, $password, $dbname, $port);
                    foreach ($storeCarts as $cart):
                        $product = $productController->getProductById($cart->ProductId);
                        $discount = 0;
                        $resSP = $dbTemp->query("SELECT DiscountPercent FROM storeproduct WHERE ProductId = " . (int)$cart->ProductId . " AND StoreId = " . (int)$storeId);
                        if ($resSP && $rowSP = $resSP->fetch_assoc()) $discount = (int)$rowSP['DiscountPercent'];
                        
                        $basePrice = $product->Price;
                        $finalPrice = $basePrice * (1 - $discount / 100);
                        $subtotal = $finalPrice * $cart->Quantity;
                    ?>
                        <tr>
                            <td style="padding: 15px;"><?= htmlspecialchars($product->Title) ?></td>
                            <td><?= number_format($finalPrice, 0, ',', '.') ?> VNĐ</td>
                            <td><?= $cart->Quantity ?></td>
                            <td style="font-weight:bold; color:#ffb300;"><?= number_format($subtotal, 0, ',', '.') ?> VNĐ</td>
                        </tr>
                    <?php endforeach; $dbTemp->close(); ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 40px; background: #fff7e6; border: 2px solid #ffb300; padding: 25px; border-radius: 15px; text-align: right;">
            <p style="font-size: 16px; margin-bottom: 10px;">Tổng tiền hàng: <strong><?= number_format($storeTotal, 0, ',', '.') ?> VNĐ</strong></p>
            <p style="font-size: 16px; margin-bottom: 10px; color: #d35400;">Phí vận chuyển (GHN): <strong>+ <?= number_format($storeShippingFee, 0, ',', '.') ?> VNĐ</strong></p>
            <hr style="border-top: 1px dashed #ccc; margin: 15px 0;">
            <p style="font-size: 22px; color: #c0392b;">Tổng thanh toán: <strong><?= number_format($grandTotal, 0, ',', '.') ?> VNĐ</strong></p>
            <p style="font-size: 15px; color: #27ae60; margin-top: 5px;"><i class="fa fa-truck"></i> Thời gian giao hàng: <strong><?= $storeLeadtime ?></strong></p>
        </div>
        
        <div id="toastContainer" style="position: fixed;top: 20px;left: 50%;transform: translateX(-50%); z-index: 9999;"></div>

        <form id="checkoutForm" method="POST" style="margin-top: 30px; display:flex; flex-direction: column; gap:15px;">
            <input type="hidden" name="storeId" value="<?= $storeId ?>">
            <input type="hidden" name="shippingFee" value="<?= $storeShippingFee ?>">
            <input type="hidden" name="grandTotal" value="<?= number_format((float)$grandTotal, 2, '.', '') ?>">
            <input type="hidden" name="vnp_order_id" value="<?= time() ?>">
            <input type="hidden" name="vnp_amount" value="<?= number_format((float)$grandTotal, 2, '.', '') ?>">

            <div style="display:flex; gap:20px; flex-wrap:wrap; justify-content:center;">
                <label class="paymentOption">
                    <input type="radio" name="paymentMethod" value="cod" checked>
                    <div class="paymentCard">
                        <span class="icon">💵</span>
                        <span class="text">Thanh toán khi nhận hàng</span>
                    </div>
                </label>

                <label class="paymentOption">
                    <input type="radio" name="paymentMethod" value="bank">
                    <div class="paymentCard">
                        <span class="icon">🏦</span>
                        <span class="text">Ngân hàng (VNPay)</span>
                    </div>
                </label>
            </div>

            <button type="button" id="checkoutBtn" class="checkoutBtn" style="width: 100%; max-width: 400px; margin: 0 auto; display: block;">Đặt hàng (<?= number_format($grandTotal, 0, ',', '.') ?> VNĐ)</button>
        </form>

        <style>
            .paymentOption input[type="radio"] { display: none; }
            .paymentCard { display: flex; align-items: center; gap: 10px; padding: 15px 25px; border: 2px solid #ccc; border-radius: 12px; cursor: pointer; transition: all 0.3s; min-width: 220px; justify-content: center; font-weight: bold; background: #fff; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); }
            .paymentCard .icon { font-size: 24px; }
            .paymentOption input[type="radio"]:checked+.paymentCard { border-color: #ff9800; background: linear-gradient(145deg, #fff7e6, #fff2d1); box-shadow: 0 6px 20px rgba(255, 152, 0, 0.3); }
            .checkoutBtn { background-color: #ff9800; color: #fff; border: none; padding: 18px; font-size: 18px; font-weight: bold; border-radius: 12px; cursor: pointer; transition: all 0.3s; }
            .checkoutBtn:hover { background-color: #e68a00; transform: translateY(-2px); }
        </style>

        <script>
            function showToast(message, type = 'success', duration = 3000) {
                const toast = document.createElement('div');
                toast.innerText = message;
                toast.style.background = type === 'success' ? '#4CAF50' : '#f44336';
                toast.style.color = '#fff';
                toast.style.padding = '15px 25px';
                toast.style.marginTop = '10px';
                toast.style.borderRadius = '8px';
                toast.style.boxShadow = '0 4px 10px rgba(0,0,0,0.1)';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-20px)';
                toast.style.transition = 'all 0.5s ease';
                document.getElementById('toastContainer').appendChild(toast);

                setTimeout(() => { toast.style.opacity = '1'; toast.style.transform = 'translateY(0)'; }, 50);
                setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateY(-20px)'; setTimeout(() => toast.remove(), 500); }, duration);
            }

            document.getElementById('checkoutBtn').addEventListener('click', function() {
                const method = document.querySelector('input[name="paymentMethod"]:checked').value;
                const form = document.getElementById('checkoutForm');

                if (method === 'cod') {
                    showToast('Đang xử lý đơn hàng...');
                    form.action = 'checkout_process.php';
                    setTimeout(() => form.submit(), 1500);
                } else if (method === 'bank') {
                    showToast('Đang chuyển hướng VNPay...', 'success', 2000);
                    form.action = 'checkout_process.php';
                    setTimeout(() => form.submit(), 1000);
                }
            });
        </script>
    </div>
</div>