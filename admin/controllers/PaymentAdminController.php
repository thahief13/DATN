<?php
require_once __DIR__ . '/../../env.php';
// No need Payment/PaymentDetail models - using direct SQL


class PaymentAdminController 
{
    public function getAllPayments()
    {
        global $hostname, $username, $password, $dbname, $port;
        $conn = new mysqli($hostname, $username, $password, $dbname, $port);
        
        $sql = "SELECT p.Id, p.CustomerId, p.StoreId, p.Total, p.Status, p.CreatedAt, 
                       c.FirstName, c.LastName, s.StoreName 
                FROM payment p 
                LEFT JOIN customer c ON p.CustomerId = c.Id 
                LEFT JOIN store s ON p.StoreId = s.Id 
                ORDER BY p.CreatedAt DESC";
        
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
        
        $conn->close();
        return $payments;
    }
    
    public function updatePaymentStatus($paymentId, $status)
    {
        global $hostname, $username, $password, $dbname, $port;
        $conn = new mysqli($hostname, $username, $password, $dbname, $port);
        
        $stmt = $conn->prepare("UPDATE payment SET Status = ?, UpdatedAt = NOW() WHERE Id = ?");
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
        
        $sql = "SELECT pd.ProductId, pd.Price, pd.Quantity, p.Title, p.Img 
                FROM paymentdetail pd 
                JOIN product p ON pd.ProductId = p.Id 
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

