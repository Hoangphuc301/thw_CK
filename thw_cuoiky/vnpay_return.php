<?php
include "db.php";
session_start();

$vnp_HashSecret = "TKADMMLF5K1NN6YA5FNBR65GBGGR816M";

$vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
unset($_GET['vnp_SecureHash']);

ksort($_GET);
$hashData = "";
foreach ($_GET as $key => $value) {
    if (strval($key) !== 'vnp_SecureHash') { 
        $hashData .= urlencode($key) . "=" . urlencode($value) . "&";
    }
}
$hashData = rtrim($hashData, "&");

$secureHash = hash_hmac("sha512", $hashData, $vnp_HashSecret);

$success = false;
$order_id = null;

if ($secureHash === $vnp_SecureHash && ($_GET['vnp_ResponseCode'] ?? '') == '00') {

    $conn->beginTransaction(); 

    try {
        $user_id = intval($_SESSION['user_id'] ?? 0);
        $total   = floatval($_SESSION['order_temp']['total'] ?? 0); 
        $today   = date('Y-m-d');
        $status  = 'Đã thanh toán';

        $stmt = $conn->prepare("INSERT INTO orders(user_id, order_date, total, status)
                                 VALUES(?, ?, ?, ?)");
        $stmt->execute([$user_id, $today, $total, $status]);
        
        $order_id = $conn->lastInsertId();
        $stmt = null; 

        $stmt_details = $conn->prepare("
            INSERT INTO order_details(order_id, product_id, quantity)
            VALUES (?, ?, ?)
        ");

        if ($order_id && !empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $it) {
                $pid = intval($it['id'] ?? 0);
                $qty = intval($it['qty'] ?? 0);
                
                $stmt_details->execute([$order_id, $pid, $qty]);
            }
        }
        $stmt_details = null; 

        $conn->commit();
        
        unset($_SESSION['cart']);
        unset($_SESSION['order_temp']);

        $success = true;

    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Lỗi xử lý đơn hàng/vnpay: " . $e->getMessage());
        $success = false;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kết quả thanh toán</title>
    <link rel="stylesheet" href="css/vnpay.css">
</head>
<body>

<div class="result-box">
    <?php if ($success): ?>
        <h2>Thanh toán thành công</h2>
        <p>Cảm ơn bạn đã đặt hàng tại hệ thống</p>

        <div class="order-id">
            Mã đơn hàng: <?= $order_id ?>
        </div>

        <a href="index.php" class="btn-home">Về trang chủ</a>

    <?php else: ?>
        <h2>Thanh toán thất bại</h2>
        <p>Giao dịch không thành công hoặc bị hủy</p>

        <a href="checkout.php" class="btn-home" style="background:#dc3545">
            Quay lại thanh toán
        </a>
    <?php endif; ?>
</div>

</body>
</html>