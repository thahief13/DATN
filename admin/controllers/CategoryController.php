<?php
require_once __DIR__ . '../../../env.php';
include __DIR__ . '/../admin/models/CategoryAdmin.php';

class CategoryController
{
    public function getAllCategories()
    {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);

        $sql = "SELECT * FROM category";
        $result = $db->query($sql);

        $categories = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $category = new CategoryItem();
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

    public function createCategory($category)
    {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        $sql = "INSERT INTO category (Title, Content, CreateAt, UpdateAt) VALUES (?, ?, NOW(), NOW())";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ss", $category->Title, $category->Content);
        $result = $stmt->execute();
        return $result && ($stmt->affected_rows > 0);
    }

    public function getCategoryById($id)
    {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);

        $sql = "SELECT * FROM category WHERE Id = " . (int)$id;
        $result = $db->query($sql);

        $category = new CategoryItem();
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

    public function updateCategoryById($id)
    {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        $sql = "UPDATE category SET Title = ?, Content = ?, UpdateAt = NOW() WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ssi", (int)$id);
        $result = $stmt->execute();
        return $result && ($stmt->affected_rows > 0);
    }

    public function deleteCategoryById($id)
    {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        $sql = "DELETE FROM category WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", (int)$id);
        $result = $stmt->execute();
        return $result && ($stmt->affected_rows > 0);
    }
}
