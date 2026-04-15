<?php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Session đã được kiểm tra trong admin/views/index.php rồi, không cần kiểm tra lại
    require_once __DIR__ . '/../../../controllers/CustomerController.php';
    $customerController = new CustomerController();

    $customer = $customerController->getCustomerById($_SESSION['CustomerId']);
    $categoryAdmins = [];

    if ($customer && $customer->Role) {
        require_once __DIR__ . '/../../controllers/CategoryAdminController.php'; 

        $categoryAdminController = new CategoryAdminController();
        $categoryAdmins = $categoryAdminController->getAllCategories();
    }

    $categoriesJson = json_encode($categoryAdmins, JSON_UNESCAPED_UNICODE);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Quản lý danh mục</h4>
                </div>
                <div class="card-body">
                    <button class="btn btn-success btn-add mb-3" data-bs-toggle="modal" data-bs-target="#createModal">
                        <i class="fa fa-plus"></i> Thêm mới danh mục
                    </button>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Mã danh mục</th>
                                    <th>Tên danh mục</th>
                                    <th>Nội dung</th>
                                    <th>Ngày tạo</th>
                                    <th>Ngày cập nhật</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($categoryAdmins)): ?>
                                    <?php foreach($categoryAdmins as $category): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($category->Id); ?></td>
                                            <td><?php echo htmlspecialchars($category->Title); ?></td>
                                            <td><?php echo htmlspecialchars($category->Content); ?></td>
                                            <td><?php echo date('d/m/Y H:i:s', strtotime($category->CreateAt)); ?></td>
                                            <td><?php echo date('d/m/Y H:i:s', strtotime($category->UpdateAt)); ?></td>
                                            <td>
                                                <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal" data-bs-id="<?php echo $category->Id; ?>"><i class="fa fa-eye"></i> Chi tiết</button>
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" data-bs-id="<?php echo $category->Id; ?>"><i class="fa fa-edit"></i> Sửa</button>
                                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-bs-id="<?php echo $category->Id; ?>"><i class="fa fa-trash"></i> Xóa</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Không có danh mục nào được tìm thấy.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="/app/admin/views/category/process_create_category.php">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm danh mục mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="create-title">Tên danh mục</label>
                        <input type="text" class="form-control" id="create-title" name="title" placeholder="Nhập tên danh mục" required>
                    </div>
                    <div class="mb-3">
                        <label for="create-content">Nội dung</label>
                        <textarea class="form-control" id="create-content" name="content" placeholder="Nhập mô tả"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Thêm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="/app/admin/views/category/process_edit_category.php">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa danh mục</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="categoryId" id="edit-category-id">
                    <div class="mb-3">
                        <label for="edit-title">Tên danh mục</label>
                        <input type="text" class="form-control" id="edit-title" name="title">
                    </div>
                    <div class="mb-3">
                        <label for="edit-content">Nội dung</label>
                        <textarea class="form-control" id="edit-content" name="content"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chi tiết danh mục</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Mã danh mục:</strong> <span id="view-id"></span></p>
                    <p><strong>Tên danh mục:</strong> <span id="view-title"></span></p>
                    <p><strong>Nội dung:</strong> <span id="view-content"></span></p>
                    <p><strong>Ngày tạo:</strong> <span id="view-create-at"></span></p>
                    <p><strong>Ngày cập nhật:</strong> <span id="view-update-at"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Xóa danh mục</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn xóa danh mục **<span id="delete-category-title-display"></span>** (ID: <span id="delete-category-id-display"></span>) này không? Hành động này không thể hoàn tác.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <a id="confirmDeleteLink" class="btn btn-danger" href="#">Xóa</a>
                </div>
            </div>
        </div>
    </div>

    <textarea id="categoriesDataJson" style="display:none;"><?php echo htmlspecialchars($categoriesJson, ENT_QUOTES, 'UTF-8'); ?></textarea>

