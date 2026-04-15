<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// No header/footer - parent admin/index.php handles it

require_once __DIR__ . '/../../../admin/controllers/PaymentAdminController.php';
$paymentController = new PaymentAdminController();
$rawPayments = $paymentController->getAllPayments();

// Đảm bảo dữ liệu luôn là mảng trước khi encode ra Javascript
$payments = is_array($rawPayments) ? $rawPayments : [];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">
                    <i class="fas fa-file-invoice"></i> Quản lý đơn hàng
                    <span class="badge bg-primary ms-2"><?= count($payments) ?> đơn</span>
                </h2>
            </div>
        </div>

        <div class="row mb-4 g-3">
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="">Tất cả trạng thái</option>
                    <option value="đang xử lý">Đang xử lý</option>
                    <option value="đang giao">Đang giao</option>
                    <option value="đã giao">Đã giao</option>
                    <option value="hủy">Hủy</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" id="searchInput" placeholder="Tìm mã đơn, khách hàng...">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="storeFilter">
                    <option value="">Tất cả cửa hàng</option>
                    </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100" onclick="loadPayments()">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Cửa hàng</th>
                                <th>Tổng tiền</th>
                                <th>Thời gian</th>
                                <th>Trạng thái</th>
                                <th class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="paymentTableBody">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Cập nhật trạng thái</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Chọn trạng thái mới:</label>
                        <select class="form-select" id="newStatus">
                            <option value="đang xử lý">Đang xử lý</option>
                            <option value="đang giao">Đang giao</option>
                            <option value="đã giao">Đã giao</option>
                            <option value="hủy">Hủy</option>
                        </select>
                    </div>
                    <div id="statusNote" class="alert alert-danger mb-0">
                        <i class="fas fa-exclamation-triangle me-1"></i> Lưu ý: Hủy đơn sẽ không thể khôi phục!
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" onclick="updateStatus()">Cập nhật</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Lấy dữ liệu từ PHP
        let payments = <?= json_encode($payments) ?> || [];
        let currentPaymentId = 0;

        // 1. TỰ ĐỘNG ĐỔ DỮ LIỆU CỬA HÀNG VÀO BỘ LỌC
        function populateStoreFilter() {
            const storeSelect = document.getElementById('storeFilter');
            const storeMap = {};
            
            // Lấy danh sách ID và Tên cửa hàng duy nhất
            payments.forEach(p => {
                if (p.StoreId) {
                    storeMap[p.StoreId] = p.StoreName || `Cửa hàng #${p.StoreId}`;
                }
            });

            // Tạo các thẻ option
            for (const [storeId, storeName] of Object.entries(storeMap)) {
                const option = document.createElement('option');
                option.value = storeId;
                option.textContent = storeName;
                storeSelect.appendChild(option);
            }
        }

        // 2. RENDER BẢNG
        function renderPayments(filteredPayments) {
            const tbody = document.getElementById('paymentTableBody');
            tbody.innerHTML = '';

            if (filteredPayments.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center py-3 text-muted">Không tìm thấy đơn hàng nào!</td></tr>`;
                return;
            }

            filteredPayments.forEach(payment => {
                const statusBadge = getStatusBadge(payment.Status);
                const customerName = payment.CustomerName || payment.CustomerId || 'Khách vãng lai';
                const storeName = payment.StoreName || 'N/A';
                
                const row = `
                    <tr>
                        <td><strong>#${payment.Id}</strong></td>
                        <td>${customerName}</td>
                        <td>${storeName}</td>
                        <td class="fw-bold text-success">${formatCurrency(payment.Total)}</td>
                        <td>${formatDate(payment.CreatedAt)}</td>
                        <td>${statusBadge}</td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" title="Xem chi tiết" onclick="viewDetail(${payment.Id})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning" title="Cập nhật trạng thái" onclick="showStatusModal(${payment.Id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        }

        // 3. XỬ LÝ TRẠNG THÁI (BADGE)
        function getStatusBadge(status) {
            const safeStatus = (status || '').toLowerCase();
            const statusMap = {
                'pending': 'đang xử lý',
                'processing': 'đang xử lý',
                'đang xử lý': 'đang xử lý',
                'đang giao': 'đang giao', 
                'paid': 'đã giao',
                'delivered': 'đã giao',
                'đã giao': 'đã giao',
                'thành công': 'đã giao',
                'cancelled': 'hủy',
                'hủy': 'hủy',
                'đã hủy': 'hủy'
            };
            
            const vietStatus = statusMap[safeStatus] || safeStatus;
            
            const badges = {
                'đang xử lý': '<span class="badge bg-warning text-dark">Đang xử lý</span>',
                'đang giao': '<span class="badge bg-info text-dark">Đang giao</span>',
                'đã giao': '<span class="badge bg-success">Đã giao</span>',
                'hủy': '<span class="badge bg-danger">Hủy</span>'
            };
            
            return badges[vietStatus] || `<span class="badge bg-secondary">${vietStatus || 'N/A'}</span>`;
        }

        // 4. TIỆN ÍCH FORMAT
        function formatCurrency(amount) {
            const num = parseFloat(amount) || 0;
            return new Intl.NumberFormat('vi-VN').format(num) + ' ₫';
        }

        function formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            return new Date(dateStr).toLocaleString('vi-VN');
        }

        // 5. CHỨC NĂNG LỌC VÀ TÌM KIẾM
        function loadPayments() {
            let filtered = payments;
            const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
            const search = document.getElementById('searchInput').value.toLowerCase().trim();
            const store = document.getElementById('storeFilter').value;

            const statusMap = {
                'pending': 'đang xử lý',
                'processing': 'đang xử lý',
                'paid': 'đã giao',
                'delivered': 'đã giao',
                'thành công': 'đã giao',
                'cancelled': 'hủy',
                'đã hủy': 'hủy'
            };

            // Lọc theo trạng thái
            if (statusFilter) {
                filtered = filtered.filter(p => {
                    const rawStatus = (p.Status || '').toLowerCase();
                    const pStatus = statusMap[rawStatus] || rawStatus;
                    return pStatus === statusFilter;
                });
            }

            // Lọc theo từ khóa (Mã đơn hoặc Tên/Mã khách)
            if (search) {
                filtered = filtered.filter(p => {
                    const idMatch = `#${p.Id}`.toLowerCase().includes(search);
                    const customerMatch = (p.CustomerName || p.CustomerId || '').toString().toLowerCase().includes(search);
                    return idMatch || customerMatch;
                });
            }

            // Lọc theo cửa hàng
            if (store) {
                filtered = filtered.filter(p => p.StoreId == store);
            }

            renderPayments(filtered);
        }

        // 6. XỬ LÝ MODAL & CẬP NHẬT
        function showStatusModal(paymentId) {
            currentPaymentId = paymentId;
            const modal = new bootstrap.Modal(document.getElementById('statusModal'));
            modal.show();
        }

        function updateStatus() {
            const status = document.getElementById('newStatus').value;
            
            fetch('process_update_status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `paymentId=${currentPaymentId}&status=${encodeURIComponent(status)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Cập nhật thành công!');
                    location.reload(); // Hoặc cập nhật array payments tại local thay vì reload
                } else {
                    alert('❌ Lỗi: ' + (data.message || 'Không thể cập nhật'));
                }
            })
            .catch(error => {
                console.error("Lỗi cập nhật:", error);
                alert("❌ Đã có lỗi xảy ra trong quá trình cập nhật!");
            });
            
            bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
        }

        function viewDetail(paymentId) {
            window.location.href = `payment/detail.php?id=${paymentId}`;
        }




        // 7. KHỞI TẠO KHI LOAD TRANG
        document.addEventListener('DOMContentLoaded', () => {
            populateStoreFilter();
            loadPayments();
        });

        // Lắng nghe sự kiện để lọc realtime (không cần bấm nút tìm kiếm cũng được)
        document.getElementById('statusFilter').addEventListener('change', loadPayments);
        document.getElementById('storeFilter').addEventListener('change', loadPayments);
        document.getElementById('searchInput').addEventListener('keyup', (e) => {
            // Có thể dùng debounce nếu data quá lớn, ở đây lọc realtime
            loadPayments();
        });
    </script>
</body>
</html>