<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../env.php';

global $hostname, $username, $password, $dbname, $port;
$dbAuth = new mysqli($hostname, $username, $password, $dbname, $port);
$dbAuth->set_charset("utf8mb4");

$authQuery = "
    SELECT Role, StoreId
    FROM customer
    WHERE Id = " . (int)($_SESSION['CustomerId'] ?? 0);

$authResult = $dbAuth->query($authQuery);

$authData = $authResult
    ? $authResult->fetch_assoc()
    : [];

$userRole = (int)($authData['Role'] ?? 1);
$userStoreId = (int)($authData['StoreId'] ?? 0);

$dbAuth->close();

$_SESSION['Role'] = $userRole;
$_SESSION['StoreId'] = $userStoreId;

require_once __DIR__ . '/../../../admin/controllers/ReviewAdminController.php';
require_once __DIR__ . '/../../../admin/controllers/StoreAdminController.php';

$reviewController = new ReviewAdminController();
$storeController = new StoreAdminController();


$storeId = ($userRole == 2 && $userStoreId > 0)
    ? $userStoreId
    : (isset($_GET['storeId']) ? (int)$_GET['storeId'] : 0);

$ratingType = $_GET['ratingType'] ?? '';



$reviews = $reviewController->getAllReviews($storeId, $ratingType);
$stores = $storeController->getAllStores();


$goodCount = 0;
$neutralCount = 0;
$badCount = 0;

