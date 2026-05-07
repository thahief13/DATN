<?php
require_once __DIR__ . '/../../env.php';

class ReviewAdminController 
{
    // Lấy danh sách đánh giá kèm bộ lọc
    public function getAllReviews($storeId = 0, $ratingType = '')
    {
        global $hostname, $username, $password, $dbname, $port;
        $conn = new mysqli($hostname, $username, $password, $dbname, $port);
        $conn->set_charset("utf8mb4");

        $sql = "SELECT r.Id, r.Rating, r.Comment, r.CreatedAt, 
                       c.FirstName, c.LastName, 
                       p.Title AS ProductName, p.Img,
                       s.StoreName 
                FROM productreview r
                JOIN customer c ON r.CustomerId = c.Id
                JOIN storeproduct sp ON r.StoreProductId = sp.Id
                JOIN product p ON sp.ProductId = p.Id
                JOIN store s ON sp.StoreId = s.Id
                WHERE 1=1";

        // Lọc theo cửa hàng
        if ($storeId > 0) {
            $sql .= " AND s.Id = " . (int)$storeId;
        }

        // Tool lọc đánh giá Tốt/Xấu
        if ($ratingType === 'good') {
            $sql .= " AND r.Rating >= 4";
        } elseif ($ratingType === 'bad') {
            $sql .= " AND r.Rating <= 3";
        }

        $sql .= " ORDER BY r.CreatedAt DESC";

        $result = $conn->query($sql);
        $reviews = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $reviews[] = $row;
            }
        }
        
        $conn->close();
        return $reviews;
    }

    // Xóa đánh giá (nếu khách chửi bậy hoặc spam)
    public function deleteReview($reviewId)
    {
        global $hostname, $username, $password, $dbname, $port;
        $conn = new mysqli($hostname, $username, $password, $dbname, $port);
        $stmt = $conn->prepare("DELETE FROM productreview WHERE Id = ?");
        $stmt->bind_param("i", $reviewId);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }
}
?>