<?php
    session_start();
    include '../../models/Customer.php';
    include '../../controllers/CustomerController.php';

    $errorMessage = '';
    $successMessage = '';
    $currentEmail = $_SESSION['CurrentEmail'] ?? '';
    $isVerified = $_SESSION['OTP_Verified'] ?? false;
    
    if (empty($currentEmail) || !$isVerified) {
        header('Location: forgot_password.php');
        exit();
    }

    if (isset($_POST['resetPasswordButton'])) {
        $newPassword = $_POST['newPassword'];
        $confirmPassword = $_POST['confirmPassword'];

        if (strlen($newPassword) < 6) {
            $errorMessage = "Mật khẩu phải chứa ít nhất 6 ký tự.";
        }
        else if ($newPassword !== $confirmPassword) {
            $errorMessage = "Mật khẩu mới và mật khẩu xác nhận không khớp.";
        } 
        else {
            $customerController = new CustomerController();
            $customerId = $customerController->getCustomerByEmail($currentEmail);

            if ($customerId > 0) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                $customer = new Customer();
                $customer->Id = $customerId;
                $customer->Password = $hashedPassword; 

                if ($customerController->changePassword($customer)) {
                    unset($_SESSION['CurrentEmail']);
                    unset($_SESSION['OTP_Verified']);
                    
                    $_SESSION['LoginSuccessMessage'] = "Đặt lại mật khẩu thành công. Vui lòng đăng nhập.";
                    header('Location: sign_in.php');
                    exit();
                } else {
                    $errorMessage = "Lỗi hệ thống: Không thể cập nhật mật khẩu.";
                }
            } else {
                $errorMessage = "Lỗi: Không tìm thấy tài khoản để đặt lại mật khẩu.";
            }
        }
    }
?>

<?php include '../header.php'; ?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #fff1e0;
        color: #333;
    }

    .reset-container {
        margin-top: 180px;
        margin-bottom: 40px;
        display: flex;
        justify-content: center;
    }

    .reset-box {
        background: linear-gradient(135deg, #ffffff, #f8f9fa);
        padding: 30px;
        width: 100%;
        max-width: 380px;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .reset-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
    }

    .reset-box h2 {
        text-align: center;
        font-weight: 700;
        margin-bottom: 20px;
        color: #343a40;
    }

    .form-control {
        border-radius: 10px;
        padding: 10px 12px;
        border: 1px solid #ced4da;
        width: 100%;
        margin-bottom: 15px;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .form-control:focus {
        border-color: #ffb300;
        box-shadow: 0 0 0 0.25rem rgba(255, 179, 0, 0.3);
    }

    .btn-primary {
        background-color: #ffb300;
        border-color: #ffb300;
        padding: 12px;
        width: 100%;
        border-radius: 30px;
        font-weight: 600;
        transition: all 0.3s;
        border: none;
    }

    .btn-primary:hover {
        background-color: #ff9800;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 152, 0, 0.35);
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
</style>

<div class="container-fluid reset-container">
    <div class="reset-box">
        <h2>Đặt lại mật khẩu</h2>
        <p style='margin-bottom:20px; text-align: center; font-size: 0.95rem;'>
            Đặt mật khẩu mới cho tài khoản: <strong><?= htmlspecialchars($currentEmail) ?></strong>
        </p>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" id="resetForm">
            <input type="password" name="newPassword" class="form-control" placeholder="Mật khẩu mới (ít nhất 6 ký tự)" required minlength="6">
            <input type="password" name="confirmPassword" class="form-control" placeholder="Nhập lại mật khẩu mới" required minlength="6">
            <button type="submit" name="resetPasswordButton" class="btn btn-primary">Xác nhận</button>
        </form>

        <div class="text-center mt-3">
            <a href="sign_in.php">Quay lại Đăng nhập</a>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>