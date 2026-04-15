<?php
    session_start();
    if (!isset($_SESSION['CustomerId'])) {
        header('Location: sign_in.php'); 
        exit();
    }
    
    include '../../models/Customer.php';
    include '../../controllers/CustomerController.php';

    $customerId = $_SESSION['CustomerId'];
    $customerController = new CustomerController();
    $customer = $customerController->getCustomerById($customerId);
    
    if (!$customer) {
        session_destroy();
        header('Location: sign_in.php');
        exit();
    }

    $errorMessage = $_SESSION['ChangePasswordErrorMessage'] ?? '';
    $successMessage = $_SESSION['ChangePasswordSuccessMessage'] ?? '';

    unset($_SESSION['ChangePasswordErrorMessage']);
    unset($_SESSION['ChangePasswordSuccessMessage']);
    

    if (isset($_POST['changePasswordButton'])) {
        $oldPassword = $_POST['oldPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        
        if (!password_verify($oldPassword, $customer->Password)) {
            $errorMessage = "Mật khẩu cũ không chính xác.";
        } 
        else if (strlen($newPassword) < 6) {
            $errorMessage = "Mật khẩu mới phải chứa ít nhất 6 ký tự.";
        }
        else if ($newPassword !== $confirmPassword) {
            $errorMessage = "Mật khẩu mới và mật khẩu xác nhận không khớp.";
        } 
        else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $customerChange = new Customer();
            $customerChange->Id = $customerId;
            $customerChange->Password = $hashedPassword; 
            
            if ($customerController->changePassword($customerChange)) {
                
                $_SESSION['ChangePasswordSuccessMessage'] = "Đổi mật khẩu thành công!";
                header('Location: profile.php'); 
                exit();
            } else {
                
                $_SESSION['ChangePasswordErrorMessage'] = "Lỗi hệ thống: Không thể cập nhật mật khẩu.";
                header('Location: change_password.php');
                exit();
            }
        }
    }
?>

<?php include '../header.php';?>

<style>
  
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f7f7f7;
        color: #333;
    }

    
    .change-password-wrapper {
        display: flex;
        justify-content: center;
        align-items: center; 
        padding: 20px;
        margin-top: 50px; 
        margin-bottom: 50px;
        min-height: calc(100vh - 210px); 
    }

    
    .change-password-container {
        width: 100%; 
        max-width: 400px; 
        padding: 30px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); 
        transition: box-shadow 0.3s;
    }
    
    .change-password-container h3 {
        font-weight: 700;
        color: #37474f;
        margin-bottom: 25px;
    }

    
    .form-control {
        border-radius: 8px;
        padding: 12px; 
        border: 1px solid #e0e0e0;
        transition: border-color 0.3s, box-shadow 0.3s;
        width: 100%; 
    }

    .form-control:focus {
        border-color: #ffb300;
        box-shadow: 0 0 0 0.25rem rgba(255, 179, 0, 0.2);
        outline: none; 
    }

    
    .btn-primary-custom {
        background-color: #ffb300;
        border-color: #ffb300;
        width: 100%;
        padding: 14px; 
        border-radius: 8px;
        font-weight: 600;
        border: none;
        color: #fff; 
        cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
    }
    
    .btn-primary-custom:hover {
        background-color: #ff9800;
        transform: translateY(-1px);
    }
    
    
    .btn-secondary-custom {
        
        display: block; 
        text-align: center;
        text-decoration: none;
        
        background-color: #f8f9fa; 
        color: #6c757d; 
        border: 1px solid #dee2e6; 
        width: 100%;
        padding: 14px; 
        border-radius: 8px;
        font-weight: 600;
        margin-top: 15px; 
        transition: background-color 0.3s;
    }

    .btn-secondary-custom:hover {
        background-color: #e2e6ea;
    }

    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }

    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }

    
    @media (max-width: 576px) {
        .change-password-wrapper {
            padding: 0;
            margin-top: 0; 
            margin-bottom: 0;
            min-height: 100vh; 
            align-items: flex-start; 
            background-color: #fff; 
        }
        
        .change-password-container {
            padding: 20px;
            box-shadow: none; 
            border-radius: 0;
            max-width: 100%; 
        }
        
        .change-password-container h3 {
            font-size: 1.5rem;
            margin-top: 20px;
        }
        
        .form-control, .btn-primary-custom, .btn-secondary-custom {
            padding: 12px;
            font-size: 0.95rem;
        }
    }
</style>

<div class="change-password-wrapper">
    <div class="change-password-container">

        <h3 class="text-center mb-4">Đổi mật khẩu</h3>

        <?php if (!empty($successMessage)) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <form method="post" action="change_password.php">
            
            <div class="mb-3">
                <label for="oldPassword" class="form-label">Mật khẩu cũ</label>
                <input type="password" name="oldPassword" id="oldPassword" class="form-control" required autocomplete="off">
            </div>

            <div class="mb-3">
                <label for="newPassword" class="form-label">Mật khẩu mới</label>
                <input type="password" name="newPassword" id="newPassword" class="form-control" required minlength="6" autocomplete="new-password">
            </div>

            <div class="mb-4">
                <label for="confirmPassword" class="form-label">Xác nhận mật khẩu mới</label>
                <input type="password" name="confirmPassword" id="confirmPassword" class="form-control" required minlength="6" autocomplete="new-password">
            </div>

            <button type="submit" name="changePasswordButton" class="btn-primary-custom">
                Đổi mật khẩu
            </button>

            <a href="profile.php" class="btn-secondary-custom">Quay lại Hồ sơ</a>
        </form>

    </div>
</div>

<?php include '../footer.php';?>