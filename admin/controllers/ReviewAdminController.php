<?php
require_once __DIR__ . '/../../env.php';

class ReviewAdminController 
{
    public function getAllReviews($storeId = 0, $ratingType = '')
    {
        global $hostname, $username, $password, $dbname, $port;
        $conn = new mysqli($hostname, $username, $password, $dbname, $port);
        $conn->set_charset("utf8mb4");

        // --- BẢO MẬT: ĐỌC QUYỀN TRỰC TIẾP TỪ DATABASE ---
        $customerId = $_SESSION['CustomerId'] ?? 0;
        $sqlUser = "SELECT Role, StoreId FROM customer WHERE Id = " . (int)$customerId;
        $resUser = $conn->query($sqlUser);
        
        $role = 1; // Mặc định là Admin
        $userStoreId = 0;
        
        if ($resUser && $resUser->num_rows > 0) {
            $u = $resUser->fetch_assoc();
            $role = (int)$u['Role'];
            $userStoreId = (int)$u['StoreId'];
        }

        // ÉP BUỘC: Nếu là quản lý chi nhánh (Role 2), bỏ qua storeId người dùng chọn, chỉ lấy của họ
        if ($role == 2 && $userStoreId > 0) {
            $storeId = $userStoreId;
        }

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

        if ($storeId > 0) {
            $sql .= " AND s.Id = " . (int)$storeId;
        }

        if ($ratingType === 'good') $sql .= " AND r.Rating >= 4";
        elseif ($ratingType === 'bad') $sql .= " AND r.Rating <= 3";

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