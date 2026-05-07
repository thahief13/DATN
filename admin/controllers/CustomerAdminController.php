<?php
require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../models/CustomerAdmin.php';

class CustomerAdminController {
    // Lấy danh sách kèm tìm kiếm
    public function getAllCustomers($search = '') {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        $db->set_charset("utf8mb4");

        if (!empty($search)) {
            $search = $db->real_escape_string($search);
            $sql = "SELECT * FROM customer WHERE Role = 0 AND (FirstName LIKE '%$search%' OR LastName LIKE '%$search%' OR Email LIKE '%$search%' OR Phone LIKE '%$search%')";
        } else {
            $sql = "SELECT * FROM customer WHERE Role = 0";
        }

        $result = $db->query($sql);
        $customers = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $customer = new CustomerAdmin();
                $customer->Id = $row['Id'];
                $customer->FirstName = $row['FirstName'];
                $customer->LastName = $row['LastName'];
                $customer->Email = $row['Email'];
                $customer->Phone = $row['Phone'];
                $customer->Address = $row['Address'];
                $customer->RegisteredAt = $row['RegisteredAt'];
                $customer->IsActive = $row['IsActive'];
                $customers[] = $customer;
            }
        }
        $db->close();
        return $customers;
    }

    // Lấy chi tiết 1 khách hàng
    public function getCustomerById($id) {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        $db->set_charset("utf8mb4");
        
        $sql = "SELECT * FROM customer WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $customer = $result->fetch_object('CustomerAdmin');
        
        $db->close();
        return $customer;
    }

    // Cập nhật trạng thái khóa/mở
    public function updateCustomer($customerId, $isActive) {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        $sql = "UPDATE customer SET IsActive = ? WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ii", $isActive, $customerId);
        $success = $stmt->execute();
        $db->close();
        return $success;
    }

    // Xóa khách hàng
    public function deleteCustomer($id) {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        $sql = "DELETE FROM customer WHERE Id = ? AND Role = 0";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $db->close();
        return $success;
    }

    // Thêm khách hàng mới
    public function createCustomer($firstName, $lastName, $email, $phone, $address, $password) {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        $db->set_charset("utf8mb4");

        // Kiểm tra email đã tồn tại
        $checkSql = "SELECT Id FROM customer WHERE Email = ?";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->num_rows > 0) {
            $db->close();
            return false; // Email đã tồn tại
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $randomKey = bin2hex(random_bytes(16));
        $registeredAt = date('Y-m-d H:i:s');

        $sql = "INSERT INTO customer (FirstName, LastName, Email, Phone, Address, Password, RandomKey, RegisteredAt, IsActive, Role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 0)";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ssssssss", $firstName, $lastName, $email, $phone, $address, $hashedPassword, $randomKey, $registeredAt);
        $success = $stmt->execute();
        $db->close();
        return $success;
    }

    // Cập nhật thông tin khách hàng
    public function updateCustomerInfo($id, $firstName, $lastName, $email, $phone, $address) {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        $db->set_charset("utf8mb4");

        // Kiểm tra email đã tồn tại với id khác
        $checkSql = "SELECT Id FROM customer WHERE Email = ? AND Id != ?";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->bind_param("si", $email, $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->num_rows > 0) {
            $db->close();
            return false; // Email đã tồn tại
        }

        $sql = "UPDATE customer SET FirstName = ?, LastName = ?, Email = ?, Phone = ?, Address = ? WHERE Id = ? AND Role = 0";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("sssssi", $firstName, $lastName, $email, $phone, $address, $id);
        $success = $stmt->execute();
        $db->close();
        return $success;
    }
    
}