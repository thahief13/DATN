<?php
session_start();
if (isset($_SESSION['vnp_PaymentId'])) {
    require_once '../../env.php';
    $db = new mysqli($hostname, $username, $password, $dbname, $port);
    if ($db->connect_error) {
        die('DB Error');
    }
    $stmt = $db->prepare("UPDATE payment SET Status = 'paid', UpdatedAt = NOW() WHERE Id = ? AND Status = 'pending'");
    $stmt->bind_param("i", $_SESSION['vnp_PaymentId']);
    $result = $stmt->execute();
    $stmt->close();
    $db->close();
    
    if ($result) {
        echo "DEMO VNPAY THANH TOÀN THÀNH CÔNG! Chuyển 2s...";
        echo "<script>setTimeout(() => {
            window.location.href = '../payment/detail.php?paymentId=" . $_SESSION['vnp_PaymentId'] . "';
        }, 2000);</script>";
    } else {
        echo "Demo fail - payment not pending";
    }
    unset($_SESSION['vnp_PaymentId']);
} else {
    echo "No payment session";
}
?>

