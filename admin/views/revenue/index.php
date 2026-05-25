<?php
require_once __DIR__ . '/../../controllers/RevenueAdminController.php';

$userRole = $_SESSION['Role'] ?? 1;
$footerText = ($userRole == 2) ? "TỔNG CỘNG CHI NHÁNH" : "TỔNG CỘNG HỆ THỐNG";

$revenueController = new RevenueAdminController();

$selectedDay = isset($_GET['day']) ? (int)$_GET['day'] : 0; 
$selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

$revenues = $revenueController->getRevenueByStore($selectedDay, $selectedMonth, $selectedYear);

$chartDataMap = [];
$managerStoreName = ""; // Biến chứa tên chi nhánh để hiển thị

foreach ($revenues as $r) {
    if (!isset($chartDataMap[$r->StoreName])) {
        $chartDataMap[$r->StoreName] = 0;
    }
    $chartDataMap[$r->StoreName] += $r->TotalRevenue;

    // Lấy chính xác tên chi nhánh của Quản lý từ dữ liệu
    if ($userRole == 2 && empty($managerStoreName)) {
        $managerStoreName = $r->StoreName;
    }
}

// Nếu tháng/ngày đó chưa có đơn hàng nào, dùng tên dự phòng
if ($userRole == 2 && empty($managerStoreName)) {
    $managerStoreName = "Chi nhánh của bạn";
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
                    <label class="form-label fw-bold">Ngày báo cáo</label>
                    <select name="day" class="form-select border-primary">
                        <option value="0">Tất cả ngày</option>
                        <?php for($i=1; $i<=31; $i++) echo "<option value='$i' ".($selectedDay==$i?'selected':'').">Ngày $i</option>"; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Tháng báo cáo</label>
                    <select name="month" class="form-select border-primary">
                        <?php for($i=1; $i<=12; $i++) echo "<option value='$i' ".($selectedMonth==$i?'selected':'').">Tháng $i</option>"; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Năm báo cáo</label>
                    <select name="year" class="form-select border-primary">
                        <?php for($i=date('Y')-2; $i<=date('Y'); $i++) echo "<option value='$i' ".($selectedYear==$i?'selected':'').">Năm $i</option>"; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 shadow-sm"><i class="fa fa-sync-alt me-2"></i>Cập nhật</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold text-primary">
                        <?= ($userRole == 2) ? '<i class="fas fa-store me-2"></i>Biểu đồ: ' . htmlspecialchars($managerStoreName) : '<i class="fas fa-chart-bar me-2"></i>Tổng doanh thu tháng theo chi nhánh' ?>
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
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 100px;">Ngày</th>
                                <th>Chi nhánh</th>
                                <th class="text-center" style="width: 90px;">Số đơn</th>
                                <th class="text-end" style="width: 130px;">Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $grandTotal = 0;
                            if (empty($revenues)): ?>
                                <tr><td colspan="4" class="text-center py-3 text-muted">Chưa có dữ liệu doanh thu cho thời gian này.</td></tr>
                            <?php else: 
                                foreach ($revenues as $r): 
                                    $grandTotal += $r->TotalRevenue;
                            ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?= date('d/m/Y', strtotime($r->RevenueDate)) ?></span></td>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($r->StoreName) ?></td>
                                    <td class="text-center"><span class="badge bg-info text-dark"><?= $r->OrderCount ?></span></td>
                                    <td class="text-end text-success fw-bold"><?= number_format($r->TotalRevenue, 0, ',', '.') ?> ₫</td>
                                </tr>
                            <?php 
                                endforeach; 
                            endif; 
                            ?>
                        </tbody>
                        <tfoot class="table-warning shadow-sm">
                            <tr class="fw-bold text-danger">
                                <td colspan="3"><?= $footerText ?></td>
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
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: <?= json_encode($chartData) ?>,
                backgroundColor: 'rgba(255, 179, 0, 0.7)',
                borderColor: '#ffb300',
                borderWidth: 2,
                borderRadius: 8,
                maxBarThickness: 60 
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { 
                y: { 
                    beginAtZero: true, 
                    grid: { display: false } 
                }, 
                x: { 
                    grid: { display: false } 
                } 
            },
            plugins: { 
                legend: { display: false } 
            }
        }
    });
</script>