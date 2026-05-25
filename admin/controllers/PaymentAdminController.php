<?php
require_once __DIR__ . '/../../env.php';

class PaymentAdminController 
{
   public function getAllPayments()
    {
        global $hostname, $username, $password, $dbname, $port;
        $conn = new mysqli($hostname, $username, $password, $dbname, $port);
        
        // TỰ ĐỘNG ĐỌC QUYỀN TỪ DATABASE DỰA TRÊN ID ĐĂNG NHẬP
        $customerId = $_SESSION['CustomerId'] ?? 0;
        $sqlUser = "SELECT Role, StoreId FROM customer WHERE Id = " . (int)$customerId;
        $resUser = $conn->query($sqlUser);
        $role = 1; 
        $storeId = 0;
        if ($resUser && $resUser->num_rows > 0) {
            $u = $resUser->fetch_assoc();
            $role = (int)$u['Role'];
            $storeId = (int)$u['StoreId'];
        }

        $sql = "SELECT p.Id, p.CustomerId, p.StoreId, p.Total, p.Status, p.CreatedAt, 
                       c.FirstName, c.LastName, s.StoreName 
                FROM payment p 
                LEFT JOIN customer c ON p.CustomerId = c.Id 
                LEFT JOIN store s ON p.StoreId = s.Id";
        
        // NẾU LÀ QUẢN LÝ CHI NHÁNH -> CHỈ LẤY ĐƠN CỦA CHI NHÁNH ĐÓ
        if ($role == 2 && $storeId > 0) {
            $sql .= " WHERE p.StoreId = " . (int)$storeId;
        }

        $sql .= " ORDER BY p.CreatedAt DESC";
        
        $result = $conn->query($sql);
        $payments = [];
        
        while ($row = $result->fetch_assoc()) {
            $payment = new stdClass();
            $payment->Id = $row['Id'];
            $payment->CustomerId = $row['CustomerId'];
            $payment->CustomerName = trim(($row['FirstName'] ?? '') . ' ' . ($row['LastName'] ?? ''));
            $payment->StoreId = $row['StoreId'];
            $payment->StoreName = $row['StoreName'] ?? 'N/A';
            $payment->Total = $row['Total'];
            $payment->Status = $row['Status'];
            $payment->CreatedAt = $row['CreatedAt'];
            $payments[] = $payment;
        }
        
        return $payments;
    }
    
    public function updatePaymentStatus($paymentId, $status)
    {
        global $hostname, $username, $password, $dbname, $port;
        $conn = new mysqli($hostname, $username, $password, $dbname, $port);
        
        $stmt = $conn->prepare("UPDATE payment SET Status = ? WHERE Id = ?");
        $stmt->bind_param("si", $status, $paymentId);
        $result = $stmt->execute();
        $stmt->close();
        $conn->close();
        
        return $result;
    }
    
    public function getPaymentDetail($paymentId)
    {
        global $hostname, $username, $password, $dbname, $port;
        $conn = new mysqli($hostname, $username, $password, $dbname, $port);
        
        $sql = "SELECT pd.Price, pd.Quantity as OrderQty, 
                       p.Title, p.Img, 
                       sp.IsAvailable 
                FROM paymentdetail pd 
                JOIN storeproduct sp ON pd.StoreProductId = sp.Id 
                JOIN product p ON sp.ProductId = p.Id 
                WHERE pd.PaymentId = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $paymentId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $details = [];
        while ($row = $result->fetch_assoc()) {
            $details[] = $row;
        }
        
        $stmt->close();
        $conn->close();
        return $details;
    }
}
?>