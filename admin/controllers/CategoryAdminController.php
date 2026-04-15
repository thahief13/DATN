<?php
    require_once __DIR__ .'/../../config/env.php';
    require_once __DIR__ .'/../models/CategoryAdmin.php';

    class CategoryAdminController {
        public function getAllCategories(){
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);
            $sql = "SELECT * FROM category";
            $result = $db->query($sql);
            $categories = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $category = new CategoryAdmin();
                    $category->Id = $row['Id'];
                    $category->Title = $row['Title'];
                    $category->Content = $row['Content'];
                    $category->CreateAt = $row['CreateAt'];
                    $category->UpdateAt = $row['UpdateAt'];
                    $categories[] = $category;
                }
            }
            $db->close();
            return $categories;
        }

        public function createCategory($category){
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);
            $sql = "INSERT INTO category (Title, Content, CreateAt, UpdateAt) VALUES (?, ?, NOW(), NOW())";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("ss", $category->Title, $category->Content);
            $isSuccess = $stmt->execute();
            $result = $isSuccess && ($stmt->affected_rows > 0);
            $stmt->close();
            $db->close();
            return $result;
        }
        // Thêm vào trong class CategoryAdminController
public function deleteCategory($id){
    global $hostname, $username, $password, $dbname, $port;
    $db = new mysqli($hostname, $username, $password, $dbname, $port);
    $sql = "DELETE FROM category WHERE Id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $id);
    $isSuccess = $stmt->execute();
    $result = $isSuccess && ($stmt->affected_rows > 0);
    $stmt->close();
    $db->close();
    return $result;
}

        public function getCategoryById($id){
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);
            $sql = "SELECT * FROM category WHERE Id = " . (int)$id;
            $result = $db->query($sql);
            $category = new CategoryAdmin();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $category->Id = $row['Id'];
                $category->Title = $row['Title'];
                $category->Content = $row['Content'];
                $category->CreateAt = $row['CreateAt'];
                $category->UpdateAt = $row['UpdateAt'];
            }
            $db->close();
            return $category;
        }

        public function updateCategory($category){
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);
            $sql = "UPDATE category SET Title = ?, Content = ?, UpdateAt = NOW() WHERE Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("ssi", $category->Title, $category->Content, $category->Id);
            $isSuccess = $stmt->execute();
            $result = $isSuccess && ($stmt->affected_rows > 0);
            $stmt->close();
            $db->close();
            return $result;
        }        
    }
?>