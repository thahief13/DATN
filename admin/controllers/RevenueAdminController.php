<?php
require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../models/RevenueAdmin.php';

class RevenueAdminController {
    // ĐÃ THÊM: Tham số $day vào hàm
    public function getRevenueByStore($day = 0, $month = 0, $year = 0) {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        $db->set_charset("utf8mb4");

        // Ép kiểu để an toàn tuyệt đối với cơ sở dữ liệu
        $day = (int)$day;
        $month = (int)$month;
        $year = (int)$year;

        $sql = "SELECT s.Id, s.StoreName, DATE(p.CreatedAt) as RevDate, SUM(p.Total) as TotalAmount, COUNT(p.Id) as TotalOrders 
                FROM store s
                JOIN payment p ON s.Id = p.StoreId AND p.Status IN ('paid', 'đã giao', 'thành công')
                WHERE 1=1 ";

        // Nối thêm điều kiện lọc theo tháng/năm
        if ($month > 0 && $year > 0) {
            $sql .= " AND MONTH(p.CreatedAt) = $month AND YEAR(p.CreatedAt) = $year ";
        }
        // Nối thêm điều kiện lọc theo ngày nếu có chọn
        if ($day > 0) {
            $sql .= " AND DAY(p.CreatedAt) = $day ";
        }

        $sql .= " GROUP BY s.Id, DATE(p.CreatedAt) ORDER BY RevDate DESC, TotalAmount DESC";
        
        $result = $db->query($sql);

        $revenues = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rev = new RevenueAdmin();
                $rev->StoreId = $row['Id'];
                $rev->StoreName = $row['StoreName'];
                $rev->RevenueDate = $row['RevDate'];
                $rev->TotalRevenue = $row['TotalAmount'] ?? 0;
                $rev->OrderCount = $row['TotalOrders'] ?? 0;
                $revenues[] = $rev;
            }
        }
        $db->close();
        return $revenues;
    }

    public function syncRevenue($storeId, $amount, $date) {
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);
        
        $month = (int)date('m', strtotime($date));
        $year = (int)date('Y', strtotime($date));

        $sql = "INSERT INTO revenue (StoreId, Month, Year, Total) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE Total = Total + ?";
                
        $stmt = $db->prepare($sql);
        $stmt->bind_param("iiidd", $storeId, $month, $year, $amount, $amount);
        $result = $stmt->execute();
        $db->close();
        return $result;
    }
}
?>