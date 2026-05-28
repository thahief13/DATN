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
$rating = (float)$_POST['rating'];
$comment = trim($_POST['comment']);

if ($storeProductId === 0) {
    echo json_encode(['success' => false, 'message' => 'Không xác định được chi nhánh!']);
    exit;
}

if ($rating < 0.5 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Đánh giá phải từ 0.5-5 sao']);
    exit;
}


function analyzeCommentWithAI($commentText, $rating) {
    
    $apiKey = 'AIzaSyCqAyErPiUAmYKNP-IxnfsSJ_s6bqcGP_w';
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey;

    $prompt = "Phân tích cảm xúc bình luận sau. BẮT BUỘC chỉ trả về 1 (nếu là khen ngợi, tốt) hoặc -1 (nếu là chê bai, phàn nàn, xấu). KHÔNG TRẢ LỜI THÊM BẤT CỨ TỪ NÀO. Bình luận: \"" . $commentText . "\"";
    $data = ["contents" => [["parts" => [["text" => $prompt]]]]];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $resultData = json_decode($response, true);
        if (isset($resultData['candidates'][0]['content']['parts'][0]['text'])) {
            $val = trim($resultData['candidates'][0]['content']['parts'][0]['text']);
            if (strpos($val, '-1') !== false) return 'XAU';
            if (strpos($val, '1') !== false) return 'TOT';
        }
    }
    
    return ($rating >= 4) ? 'TOT' : 'XAU'; 
}

$aiSentiment = analyzeCommentWithAI($comment, $rating);


$reviewController = new ReviewController();

$result = $reviewController->addReview($storeProductId, $customerId, $rating, $comment, $aiSentiment);

if ($result) {
    $reviewController->updateProductRating($storeProductId);
    echo json_encode(['success' => true, 'message' => 'Cảm ơn đánh giá của bạn!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Bạn đã đánh giá sản phẩm này rồi.']);
}