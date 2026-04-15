<?php
require_once __DIR__ . '/ProductController.php';

class PaymentController
{
    protected $productController;

    public function __construct()
    {
        $this->productController = new ProductController();
    }

   public function getPaymentById(int $paymentId)
    {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        if ($db->connect_errno) die("DB lỗi: " . $db->connect_error);

        // 1. Lấy thông tin chung của đơn hàng
        $sql = "SELECT pay.*, c.FirstName, c.LastName, c.Phone, c.Email, c.Address AS CustomerAddress, s.StoreName, s.Address AS StoreAddress
                FROM payment pay
                JOIN customer c ON pay.CustomerId = c.Id
                JOIN store s ON pay.StoreId = s.Id
                WHERE pay.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $paymentId);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows == 0) {
            $db->close();
            return null;
        }
        $payment = $res->fetch_assoc();
        $stmt->close();

        // 2. Lấy chi tiết sản phẩm 
        $sqlDetail = "SELECT pd.*, sp.ProductId, p.Title, p.Img 
                      FROM paymentdetail pd
                      JOIN storeproduct sp ON pd.StoreProductId = sp.Id
                      JOIN product p ON sp.ProductId = p.Id
                      WHERE pd.PaymentId = ?";
        $stmtDetail = $db->prepare($sqlDetail);
        $stmtDetail->bind_param("i", $paymentId);
        $stmtDetail->execute();
        $resDetail = $stmtDetail->get_result();

        $paymentDetails = [];
        if ($resDetail && $resDetail->num_rows > 0) {
            while ($row = $resDetail->fetch_assoc()) {
                $paymentDetails[] = [
                    'PaymentDetailId' => $row['Id'],
                    'ProductId' => $row['ProductId'],
                    'ProductName' => $row['Title'] ?? 'Sản phẩm đã xóa',
                    'ImageUrl' => $row['Img'] ?? '',
                    'Price' => $row['Price'],
                    'Quantity' => $row['Quantity'],
                    'Total' => $row['Price'] * $row['Quantity']
                ];
            }
        }
        $stmtDetail->close();

        $payment['PaymentDetail'] = $paymentDetails;
        $db->close();
        return $payment;
    }

    public function updatePaymentAddresses($paymentId, $storeAddress, $deliveryAddress) {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        if ($db->connect_errno) die("DB lỗi: " . $db->connect_error);

        $sql = "UPDATE payment SET StoreAddress = ?, DeliveryAddress = ? WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ssi", $storeAddress, $deliveryAddress, $paymentId);
        $result = $stmt->execute();
        $stmt->close();
        $db->close();
        return $result;
    }
    
}
