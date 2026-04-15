<?php
    session_start();
    include '../../models/Customer.php';
    include '../../controllers/CustomerController.php';
    
    $lastName = $_POST['LastName'] ?? '';
    $firstName = $_POST['FirstName'] ?? '';
    $email = $_POST['Email'] ?? '';
    $phone = $_POST['Phone'] ?? '';
    $address = $_POST['Address'] ?? '';
    $dateOfBirth = $_POST['DateOfBirth'] ?? ''; // Đã thêm biến này
    
    $UPLOAD_DIR = '../../img/KhachHang/';

    function printVar($var){
        if (isset($var)) echo $var;
    }

    function uploadImage($uploadDir, $identifier){
        if (!isset($_FILES['ImgUpload']) || $_FILES['ImgUpload']['error'] !== UPLOAD_ERR_OK) {
            return 'avatar-default.png';
        }

        $file = $_FILES['ImgUpload'];
        $temp_path = $file['tmp_name'];
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $folder_name = md5(trim($identifier));
        $target_folder = $uploadDir . $folder_name . '/';

        if (!is_dir($target_folder)) {
            if (!mkdir($target_folder, 0755, true)){
                error_log('Failed to create directory: ' . $target_folder);
                return false;
            }
        }

        $file_name = 'avatar_' . time() . '.' . $file_extension;
        $target_file = $target_folder . $file_name;

        if (move_uploaded_file($temp_path, $target_file)) {
            return $file_name;
        } else {
            error_log('Failed to move uploaded file to: ' . $target_file);
            return false;
        }
    }

   if (isset($_POST['SignUp'])){
        $customer = new Customer();
        $customerController = new CustomerController();

        $customer->LastName = $lastName;
        $customer->FirstName = $firstName;
        $customer->Email = trim($email);
        $customer->Phone = trim($phone);
        $customer->Address = $address;
        $customer->DateOfBirth = $dateOfBirth; // Gán dữ liệu thay vì 0000-00-00
        
        // Gán giá trị mặc định để tránh lỗi SQL hoặc lỗi bind_param
        $customer->ProvinceId = 0; 
        $customer->DistrictId = 0;
        $customer->WardCode = '';
        
        $customer->IsActive = 1;
        $customer->Password = password_hash($_POST['Password'], PASSWORD_DEFAULT);
        $confirmPass = $_POST['ConfirmPassword'] ?? ' ';
        $customer->RandomKey = '';
        
        $email_exists = $customerController->checkDuplicateByEmail($customer);

        $tempErrorMessage = '';
        if ($email_exists){
            $_SESSION['SignUpErrorMessage'] = 'Email đã tồn tại. Vui lòng sử dụng email khác.';
        }
        else{
            if(strlen($_POST['Password']) < 6){
                $_SESSION['SignUpErrorMessage'] = 'Mật khẩu phải đủ 6 kí tự.';
            }
            else{
                if (!password_verify($confirmPass, $customer->Password)){
                    $_SESSION['SignUpErrorMessage'] = 'Mật khẩu xác nhận không đúng.';
                }
                else{
                    $img_path = uploadImage($UPLOAD_DIR, $customer->Email);
                    if ($img_path === false){
                        $_SESSION['SignUpErrorMessage'] = 'Đăng ký thất bại do lỗi tải ảnh. Vui lòng thử lại.';
                    }
                    else{
                        $customer->Img = $img_path;
                        $isSuccess = $customerController->signUp($customer);
                        if ($isSuccess) {
                            $_SESSION['SignUpSuccessMessage'] = 'Đăng ký thành công!';
                            header('Location: sign_in.php');
                            exit();
                        } else {
                            $_SESSION['SignUpErrorMessage'] = 'Đăng ký thất bại. Vui lòng thử lại.';
                        }
                    }
                }
            }
        }
        $signUpErrorMessage = $_SESSION['SignUpErrorMessage'] ?? '';
        unset($_SESSION['SignUpErrorMessage']);
    }
?>

