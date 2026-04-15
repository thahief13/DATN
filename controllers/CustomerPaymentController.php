<?php
require_once __DIR__ . '/PaymentController.php';

class CustomerPaymentController extends PaymentController
{
    public function getCustomerPayments(int $customerId)
    {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        if ($db->connect_errno) die("DB lỗi: " . $db->connect_error);

        $sql = "SELECT pay.*, c.FirstName, c.LastName, c.Phone, c.Email
                FROM payment pay
                JOIN customer c ON pay.CustomerId = c.Id
                WHERE pay.CustomerId = ? ORDER BY pay.CreatedAt DESC";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $payments = [];
        while ($row = $result->fetch_assoc()) {
            $payment = $row;
            $payment['canEdit'] = false;
            if ($row['Status'] == 'pending') {
                $created = strtotime($row['CreatedAt']);
                $now = time();
                if (($now - $created) < 24*3600) {
                    $payment['canEdit'] = true;
                }
            }
            $payments[] = $payment;
        }
        
        $db->close();
        return $payments;
    }

    public function deletePayment(int $paymentId, int $customerId)
    {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        
        // Check permission + status
        $sql = "SELECT Status FROM payment WHERE Id = ? AND CustomerId = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ii", $paymentId, $customerId);
        $stmt->execute();
        $result = $stmt->get_result();
        $payment = $result->fetch_assoc();
        $stmt->close();
        
        if (!$payment || $payment['Status'] != 'pending') {
            $db->close();
            return false;
        }
        
        // Delete cascade
        $db->begin_transaction();
        try {
            $db->query("DELETE FROM paymentdetail WHERE PaymentId = $paymentId");
            $db->query("DELETE FROM shipment WHERE PaymentId = $paymentId");
            $db->query("DELETE FROM payment WHERE Id = $paymentId");
            $db->commit();
            $db->close();
            return true;
        } catch (Exception $e) {
            $db->rollback();
            $db->close();
            return false;
        }
    }
}
?>

