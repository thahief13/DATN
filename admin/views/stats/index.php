<?php
$title = "Thống kê - Trung Nguyên Coffee";

// Giả lập dữ liệu
$stats = [
    "PaymentsPerMonth" => 120,
    "TotalProducts" => 55,
    "TotalCategories" => 12,
    "TotalCustomer" => 200,
    "MonthlyRevenue" => [
        ["Month" => "Tháng 1", "DoanhThu" => 50000000],
        ["Month" => "Tháng 2", "DoanhThu" => 60000000],
        ["Month" => "Tháng 3", "DoanhThu" => 45000000],
        ["Month" => "Tháng 4", "DoanhThu" => 70000000],
        ["Month" => "Tháng 5", "DoanhThu" => 80000000],
        ["Month" => "Tháng 6", "DoanhThu" => 75000000],
        ["Month" => "Tháng 7", "DoanhThu" => 82000000],
        ["Month" => "Tháng 8", "DoanhThu" => 90000000],
        ["Month" => "Tháng 9", "DoanhThu" => 95000000],
        ["Month" => "Tháng 10", "DoanhThu" => 100000000],
        ["Month" => "Tháng 11", "DoanhThu" => 105000000],
        ["Month" => "Tháng 12", "DoanhThu" => 110000000],
    ],
    "MonthlyOrders" => [
        ["Month" => "Tháng 1", "OrderCount" => 10],
        ["Month" => "Tháng 2", "OrderCount" => 15],
        ["Month" => "Tháng 3", "OrderCount" => 12],
        ["Month" => "Tháng 4", "OrderCount" => 18],
        ["Month" => "Tháng 5", "OrderCount" => 20],
        ["Month" => "Tháng 6", "OrderCount" => 22],
        ["Month" => "Tháng 7", "OrderCount" => 25],
        ["Month" => "Tháng 8", "OrderCount" => 28],
        ["Month" => "Tháng 9", "OrderCount" => 30],
        ["Month" => "Tháng 10", "OrderCount" => 32],
        ["Month" => "Tháng 11", "OrderCount" => 35],
        ["Month" => "Tháng 12", "OrderCount" => 40],
    ],
];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
</head>

<body style="background: #f8f9fa;">
    <div class="container my-5">
        <h2 class="mb-4"><?php echo $title; ?></h2>

        <!-- Tổng quan -->
        <div class="row mb-4">
            <?php
            $cards = [
                ["title" => "Số lượng hóa đơn", "value" => $stats["PaymentsPerMonth"], "color" => "bg-primary text-white"],
                ["title" => "Số lượng sản phẩm", "value" => $stats["TotalProducts"], "color" => "bg-info text-white"],
                ["title" => "Số lượng danh mục", "value" => $stats["TotalCategories"], "color" => "bg-warning text-dark"],
                ["title" => "Số lượng khách hàng", "value" => $stats["TotalCustomer"], "color" => "bg-success text-white"],
            ];
            foreach ($cards as $card):
            ?>
                <div class="col-md-3 mb-3">
                    <div class="card <?php echo $card["color"]; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $card["title"]; ?></h5>
                            <p class="card-text"><?php echo $card["value"]; ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Biểu đồ doanh thu -->
        <h4>Doanh thu theo tháng (triệu VND)</h4>
        <div style="overflow-x:auto;">
            <canvas id="revenueChart" style="min-width:800px; height:400px;"></canvas>
        </div>

        <!-- Biểu đồ số lượng hóa đơn -->
        <h4 class="mt-5">Số lượng hóa đơn theo tháng</h4>
        <div style="overflow-x:auto;">
            <canvas id="orderChart" style="min-width:800px; height:400px;"></canvas>
        </div>
    </div>

    <script>
        const revenueData = <?php echo json_encode($stats["MonthlyRevenue"]); ?>;
        const revenueLabels = revenueData.map(r => r.Month);
        const revenue = revenueData.map(r => r.DoanhThu / 1000000);

        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Doanh thu (triệu VND)',
                    data: revenue,
                    backgroundColor: 'green',
                    borderColor: 'darkgreen',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        color: 'white',
                        font: {
                            weight: 'bold',
                            size: 12
                        },
                        formatter: v => v.toLocaleString() + ' triệu'
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Tháng'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Doanh thu (triệu VND)'
                        },
                        beginAtZero: true
                    }
                }
            }
        });

        const orderData = <?php echo json_encode($stats["MonthlyOrders"]); ?>;
        const orderLabels = orderData.map(r => r.Month);
        const orderCounts = orderData.map(r => r.OrderCount);

        const orderCtx = document.getElementById('orderChart').getContext('2d');
        new Chart(orderCtx, {
            type: 'bar',
            data: {
                labels: orderLabels,
                datasets: [{
                    label: 'Số lượng hóa đơn',
                    data: orderCounts,
                    backgroundColor: 'blue',
                    borderColor: 'darkblue',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        color: 'white',
                        font: {
                            weight: 'bold',
                            size: 12
                        },
                        formatter: v => v.toLocaleString()
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Tháng'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Số lượng hóa đơn'
                        },
                        beginAtZero: true,
                        stepSize: 1
                    }
                }
            }
        });
    </script>
</body>

</html>