<?php
include "header.php"; 
?>

<h2>Giỏ hàng</h2>

<?php
if (isset($_GET['delete'])) {
    $idx = intval($_GET['delete']);
    if (isset($_SESSION['cart'][$idx])) {
        unset($_SESSION['cart'][$idx]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
    header("Location: cart.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    foreach ($_POST['qty'] as $i => $q) {
        $q = max(1, intval($q));
        if (isset($_SESSION['cart'][$i])) {
            $_SESSION['cart'][$i]['qty'] = $q;
        }
    }
    header("Location: cart.php");
    exit();
}
?>

<?php if (empty($_SESSION['cart'])) { ?>
    <p style="text-align:center;">Giỏ hàng trống</p>
    <div style="text-align:center;margin-top:10px;">
        <a href="index.php" class="btn-back">Quay lại trang chủ</a>
    </div>
<?php } else { ?>

<form method="post">
<table>
    <tr>
        <th>Ảnh</th>
        <th>Tên</th>
        <th>Giá</th>
        <th>Số lượng</th>
        <th>Tổng</th>
        <th>Hành động</th>
    </tr>

    <?php 
    $total = 0;
    foreach ($_SESSION['cart'] as $i => $item) { 
        $line = $item['price'] * $item['qty'];
        $total += $line;
    ?>
    <tr>
        <td>
            <img src="admin/images/<?= urlencode(htmlspecialchars($item['img'])) ?>" width="80">
        </td>
        
        <td><?= htmlspecialchars($item['name']) ?></td>
        <td><?= number_format($item['price'], 0, ',', '.') ?> VNĐ</td> 

        <td>
            <input type="number" 
                    name="qty[<?= $i ?>]" 
                    value="<?= $item['qty'] ?>" 
                    min="1" 
                    style="width:60px">
        </td>

        <td><?= number_format($line, 0, ',', '.') ?> VNĐ</td>

        <td>
            <a class="btn-delete" 
                href="cart.php?delete=<?= $i ?>" 
                onclick="return confirm('Bạn có muốn xóa sản phẩm này không?')">✖</a>
        </td>
    </tr>
    <?php } ?>

    <tr>
        <td colspan="4"><b>Tổng tiền thanh toán</b></td>
        <td colspan="2"><b><?= number_format($total, 0, ',', '.') ?> VNĐ</b></td>
    </tr>
</table>

<div class="cart-actions">
    <a href="index.php" class="btn-back">Quay lại trang chủ</a>

    <div style="display:flex;gap:10px;">
        <button type="submit" name="update_qty" class="btn-order" style="background:#444">
            Cập nhật số lượng
        </button>

        <?php if (isset($_SESSION['user_id'])) { ?>
            <a href="checkout.php" class="btn-order">Đặt hàng</a>
        <?php } else { ?>
            <a href="login.php?redirect=checkout.php" 
                class="btn-order"
                onclick="alert('Vui lòng đăng nhập để đặt hàng!')">
                Đặt hàng
            </a>
        <?php } ?>
    </div>
</div>
</form>

<?php } ?>

<?php include "footer.php"; ?>