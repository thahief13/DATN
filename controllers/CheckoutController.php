<?php
require_once __DIR__ . '/CartController.php';
require_once __DIR__ . '/ProductController.php';
require_once __DIR__ . '/StoreController.php';
require_once __DIR__ . '/CustomerController.php';
require_once __DIR__ . '/../env.php';

class CheckoutController
{
    private $cartController;
    private $productController;
    private $storeController;
    private $customerController;
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->cartController = new CartController();
        $this->productController = new ProductController();
        $this->storeController = new StoreController();
        $this->customerController = new CustomerController();
    }

   public function processOrder($customerId, $storeId, $paymentMethod = 'cod', $isDemo = true, $shippingFee = 0, $grandTotal = 0, $selectedIdsStr = '')
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        if (!$customerId || !$storeId) {
            throw new Exception("Thiếu thông tin khách hàng hoặc chi nhánh");
        }
        
        // 1. Lấy giỏ hàng gốc
        $carts = $this->cartController->getCartByCustomerId($customerId, $storeId);
        
        // 2. Lọc mảng giỏ hàng theo những món khách đã chọn
        if (!empty($selectedIdsStr)) {
            $selectedIds = explode(',', $selectedIdsStr);
            $carts = array_filter($carts, function($item) use ($selectedIds) {
                return in_array($item->ProductId, $selectedIds);
            });
        }

        if (empty($carts)) throw new Exception("Giỏ hàng trống hoặc sản phẩm chưa được chọn.");

        // 3. Tính tổng tiền có áp dụng Giảm Giá
        $totalCOD = 0;
        $stmtDiscount = $this->conn->prepare("SELECT DiscountPercent FROM storeproduct WHERE ProductId = ? AND StoreId = ?");
        
        foreach ($carts as $cart) {
            $product = $this->productController->getProductById($cart->ProductId);
            
            $stmtDiscount->bind_param("ii", $cart->ProductId, $storeId);
            $stmtDiscount->execute();
            $discResult = $stmtDiscount->get_result();
            $discount = 0;
            if ($discRow = $discResult->fetch_assoc()) {
                $discount = (int)$discRow['DiscountPercent'];
            }
            
            $finalPrice = $product->Price * (1 - $discount / 100);
            $totalCOD += $finalPrice * $cart->Quantity;
        }
        $stmtDiscount->close();
        
        $finalTotal = $grandTotal > 0 ? $grandTotal : $totalCOD;

        // 4. CHUNG: Tạo hóa đơn (Bảng payment)
        $stmtPay = $this->conn->prepare("INSERT INTO payment (CustomerId, StoreId, Total, PaymentMethod, Status, CreatedAt) VALUES (?, ?, ?, ?, 'pending', NOW())");
        $stmtPay->bind_param("iids", $customerId, $storeId, $finalTotal, $paymentMethod);
        $stmtPay->execute();
        $paymentId = $stmtPay->insert_id;
        $stmtPay->close();

        // 5. CHUNG: Lưu thông tin vận chuyển (Shipment)
        $carrier = $isDemo ? "DEMO" : "GHN";
        $trackingCode = $isDemo ? 'DEMO' . time() : '';
        $statusShip = 'ready_to_pick';
        $lat = null;
        $lng = null;
        $stmtShip = $this->conn->prepare("INSERT INTO shipment (PaymentId, Carrier, TrackingCode, Status, Latitude, Longitude, UpdatedAt) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmtShip->bind_param("isssdd", $paymentId, $carrier, $trackingCode, $statusShip, $lat, $lng);
        $stmtShip->execute();
        $stmtShip->close();

        // 6. CHUNG: Lưu chi tiết hóa đơn (PaymentDetail) KÈM GIÁ ĐÃ GIẢM
        $stmtDetail = $this->conn->prepare("INSERT INTO paymentdetail (PaymentId, StoreProductId, Price, Quantity) VALUES (?, ?, ?, ?)");
        $stmtSP = $this->conn->prepare("SELECT Id, DiscountPercent FROM storeproduct WHERE ProductId = ? AND StoreId = ?");

        foreach ($carts as $cart) {
            $stmtSP->bind_param("ii", $cart->ProductId, $storeId);
            $stmtSP->execute();
            $spResult = $stmtSP->get_result();
            if ($spRow = $spResult->fetch_assoc()) {
                $storeProductId = $spRow['Id'];
                $discount = (int)$spRow['DiscountPercent'];
                
                $product = $this->productController->getProductById($cart->ProductId);
                $finalPrice = (int)($product->Price * (1 - $discount / 100)); // Lưu giá đã giảm vào DB
                $quantity = (int)$cart->Quantity;
                
                $stmtDetail->bind_param("iiid", $paymentId, $storeProductId, $finalPrice, $quantity);
                $stmtDetail->execute();
            }
        }
        $stmtSP->close();
        $stmtDetail->close();

        // 7. XÓA GIỎ HÀNG (QUAN TRỌNG: CHỈ XÓA NẾU LÀ COD)
        if ($paymentMethod !== 'bank') {
            foreach ($carts as $cart) {
                $this->cartController->removeFromCart($customerId, $cart->ProductId, $storeId);
            }
        }

        // 8. RẼ NHÁNH: Tạo Link chuyển hướng VNPay
        if ($paymentMethod === 'bank') {
            $_SESSION['vnp_PaymentId'] = $paymentId;
            require_once __DIR__ . '/../vnpay_php/config.php';
            
            $vnp_TxnRef = $paymentId . '_' . date('His');
            $vnp_OrderInfo = 'Thanh toan don hang ' . $paymentId;
            $vnp_OrderType = 'other';
            $vnp_Amount = $finalTotal * 100; // Đổi ra Hào
            $vnp_Locale = 'vn';
            $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
            $vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes', time()));
            $vnp_Returnurl = "http://localhost/app/vnpay_php/vnpay_return.php"; 

            $inputData = array(
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode ?? '',
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef,
                "vnp_ExpireDate" => $vnp_ExpireDate
            );

            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $final_vnp_Url = ($vnp_Url ?? '') . "?" . $query;
            if (isset($vnp_HashSecret) && $vnp_HashSecret != "") {
                $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                $final_vnp_Url .= '&vnp_SecureHash=' . $vnpSecureHash;
            }

            return ['vnp_url' => $final_vnp_Url, 'paymentId' => $paymentId];
        }

        // RẼ NHÁNH: Trả về nếu là COD
        return ['vnp_url' => null, 'paymentId' => $paymentId];
    }
}