<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../admin/controllers/ReviewAdminController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reviewId'])) {
    $reviewId = (int)$_POST['reviewId'];
    
    $controller = new ReviewAdminController();
    $result = $controller->deleteReview($reviewId);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
?>