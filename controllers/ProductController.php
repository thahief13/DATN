<?php
if (!class_exists('ProductController')) {
    require_once __DIR__ . '/../env.php';
    require_once __DIR__ . '/../models/Product.php';

    class ProductController
    {       // lấy toàn bộ sản phẩm theo danh mục và chi nhánh
        public function getAllProducts(int $storeId, int $categoryId, $sortString, $searchString)
        {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);

            $sql = "SELECT p.Id, p.Title, p.Content, p.Img, p.Price, p.Rate, p.CreateAt, p.UpdateAt, 
                            c.Id AS CategoryId, c.Title AS CategoryTitle";
            if ($categoryId > 0) {
                $sql .= " FROM product p JOIN category c ON p.CategoryId = c.Id WHERE c.Id = " . intval($categoryId);
            } else {
                if ($storeId > 0) {
                    $sql .= ", s.Id as StoreId, s.StoreName AS StoreName FROM product p
                            JOIN category c ON p.CategoryId = c.Id
                            JOIN storeproduct sp ON p.Id = sp.ProductId
                            JOIN store s ON sp.StoreId = s.Id AND s.Id = " . intval($storeId) . " and sp.IsAvailable = 1";
                } else {
                    $sql .= " FROM product p JOIN category c ON p.CategoryId = c.Id";
                }
            }
            if (!empty($searchString)) {
                if ($categoryId > 0) {
                    $sql .= " AND p.Title LIKE '%" . $searchString . "%'";
                } else {
                    $sql .= " WHERE p.Title LIKE '%" . $searchString . "%'";
                }
            }
            $orderBy = " p.Id DESC";
            switch ($sortString) {
                case 'price_desc':
                    $orderBy = ' p.Price DESC';
                    break;
                case 'price_asc':
                    $orderBy = ' p.Price ASC';
                    break;
                case 'rate_desc':
                    $orderBy = ' p.Rate DESC';
                    break;
                case 'latest_desc':
                    $orderBy = ' p.Id DESC';
                    break;
            }
            $sql .= " ORDER BY" . $orderBy;
            $result = $db->query($sql);

            $products = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $product = new Product();
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
                    $product->StoreId = isset($row['StoreId']) ? $row['StoreId'] : 0;
                    $product->StoreName = isset($row['StoreName']) ? $row['StoreName'] : '';
                    $products[] = $product;
                }
            }

            $db->close();
            return $products;
        }
                    //lấy sản phẩm trên 1 trang để heienr thị
        public function getProducts(int $storeId, int $categoryId, string $searchString, string $sortString, int $offset, int $limit)
        {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);

            $sql = "SELECT p.Id, p.Title, p.Content, p.Img, p.Price, p.Rate, p.CreateAt, p.UpdateAt,
                c.Id AS CategoryId, c.Title AS CategoryTitle";

            if ($storeId > 0) {
                $sql .= ", s.Id AS StoreId, s.StoreName AS StoreName
                 FROM product p
                 JOIN category c ON p.CategoryId = c.Id
                 JOIN storeproduct sp ON p.Id = sp.ProductId
                 JOIN store s ON sp.StoreId = s.Id
                 WHERE s.Id = " . intval($storeId) . " AND sp.IsAvailable = 1";
                if ($categoryId > 0) $sql .= " AND c.Id = " . intval($categoryId);
            } else {
                $sql .= " FROM product p
                  JOIN category c ON p.CategoryId = c.Id";
                if ($categoryId > 0) $sql .= " WHERE c.Id = " . intval($categoryId);
            }

            if (!empty($searchString)) {
                $sql .= ($storeId > 0 || $categoryId > 0) ? " AND " : " WHERE ";
                $sql .= "p.Title LIKE '%" . $db->real_escape_string($searchString) . "%'";
            }

            // phân loại
            $orderBy = " p.Id DESC";
            switch ($sortString) {
                case 'price_desc':
                    $orderBy = ' p.Price DESC';
                    break;
                case 'price_asc':
                    $orderBy = ' p.Price ASC';
                    break;
                case 'rate_desc':
                    $orderBy = ' p.Rate DESC';
                    break;
                case 'latest_desc':
                    $orderBy = ' p.CreateAt DESC';
                    break;
            }
            $sql .= " ORDER BY" . $orderBy;

            // Giới hạn
            $sql .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);

            $result = $db->query($sql);
            $products = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $product = new Product();
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
                    $product->StoreId = $row['StoreId'] ?? 0;
                    $product->StoreName = $row['StoreName'] ?? '';
                    $products[] = $product;
                }
            }

            $db->close();
            return $products;
        }
                    // đếm tổng sản phẩm khi lọc
        public function countProducts(int $storeId, int $categoryId, string $searchString)
        {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);

            $sql = "SELECT COUNT(*) as total FROM product p
            JOIN category c ON p.CategoryId = c.Id";

            if ($storeId > 0) {
                $sql .= " JOIN storeproduct sp ON p.Id = sp.ProductId
                  JOIN store s ON sp.StoreId = s.Id
                  WHERE s.Id = " . intval($storeId) . " AND sp.IsAvailable = 1";
                if ($categoryId > 0) $sql .= " AND c.Id = " . intval($categoryId);
            } else {
                if ($categoryId > 0) $sql .= " WHERE c.Id = " . intval($categoryId);
            }

            if (!empty($searchString)) {
                $sql .= ($storeId > 0 || $categoryId > 0) ? " AND " : " WHERE ";
                $sql .= "p.Title LIKE '%" . $db->real_escape_string($searchString) . "%'";
            }

            $result = $db->query($sql);
            $total = 0;
            if ($result) {
                $row = $result->fetch_assoc();
                $total = $row['total'] ?? 0;
            }

            $db->close();
            return $total;
        }
                // lấy thông tin chi tiết của 1 sản phẩm
        public function getProductById(int $productId)
        {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);
            

            $sql = "SELECT p.Id, p.Title, p.Content, p.Img, p.Price, p.Rate, p.CreateAt, p.UpdateAt, 
                            c.Id AS CategoryId, c.Title AS CategoryTitle, s.Id as StoreId, s.StoreName AS StoreName
                        FROM product p
                        JOIN category c ON p.CategoryId = c.Id
                        JOIN storeproduct sp on p.Id = sp.ProductId
                        JOIN store s on sp.StoreId = s.Id
                        WHERE p.Id = " . intval($productId);
            $result = $db->query($sql);

            $product = new Product();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $product = new Product();
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
                    $product->StoreId = $row['StoreId'];
                    $product->StoreName = $row['StoreName'];
                }
            }

            $db->close();
            return $product;
        }
                    // Lấy danh sách sản phẩm nổi bật
        public function getFeaturedProducts(int $storeId, int $limits)
        {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);

            $sql = "SELECT p.Id, p.Title, p.Content, p.Img, p.Price, p.Rate, p.CreateAt, p.UpdateAt, 
                            c.Id AS CategoryId, c.Title AS CategoryTitle";
            if ($storeId > 0) {
                $sql .= ", s.Id as StoreId, s.StoreName AS StoreName FROM product p
                        JOIN category c ON p.CategoryId = c.Id
                        JOIN storeproduct sp ON p.Id = sp.ProductId
                        JOIN store s ON sp.StoreId = s.Id AND s.Id = " . intval($storeId) . " and sp.IsAvailable = 1";
            } else {
                $sql .= " FROM product p JOIN category c ON p.CategoryId = c.Id";
            }

            $sql .= " ORDER BY p.Rate DESC LIMIT " . intval($limits);

            $result = $db->query($sql);

            $products = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $product = new Product();
                    $product->Id = $row['Id'];
                    $product->Title = $row['Title'];
                    $product->Content = $row['Content'];
                    $product->Img = $row['Img'];
                    $product->Price = $row['Price'];
                    $product->Rate = $row['Rate'];
                    $product->CreateAt = $row['CreateAt'];
                    $product->UpdateAt = $row['UpdateAt'];
                    $products[] = $product;
                }
            }

            $db->close();
            return $products;
        }
                    // lấy sản phẩm mới nhất
        public function getLatestProducts(int $storeId, int $limits)
        {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);

            $sql = "SELECT p.Id, p.Title, p.Content, p.Img, p.Price, p.Rate, p.CreateAt, p.UpdateAt, 
                            c.Id AS CategoryId, c.Title AS CategoryTitle";
            if ($storeId > 0) {
                $sql .= ", s.Id as StoreId, s.StoreName AS StoreName FROM product p
                        JOIN category c ON p.CategoryId = c.Id
                        JOIN storeproduct sp ON p.Id = sp.ProductId
                        JOIN store s ON sp.StoreId = s.Id AND s.Id = " . intval($storeId) . " and sp.IsAvailable = 1";
            } else {
                $sql .= " FROM product p JOIN category c ON p.CategoryId = c.Id";
            }

            $sql .= " ORDER BY p.CreateAt DESC LIMIT " . intval($limits);

            $result = $db->query($sql);

            $products = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $product = new Product();
                    $product->Id = $row['Id'];
                    $product->Title = $row['Title'];
                    $product->Content = $row['Content'];
                    $product->Img = $row['Img'];
                    $product->Price = $row['Price'];
                    $product->Rate = $row['Rate'];
                    $product->CreateAt = $row['CreateAt'];
                    $product->UpdateAt = $row['UpdateAt'];
                    $products[] = $product;
                }
            }

            $db->close();
            return $products;
        }

    public function getRelatedProducts(int $storeId, int $productId) //lấy sản phẩm hiển thị trong trang chi tiết
        {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);

            $sql = "SELECT p.Id, p.Title, p.Content, p.Img, p.Price, p.Rate, p.CreateAt, p.UpdateAt, 
                    c.Id AS CategoryId, c.Title AS CategoryTitle, s.Id as StoreId, s.StoreName AS StoreName
                    FROM product p
                    JOIN category c ON p.CategoryId = c.Id
                    JOIN storeproduct sp on p.Id = sp.ProductId
                    JOIN store s on sp.StoreId = s.Id
                    WHERE p.Id != " . intval($productId) . " AND c.Id = (SELECT CategoryId FROM product WHERE Id = " . intval($productId) . ")";
            
            if ($storeId > 0) {
                $sql .= " AND s.Id = " . intval($storeId) . " AND sp.IsAvailable = 1";
            }
            
