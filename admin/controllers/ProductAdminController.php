<?php
    require_once __DIR__ .'/../../config/env.php';
    require_once __DIR__ . '/../models/ProductAdmin.php';

    class ProductAdminController {
        public function getAllProducts(int $categoryId, int $storeId = 0)
        {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);

            $sql = "SELECT DISTINCT p.Id, p.Title, p.Content, p.Img, p.Price, p.Rate, p.CreateAt, p.UpdateAt, 
                        c.Id AS CategoryId, c.Title AS CategoryTitle
                    FROM product p
                    JOIN category c ON p.CategoryId = c.Id";

            if ($storeId > 0) {
                $sql .= " JOIN storeproduct sp ON sp.ProductId = p.Id";
            }

            $conditions = [];
            if ($categoryId > 0) {
                $conditions[] = "c.Id = " . intval($categoryId);
            }
            if ($storeId > 0) {
                $conditions[] = "sp.StoreId = " . intval($storeId) . " AND sp.IsAvailable = 1";
            }

            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(' AND ', $conditions);
            }

$sql .= " ORDER BY p.Id ASC";
            $result = $db->query($sql);

            $products = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $product = new ProductAdmin();
                    $product->Id = $row['Id'];
                    $product->Title = $row['Title'];
                    $product->Content = $row['Content'];
                    $product->Img = $row['Img'];
                    $product->Price = $row['Price'];
                    $product->Rate = $row['Rate'];
                    $product->CreateAt = $row['CreateAt'];
                    $product->UpdateAt = $row['UpdateAt'];
                    $product->CategoryId = $row['CategoryId'];
                    $product->CategoryTitle = $row['CategoryTitle'];
                    $product->StoreId = '';
                    $product->StoreName = '';
                    $products[] = $product;
                }
            }

            $db->close();
            return $products;
        }

        public function getProductById($productId){
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);

            $sql = "SELECT p.Id, p.Title, p.Content, p.Img, p.Price, p.Rate, p.CreateAt, p.UpdateAt, 
                        c.Id AS CategoryId, c.Title AS CategoryTitle
                    FROM product p
                    JOIN category c ON p.CategoryId = c.Id WHERE p.Id = " . (int)$productId;
            $result = $db->query($sql);
            $product = new ProductAdmin();
            if($result->num_rows > 0){
                $row = $result->fetch_assoc();
                $product->Id = $row['Id'];
                $product->Title = $row['Title'];
                $product->Content = $row['Content'];
                $product->Img = $row['Img'];
                $product->Price = $row['Price'];
                $product->Rate = $row['Rate'];
                $product->CreateAt = $row['CreateAt'];
                $product->UpdateAt = $row['UpdateAt'];
                $product->CategoryId = $row['CategoryId'];
                $product->CategoryTitle = $row['CategoryTitle'];
            }
            $db->close();
            return $product;
        }

        public function addProduct($product){
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);

            $sql = "INSERT INTO product (Title, Content, Img, Price, Rate, CreateAt, CategoryId)
                    VALUES (?, ?, ?, ?, ?, NOW(), ?)";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("sssisi",
                $product->Title,
                $product->Content,
                $product->Img,
                $product->Price,
                $product->Rate,
                $product->CategoryId);
            $isSuccess = $stmt->execute();
            $insertId = $stmt->insert_id;
            $stmt->close();
            $db->close();
            return $isSuccess && ($insertId > 0) ? $insertId : 0;
        }

        public function updateProduct($product, $newImage = null) {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);
            if ($newImage) {
                $sql = "UPDATE product SET Title=?, Content=?, Img=?, Price=?, Rate=?, UpdateAt=NOW(), CategoryId=? WHERE Id=?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("sssisii",
                    $product->Title,
                    $product->Content,
                    $newImage,
                    $product->Price,
                    $product->Rate,
                    $product->CategoryId,
                    $product->Id);
            } else {
                $sql = "UPDATE product SET Title=?, Content=?, Price=?, Rate=?, UpdateAt=NOW(), CategoryId=? WHERE Id=?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("ssisii",
                    $product->Title,
                    $product->Content,
                    $product->Price,
                    $product->Rate,
                    $product->CategoryId,
                    $product->Id);
            }
            $isSuccess = $stmt->execute();
            $result = $isSuccess && ($stmt->affected_rows > 0);
            $stmt->close();
            $db->close();
            return $result;
        }

       public function deleteProduct($id) {
    global $hostname, $username, $password, $dbname, $port;
    $db = new mysqli($hostname, $username, $password, $dbname, $port);

    if ($db->connect_error) {
        return false;
    }

    // BƯỚC 1: Phải xóa dữ liệu ở bảng con (storeproduct) trước để gỡ bỏ ràng buộc
    $sql1 = "DELETE FROM storeproduct WHERE ProductId = ?";
    $stmt1 = $db->prepare($sql1);
    if ($stmt1) {
        $stmt1->bind_param("i", $id);
        $stmt1->execute();
        $stmt1->close();
    }

    // BƯỚC 2: Sau khi bảng con đã xóa xong, tiến hành xóa sản phẩm gốc ở bảng product
    $sql2 = "DELETE FROM product WHERE Id = ?";
    $stmt2 = $db->prepare($sql2);
    $result = false;
    if ($stmt2) {
        $stmt2->bind_param("i", $id);
        $result = $stmt2->execute();
        $stmt2->close();
    }
    
    $db->close();
    return $result;
}
        public function getAllStores() {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);

            $sql = "SELECT Id, StoreName FROM store ORDER BY StoreName ASC";
            $result = $db->query($sql);

            $stores = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $stores[] = $row;
                }
            }

            $db->close();
            return $stores;
        }

        public function addProductToStore($productId, $storeIds) {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);

            // Xóa mối liên kết cũ nếu có
            $deleteSql = "DELETE FROM storeproduct WHERE ProductId = ?";
            $deleteStmt = $db->prepare($deleteSql);
            $deleteStmt->bind_param("i", $productId);
            $deleteStmt->execute();
            $deleteStmt->close();

            // Thêm mối liên kết mới
            if (is_array($storeIds) && count($storeIds) > 0) {
                $insertSql = "INSERT INTO storeproduct (StoreId, ProductId, IsAvailable) VALUES (?, ?, 1)";
                $insertStmt = $db->prepare($insertSql);
                
                foreach ($storeIds as $storeId) {
                    $storeId = intval($storeId);
                    $insertStmt->bind_param("ii", $storeId, $productId);
                    $insertStmt->execute();
                }
                $insertStmt->close();
            }

            $db->close();
            return true;
        }

        public function getProductStores($productId) {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);

            $sql = "SELECT StoreId FROM storeproduct WHERE ProductId = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();

            $storeIds = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $storeIds[] = $row['StoreId'];
                }
            }

            $stmt->close();
            $db->close();
            return $storeIds;
        }
    }
?>

