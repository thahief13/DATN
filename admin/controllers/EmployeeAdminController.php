<?php
require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../models/EmployeeAdmin.php';

class EmployeeAdminController
{
    public function getAllEmployees()
    {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);

        $sql = "SELECT Id, FullName, StoreId, RoleId, Salary FROM employee";
        $result = $db->query($sql);

        $employees = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $employee = new EmployeeAdmin();
                $employee->Id = $row['Id'];
                $employee->FullName = $row['FullName']; // sửa từ Name thành FullName
                $employee->StoreId = $row['StoreId'];
                $employee->RoleId = $row['RoleId'];
                $employee->Salary = $row['Salary'];
                $employees[] = $employee;
            }
        }
        $db->close();
        return $employees;
    }

    public function addEmployee($employee)
    {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);

        $sql = "INSERT INTO employee (FullName, StoreId, RoleId, Salary) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->bind_param(
            "sidd",
            $employee->FullName, // sửa Name -> FullName
            $employee->StoreId,
            $employee->RoleId,
            $employee->Salary
        );
        $isSuccess = $stmt->execute();
        $result = $isSuccess && ($stmt->affected_rows > 0);
        $stmt->close();
        $db->close();
        return $result;
    }

    public function getEmployeeById($employeeId)
    {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);

        $employee = new EmployeeAdmin();
        $sql = "SELECT * FROM employee WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $employeeId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $employee->Id = $row['Id'];
            $employee->FullName = $row['FullName']; // sửa Name -> FullName
            $employee->StoreId = $row['StoreId'];
            $employee->RoleId = $row['RoleId'];
            $employee->Salary = $row['Salary'];
        }
        $stmt->close();
        $db->close();
        return $employee;
    }

    public function updateEmployee($employee)
    {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);

        $sql = "UPDATE employee SET FullName = ?, StoreId = ?, RoleId = ?, Salary = ? WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param(
            "siddi",
            $employee->FullName, // sửa Name -> FullName
            $employee->StoreId,
            $employee->RoleId,
            $employee->Salary,
            $employee->Id
        );
        $isSuccess = $stmt->execute();
        $result = $isSuccess && ($stmt->affected_rows > 0);
        $stmt->close();
        $db->close();
        return $result;
    }

    public function deleteEmployeeById($employeeId)
    {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);

        $sql = "DELETE FROM employee WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $employeeId);
        $isSuccess = $stmt->execute();
        $result = $isSuccess && ($stmt->affected_rows > 0);
        $stmt->close();
        $db->close();
        return $result;
    }
}
