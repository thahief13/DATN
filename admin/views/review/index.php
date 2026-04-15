<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đánh giá sản phẩm</title>
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
        <h1>Quản lý đánh giá sản phẩm</h1>

        <div class="table-wrapper">

            <!-- Lọc theo sản phẩm -->
            <div class="filter-section d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex">
                    <select class="form-select me-2">
                        <option selected>Chọn sản phẩm</option>
                        <option value="1">Cà phê Arabica</option>
                        <option value="2">Cà phê Robusta</option>
                        <option value="3">Cà phê Blend</option>
                    </select>
                    <button class="btn btn-primary">Lọc</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Sản phẩm</th>
                            <th>Khách hàng</th>
                            <th>Đánh giá</th>
                            <th>Bình luận</th>
                            <th>Ngày tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dữ liệu mẫu -->
                        <tr>
                            <td>1</td>
                            <td>Cà phê Arabica</td>
                            <td>Nguyễn Văn A</td>
                            <td>5</td>
                            <td>Rất thơm và ngon!</td>
                            <td>2025-12-01 08:00</td>
                            <td>
                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="fa fa-trash"></i>Xóa
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Cà phê Robusta</td>
                            <td>Trần Thị B</td>
                            <td>4</td>
                            <td>Hương vị đậm đà, vừa miệng.</td>
                            <td>2025-12-01 09:15</td>
                            <td>
                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="fa fa-trash"></i>Xóa
                                </button>
                            </td>
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
    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Xóa đánh giá</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn xóa đánh giá này không?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger">Xóa</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>