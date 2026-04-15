<?php
if (!class_exists('CartController')) {
    require_once __DIR__ . '/../env.php';
    require_once __DIR__ . '/../models/CartItem.php';

    class CartController {
        // Lấy giỏ hàng theo khách hàng và chi nhánh
        public function getCartByCustomerId($customerId, $storeId = 0) {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);

            // BỔ SUNG: Lấy thêm sp.DiscountPercent từ cơ sở dữ liệu
            $sql = "SELECT ci.*, sp.ProductId, sp.StoreId, sp.DiscountPercent 
                    FROM cart c
                    JOIN cart_item ci ON c.Id = ci.CartId
                    JOIN storeproduct sp ON ci.StoreProductId = sp.Id
                    WHERE c.CustomerId = ?";
            
            if ($storeId > 0) {
                $sql .= " AND sp.StoreId = " . (int)$storeId;
            }

            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $customerId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $cartItems = [];
            while ($row = $result->fetch_assoc()) {
                $item = new CartItem();
                $item->Id = $row['Id'];
                $item->ProductId = $row['ProductId'];
                $item->StoreId = $row['StoreId'];
                $item->Quantity = $row['Quantity'];
                // BỔ SUNG: Lưu lại % giảm giá
                $item->DiscountPercent = $row['DiscountPercent'] ?? 0;
                $cartItems[] = $item;
            }
            $db->close();
            return $cartItems;
        }

        // Thêm sản phẩm vào giỏ hàng (Xử lý logic 2 bảng)
        public function addToCart($customerId, $productId, $storeId, $quantity) {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);

            $stmt = $db->prepare("SELECT Id FROM cart WHERE CustomerId = ?");
            $stmt->bind_param("i", $customerId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $cartId = $result->fetch_assoc()['Id'];
            } else {
                $stmtInsert = $db->prepare("INSERT INTO cart (CustomerId) VALUES (?)");
                $stmtInsert->bind_param("i", $customerId);
                $stmtInsert->execute();
                $cartId = $db->insert_id;
            }

            $stmtStore = $db->prepare("SELECT Id FROM storeproduct WHERE ProductId = ? AND StoreId = ?");
            $stmtStore->bind_param("ii", $productId, $storeId);
            $stmtStore->execute();
            $storeProduct = $stmtStore->get_result()->fetch_assoc();
            if (!$storeProduct) return false;
            $storeProductId = $storeProduct['Id'];

            $stmtCheck = $db->prepare("SELECT Id, Quantity FROM cart_item WHERE CartId = ? AND StoreProductId = ?");
            $stmtCheck->bind_param("ii", $cartId, $storeProductId);
            $stmtCheck->execute();
            $resCheck = $stmtCheck->get_result();

            if ($resCheck->num_rows > 0) {
                $item = $resCheck->fetch_assoc();
                $newQty = $item['Quantity'] + $quantity;
                $stmtUpdate = $db->prepare("UPDATE cart_item SET Quantity = ? WHERE Id = ?");
                $stmtUpdate->bind_param("ii", $newQty, $item['Id']);
                $finalResult = $stmtUpdate->execute();
            } else {
                $stmtAdd = $db->prepare("INSERT INTO cart_item (CartId, StoreProductId, Quantity) VALUES (?, ?, ?)");
                $stmtAdd->bind_param("iii", $cartId, $storeProductId, $quantity);
                $finalResult = $stmtAdd->execute();
            }
            $db->close();
            return $finalResult;
        }

        public function updateQuantity($customerId, $productId, $storeId, $quantity) {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);

            $sql = "UPDATE cart_item ci
                    JOIN cart c ON ci.CartId = c.Id
                    JOIN storeproduct sp ON ci.StoreProductId = sp.Id
                    SET ci.Quantity = ? 
                    WHERE c.CustomerId = ? AND sp.ProductId = ? AND sp.StoreId = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("iiii", $quantity, $customerId, $productId, $storeId);
            $result = $stmt->execute();
            $db->close();
            return $result;
        }

        public function deleteItemInCart($customerId, $productId, $storeId) {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);

            $sql = "DELETE ci FROM cart_item ci
                    JOIN cart c ON ci.CartId = c.Id
                    JOIN storeproduct sp ON ci.StoreProductId = sp.Id
                    WHERE c.CustomerId = ? AND sp.ProductId = ? AND sp.StoreId = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("iii", $customerId, $productId, $storeId);
            $result = $stmt->execute();
            $db->close();
            return $result;
        }

        public function getTotal($customerId) {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);
            $sql = "SELECT SUM(ci.Quantity) as total 
                    FROM cart c 
                    JOIN cart_item ci ON c.Id = ci.CartId 
                    WHERE c.CustomerId = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $customerId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $db->close();
            return (int)($row['total'] ?? 0);
        }

        public function removeFromCart($customerId, $productId, $storeId) {
            return $this->deleteItemInCart($customerId, $productId, $storeId);
        }
    }
}