<?php
    require_once __DIR__ .'/../../config/env.php';
    require_once __DIR__ . '/../models/ProductAdmin.php';

    class ProductAdminController {
        
        // Đã thêm tham số $keyword để tìm kiếm bằng SQL
        public function getAllProducts(int $categoryId = 0, int $storeId = 0, string $keyword = '')
        {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);

            // --- BẢO MẬT: ĐỌC QUYỀN TRỰC TIẾP TỪ DATABASE (GIỐNG FILE REVIEW) ---
            $customerId = $_SESSION['CustomerId'] ?? 0;
            $sqlUser = "SELECT Role, StoreId FROM customer WHERE Id = " . (int)$customerId;
            $resUser = $db->query($sqlUser);
            
            $role = 1; // Mặc định Admin
            $userStoreId = 0;
            if ($resUser && $resUser->num_rows > 0) {
                $u = $resUser->fetch_assoc();
                $role = (int)$u['Role'];
                $userStoreId = (int)$u['StoreId'];
            }

            // ÉP BUỘC: Nếu là quản lý chi nhánh (Role 2), chỉ lấy sản phẩm của họ
            if ($role == 2 && $userStoreId > 0) {
                $storeId = $userStoreId;
            }

            $sql = "SELECT DISTINCT p.Id, p.Title, p.Content, p.Img, p.Price, p.Rate, p.CreateAt, p.UpdateAt, 
                        c.Id AS CategoryId, c.Title AS CategoryTitle
                    FROM product p
                    JOIN category c ON p.CategoryId = c.Id
                    JOIN storeproduct sp ON sp.ProductId = p.Id";

            $conditions = ["sp.IsAvailable = 1"];
            
            // Lọc theo cửa hàng
            if ($storeId > 0) {
                $conditions[] = "sp.StoreId = " . (int)$storeId;
            }
            // Lọc theo danh mục
            if ($categoryId > 0) {
                $conditions[] = "c.Id = " . (int)$categoryId;
            }
            // Tìm kiếm bằng từ khóa
            if (!empty($keyword)) {
                $k = $db->real_escape_string($keyword);
                $conditions[] = "(p.Title LIKE '%$k%' OR p.Content LIKE '%$k%' OR c.Title LIKE '%$k%' OR p.Id = '$k')";
            }

            $sql .= " WHERE " . implode(' AND ', $conditions);
            $sql .= " ORDER BY p.Id ASC";
            
            $result = $db->query($sql);
            $products = [];
            if ($result && $result->num_rows > 0) {
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
                    $products[] = $product;
                }
            }
            $db->close();
            return $products;
        }

        // --- CÁC HÀM CÒN LẠI GIỮ NGUYÊN KHÔNG ĐỔI ---
        public function getProductById($productId){
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);
            $sql = "SELECT p.Id, p.Title, p.Content, p.Img, p.Price, p.Rate, p.CreateAt, p.UpdateAt, 
                        c.Id AS CategoryId, c.Title AS CategoryTitle
                    FROM product p JOIN category c ON p.CategoryId = c.Id WHERE p.Id = " . (int)$productId;
            $result = $db->query($sql);
            $product = new ProductAdmin();
            if($result->num_rows > 0){
                $row = $result->fetch_assoc();
                $product->Id = $row['Id']; $product->Title = $row['Title']; $product->Content = $row['Content'];
                $product->Img = $row['Img']; $product->Price = $row['Price']; $product->Rate = $row['Rate'];
                $product->CreateAt = $row['CreateAt']; $product->UpdateAt = $row['UpdateAt'];
                $product->CategoryId = $row['CategoryId']; $product->CategoryTitle = $row['CategoryTitle'];
            }
            $db->close(); return $product;
        }

        public function addProduct($product){
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);
            $sql = "INSERT INTO product (Title, Content, Img, Price, Rate, CreateAt, CategoryId) VALUES (?, ?, ?, ?, ?, NOW(), ?)";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("sssisi", $product->Title, $product->Content, $product->Img, $product->Price, $product->Rate, $product->CategoryId);
            $isSuccess = $stmt->execute();
            $insertId = $stmt->insert_id;
            $stmt->close(); $db->close();
            return $isSuccess && ($insertId > 0) ? $insertId : 0;
        }

        public function updateProduct($product, $newImage = null) {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);
            if ($newImage) {
                $sql = "UPDATE product SET Title=?, Content=?, Img=?, Price=?, Rate=?, UpdateAt=NOW(), CategoryId=? WHERE Id=?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("sssisii", $product->Title, $product->Content, $newImage, $product->Price, $product->Rate, $product->CategoryId, $product->Id);
            } else {
                $sql = "UPDATE product SET Title=?, Content=?, Price=?, Rate=?, UpdateAt=NOW(), CategoryId=? WHERE Id=?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("ssisii", $product->Title, $product->Content, $product->Price, $product->Rate, $product->CategoryId, $product->Id);
            }
            $isSuccess = $stmt->execute();
            $result = $isSuccess && ($stmt->affected_rows > 0);
            $stmt->close(); $db->close();
            return $result;
        }

        public function deleteProduct($id) {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);
            if ($db->connect_error) return false;
            $stmt1 = $db->prepare("DELETE FROM storeproduct WHERE ProductId = ?");
            if ($stmt1) { $stmt1->bind_param("i", $id); $stmt1->execute(); $stmt1->close(); }
            $stmt2 = $db->prepare("DELETE FROM product WHERE Id = ?");
            $result = false;
            if ($stmt2) { $stmt2->bind_param("i", $id); $result = $stmt2->execute(); $stmt2->close(); }
            $db->close(); return $result;
        }

        public function getAllStores() {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);
            $res = $db->query("SELECT Id, StoreName FROM store ORDER BY StoreName ASC");
            $stores = [];
            if ($res) { while ($row = $res->fetch_object()) { $stores[] = $row; } }
            $db->close(); return $stores;
        }

        public function addProductToStore($productId, $storeIds) {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);
            $stmt = $db->prepare("DELETE FROM storeproduct WHERE ProductId = ?");
            $stmt->bind_param("i", $productId); $stmt->execute(); $stmt->close();
            if (is_array($storeIds) && count($storeIds) > 0) {
                $insertStmt = $db->prepare("INSERT INTO storeproduct (StoreId, ProductId, IsAvailable) VALUES (?, ?, 1)");
                foreach ($storeIds as $storeId) {
                    $sId = intval($storeId);
                    $insertStmt->bind_param("ii", $sId, $productId);
                    $insertStmt->execute();
                }
                $insertStmt->close();
            }
            $db->close(); return true;
        }

        public function getProductStores($productId) {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);
            $stmt = $db->prepare("SELECT StoreId FROM storeproduct WHERE ProductId = ?");
            $stmt->bind_param("i", $productId); $stmt->execute(); $result = $stmt->get_result();
            $storeIds = [];
            if ($result->num_rows > 0) { while ($row = $result->fetch_assoc()) { $storeIds[] = $row['StoreId']; } }
            $stmt->close(); $db->close(); return $storeIds;
        }
    }
?>