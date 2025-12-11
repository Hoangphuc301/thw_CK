<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cartCount = count($_SESSION['cart']);

$userName = $_SESSION['user_name'] ?? '';
$userRole = $_SESSION['user_role'] ?? 'guest';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Shop giày của tôi</title>
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body>

<div class="menu">
    <div class="center-links">
        <div class="nav-links">
            <a href="index.php">Trang chủ</a>
            <a href="cart.php">Giỏ hàng (<?= $cartCount ?>)</a>

            <?php if ($userName): ?>
                <span class="welcome-text">
                    Xin chào, <?= htmlspecialchars($userName) ?>
                </span>

                <?php if ($userRole === 'admin'): ?>
                    <a href="admin/products.php" class="admin-link">Quản trị</a>
                <?php endif; ?>

                <a href="logout.php" class="logout-link">Đăng xuất</a>
            <?php else: ?>
                <a href="login.php">Đăng nhập</a>
                <a href="register.php">Đăng ký</a>     
            <?php endif; ?>
        </div>
    </div>
    <form action="search.php" method="GET" class="search-form">
        <div class="search-box">
            <input type="text" name="query" class="search-input" placeholder="Tìm kiếm sản phẩm..." required>
            <button type="submit" class="btn-search">
                🔍 
            </button>
        </div>
    </form>
</div>

<div class="container">
