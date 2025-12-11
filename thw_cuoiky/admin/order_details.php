<?php
include "../db.php";
include "auth_check.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Lỗi: Không tìm thấy ID đơn hàng.");
}
$order_id = intval($_GET['id']);

$sql_order = "
    SELECT o.*, u.name AS user_name, u.email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
";
$stmt_order = $conn->prepare($sql_order);
$stmt_order->execute([$order_id]);
$order_info = $stmt_order->fetch(PDO::FETCH_ASSOC);
$stmt_order = null; 

if (!$order_info) {
    die("Lỗi: Đơn hàng không tồn tại.");
}

$sql_details = "
    SELECT od.*, p.name AS product_name, p.price, p.image 
    FROM order_details od
    JOIN products p ON od.product_id = p.id
    WHERE od.order_id = ?
";
$stmt_details = $conn->prepare($sql_details);
$stmt_details->execute([$order_id]);
$details_result = $stmt_details->fetchAll(PDO::FETCH_ASSOC);
$stmt_details = null;

$order_status = htmlspecialchars($order_info['status'] ?? 'Mới');
$status_class = 'status-' . strtolower(str_replace(' ', '-', $order_status));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Chi tiết đơn hàng #<?= $order_id ?></title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/product.css"> 
    <link rel="stylesheet" href="css/or_details.css"> 
</head>
<body>
<div class="admin-container">
    <div class="sidebar">
        <h3 style="text-align:center;color:white">ADMIN</h3>
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
            <h2>Chi tiết đơn hàng #<?= $order_id ?></h2>
            <a href="orders.php" class="btn-action btn-edit">Quay lại danh sách</a>
        </div>
        
        <div class="order-summary">
            <h3>Thông tin chung</h3>
            <div style="display: flex; gap: 40px;">
                <div>
                    <p><strong>Mã Đơn hàng:</strong> #<?= $order_id ?></p>
                    <p><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($order_info['order_date'])) ?></p>
                    <p><strong>Trạng thái:</strong> <span class="<?= $status_class ?>"><?= $order_status ?></span></p>
                    <p><strong>Tổng tiền:</strong> <strong style="color: #dc3545;"><?= number_format($order_info['total']) ?> VNĐ</strong></p>
                </div>
                <div>
                    <h3>Thông tin Khách hàng</h3>
                    <p><strong>Tên Khách hàng:</strong> <?= htmlspecialchars($order_info['user_name'] ?? 'Khách vãng lai') ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($order_info['email'] ?? 'Không có') ?></p>
                    <p><strong>User ID:</strong> <?= $order_info['user_id'] ?? 'NULL' ?></p>
                </div>
            </div>
        </div>

        <h3>Danh sách Sản phẩm đã mua</h3>
        <table class="order-details-table">
            <thead>
                <tr>
                    <th>Hình ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Giá bán</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $overall_total = 0;
            foreach ($details_result as $detail) {
                $unit_price = $detail['price'];
                $quantity = $detail['quantity'];
                $subtotal = $unit_price * $quantity;
                $overall_total += $subtotal;
                
                $image_src = "../admin/images/" . htmlspecialchars($detail['image']);
                $fallback_src = "../images/no-image.png";
            ?>
                <tr>
                    <td>
                        <img src='<?= $image_src ?>' 
                             onerror="this.onerror=null;this.src='<?= $fallback_src ?>';"
                             class="product-image"
                             alt="<?= htmlspecialchars($detail['product_name']) ?>" />
                    </td>
                    <td style="text-align:left;"><?= htmlspecialchars($detail['product_name']) ?></td>
                    <td><?= number_format($unit_price) ?> VNĐ</td> <td><?= $quantity ?></td>
                    <td><?= number_format($subtotal) ?> VNĐ</td> </tr>
            <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right; font-weight:bold;">TỔNG THANH TOÁN:</td>
                    <td style="text-align:center; font-weight:bold; color: #dc3545;"><?= number_format($order_info['total']) ?> VNĐ</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
</body>
</html>