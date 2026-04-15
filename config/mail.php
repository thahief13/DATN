<?php
    return [
        'host' => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
        'port' => (int) (getenv('MAIL_PORT') ?: 465),
        'username' => getenv('MAIL_USERNAME') ?: 'kinxedo78@gmail.com',
        'password' => getenv('MAIL_PASSWORD') ?: 'orzc tvcb gezp acsc',
        'encryption' => strtolower(getenv('MAIL_ENCRYPTION') ?: 'ssl'),
        'from_email' => getenv('MAIL_FROM_ADDRESS') ?: 'trungnguyen.coffee@gmail.com',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'Trung Nguyen Coffee Support',
        'base_reset_url' => getenv('RESET_PASSWORD_BASE_URL')

            ?: 'http://localhost/oss_trung_nguyen_coffee/views/customer/reset_password.php',
    ];