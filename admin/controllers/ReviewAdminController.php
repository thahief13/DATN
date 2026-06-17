<?php
require_once __DIR__ . '/../../env.php';

class ReviewAdminController 
{
    public function getAllReviews($storeId = 0, $ratingType = '')
    {
        global $hostname, $username, $password, $dbname, $port;

        $conn = new mysqli($hostname, $username, $password, $dbname, $port);
        $conn->set_charset("utf8mb4");

        $customerId = $_SESSION['CustomerId'] ?? 0;

        $sqlUser = "SELECT Role, StoreId FROM customer WHERE Id = " . (int)$customerId;
        $resUser = $conn->query($sqlUser);

        $role = 1;
        $userStoreId = 0;

        if ($resUser && $resUser->num_rows > 0) {
            $u = $resUser->fetch_assoc();
            $role = (int)$u['Role'];
            $userStoreId = (int)$u['StoreId'];
        }

        // Nếu là quản lý chi nhánh thì chỉ xem được chi nhánh của mình
        if ($role == 2 && $userStoreId > 0) {
            $storeId = $userStoreId;
        }

        $sql = "
            SELECT 
                r.Id,
                r.Rating,
                r.Comment,
                r.AiSentiment,
                r.CreatedAt,
                c.FirstName,
                c.LastName,
                p.Title AS ProductName,
                p.Img,
                s.StoreName
            FROM productreview r
            JOIN customer c ON r.CustomerId = c.Id
            JOIN storeproduct sp ON r.StoreProductId = sp.Id
            JOIN product p ON sp.ProductId = p.Id
            JOIN store s ON sp.StoreId = s.Id
            WHERE 1=1
        ";

        // Lọc theo chi nhánh
        if ($storeId > 0) {
            $sql .= " AND s.Id = " . (int)$storeId;
        }

       
        if ($ratingType === 'good') {
            // Tích cực: 
            $sql .= "
                AND (
                    r.Rating >= 4
                    OR (
                        (r.Rating IS NULL OR r.Rating = 0)
                        AND r.AiSentiment = 'TOT'
                    )
                )
            ";

        } elseif ($ratingType === 'neutral') {
            // Trung tính
            $sql .= "
                AND (
                    (r.Rating >= 3 AND r.Rating <= 3.5)
                    OR (
                        (r.Rating IS NULL OR r.Rating = 0)
                        AND r.AiSentiment = 'TRUNG_TINH'
                    )
                )
            ";

        } elseif ($ratingType === 'bad') {
            // Tiêu cực: 
            $sql .= "
                AND (
                    (r.Rating > 0 AND r.Rating <= 2.5)
                    OR (
                        (r.Rating IS NULL OR r.Rating = 0)
                        AND r.AiSentiment = 'XAU'
                    )
                )
            ";
        }

        $sql .= " ORDER BY r.CreatedAt DESC";

        $result = $conn->query($sql);

        $reviews = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $reviews[] = $row;
            }
        }

        $conn->close();

        return $reviews;
    }

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