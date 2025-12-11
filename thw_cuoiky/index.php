<?php
include "header.php";
include "db.php";

$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$start = ($page - 1) * $limit;
if ($start < 0) $start = 0;

try {
    $total_result = $conn->query("SELECT COUNT(id) AS total FROM products");
    $total_row = $total_result->fetch(); 
    $total_products = $total_row['total'] ?? 0;
    $total_pages = ceil($total_products / $limit);
} catch (PDOException $e) {
    $total_products = 0;
    $total_pages = 0;
    error_log("Lỗi truy vấn đếm: " . $e->getMessage());
}

if ($page > $total_pages && $total_pages > 0) $page = $total_pages;
$start = ($page - 1) * $limit;
if ($start < 0) $start = 0;

$result = false;
$sql = "SELECT * FROM products ORDER BY id DESC LIMIT :limit OFFSET :start";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    
    $stmt->execute();
    $result = $stmt->fetchAll(); 
} catch (PDOException $e) {
    error_log("Lỗi truy vấn sản phẩm: " . $e->getMessage());
    $result = []; 
}

$num_rows = count($result);
?>

<div class="featured-banner">
    <div class="banner-content">
        <h1>SẢN PHẨM HOT: GIẢM GIÁ 50% TUẦN NÀY</h1>
        <p>Khám phá hàng ngàn ưu đãi đặc biệt chỉ có tại GiayCuaToi</p>
        <a href="product.php" class="banner-button">Xem ngay</a>
    </div>
</div>

<div class="hero-section">
    <div class="hero-left">
        <span class="promo-tag">KHAI TRƯƠNG – GIẢM 50%</span>
        <h1>Cửa hàng giày thời trang online</h1>
        <p>Mua giày online siêu tiện lợi – giao hàng tận nơi nhanh chóng.</p>
        <a href="product.php" class="hero-button">Mua ngay</a>
    </div>

    <div class="hero-right">
        <?php
            $banner_folder = "images/banner/";
            $banner_files  = glob($banner_folder . "*.{jpg,png,jpeg,webp}", GLOB_BRACE);

            if ($banner_files && count($banner_files) > 0):
                foreach ($banner_files as $img):
        ?>
            <img src="<?= $img ?>" class="banner-shoe" alt="Banner sản phẩm">
        <?php 
                endforeach;
            else:
                echo "<p>Không tìm thấy ảnh banner!</p>";
            endif;
        ?>
    </div>
</div>

<h2 style="margin-top: 30px;">TẤT CẢ SẢN PHẨM</h2>

<?php if ($num_rows > 0): ?>

    <div class="products-container">
        <?php foreach ($result as $row): ?>

            <?php 
                $gia_goc  = $row['price'];
                $gia_giam = $gia_goc * 0.5; 
            ?>

            <div class="product-box">
                <img src="admin/images/<?= htmlspecialchars($row['image']) ?>"
                    onerror="this.src='images/no-image.png'"
                    alt="<?= htmlspecialchars($row['name']) ?>">

                <h4><?= htmlspecialchars($row['name']) ?></h4>

                <p class="discount-price"><?= number_format($gia_giam, 0, ',', '.') ?> VNĐ</p>
                <p class="original-price"><s><?= number_format($gia_goc, 0, ',', '.') ?> VNĐ</s></p>

                <p><?= htmlspecialchars(mb_substr($row['description'], 0, 100, 'UTF-8')) . '...' ?></p>

                <form method="post" action="add_to_cart.php">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="name" value="<?= htmlspecialchars($row['name']) ?>">
                    <input type="hidden" name="price" value="<?= $gia_giam ?>">
                    <input type="hidden" name="img" value="<?= htmlspecialchars($row['image']) ?>">
                    <button type="submit">Thêm giỏ hàng</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="pagination">
        <?php
        if ($total_pages > 1) {
            if ($page > 1) {
                echo "<a href='?page=" . ($page - 1) . "'>&laquo; Trang trước</a>";
            }

            $start_loop = max(1, $page - 2);
            $end_loop = min($total_pages, $page + 2);

            if ($start_loop > 1) echo "<a href='?page=1'>1</a><span>...</span>";

            for ($i = $start_loop; $i <= $end_loop; $i++) {
                $active_class = ($i == $page) ? 'active' : '';
                echo "<a class='$active_class' href='?page=$i'>$i</a>";
            }

            if ($end_loop < $total_pages) echo "<span>...</span><a href='?page=$total_pages'>$total_pages</a>";

            if ($page < $total_pages) {
                echo "<a href='?page=" . ($page + 1) . "'>Trang sau &raquo;</a>";
            }
        }
        ?>
    </div>

<?php else: ?>
    <p>Không có sản phẩm nào để hiển thị.</p>
<?php endif; ?>

<script src="slideshow.js"></script> 

<?php include "footer.php"; ?>