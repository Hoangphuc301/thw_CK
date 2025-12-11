<?php
include "header.php";
include "db.php"; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout.php");
    exit();
}

if (empty($_SESSION['cart'])) {
    echo "<p style='text-align:center'>Giỏ hàng trống. <a href='index.php'>Quay lại trang chủ</a></p>";
    include "footer.php";
    exit();
}

$total = 0;
foreach ($_SESSION['cart'] as $it) {
    $total += $it['price'] * $it['qty'];
}

$vnp_TmnCode   = "XMQPKUG8";
$vnp_HashSecret = "TKADMMLF5K1NN6YA5FNBR65GBGGR816M";
$vnp_Url       = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
$vnp_Returnurl = "http://localhost/thw_cuoiky/vnpay_return.php"; 

$error_message = '';

if (isset($_POST['pay_vnpay'])) {

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (preg_match('~[0-9]~', $name)) {
        $error_message = "Họ tên người nhận không được phép chứa số!";
    } elseif (!ctype_digit($phone)) {
        $error_message = "Số điện thoại chỉ được nhập chữ số.";
    } elseif (strlen($phone) < 9 || strlen($phone) > 11) {
        $error_message = "Số điện thoại phải có từ 9 đến 11 chữ số.";
    } else {
        $_SESSION['order_temp'] = [
            'name'    => $name,
            'phone'   => $phone,
            'address' => $address,
            'total'   => $total 
        ];

        $vnp_TxnRef = rand(100000, 999999);
        $vnp_OrderInfo = "Thanh toan don hang [" . $vnp_TxnRef . "]";
        $vnp_OrderType = "billpayment";
        $vnp_Amount = intval($total) * 100;
        $vnp_Locale = "vn";
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
        $vnp_CreateDate = date('YmdHis');

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => $vnp_CreateDate,
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef
        ];

        ksort($inputData);
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            $hashdata .= urlencode($key) . "=" . urlencode($value) . "&";
        }
        $hashdata = rtrim($hashdata, "&");

        $vnpSecureHash = hash_hmac("sha512", $hashdata, $vnp_HashSecret);
        
        $vnp_Url .= "?" . http_build_query($inputData);
        $vnp_Url .= "&vnp_SecureHash=" . $vnpSecureHash;

        header("Location: " . $vnp_Url);
        exit();
    }
} else {
    $name = $_SESSION['order_temp']['name'] ?? $_SESSION['user_name'] ?? '';
    $phone = $_SESSION['order_temp']['phone'] ?? '';
    $address = $_SESSION['order_temp']['address'] ?? '';
}

$name_value = htmlspecialchars($name);
$phone_value = htmlspecialchars($phone);
$address_value = htmlspecialchars($address);

?>

<h2>Đặt hàng</h2>

<div class="login-container">
<form method="post">
    <?php if ($error_message): ?>
        <p style="color:red; text-align:center; padding: 10px; border: 1px solid red; border-radius: 5px;">
            <?= htmlspecialchars($error_message) ?>
        </p>
    <?php endif; ?>

    <input type="text" name="name" placeholder="Họ tên người nhận" required 
        value="<?= $name_value ?>"
        onkeypress="return blockNumbers(event)">
        
    <input type="text" name="phone" placeholder="Số điện thoại" required 
        value="<?= $phone_value ?>"
        onkeypress="return blockChars(event)">
        
    <input type="text" name="address" placeholder="Địa chỉ giao hàng" required 
        value="<?= $address_value ?>">

    <h3>Sản phẩm</h3>
    <ul class="checkout-list" style="list-style:none;padding:0">
        <?php foreach ($_SESSION['cart'] as $it): ?>
        <li style="display:flex;gap:10px;padding:8px 0">
            <img src="admin/images/<?= htmlspecialchars($it['img']) ?>" width="60">
            <span style="flex:1"><?= htmlspecialchars($it['name']) ?> (SL: <?= $it['qty'] ?>)</span>
            <b><?= number_format($it['price'] * $it['qty'], 0, ',', '.') ?> VNĐ</b>
        </li>
        <?php endforeach; ?>
    </ul>

    <p style="text-align:right"><b>Tổng tiền: <?= number_format($total, 0, ',', '.') ?> VNĐ</b></p>

    <div class="cart-actions">
        <a href="cart.php" class="btn-back">Quay lại</a>
        <button type="submit" name="pay_vnpay" class="btn-order" style="background:#0057e7">
            Thanh toán VNPAY
        </button>
    </div>
</form>
</div>

<script>
function blockNumbers(event) {
    const charCode = (event.which) ? event.which : event.keyCode;
    if (charCode >= 48 && charCode <= 57) {
        return false;
    }
    return true;
}

function blockChars(event) {
    const charCode = (event.which) ? event.which : event.keyCode;
    
    if (charCode === 0 || charCode === 8 || charCode === 9 || charCode === 13) {
        return true;
    }
    
    if (charCode >= 48 && charCode <= 57) {
        return true;
    }
    return false;
}
</script>

<?php include "footer.php"; ?>