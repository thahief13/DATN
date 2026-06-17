<?php
if (session_status() == PHP_SESSION_NONE) session_start();


require_once __DIR__ . '/../../../env.php';
global $hostname, $username, $password, $dbname, $port;
$dbAuth = new mysqli($hostname, $username, $password, $dbname, $port);
$dbAuth->set_charset("utf8mb4");
$authQuery = "SELECT Role, StoreId FROM customer WHERE Id = " . (int)($_SESSION['CustomerId'] ?? 0);
$authResult = $dbAuth->query($authQuery);
$authData = $authResult ? $authResult->fetch_assoc() : [];

$userRole = (int)($authData['Role'] ?? 1);
$userStoreId = (int)($authData['StoreId'] ?? 0);
$dbAuth->close();

$_SESSION['Role'] = $userRole;
$_SESSION['StoreId'] = $userStoreId;

require_once __DIR__ . '/../../controllers/RevenueAdminController.php';
require_once __DIR__ . '/../../controllers/StoreAdminController.php';

$storeController = new StoreAdminController();
$stores = $storeController->getAllStores();

$revenueController = new RevenueAdminController();

// Mặc định hiển thị từ ngày đầu tháng đến ngày hiện tại
$fromDate = isset($_GET['fromDate']) ? $_GET['fromDate'] : date('Y-m-01');
$toDate = isset($_GET['toDate']) ? $_GET['toDate'] : date('Y-m-t');

$filterStoreId = isset($_GET['store_id']) ? (int)$_GET['store_id'] : 0;

if ($userRole == 2 && $userStoreId > 0) {
    $filterStoreId = $userStoreId; 
}

$filterStoreName = "";
$managerStoreName = "Hệ thống Trung Nguyên";

foreach ($stores as $s) {
    if ($userRole == 2 && $s->Id == $userStoreId) {
        $managerStoreName = $s->StoreName; 
    }
    if ($filterStoreId > 0 && $s->Id == $filterStoreId) {
        $filterStoreName = $s->StoreName;
    }
}

$footerText = ($filterStoreId > 0 || $userRole == 2) ? "TỔNG CỘNG CHI NHÁNH" : "TỔNG CỘNG HỆ THỐNG";

// XÁC ĐỊNH LOẠI BIỂU ĐỒ (Tròn cho Admin tổng, Cột cho Chi nhánh)
$chartType = ($userRole == 1 && $filterStoreId == 0) ? 'pie' : 'bar';
$chartTitle = ($filterStoreId > 0) ? 'Biểu đồ: ' . htmlspecialchars($filterStoreName) : 'Tỷ trọng doanh thu các chi nhánh';

//  lọc dữ liệu theo thời gian ngày tháng năm
$rawRevenues = $revenueController->getRevenueByStore(0, 0, 0);
$revenues = [];
$chartDataMap = [];

$startTimestamp = strtotime($fromDate . ' 00:00:00');
$endTimestamp = strtotime($toDate . ' 23:59:59');

foreach ($rawRevenues as $r) {
    $rTime = strtotime($r->RevenueDate);
    
    
    if ($rTime < $startTimestamp || $rTime > $endTimestamp) {
        continue;
    }

   
    if ($filterStoreId > 0 && $r->StoreName !== $filterStoreName) {
        continue;
    }
    
    $revenues[] = $r;
    
    if (!isset($chartDataMap[$r->StoreName])) {
        $chartDataMap[$r->StoreName] = 0;
    }
    $chartDataMap[$r->StoreName] += $r->TotalRevenue;
}

$chartLabels = array_keys($chartDataMap);
$chartData = array_values($chartDataMap);
?>

