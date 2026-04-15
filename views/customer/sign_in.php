<?php
    session_start();
    $signUpSuccessMessage = $_SESSION['SignUpSuccessMessage'] ?? '';
    $signInErrorMessage = $_SESSION['SignInErrorMessage'] ?? '';

    unset($_SESSION['SignUpSuccessMessage']);
    unset($_SESSION['SignInErrorMessage']);

    include '../../models/Customer.php';
    include '../../controllers/CustomerController.php';


    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    function printVar($var){
        if (isset($var)) echo $var;
    }

    if (isset($_POST['SignIn'])) {
        $customerController = new CustomerController();
        $customerId = $customerController->getCustomerByEmail($email);

        if (empty($customerId)) {
            $_SESSION['SignInErrorMessage'] = 'Không tìm thấy tài khoản với email này.';
            header('Location: sign_in.php');
            exit();
        }
        else{
            $customer = $customerController->getCustomerById($customerId);
            if (!password_verify($password, $customer->Password)) {
                $_SESSION['SignInErrorMessage'] = 'Mật khẩu không đúng.';
                header('Location: sign_in.php');
                exit();
            }
            else if (!$customer->IsActive){
                $_SESSION['temp_error'] = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.';
                header('Location: access_denied.php');
                exit();
            }
            else{
                $_SESSION['CustomerId'] = $customer->Id;
                $_SESSION['CustomerName'] = $customer->LastName . ' ' . $customer->FirstName;

                if ($customer->Role){
                    header('Location: ../../admin/views/index.php');
                    exit();
                }
                else{
                    header('Location: ../home/index.php');
                    exit();
                }
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

    .login-container {
        margin-top: 180px;
        margin-bottom: 30px;
        display: flex;
        justify-content: center;
    }

    .login-table {
        background: linear-gradient(135deg, #ffffff, #f8f9fa);
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        padding: 30px;
        width: 100%;
        max-width: 380px;
        margin: 0 auto;
        transition: transform 0.3s, box-shadow 0.3s;
    }


    .login-table:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
    }

    .login-table h2 {
        text-align: center;
        margin-bottom: 25px;
        font-weight: 700;
        color: #343a40;
    }

    .login-table table {
        width: 100%;
    }

    .login-table td {
        padding: 12px;
        vertical-align: middle;
    }

    .login-table label {
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

    .alert-success {
        background-color: #d4edda;
        color: #155724;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
    }

    .forgot-link {
        color: #ffb300;
        text-decoration: none;
    }

    .forgot-link:hover {
        color: #ff9800;
        text-decoration: underline;
    }

    .social-btn {
        border-radius: 50%;
        width: 45px;
        height: 45px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        margin: 0 5px;
        border-width: 2px;
        transition: transform 0.3s, border-color 0.3s;
    }

    .social-btn:hover {
        transform: scale(1.15);
    }

    .btn-outline-facebook {
        color: #3b5998;
        border-color: #3b5998;
    }

    .btn-outline-facebook:hover {
        background-color: #3b5998;
        color: white;
    }

    .btn-outline-google {
        color: #db4437;
        border-color: #db4437;
    }

    .btn-outline-google:hover {
        background-color: #db4437;
        color: white;
    }

    .btn-outline-twitter {
        color: #1da1f2;
        border-color: #1da1f2;
    }

    .btn-outline-twitter:hover {
        background-color: #1da1f2;
        color: white;
    }

    .btn-outline-github {
        color: #333;
        border-color: #333;
    }

    .btn-outline-github:hover {
        background-color: #333;
        color: white;
    }

    @media (max-width: 576px) {
        .login-table {
            max-width: 90%;
            padding: 20px;
        }
    }
</style>

<div class="container-fluid login-container">
    <div class="login-table">
        <h2>Đăng nhập</h2>

        <?php if (!empty($signUpSuccessMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <strong><?= htmlspecialchars($signUpSuccessMessage) ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <?php if (!empty($signInErrorMessage)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong><?= htmlspecialchars($signInErrorMessage) ?></strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <table>
                <tr>
                    <td><label for="email">Email</label></td>
                    <td><input type="email" name="email" id="email" class="form-control" required value="<?php printVar($email)?>"></td>
                </tr>
                <tr>
                    <td><label for="password">Mật khẩu</label></td>
                    <td><input type="password" name="password" id="password" class="form-control" required></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="rememberMe" id="rememberMe">
                                <label class="form-check-label" for="rememberMe">Ghi nhớ đăng nhập</label>
                            </div>
                            <a href="forgot_password.php" class="forgot-link">Quên mật khẩu?</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><button type="submit" name="SignIn" class="btn btn-primary mt-3">Đăng nhập</button></td>
                </tr>
            </table>

            <div class="text-center mt-3">
                Chưa có tài khoản? <a href="../customer/sign_up.php">Đăng ký</a>
            </div>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?>