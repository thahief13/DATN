<?php
if (!class_exists('CustomerController')) {
    require_once __DIR__ . '/../env.php';
    require_once __DIR__ . '/../models/Customer.php';

    class CustomerController
    {
        private $conn;

        public function __construct($connection = null)
        {
            // Ép buộc nạp file cấu hình ngay tại đây để tránh lỗi mất biến global
            require __DIR__ . '/../env.php'; 
            
            // Khởi tạo kết nối trực tiếp bằng các biến từ env.php
            if ($connection) {
                $this->conn = $connection;
            } else {
                // Sử dụng biến cục bộ vừa được require từ env.php
                $this->conn = new mysqli($hostname, $username, $password, $dbname, $port);
            }

            if ($this->conn->connect_error) {
                throw new Exception("Kết nối thất bại: " . $this->conn->connect_error);
            }
        }

        public function getCustomerByEmail($email)
        {
            $sql = "SELECT Id FROM customer WHERE Email = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $customerId = 0;
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $customerId = $row['Id'];
            }
            $stmt->close();
            return $customerId;
        }

        public function getCustomerById($id)
        {
            $sql = "SELECT * FROM customer WHERE Id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            $customer = new Customer();
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
                $customer->Role = $row['Role'] ?? 0;
            }
            $stmt->close();
            return $customer;
        }

        public function checkDuplicateByEmail($customer)
        {
            $sql = "SELECT Id FROM customer WHERE Email = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $customer->Email);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = $result->num_rows;
            $stmt->close();
            return $count;
        }

        public function signUp($customer)
        {
            $sql = "INSERT INTO customer 
                (FirstName, LastName, Address, Phone, Email, Img, RegisteredAt, DateOfBirth, Password, RandomKey, IsActive, Role, ProvinceId, DistrictId, WardCode)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, 0, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            
            $fName = $customer->FirstName;
            $lName = $customer->LastName;
            $addr = $customer->Address;
            $phone = $customer->Phone;
            $email = $customer->Email;
            $img = $customer->Img;
            $dob = $customer->DateOfBirth;
            $pass = $customer->Password;
            $rand = $customer->RandomKey;
            $isActive = $customer->IsActive;
            $provId = $customer->ProvinceId;
            $distId = $customer->DistrictId;
            $wardCode = $customer->WardCode;

            $stmt->bind_param("sssssssssiiis", $fName, $lName, $addr, $phone, $email, $img, $dob, $pass, $rand, $isActive, $provId, $distId, $wardCode);

            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }

        public function updateCustomer($customer)
        {
            $sql = "UPDATE customer SET 
                        FirstName = ?, LastName = ?, Address = ?, Phone = ?, Img = ?, UpdateAt = NOW(), DateOfBirth = ?
                    WHERE Id = ?";
            $stmt = $this->conn->prepare($sql);

            $fName = $customer->FirstName;
            $lName = $customer->LastName;
            $addr = $customer->Address;
            $phone = $customer->Phone;
            $img = $customer->Img;
            $dob = $customer->DateOfBirth;
            $id = $customer->Id;

            $stmt->bind_param("ssssssi", $fName, $lName, $addr, $phone, $img, $dob, $id);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }

      
        public function changePassword($customer)
        {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);
            $sql = "UPDATE customer SET 
                        Password = ?,
                        UpdateAt = NOW()
                    WHERE Id = ?";
            $stmt = $db->prepare($sql);
            
            // Sử dụng biến tạm
            $pass = $customer->Password;
            $id = $customer->Id;

            $stmt->bind_param("si", $pass, $id);
            $result = $stmt->execute();
            
            $stmt->close();
            $db->close();
            return $result;
        }
    }
}