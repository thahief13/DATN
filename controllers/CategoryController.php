<?php
    if (!class_exists('CategoryController')) {
        require_once __DIR__ .'/../env.php';
        require_once __DIR__ .'/../models/CategoryItem.php';

        class CategoryController {
            public function getAllCategories(){
                global $hostname, $username, $password, $dbname, $port;
                $db = new mysqli($hostname, $username, $password, $dbname, $port);

                $sql = "SELECT * FROM category ORDER BY Title ASC";
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
        }
    }
?>