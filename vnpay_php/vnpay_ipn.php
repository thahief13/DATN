<?php


require_once("./config.php");
$inputData = array();
$returnData = array();
foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

$vnp_SecureHash = $inputData['vnp_SecureHash'];
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
$vnpTranId = $inputData['vnp_TransactionNo']; //Mã giao dịch tại VNPAY
$vnp_BankCode = $inputData['vnp_BankCode']; //Ngân hàng thanh toán
$vnp_Amount = $inputData['vnp_Amount']/100; // Số tiền thanh toán VNPAY phản hồi

$Status = 0; // Là trạng thái thanh toán của giao dịch chưa có IPN lưu tại hệ thống của merchant chiều khởi tạo URL thanh toán.
$orderId = $inputData['vnp_TxnRef'];

try {
    //Check Orderid    
    //Kiểm tra checksum của dữ liệu
    if ($secureHash == $vnp_SecureHash) {
        


        // Connect DB app
        require_once '../../env.php';
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        if ($db->connect_error) {
            $returnData['RspCode'] = '99';
            $returnData['Message'] = 'DB Error';
        } else {
            $paymentId = (int)$orderId;
            $stmt = $db->prepare("SELECT Total FROM payment WHERE Id = ? AND Status = 'pending'");
            $stmt->bind_param("i", $paymentId);
            $stmt->execute();
            $result = $stmt->get_result();
            $order = $result->fetch_assoc();
            $stmt->close();
            $db->close();

            if ($order && $order['Total'] * 100 == $vnp_Amount) {
                if ($inputData['vnp_ResponseCode'] == '00') {
                    // Update status
                    $db = new mysqli($hostname, $username, $password, $dbname, $port);
                    $stmt = $db->prepare("UPDATE payment SET Status = 'paid', UpdatedAt = NOW() WHERE Id = ?");
                    $stmt->bind_param("i", $paymentId);
                    $stmt->execute();
                    $stmt->close();
                    $db->close();
                    $Status = 1;
                    $returnData['RspCode'] = '00';
                    $returnData['Message'] = 'Confirm Success - DB Updated';
                } else {
                    $Status = 2;
                    $returnData['RspCode'] = '02';
                    $returnData['Message'] = 'Payment failed';
                }
            } else {
                $returnData['RspCode'] = '04';
                $returnData['Message'] = 'Invalid amount or not pending';
            }
        }

    } else {
        $returnData['RspCode'] = '97';
        $returnData['Message'] = 'Invalid signature';
    }
} catch (Exception $e) {
    $returnData['RspCode'] = '99';
    $returnData['Message'] = 'Unknow error';
}
//Trả lại VNPAY theo định dạng JSON
echo json_encode($returnData);
