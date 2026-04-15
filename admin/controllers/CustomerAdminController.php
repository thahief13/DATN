<?php
require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../models/CustomerAdmin.php';

class CustomerAdminController
{
    // Thêm vào trong class CustomerAdminController

public function getAllCustomers($search = '') {
    global $hostname, $username, $dbname, $port;
    $db = new mysqli($hostname, $username, '', $dbname, $port);
    
    // Xử lý tìm kiếm nếu có từ khóa
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
            // ... (giữ nguyên phần gán thuộc tính cũ của bạn)
            $customer->Id = $row['Id'];
            $customer->FirstName = $row['FirstName'];
            $customer->LastName = $row['LastName'];
            $customer->Email = $row['Email'];
            $customer->Phone = $row['Phone'];
            $customer->Address = $row['Address'];
            $customer->Img = $row['Img'];
            $customer->IsActive = $row['IsActive'];
            $customer->RegisteredAt = $row['RegisteredAt'];
            $customers[] = $customer;
        }
    }
    $db->close();
    return $customers;
}

public function createCustomer($customer) {
    global $hostname, $username, $dbname, $port;
    $db = new mysqli($hostname, $username, '', $dbname, $port);
    
    $sql = "INSERT INTO customer (FirstName, LastName, Email, Phone, Address, DateOfBirth, Img, IsActive, Role, RegisteredAt, Password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, 0, NOW(), ?)";
    
    $stmt = $db->prepare($sql);
    // Lưu ý: Password nên được hash, ở đây tạm để mặc định là '123456'
    $passwordDefault = password_hash('123456', PASSWORD_DEFAULT);
    $stmt->bind_param("ssssssss", 
        $customer->FirstName, 
        $customer->LastName, 
        $customer->Email, 
        $customer->Phone, 
        $customer->Address, 
        $customer->DateOfBirth, 
        $customer->Img,
        $passwordDefault
    );
    
    $result = $stmt->execute();
    $db->close();
    return $result;
}
    public function getCustomerById($id)
    {
        global $hostname, $username, $dbname, $port;
        $db = new mysqli($hostname, $username, '', $dbname, $port);
        $sql = "SELECT * FROM customer WHERE Role = 0 AND Id = " . (int)$id;
        $result = $db->query($sql);
        $customer = new CustomerAdmin();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $customer->Id = $row['Id'];
            $customer->FirstName = $row['FirstName'];
            $customer->LastName = $row['LastName'];
            $customer->Address = $row['Address'];
            $customer->Phone = $row['Phone'];
            $customer->Email = $row['Email'];
            $customer->Img = $row['Img'];
            $customer->RegisteredAt = $row['RegisteredAt'];
            $customer->UpdateAt = $row['UpdateAt'];
            $customer->DateOfBirth = $row['DateOfBirth'];
            $customer->Password = $row['Password'];
            $customer->RandomKey = $row['RandomKey'];
            $customer->IsActive = $row['IsActive'];
            $customer->Role = $row['Role'];
        }
        $db->close();
        return $customer;
    }
    public function updateCustomer($customerId, $isActive)
    {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        $sql = "UPDATE customer SET IsActive = ? WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ii", $isActive, $customerId);
        $result = $stmt->execute();
        return $result && ($stmt->affected_rows > 0);
    }
}
