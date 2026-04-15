<?php
require_once '../../../env.php';


class PaymentAdmin 
{
    public function getPaymentById($paymentId)
    {
        global $hostname, $username, $password, $dbname, $port;
        $conn = new mysqli($hostname, $username, $password, $dbname, $port);
        
        $sql = "SELECT p.*, 
                       c.FirstName, c.LastName, c.Phone as CustomerPhone, c.Address as CustomerAddress,
                       s.StoreName, s.Phone as StorePhone, s.Address as StoreAddress,
                       sh.Carrier, sh.TrackingCode, sh.Status as ShipmentStatus
                FROM payment p 
                LEFT JOIN customer c ON p.CustomerId = c.Id 
                LEFT JOIN store s ON p.StoreId = s.Id
                LEFT JOIN shipment sh ON p.Id = sh.PaymentId
                WHERE p.Id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $paymentId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $payment = $result->fetch_object();
        $stmt->close();
        $conn->close();
        
        if ($payment) {
            $payment->CustomerName = trim(($payment->FirstName ?? '') . ' ' . ($payment->LastName ?? ''));
            $payment->PaymentMethod = $payment->PaymentMethod ?? 'COD';
        }
        
        return $payment;
    }
}
?>

