<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý doanh thu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #343a40;
            font-weight: 700;
        }

        .table-wrapper {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .table thead {
            background-color: #343a40;
            color: #fff;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
        }

        .btn i {
            margin-right: 5px;
        }

        .filter-section {
            margin-bottom: 15px;
        }

        .pagination .btn {
            min-width: 40px;
        }

        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>
    <div class="container my-5">
        <h1>Quản lý doanh thu</h1>

        <div class="table-wrapper">

            <!-- Lọc theo cửa hàng -->
            <div class="filter-section d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex">
                    <select class="form-select me-2">
                        <option selected>Chọn cửa hàng</option>
                        <option value="1">Cửa hàng Trung Nguyên 1</option>
                        <option value="2">Cửa hàng Trung Nguyên 2</option>
                        <option value="3">Cửa hàng Trung Nguyên 3</option>
                    </select>
                    <button class="btn btn-primary">Lọc</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Cửa hàng</th>
                            <th>Tháng</th>
                            <th>Năm</th>
                            <th>Tổng doanh thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dữ liệu mẫu -->
                        <tr>
                            <td>1</td>
                            <td>Cửa hàng Trung Nguyên 1</td>
                            <td>11</td>
                            <td>2025</td>
                            <td>5,200,000 VND</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Cửa hàng Trung Nguyên 2</td>
                            <td>11</td>
                            <td>2025</td>
                            <td>3,800,000 VND</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            <div class="d-flex justify-content-center mt-4">
                <button class="btn btn-outline-dark mx-1">&laquo;</button>
                <button class="btn btn-outline-dark active mx-1">1</button>
                <button class="btn btn-outline-dark mx-1">2</button>
                <button class="btn btn-outline-dark mx-1">3</button>
                <button class="btn btn-outline-dark mx-1">&raquo;</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>