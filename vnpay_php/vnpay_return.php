<?php
// BẮT BUỘC PHẢI CÓ SESSION START Ở ĐÂY ĐỂ LẤY CUSTOMER ID
session_start(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>VNPAY RESPONSE</title>
    <link href="/vnpay_php/assets/bootstrap.min.css" rel="stylesheet" />
    <link href="/vnpay_php/assets/jumbotron-narrow.css" rel="stylesheet">
</head>
<body>
    <?php
    require_once("./config.php");
    $vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
    $inputData = array();
    foreach ($_GET as $key => $value) {
        if (substr($key, 0, 4) == "vnp_") {
            $inputData[$key] = $value;
        }
    }

    unset($inputData['vnp_SecureHash']);
    ksort($inputData);
    $i = 0;
    $hashData = "";
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
    }

    $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
    ?>
    <div class="container">
        <div class="header clearfix">
            <h3 class="text-muted">VNPAY RESPONSE</h3>
        </div>
        <div class="table-responsive">
            <div class="form-group"><label>Mã đơn hàng:</label><label><?php echo $_GET['vnp_TxnRef'] ?></label></div>
            <div class="form-group"><label>Số tiền:</label><label><?php echo number_format($_GET['vnp_Amount'] / 100, 0, ',', '.') ?> VNĐ</label></div>
            <div class="form-group"><label>Nội dung:</label><label><?php echo $_GET['vnp_OrderInfo'] ?></label></div>
            <div class="form-group"><label>Mã phản hồi:</label><label><?php echo $_GET['vnp_ResponseCode'] ?></label></div>
            
            <div class="form-group">
                <label>Kết quả:</label>
                <label>
                    <?php
                    if ($secureHash == $vnp_SecureHash) {
                        
                        $txnRefParts = explode('_', $_GET['vnp_TxnRef']);
                        $paymentId = (int)$txnRefParts[0];

                        require_once __DIR__ . '/../env.php';
                        global $hostname, $username, $password, $dbname, $port;
                        $conn = new mysqli($hostname, $username, $password, $dbname, $port);
                        
                        if ($conn->connect_error) die("<span style='color:red'>Lỗi CSDL</span>");

                        if ($_GET['vnp_ResponseCode'] == '00') {
                            // 1. CẬP NHẬT TRẠNG THÁI ĐÃ THANH TOÁN
                            $stmt = $conn->prepare("UPDATE payment SET Status = 'pending' WHERE Id = ?");
                            $stmt->bind_param("i", $paymentId);
                            $stmt->execute();
                            $stmt->close();

                            // 2. XÓA NHỮNG MÓN HÀNG ĐÃ MUA KHỎI GIỎ HÀNG BẰNG SQL NÂNG CAO
                            $customerId = $_SESSION['CustomerId'] ?? 0;
                            if ($customerId > 0) {
                                $sqlDeleteCart = "DELETE ci FROM cart_item ci
                                                  JOIN cart c ON ci.CartId = c.Id
                                                  JOIN paymentdetail pd ON ci.StoreProductId = pd.StoreProductId
                                                  WHERE c.CustomerId = ? AND pd.PaymentId = ?";
                                $stmtDel = $conn->prepare($sqlDeleteCart);
                                $stmtDel->bind_param("ii", $customerId, $paymentId);
                                $stmtDel->execute();
                                $stmtDel->close();
                            }

                            echo "<span style='color:blue'>Thanh toán thành công! Đang chuyển đến hóa đơn...</span>";
                            echo "<script>setTimeout(() => {
                                window.location.href = '/app/views/payment/detail.php?paymentId=" . $paymentId . "';
                            }, 2000);</script>";

                        } else {
                            // KHÁCH HỦY GIAO DỊCH
                            $stmt = $conn->prepare("UPDATE payment SET Status = 'cancelled' WHERE Id = ?");
                            $stmt->bind_param("i", $paymentId);
                            $stmt->execute();
                            $stmt->close();

                            // CHUYỂN HƯỚNG QUAY LẠI TRANG CHECKOUT THAY VÌ TRANG PAYMENT
                            echo "<span style='color:red'>Giao dịch đã bị hủy. Đang quay lại trang đặt hàng...</span>";
                            echo "<script>setTimeout(() => {
                                window.location.href = '/app/views/cart/checkout.php';
                            }, 2000);</script>";
                        }
                        $conn->close();

                    } else {
                        echo "<span style='color:red'>Chữ ký không hợp lệ!</span>";
                        echo "<script>setTimeout(() => { window.location.href = '/app/views/cart/index.php'; }, 2000);</script>";
                    }
                    ?>
                </label>
            </div>
        </div>
    </div>
</body>
</html>