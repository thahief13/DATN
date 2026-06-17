<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// TRÁNH LỖI BỘ NHỚ TẠM (SESSION)
require_once __DIR__ . '/../../../controllers/CustomerController.php';
$customerCtrl = new CustomerController();
$loggedInUser = $customerCtrl->getCustomerById($_SESSION['CustomerId'] ?? 0);

$userRole = $loggedInUser->Role ?? 1;
$userStoreId = $loggedInUser->StoreId ?? 0;

//p nhật lại Session cho đồng bộ
$_SESSION['Role'] = $userRole;
$_SESSION['StoreId'] = $userStoreId;


require_once __DIR__ . '/../../../admin/controllers/PaymentAdminController.php';
$paymentController = new PaymentAdminController();
$rawPayments = $paymentController->getAllPayments();


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
    <style>
        .pagination .page-link { cursor: pointer; }
        .table-hover tbody tr:hover { background-color: rgba(0,0,0,.03); }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">
                    <i class="fas fa-file-invoice"></i> Quản lý đơn hàng
                    <span class="badge bg-primary ms-2" id="totalOrdersCount"><?= count($payments) ?> đơn</span>
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
            <div class="card-footer bg-white border-0 py-3">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mb-0" id="pagination">
                        </ul>
                </nav>
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
                    <div class="alert alert-danger mb-0">
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
        // phân trang
        var allPayments = <?= json_encode($payments) ?> || [];
        var filteredData = [];
        var currentPage = 1;
        var rowsPerPage = 10;
        var currentPaymentId = 0;
        var userRole = <?= $userRole ?>;
        var userStoreId = <?= $userStoreId ?>;

        function populateStoreFilter() {
            const storeSelect = document.getElementById('storeFilter');
            if (!storeSelect) return;
            storeSelect.innerHTML = '<option value="">Tất cả cửa hàng</option>';
            
            const storeMap = {};
            allPayments.forEach(p => {
                if (p.StoreId) storeMap[p.StoreId] = p.StoreName || `Cửa hàng #${p.StoreId}`;
            });

            for (const [storeId, storeName] of Object.entries(storeMap)) {
                const option = document.createElement('option');
                option.value = storeId;
                option.textContent = storeName;
                storeSelect.appendChild(option);
            }
            if (userRole == 2 && userStoreId > 0) {
                storeSelect.value = userStoreId;
                storeSelect.disabled = true;
            }
        }

        function renderPayments(data) {
            const tbody = document.getElementById('paymentTableBody');
            if (!tbody) return;
            
            tbody.innerHTML = '';
            filteredData = data;
            document.getElementById('totalOrdersCount').innerText = `${data.length} đơn`;

            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center py-3 text-muted">Không tìm thấy đơn hàng nào!</td></tr>`;
                document.getElementById('pagination').innerHTML = '';
                return;
            }

            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const paginatedItems = data.slice(start, end);

            paginatedItems.forEach(payment => {
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

            renderPagination(data.length);
        }

        function renderPagination(totalRows) {
            const paginationUl = document.getElementById('pagination');
            const totalPages = Math.ceil(totalRows / rowsPerPage);
            paginationUl.innerHTML = '';

            if (totalPages <= 1) return;

            paginationUl.innerHTML += `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" onclick="changePage(${currentPage - 1})">Trước</a>
                </li>
            `;

            for (let i = 1; i <= totalPages; i++) {
                paginationUl.innerHTML += `
                    <li class="page-item ${currentPage === i ? 'active' : ''}">
                        <a class="page-link" onclick="changePage(${i})">${i}</a>
                    </li>
                `;
            }

            paginationUl.innerHTML += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" onclick="changePage(${currentPage + 1})">Sau</a>
                </li>
            `;
        }

        function changePage(page) {
            currentPage = page;
            renderPayments(filteredData);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function getStatusBadge(status) {
            const safeStatus = (status || '').toLowerCase();
            const statusMap = {
                'pending': 'đang xử lý', 'processing': 'đang xử lý', 'đang xử lý': 'đang xử lý',
                'đang giao': 'đang giao',
                'paid': 'đã giao', 'delivered': 'đã giao', 'đã giao': 'đã giao', 'thành công': 'đã giao',
                'cancelled': 'hủy', 'hủy': 'hủy', 'đã hủy': 'hủy'
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

        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount || 0) + ' ₫';
        }

        function formatDate(dateStr) {
            return dateStr ? new Date(dateStr).toLocaleString('vi-VN') : 'N/A';
        }

        function loadPayments() {
            currentPage = 1; 
            const status = document.getElementById('statusFilter').value.toLowerCase();
            const search = document.getElementById('searchInput').value.toLowerCase().trim();
            const store = document.getElementById('storeFilter').value;

            const statusMap = {
                'pending': 'đang xử lý', 'processing': 'đang xử lý',
                'paid': 'đã giao', 'delivered': 'đã giao', 'thành công': 'đã giao',
                'cancelled': 'hủy', 'đã hủy': 'hủy'
            };

            let filtered = allPayments.filter(p => {
                const pStatusRaw = (p.Status || '').toLowerCase();
                const pStatus = statusMap[pStatusRaw] || pStatusRaw;
                const statusMatch = !status || pStatus === status;
                
                const idMatch = `#${p.Id}`.toLowerCase().includes(search);
                const customerMatch = (p.CustomerName || p.CustomerId || '').toString().toLowerCase().includes(search);
                const searchMatch = !search || idMatch || customerMatch;
                
                const storeMatch = !store || p.StoreId == store;

                return statusMatch && searchMatch && storeMatch;
            });

            renderPayments(filtered);
        }

        function showStatusModal(paymentId) {
            currentPaymentId = paymentId;
            new bootstrap.Modal(document.getElementById('statusModal')).show();
        }

        function updateStatus() {
            const status = document.getElementById('newStatus').value;
            fetch('payment/process_update_status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `paymentId=${currentPaymentId}&status=${encodeURIComponent(status)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(' Cập nhật thành công!');
                    location.reload(); 
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể cập nhật'));
                }
            })
            .catch(() => alert(" Lỗi kết nối!"));
        }

        function viewDetail(paymentId) {
            window.location.href = `payment/detail.php?id=${paymentId}`;
        }

        setTimeout(() => {
            populateStoreFilter();
            renderPayments(allPayments);
        }, 50);
        
    </script>
    <div id="adminToastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 99999; display: flex; flex-direction: column; gap: 10px;"></div>