$sql .= " ORDER BY RAND() LIMIT 6";


            $result = $db->query($sql);

            $products = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $product = new Product();
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
                    // lấy số sao đánh giá
        public function getReviews(int $productId) {
            require_once __DIR__ . '/ReviewController.php';
            $reviewController = new ReviewController();
            return $reviewController->getReviews($productId);
        }

                    // sản phẩm bán chạy
        
     public function getTopSelling(int $storeId, int $limits)
        {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);

            $sql = "SELECT p.Id, p.Title, p.Content, p.Img, p.Price, p.Rate, p.CreateAt, p.UpdateAt,
                    c.Id AS CategoryId, c.Title AS CategoryTitle, s.Id AS StoreId, s.StoreName AS StoreName,
                    (SELECT COALESCE(SUM(Quantity), 0) FROM paymentdetail WHERE StoreProductId = sp.Id) as sold_count
                    FROM product p
                    JOIN category c ON p.CategoryId = c.Id
                    JOIN storeproduct sp ON p.Id = sp.ProductId AND sp.IsAvailable = 1
                    JOIN store s ON sp.StoreId = s.Id
                    WHERE s.Id = " . intval($storeId) . "
                    ORDER BY sold_count DESC
                    LIMIT " . intval($limits);

            $result = $db->query($sql);
            $products = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $product = new Product();
                    $product->Id = $row['Id'];
                    $product->Title = $row['Title'];
                    $product->Content = $row['Content'];
                    $product->Img = $row['Img'];
                    $product->Price = $row['Price'];
                    $product->Rate = $row['Rate'];
                    $product->CreateAt = $row['CreateAt'];
                    $product->UpdateAt = $row['UpdateAt'];
                    $product->StoreId = $row['StoreId'];
                    $product->StoreName = $row['StoreName'];
                    $products[] = $product;
                }
            }
            $db->close();
            return $products;
        }
                // lấy sp giảm giá
        public function getCheapest(int $storeId, int $limits)
        {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);

            $sql = "SELECT p.Id, p.Title, p.Content, p.Img, p.Price, p.Rate, p.CreateAt, p.UpdateAt,
                    c.Id AS CategoryId, c.Title AS CategoryTitle, s.Id AS StoreId, s.StoreName AS StoreName,
                    sp.DiscountPercent
                    FROM product p
                    JOIN category c ON p.CategoryId = c.Id
                    JOIN storeproduct sp ON p.Id = sp.ProductId AND sp.IsAvailable = 1
                    JOIN store s ON sp.StoreId = s.Id
                    WHERE s.Id = " . intval($storeId) . " AND sp.DiscountPercent > 0
                    ORDER BY sp.DiscountPercent DESC, p.Price ASC
                    LIMIT " . intval($limits);

            $result = $db->query($sql);
            $products = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $product = new Product();
                    $product->Id = $row['Id'];
                    $product->Title = $row['Title'];
                    $product->Content = $row['Content'];
                    $product->Img = $row['Img'];
                    $product->Price = $row['Price'];
                    $product->Rate = $row['Rate'];
                    $product->CreateAt = $row['CreateAt'];
                    $product->UpdateAt = $row['UpdateAt'];
                    $product->DiscountPercent = $row['DiscountPercent'];
                    $products[] = $product;
                }
            }
            $db->close();
            return $products;
        }
    }
}
?>
