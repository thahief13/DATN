<?php
require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../models/EmployeeAdmin.php';

class EmployeeAdminController {
    public function getAllEmployees() {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        $db->set_charset("utf8mb4");
        $sql = "SELECT * FROM employee ORDER BY Id ASC";
        $result = $db->query($sql);
        $employees = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $employee = new EmployeeAdmin();
                $employee->Id = $row['Id'];
                $employee->FullName = $row['FullName']; 
                $employee->StoreId = $row['StoreId'];
                $employee->RoleId = $row['RoleId'];
                $employee->Salary = $row['Salary'];
                $employees[] = $employee;
            }
        }
        $db->close();
        return $employees;
    }

    public function addEmployee($employee) {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        $db->set_charset("utf8mb4");
        
        $sql = "INSERT INTO employee (FullName, StoreId, RoleId, Salary) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        if (!$stmt) return "Lỗi truy vấn SQL: " . $db->error;
        
        $stmt->bind_param("siid", $employee->FullName, $employee->StoreId, $employee->RoleId, $employee->Salary);
        $isSuccess = $stmt->execute();
        $error = $stmt->error; // Bắt ngay lỗi nếu MySQL từ chối
        
        $stmt->close(); $db->close();
        return $isSuccess ? true : "Lỗi Database: " . $error;
    }

    public function updateEmployee($employee) {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        $db->set_charset("utf8mb4");
        
        $sql = "UPDATE employee SET FullName = ?, StoreId = ?, RoleId = ?, Salary = ? WHERE Id = ?";
        $stmt = $db->prepare($sql);
        if (!$stmt) return "Lỗi truy vấn SQL: " . $db->error;
        
        $stmt->bind_param("siidi", $employee->FullName, $employee->StoreId, $employee->RoleId, $employee->Salary, $employee->Id);
        $isSuccess = $stmt->execute();
        $error = $stmt->error;
        
        $stmt->close(); $db->close();
        return $isSuccess ? true : "Lỗi Database: " . $error;
    }

    public function deleteEmployeeById($employeeId) {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        
        $sql = "DELETE FROM employee WHERE Id = ?";
        $stmt = $db->prepare($sql);
        if (!$stmt) return "Lỗi truy vấn SQL: " . $db->error;
        
        $stmt->bind_param("i", $employeeId);
        $isSuccess = $stmt->execute();
        $error = $stmt->error;
        
        $stmt->close(); $db->close();
        return $isSuccess ? true : "Lỗi Database: " . $error;
    }
}
?>