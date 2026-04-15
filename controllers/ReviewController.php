<?php
require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

class ReviewController {
    
    public function getReviews($storeProductId) { 
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        
        $sql = "SELECT r.Id, r.StoreProductId, r.CustomerId, r.Rating, r.Comment, r.CreatedAt, 
                       CONCAT('Khách ', r.CustomerId) as CustomerName
                FROM productreview r 
                WHERE r.StoreProductId = " . intval($storeProductId) . " 
                ORDER BY r.CreatedAt DESC";

        $result = $db->query($sql);
        $reviews = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $review = new Review();
                $review->Id = $row['Id'];
                $review->ProductId = $row['StoreProductId'];
                $review->CustomerId = $row['CustomerId'];
                $review->Rating = $row['Rating'];
                $review->Comment = $row['Comment'];
                $review->CreatedAt = $row['CreatedAt'];
                $review->CustomerName = $row['CustomerName'];
                $reviews[] = $review;
            }
        }
        $db->close();
        return $reviews;
    } // Kết thúc hàm getReviews

    public function addReview($storeProductId, $customerId, $rating, $comment) {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        
        $checkSql = "SELECT Id FROM productreview WHERE StoreProductId = " . intval($storeProductId) . " AND CustomerId = " . intval($customerId);
        $checkResult = $db->query($checkSql);
        if ($checkResult && $checkResult->num_rows > 0) {
            $db->close();
            return false; 
        }
        
        $sql = "INSERT INTO productreview (StoreProductId, CustomerId, Rating, Comment) VALUES (" . 
               intval($storeProductId) . ", " . intval($customerId) . ", " . intval($rating) . ", '" . 
               $db->real_escape_string($comment) . "')";
        
        $result = $db->query($sql);
        $db->close();
        return $result;
    }
    
    public function updateProductRating($storeProductId) {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        
        $sql = "UPDATE product p SET Rate = (
                    SELECT AVG(r.Rating) 
                    FROM productreview r 
                    JOIN storeproduct sp ON r.StoreProductId = sp.Id
                    WHERE sp.ProductId = p.Id
                ) WHERE p.Id = (SELECT ProductId FROM storeproduct WHERE Id = " . intval($storeProductId) . ")";
        
        $result = $db->query($sql);
        $db->close();
        return $result;
    }
}