foreach ($reviews as $r) {
    $aiStatus = trim($r['AiSentiment'] ?? '');
    $rating = (float)($r['Rating'] ?? 0);

    if ($rating >= 4) {
        $goodCount++;
    } elseif ($rating >= 3 && $rating <= 3.5) {
        $neutralCount++;
    } elseif ($rating > 0 && $rating <= 2.5) {
        $badCount++;
    } else {
        
        if ($aiStatus === 'TOT') {
            $goodCount++;
        } elseif ($aiStatus === 'TRUNG_TINH') {
            $neutralCount++;
        } else {
            $badCount++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Đánh giá</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .badge-ai {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.8rem;
            padding: 5px 10px;
        }
    </style>
</head>

<body class="bg-light">

<div class="container-fluid py-4">

    <h2 class="mb-4">
        <i class="fas fa-robot text-primary"></i>
        Quản lý Đánh giá Khách hàng
    </h2>

    <div class="row mb-4">

        <div class="col-md-3">
            <div class="card shadow-sm border-0 p-3 h-100">
                <h6 class="text-center fw-bold text-secondary mb-3">
                    Tỷ lệ Cảm xúc Khách hàng
                </h6>
                <canvas id="reviewChart"></canvas>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card shadow-sm border-0 p-3 h-100">
                <form method="GET" class="row g-3 align-items-center">
                    <input type="hidden" name="page" value="review">

                    <div class="col-md-6">
                        <label class="fw-bold">Chi nhánh</label>
                        <select
                            name="storeId"
                            class="form-select"
                            onchange="this.form.submit()"
                            <?= ($userRole == 2) ? 'disabled style="background-color: #e9ecef;"' : '' ?>
                        >
                            <?php if ($userRole != 2): ?>
                                <option value="0">-- Tất cả chi nhánh --</option>
                            <?php endif; ?>

                            <?php
                            $hasMyStore = false;
                            foreach ($stores as $s):
                                if ($userRole == 2 && $s->Id != $userStoreId) {
                                    continue;
                                }
                                $hasMyStore = true;
                            ?>
                                <option
                                    value="<?= $s->Id ?>"
                                    <?= ($storeId == $s->Id) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($s->StoreName) ?>
                                </option>
                            <?php endforeach; ?>

                            <?php if ($userRole == 2 && !$hasMyStore): ?>
                                <option value="<?= $userStoreId ?>" selected>
                                    Chi nhánh của bạn (#<?= $userStoreId ?>)
                                </option>
                            <?php endif; ?>
                        </select>

                        <?php if ($userRole == 2): ?>
                            <input type="hidden" name="storeId" value="<?= $userStoreId ?>">
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label class="fw-bold">Lọc theo Đánh giá</label>
                        <select name="ratingType" class="form-select" onchange="this.form.submit()">
                            <option value="">Tất cả đánh giá</option>
                            <option value="good" <?= $ratingType == 'good' ? 'selected' : '' ?>>
                                Phản hồi Tích cực (4 - 5*)
                            </option>
                            <option value="neutral" <?= $ratingType == 'neutral' ? 'selected' : '' ?>>
                                Phản hồi Trung tính (3 - 3.5*)
                            </option>
                            <option value="bad" <?= $ratingType == 'bad' ? 'selected' : '' ?>>
                                Phản hồi Tiêu cực (1 - 2.5*)
                            </option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 1000px;">
                <thead class="table-dark">
                <tr>
                    <th>Khách hàng</th>
                    <th>Chi nhánh</th>
                    <th>Sản phẩm</th>
                    <th class="text-center px-4" style="width: 12%;">Số sao</th>
                    <th class="ps-4" style="width: 32%;">Bình luận & Phân tích AI</th>
                    <th>Thời gian</th>
                    <th class="text-center">Thao tác</th>
                </tr>
                </thead>
                <tbody>

                <?php if (empty($reviews)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">Không có đánh giá nào!</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reviews as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['FirstName'] . ' ' . $r['LastName']) ?></td>

                            <td>
                                <span class="badge bg-secondary">
                                    <?= htmlspecialchars($r['StoreName']) ?>
                                </span>
                            </td>

                            <td>
                                <img src="../../img/SanPham/<?= htmlspecialchars($r['Img']) ?>" width="40" class="rounded me-2">
                                <?= htmlspecialchars($r['ProductName']) ?>
                            </td>

                            <td class="text-center px-4">
                                <span class="badge
                                    <?= $r['Rating'] >= 4
                                        ? 'bg-success'
                                        : ($r['Rating'] >= 3 && $r['Rating'] <= 3.5
                                            ? 'bg-warning text-dark'
                                            : 'bg-danger')
                                    ?>
                                ">
                                    <?= number_format($r['Rating'], 1) ?> sao
                                </span>
                            </td>

                            <td class="ps-4">
                                <i>"<?= htmlspecialchars($r['Comment']) ?>"</i>
                                <br>
                                <div class="mt-2">
                                    <?php
                                    $aiStatus = trim($r['AiSentiment'] ?? '');
                                    $rating = (float)($r['Rating'] ?? 0);
                                    ?>

                                    <?php if ($rating >= 4 || ($rating == 0 && $aiStatus === 'TOT')): ?>
                                        <span class="badge bg-success rounded-pill badge-ai shadow-sm">
                                            <i class="fas fa-smile"></i> Tích cực
                                        </span>

                                    <?php elseif (($rating >= 3 && $rating <= 3.5) || ($rating == 0 && $aiStatus === 'TRUNG_TINH')): ?>
                                        <span class="badge bg-warning text-dark rounded-pill badge-ai shadow-sm">
                                            <i class="fas fa-meh"></i> Trung tính
                                        </span>

                                    <?php else: ?>
                                        <span class="badge bg-danger rounded-pill badge-ai shadow-sm">
                                            <i class="fas fa-frown"></i> Tiêu cực
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td><?= date('d/m/Y H:i', strtotime($r['CreatedAt'])) ?></td>

                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteReview(<?= $r['Id'] ?>)">
                                    <i class="fas fa-trash"></i>
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
(function () {
    const canvas = document.getElementById('reviewChart');
    if (!canvas) return;

    if (window.reviewChartInstance) {
        window.reviewChartInstance.destroy();
    }

    window.reviewChartInstance = new Chart(
        canvas.getContext('2d'),
        {
            type: 'doughnut',
            data: {
                labels: ['Tích cực', 'Trung tính', 'Tiêu cực'],
                datasets: [
                    {
                        data: [
                            <?= $goodCount ?>,
                            <?= $neutralCount ?>,
                            <?= $badCount ?>
                        ],
                        backgroundColor: [
                            '#198754',
                            '#ffc107',
                            '#dc3545'
                        ]
                    }
                ]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        }
    );
})();

function deleteReview(id) {
    if (confirm('Xóa đánh giá này?')) {
        fetch('review/process_delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `reviewId=${id}`
        }).then(() => location.reload());
    }
}
</script>

</body>
</html>