<?php include '../header.php'; ?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #fff1e0;
        color: #333;
    }

    .signup-container {
        margin-top: 180px;
        margin-bottom: 30px;
        display: flex;
        justify-content: center;
    }

    .signup-table {
        background: linear-gradient(135deg, #ffffff, #f8f9fa);
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        padding: 30px;
        width: 100%;
        max-width: 450px; /* Tăng nhẹ độ rộng để chứa thêm hàng */
        margin: 0 auto;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .signup-table:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
    }

    .signup-table h2 {
        text-align: center;
        margin-bottom: 25px;
        font-weight: 700;
        color: #343a40;
    }

    .signup-table table {
        width: 100%;
    }

    .signup-table td {
        padding: 12px;
        vertical-align: middle;
    }

    .signup-table label {
        font-weight: 600;
        color: #37474f;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #ced4da;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .form-control:focus {
        border-color: #ffb300;
        box-shadow: 0 0 0 0.25rem rgba(255, 179, 0, 0.25);
    }

    .btn-primary {
        background-color: #ffb300;
        border-color: #ffb300;
        border-radius: 30px;
        padding: 12px;
        font-weight: 600;
        width: 100%;
        transition: all 0.3s;
    }

    .btn-primary:hover {
        background-color: #ff9800;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
    }

    .alert {
        border-radius: 10px;
        margin-bottom: 15px;
        padding: 12px;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
    }

    #image_preview {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .hidden-file-input {
        display: none !important;
    }

    .avatar-container {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .btn-upload-custom {
        margin-top: 10px;
        background-color: #ffb300;
        padding: 8px 20px;
        border-radius: 25px;
        color: #fff;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn-upload-custom:hover {
        background-color: #ff9800;
        transform: translateY(-2px);
    }
</style>

<div class="container-fluid signup-container">
    <div class="signup-table">
        <h2>Đăng ký tài khoản</h2>

        <?php if (!empty($signUpErrorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <strong><?= htmlspecialchars($signUpErrorMessage) ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="post" action="sign_up.php" enctype="multipart/form-data">
            <div class="avatar-container mb-3">
                <img src="../../img/KhachHang/avatar-default.png" id="image_preview" onerror="this.src='https://via.placeholder.com/100'">
                <label for="image_upload" class="btn btn-upload-custom">Chọn ảnh đại diện</label>
                <input type="file" id="image_upload" name="ImgUpload" accept="image/*" class="hidden-file-input">
            </div>

            <table>
                <tr>
                    <td><label for="LastName">Họ</label></td>
                    <td><input type="text" name="LastName" id="LastName" class="form-control" placeholder="Nhập họ" required value="<?php printVar($lastName)?>"></td>
                </tr>
                <tr>
                    <td><label for="FirstName">Tên</label></td>
                    <td><input type="text" name="FirstName" id="FirstName" class="form-control" placeholder="Nhập tên" required value="<?php printVar($firstName)?>"></td>
                </tr>
                <tr>
                    <td><label for="DateOfBirth">Ngày sinh</label></td>
                    <td><input type="date" name="DateOfBirth" id="DateOfBirth" class="form-control" required value="<?php printVar($dateOfBirth)?>"></td>
                </tr>
                <tr>
                    <td><label for="Phone">Số điện thoại</label></td>
                    <td><input type="tel" name="Phone" id="Phone" class="form-control" placeholder="Nhập số điện thoại" required value="<?php printVar($phone)?>"></td>
                </tr>
                <tr>
                    <td><label for="Email">Email</label></td>
                    <td><input type="email" name="Email" id="Email" class="form-control" placeholder="Nhập email" required value="<?php printVar($email)?>"></td>
                </tr>
                <tr>
                    <td><label for="Password">Mật khẩu</label></td>
                    <td><input type="password" name="Password" id="Password" class="form-control" placeholder="Nhập mật khẩu" required></td>
                </tr>
                <tr>
                    <td><label for="ConfirmPassword">Nhập lại</label></td>
                    <td><input type="password" name="ConfirmPassword" id="ConfirmPassword" class="form-control" placeholder="Nhập lại mật khẩu" required></td>
                </tr>
                <tr>
                    <td><label for="Address">Địa chỉ</label></td>
                    <td><input type="text" name="Address" id="Address" class="form-control" placeholder="Nhập địa chỉ" required value="<?php printVar($address) ?>"></td>
                </tr>
                <tr>
                    <td colspan="2"><button type="submit" name="SignUp" class="btn btn-primary mt-3">Đăng ký</button></td>
                </tr>
            </table>

            <div class="text-center mt-3">
                Đã có tài khoản? <a href="sign_in.php">Đăng nhập</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById("image_upload").addEventListener("change", (event) => {
        const file = event.target.files[0];
        if (file) {
            const imagePreview = document.getElementById("image_preview");
            imagePreview.src = URL.createObjectURL(file);
            imagePreview.onload = () => URL.revokeObjectURL(imagePreview.src);
        }
    });
</script>

<?php include '../footer.php'; ?>