<div class="container my-5">
    <h1 class="text-center mb-4">
        <i class="fas fa-money-bill-wave text-success"></i> Quản lý Doanh thu
        <?php if ($userRole == 2): ?>
            <br><span class="fs-4 text-primary fw-bold">- <?= htmlspecialchars($managerStoreName) ?> -</span>
        <?php endif; ?>
    </h1>

    <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="revenue">
                
                <div class="col-md-3">
                    <label class="form-label fw-bold text-secondary"> Chi nhánh</label>
                    <select name="store_id" class="form-select border-primary fw-bold" <?= ($userRole == 2) ? 'disabled style="background-color: #e9ecef;"' : '' ?>>
                        <?php if ($userRole != 2): ?>
                            <option value="0">Tất cả chi nhánh</option>
                        <?php endif; ?>
                        
                        <?php 
                        $hasMyStore = false;
                        foreach ($stores as $s): 
                            if ($userRole == 2 && $s->Id != $userStoreId) continue;
                            $hasMyStore = true;
                        ?>
                            <option value="<?= $s->Id ?>" <?= ($filterStoreId == $s->Id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s->StoreName) ?>
                            </option>
                        <?php endforeach; ?>
                        
                        <?php if ($userRole == 2 && !$hasMyStore): ?>
                            <option value="<?= $userStoreId ?>" selected>Chi nhánh của bạn (#<?= $userStoreId ?>)</option>
                        <?php endif; ?>
                    </select>
                    <?php if ($userRole == 2): ?>
                        <input type="hidden" name="store_id" value="<?= $userStoreId ?>">
                    <?php endif; ?>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Từ ngày</label>
                    <input type="date" name="fromDate" class="form-control border-primary" value="<?= htmlspecialchars($fromDate) ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Đến ngày</label>
                    <input type="date" name="toDate" class="form-control border-primary" value="<?= htmlspecialchars($toDate) ?>">
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 shadow-sm"><i class="fa fa-filter me-2"></i>Lọc báo cáo</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="<?= ($chartType == 'pie') ? 'fas fa-chart-pie' : 'fas fa-chart-bar' ?> me-2"></i>
                        <?= $chartTitle ?>
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-6 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 py-3"><h5 class="mb-0 fw-bold">Chi tiết theo ngày</h5></div>
                <div class="table-responsive px-3 pb-3">
                    <table class="table table-hover align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Ngày</th>
                                <th>Chi nhánh</th>
                                <th>Số đơn</th>
                                <th class="text-end">Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $grandTotal = 0;
                            if (empty($revenues)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">Không có dữ liệu doanh thu cho khoảng thời gian này.</td></tr>
                            <?php else: 
                                foreach ($revenues as $r): 
                                    $grandTotal += $r->TotalRevenue;
                            ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?= date('d/m/Y', strtotime($r->RevenueDate)) ?></span></td>
                                    <td class="fw-bold text-primary text-start"><?= htmlspecialchars($r->StoreName) ?></td>
                                    <td><span class="badge bg-info text-dark"><?= $r->OrderCount ?></span></td>
                                    <td class="text-end text-success fw-bold"><?= number_format($r->TotalRevenue, 0, ',', '.') ?> ₫</td>
                                </tr>
                            <?php 
                                endforeach; 
                            endif; 
                            ?>
                        </tbody>
                        <tfoot class="table-warning shadow-sm">
                            <tr class="fw-bold text-danger">
                                <td colspan=\"3\" class="text-start"><?= $footerText ?></td>
                                <td class="text-end fs-6"><?= number_format($grandTotal, 0, ',', '.') ?> ₫</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function() {
        const canvas = document.getElementById('revenueChart');
        if (!canvas) return;
        
        if (window.revenueChartInstance) {
            window.revenueChartInstance.destroy();
        }

        const ctx = canvas.getContext('2d');
        const chartType = '<?= $chartType ?>'; // Nhận loại biểu đồ từ PHP
        
        // Cấu hình màu sắc
        const bgColors = chartType === 'pie' 
            ? ['#0dcaf0', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14', '#20c997', '#0d6efd'] 
            : 'rgba(255, 179, 0, 0.7)';
        const borderColors = chartType === 'pie' ? '#ffffff' : '#ffb300';

        window.revenueChartInstance = new Chart(ctx, {
            type: chartType,
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: <?= json_encode($chartData) ?>,
                    backgroundColor: bgColors,
                    borderColor: borderColors,
                    borderWidth: chartType === 'pie' ? 1 : 2,
                    borderRadius: chartType === 'bar' ? 8 : 0,
                    maxBarThickness: chartType === 'bar' ? 60 : undefined
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: chartType === 'bar' ? { 
                    y: { beginAtZero: true, grid: { display: false } }, 
                    x: { grid: { display: false } } 
                } : {}, 
                plugins: { 
                    // Hiển thị chú thích nếu là biểu đồ tròn
                    legend: { 
                        display: chartType === 'pie', 
                        position: 'right' 
                    } 
                }
            }
        });
    })();
</script>