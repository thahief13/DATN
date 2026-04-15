<?php
    session_start();
    include '../../models/Customer.php';
    include '../../controllers/CustomerController.php';

    require_once 'utils/send_otp.php';

    $forgotErrorMessage = $_SESSION['ForgotPasswordErrorMessage'] ?? '';
    $forgotSuccessMessage = $_SESSION['ForgotPasswordSuccessMessage'] ?? '';

    unset($_SESSION['ForgotPasswordErrorMessage']);
    unset($_SESSION['ForgotPasswordErrorButton']); 
    unset($_SESSION['ForgotPasswordSuccessMessage']);

    if(isset($_POST['forgotPasswordButton'])){
        $email = $_POST['Email'];
        $customerController = new CustomerController();
        if ($customerController->getCustomerByEmail($email)){
            $otp = rand(100000, 999999);
            $expired_time = time() + 300;
            if(sendOtpEmail($email, $otp)){
                $_SESSION['OTPCode'] = $otp;
                $_SESSION['ExpiredTime'] = $expired_time;
                $_SESSION['CurrentEmail'] = $email;
                $_SESSION['ForgotPasswordSuccessMessage'] = 'Mã OTP đã được gửi qua email của bạn.';
                unset($_SESSION['ForgotPasswordErrorMessage']);
                header('Location: verify_otp.php');
                exit();
            }
        }
        else $_SESSION['ForgotPasswordErrorMessage'] = "Tài khoản chưa đăng ký.";
    }
?>

<?php include '../header.php'; ?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #fff1e0;
        color: #333;
    }

    .forgot-container {
        margin-top: 180px;
        margin-bottom: 40px;
        display: flex;
        justify-content: center;
    }

    .forgot-box {
        background: linear-gradient(135deg, #ffffff, #f8f9fa);
        padding: 30px;
        width: 100%;
        max-width: 380px;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .forgot-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
    }

    .forgot-box h2 {
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

    .alert-success {
        background-color: #d4edda;
        color: #155724;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
    }
</style>

<div class="container-fluid forgot-container">
    <div class="forgot-box">
        <h2>Quên mật khẩu?</h2>
        <p style='margin-bottom:20px;'>Vui lòng nhập Email để lấy lại mật khẩu.</p>

        <?php if (!empty($forgotSuccessMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($forgotSuccessMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($forgotErrorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($forgotErrorMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="post" action="" id="forgotForm">
            <input type="email" name="Email" class="form-control" placeholder="Nhập email" required>
            <button type="submit" name="forgotPasswordButton" class="btn btn-primary">Xác nhận</button>
        </form>

        <div class="text-center mt-3">
            <a href="../customer/sign_in.php">Đăng nhập ngay</a>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>