<style>
    .admin-toast {
        background-color: #fff;
        color: #333;
        border-left: 5px solid #0dcaf0; 
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        width: 330px;
        transform: translateX(120%);
        transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55), opacity 0.4s ease;
        opacity: 0;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .admin-toast.show { transform: translateX(0); opacity: 1; }
    .admin-toast i { font-size: 24px; margin-top: 2px; }
    
    .admin-toast.canceled { border-left-color: #dc3545; }
    .admin-toast.canceled i { color: #dc3545; }
    
    .admin-toast.new { border-left-color: #198754; }
    .admin-toast.new i { color: #198754; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // thông báo
    const adminCheckUrl = 'http://<?php echo $_SERVER["HTTP_HOST"]; ?>/app/admin/views/check_new_orders.php';

    function fetchAdminOrders() {
        fetch(adminCheckUrl) 
        .then(response => response.json())
        .then(data => {
            // Hiển thị lỗi ra Console nếu bị sai quyền hoặc sai đường dẫn
            if (!data.success && data.message) {
                console.warn('Cảnh báo từ radar Admin:', data.message);
                return;
            }

            if (data.success && data.data && data.data.length > 0) {
                data.data.forEach(noti => {
                    showAdminNotification(noti.Message, noti.Type);
                });
                
                
                setTimeout(() => location.reload(), 3000); 
            }
        })
        .catch(err => console.log('Radar Admin đang chạy ngầm...'));
    }

   
    fetchAdminOrders();

   
    setInterval(fetchAdminOrders, 4000); 

    function showAdminNotification(message, type) {
        const container = document.getElementById('adminToastContainer');
        if (!container) return; 

        const toast = document.createElement('div');
        toast.className = `admin-toast ${type}`;
        
        let iconClass = type === 'cancel' ? 'fa-ban' : 'fa-shopping-bag';
        let title = type === 'cancel' ? 'Cảnh báo Hủy Đơn' : 'Ting Ting! Đơn hàng mới';

        toast.innerHTML = `
            <i class="fas ${iconClass}"></i>
            <div>
                <strong style="font-size: 15px;">${title}</strong>
                <p style="margin: 5px 0 0; font-size: 14px; color: #555;">${message}</p>
            </div>
        `;
        container.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 100);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 500); 
        }, 7000); 
    }
});
</script>
</body>
</html>