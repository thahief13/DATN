<?php
require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../models/RoleAdmin.php';

class RoleAdminController {
    public function getAllRoles(){
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        $sql = "SELECT * FROM employeerole";
        $result = $db->query($sql);
        $roles = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $role = new RoleAdmin();
                $role->Id = $row['Id'];
                $role->RoleName = $row['RoleName'];
                $roles[] = $role;
            }
        }
        $db->close();
        return $roles;
    }

    // thêm mới nhân viên
    public function addRole($role){
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        $sql = "INSERT INTO employeerole (RoleName) VALUES (?)";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $role->RoleName);
        $isSuccess = $stmt->execute();
        $result = $isSuccess && ($stmt->affected_rows > 0);
        $stmt->close();
        $db->close();
        return $result;
    }

    // lấy chi tiết 1 nv
    public function getRoleById($id){
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        $role = null;
        $sql = "SELECT * FROM employeerole WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $role = new RoleAdmin();
            $role->Id = $row['Id'];
            $role->RoleName = $row['RoleName'];
        }
        $stmt->close();
        $db->close();
        return $role;
    }

    public function updateRole($role){
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        $sql = "UPDATE employeerole SET RoleName = ? WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("si", $role->RoleName, $role->Id);
        $isSuccess = $stmt->execute();
        $result = $isSuccess && ($stmt->affected_rows > 0);
        $stmt->close();
        $db->close();
        return $result;
    }

    public function deleteRoleById($id){
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        
        
        $sqlUnset = "UPDATE employee SET RoleId = 0 WHERE RoleId = ?";
        $stmtUnset = $db->prepare($sqlUnset);
        $stmtUnset->bind_param("i", $id);
        $stmtUnset->execute();
        $stmtUnset->close();

        
        $sqlDel = "DELETE FROM employeerole WHERE Id = ?";
        $stmtDel = $db->prepare($sqlDel);
        $stmtDel->bind_param("i", $id);
        $isSuccess = $stmtDel->execute();
        $result = $isSuccess && ($stmtDel->affected_rows > 0);
        $stmtDel->close();
        
        $db->close();
        return $result;
    }
}
?>