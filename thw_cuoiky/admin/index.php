<?php
include "auth_check.php";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang Quản Trị</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="admin-container">
    <div class="sidebar">
        <div class="admin-logo">
            <h3>ADMIN PANEL</h3>
        </div>
        <ul class="menu">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="products.php">Quản lý sản phẩm</a></li>
            <li><a href="orders.php">Quản lý đơn hàng</a></li>
            <li><a href="users.php">Quản lý người dùng</a></li>
            <li><a href="../index.php">Về trang bán hàng</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="admin-header">
            <h2>Dashboard</h2>
            <div class="admin-user">
                <span>Xin chào, Admin</span>
                <a href="../index.php">Đăng xuất</a>
            </div>
        </div>

        <div class="dashboard">
            <?php
            include "db.php";
            
            function get_single_value($conn, $sql) {
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                return $stmt->fetchColumn();
            }

            $r1 = get_single_value($conn, "SELECT COUNT(*) FROM products");

            $r2 = get_single_value($conn, "SELECT COUNT(*) FROM orders");

            $r3 = get_single_value($conn, "SELECT COUNT(*) FROM users");

            $r4 = get_single_value($conn, "SELECT IFNULL(SUM(total), 0) FROM orders");
            ?>
            <div class="card"><h3>👟 Sản phẩm</h3><p><?= htmlspecialchars($r1) ?></p></div>
            <div class="card"><h3>📦 Đơn hàng</h3><p><?= htmlspecialchars($r2) ?></p></div>
            <div class="card"><h3>👤 Người dùng</h3><p><?= htmlspecialchars($r3) ?></p></div>
            <div class="card"><h3>💰 Doanh thu</h3><p><?= number_format($r4) ?> VNĐ</p></div>
        </div>

        <div class="admin-page-content">
            <h3>Chào mừng bạn đến trang quản trị</h3>
        </div>
    </div>
</div>
</body>
</html>