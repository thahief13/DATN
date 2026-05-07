<?php
if (session_status() == PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../../admin/controllers/ReviewAdminController.php';
require_once __DIR__ . '/../../../admin/controllers/StoreController.php';

$reviewController = new ReviewAdminController();
$storeController = new StoreController();

// Lấy tham số từ URL
$storeId = isset($_GET['storeId']) ? (int)$_GET['storeId'] : 0;
$ratingType = isset($_GET['ratingType']) ? $_GET['ratingType'] : '';

$reviews = $reviewController->getAllReviews($storeId, $ratingType);
$stores = $storeController->getAllStores();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Đánh giá</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        .star-good { color: #ffc107; } /* Vàng cho sao tốt */
        .star-bad { color: #6c757d; }  /* Xám cho sao trống */
        .review-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <h2 class="mb-4"><i class="fas fa-comments text-primary"></i> Quản lý Đánh giá sản phẩm</h2>

        <div class="review-card p-3 mb-4">
            <form method="GET" class="row g-3">
                <input type="hidden" name="page" value="review">
                
                <div class="col-md-4">
                    <label class="form-label fw-bold">Chọn Chi nhánh</label>
                    <select name="storeId" class="form-select" onchange="this.form.submit()">
                        <option value="0">-- Tất cả chi nhánh --</option>
                        <?php foreach ($stores as $s): ?>
                            <option value="<?= $s->Id ?>" <?= $s->Id == $storeId ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s->StoreName) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Lọc theo Đánh giá</label>
                    <select name="ratingType" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả đánh giá --</option>
                        <option value="good" <?= $ratingType == 'good' ? 'selected' : '' ?>>⭐ Đánh giá Tốt (4 - 5 sao)</option>
                        <option value="bad" <?= $ratingType == 'bad' ? 'selected' : '' ?>>⚠️ Đánh giá Xấu (1 - 3 sao)</option>
                    </select>
                </div>
            </form>
        </div>

        <div class="review-card p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Khách hàng</th>
                            <th>Chi nhánh</th>
                            <th>Sản phẩm</th>
                            <th class="text-center">Số sao</th>
                            <th>Nội dung bình luận</th>
                            <th>Thời gian</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reviews)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">Chưa có đánh giá nào phù hợp với bộ lọc!</td></tr>
                        <?php else: ?>
                            <?php foreach ($reviews as $r): 
                                $isGood = $r['Rating'] >= 4;
                            ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($r['FirstName'] . ' ' . $r['LastName']) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($r['StoreName']) ?></span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="../../img/SanPham/<?= htmlspecialchars($r['Img']) ?>" width="40" height="40" class="rounded me-2" style="object-fit:cover;">
                                        <span><?= htmlspecialchars($r['ProductName']) ?></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $isGood ? 'bg-success' : 'bg-danger' ?> fs-6">
                                        <?= $r['Rating'] ?> <i class="fas fa-star text-warning"></i>
                                    </span>
                                </td>
                                <td style="max-width: 300px; word-wrap: break-word;">
                                    <i>"<?= htmlspecialchars($r['Comment']) ?>"</i>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($r['CreatedAt'])) ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteReview(<?= $r['Id'] ?>)">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function deleteReview(id) {
        if (confirm('Bạn có chắc chắn muốn xóa đánh giá này không? Hành động này không thể hoàn tác.')) {
            fetch('review/process_delete.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `reviewId=${id}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Lỗi: Không thể xóa!');
                }
            });
        }
    }
    </script>
</body>
</html>