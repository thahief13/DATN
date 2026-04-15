<?php
session_start();
require_once '../../env.php';
require_once '../../controllers/ReviewController.php';

header('Content-Type: application/json');

if (!isset($_SESSION['CustomerId']) || !isset($_POST['store_product_id']) || !isset($_POST['rating']) || !isset($_POST['comment'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin dữ liệu']);
    exit;
}

$customerId = (int)$_SESSION['CustomerId'];
$storeProductId = (int)$_POST['store_product_id'];
$rating = (int)$_POST['rating'];
$comment = trim($_POST['comment']);

// KIỂM TRA QUAN TRỌNG: Chặn lỗi Database do ID = 0
if ($storeProductId === 0) {
    echo json_encode(['success' => false, 'message' => 'Không xác định được chi nhánh. Vui lòng ra trang Cửa hàng chọn chi nhánh trước khi đánh giá!']);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Đánh giá phải từ 1-5 sao']);
    exit;
}

if (strlen($comment) < 10) {
    echo json_encode(['success' => false, 'message' => 'Bình luận phải có ít nhất 10 ký tự']);
    exit;
}

$reviewController = new ReviewController();
$result = $reviewController->addReview($storeProductId, $customerId, $rating, $comment);

if ($result) {
    $reviewController->updateProductRating($storeProductId);
    echo json_encode(['success' => true, 'message' => 'Cảm ơn đánh giá của bạn!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Bạn đã đánh giá sản phẩm này rồi.']);
}