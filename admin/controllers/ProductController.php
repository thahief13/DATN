<?php
    include __DIR__ .'/../env.php';
    include __DIR__ .'/../models/Product.php';

    class ProductController {
        public function getAllProducts(int $storeId, int $categoryId)
        {
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);

            $sql = "SELECT p.Id, p.Title, p.Content, p.Img, p.Price, p.Rate, p.CreateAt, p.UpdateAt, 
                        c.Id AS CategoryId, c.Title AS CategoryTitle, s.Id as StoreId, s.StoreName AS StoreName
                    FROM product p
                    JOIN category c ON p.CategoryId = c.Id
                    JOIN storeproduct sp on p.Id = sp.ProductId
                    JOIN store s on sp.StoreId = s.Id";
            if ($categoryId > 0) {
                $sql .= " WHERE c.Id = " . intval($categoryId);
            }
            if ($storeId > 0) {
                $sql .= ($categoryId > 0 ? " AND " : " WHERE ") . " s.Id = " . intval($storeId) . " and sp.IsAvailable = 1";
            }
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
                    $product->StoreId = $row['StoreId'];
                    $product->StoreName = $row['StoreName'];
                    $products[] = $product;
                }
            }

            $db->close();
            return $products;
        }
    }
?>