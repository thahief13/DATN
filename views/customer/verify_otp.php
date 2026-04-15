<?php
    session_start();

    $errorMessage = '';
    $currentEmail = $_SESSION['CurrentEmail'] ?? '';

    if (isset($_POST['verifyOtpButton'])) {
        $enteredOtp = trim($_POST['otp']);
        $sessionOtp = $_SESSION['OTPCode'] ?? null;
        $otpExpiry = $_SESSION['ExpiredTime'] ?? 0;

        if (empty($enteredOtp) || strlen($enteredOtp) !== 6 || !is_numeric($enteredOtp)) {
            $errorMessage = "Mã OTP không hợp lệ. Vui lòng nhập mã gồm 6 chữ số.";
        } 
        else if (time() > $otpExpiry) {
            $errorMessage = "Mã OTP đã hết hạn. Vui lòng quay lại trang Quên mật khẩu để yêu cầu mã mới.";
            unset($_SESSION['OTPCode']);
            unset($_SESSION['ExpiredTime']);
        } 
        else if ($enteredOtp != $sessionOtp) {
            $errorMessage = "Mã OTP không chính xác. Vui lòng kiểm tra lại.";
        } 
        else {
            unset($_SESSION['OTPCode']);
            unset($_SESSION['ExpiredTime']);

            $_SESSION['OTP_Verified'] = true;
            
            header('Location: reset_password.php');
            exit();
        }
    }

    $timeRemaining = isset($_SESSION['ExpiredTime']) ? ($_SESSION['ExpiredTime'] - time()) : 0;
?>

<?php include '../header.php'; ?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #fff1e0;
        color: #333;
    }

    .verify-container {
        margin-top: 180px;
        margin-bottom: 40px;
        display: flex;
        justify-content: center;
    }

    .verify-box {
        background: linear-gradient(135deg, #ffffff, #f8f9fa);
        padding: 30px;
        width: 100%;
        max-width: 400px;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .verify-box h2 {
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
        text-align: center;
        font-size: 1.2rem;
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
        border: none;
        cursor: pointer;
    }

    .btn-primary:hover {
        background-color: #ff9800;
        transform: translateY(-2px);
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

    .resend-link {
        display: block;
        text-align: center;
        margin-top: 15px;
        font-size: 0.9rem;
        color: #007bff;
        text-decoration: none;
    }

    .resend-link:hover {
        text-decoration: underline;
    }
</style>

<div class="container-fluid verify-container">
    <div class="verify-box">
        <h2>Xác minh mã OTP</h2>
        <p style='margin-bottom:20px; text-align: center; font-size: 0.95rem;'>
            Mã OTP đã được gửi tới email: <strong><?= htmlspecialchars($currentEmail) ?></strong>
        </p>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <?php 
            if (isset($_SESSION['ExpiredTime']) && $timeRemaining > 0): 
        ?>
            <p style="text-align: center; color: #5cb85c; font-weight: 600;">
                Mã có hiệu lực trong: <span id="timer"><?= $timeRemaining ?></span> giây.
            </p>
        <?php endif; ?>


        <form method="post" action="" id="verifyForm">
            <input type="text" name="otp" class="form-control" placeholder="Nhập mã OTP (6 chữ số)" required maxlength="6">
            <button type="submit" name="verifyOtpButton" class="btn-primary">Xác minh</button>
        </form>

        <a href="forgot_password.php" class="resend-link">Yêu cầu gửi lại mã OTP</a>
        
    </div>
</div>

<script>
    function startTimer(duration, display) {
        let timer = duration, minutes, seconds;
        let interval = setInterval(function () {
            minutes = parseInt(timer / 60, 10);
            seconds = parseInt(timer % 60, 10);

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            if (display) {
                display.textContent = minutes + ":" + seconds;
            }

            if (--timer < 0) {
                clearInterval(interval);
                const resendLink = document.querySelector('.resend-link');
                resendLink.innerHTML = "Mã đã hết hạn. Nhấn để yêu cầu mã mới.";
                if (display) {
                    display.textContent = "00:00";
                }
                
                document.querySelector('button[name="verifyOtpButton"]').disabled = true;
            }
        }, 1000);
    }

    window.onload = function () {
        const timeRemaining = <?= $timeRemaining ?>;
        const display = document.querySelector('#timer');
        if (timeRemaining > 0 && display) {
            startTimer(timeRemaining, display);
        }
    };
</script>

<?php include '../footer.php'; ?>