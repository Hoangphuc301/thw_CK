<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$required_role = 'admin'; 

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $required_role) {

    echo "<!DOCTYPE html>
<html>
<head>
    <title>Lỗi Quyền Truy Cập</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; color: #343a40; text-align: center; padding-top: 50px; }
        .alert { 
            max-width: 600px; margin: 0 auto; padding: 20px; 
            border: 1px solid #ffc107; background-color: #fff3cd; 
            border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
        }
        h1 { color: #dc3545; }
        a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class='alert'>
        <h1>Lỗi Quyền Truy Cập</h1>
        <p>Bạn không có quyền truy cập vào trang quản trị này.</p>
        <p>Vui lòng đăng nhập bằng tài khoản Quản trị viên.</p>
        <p><a href='../index.php'>Quay lại Trang chủ</a></p>
    </div>
</body>
</html>";
    exit();
}
?>