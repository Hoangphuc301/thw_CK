<?php
include "header.php";
include "db.php";

$keyword = trim($_GET['query'] ?? '');
$keyword_lower = mb_strtolower($keyword, 'UTF-8');
$search_param = "%" . $keyword_lower . "%";

$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$sql_count = "
    SELECT COUNT(id) AS total
    FROM products
    WHERE name COLLATE utf8mb4_unicode_ci LIKE :k1
       OR description COLLATE utf8mb4_unicode_ci LIKE :k2
";

$stmt_count = $conn->prepare($sql_count);
$stmt_count->bindValue(':k1', $search_param, PDO::PARAM_STR);
$stmt_count->bindValue(':k2', $search_param, PDO::PARAM_STR);
$stmt_count->execute();

$total_row = $stmt_count->fetch();
$total_products = $total_row['total'] ?? 0;
$total_pages = ceil($total_products / $limit);

if ($page > $total_pages && $total_pages > 0) $page = $total_pages;

$start = ($page - 1) * $limit;

$sql = "
    SELECT *
    FROM products
    WHERE name COLLATE utf8mb4_unicode_ci LIKE :k3
       OR description COLLATE utf8mb4_unicode_ci LIKE :k4
    ORDER BY id DESC
    LIMIT :limit OFFSET :start
";

$stmt = $conn->prepare($sql);

$stmt->bindValue(':k3', $search_param, PDO::PARAM_STR);
$stmt->bindValue(':k4', $search_param, PDO::PARAM_STR);
$stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
$stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);

$stmt->execute();

$result = $stmt->fetchAll();
$num_rows = count($result);
?>

<h2 style="margin-top: 30px;">KẾT QUẢ TÌM KIẾM cho: "<b><?= htmlspecialchars($keyword) ?></b>"</h2>

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
    $search_url = "&query=" . urlencode($keyword);

    if ($page > 1) echo "<a href='?page=" . ($page - 1) . $search_url . "'>&laquo; Trang trước</a>";

    $start_loop = max(1, $page - 2);
    $end_loop = min($total_pages, $page + 2);

    if ($start_loop > 1) echo "<a href='?page=1$search_url'>1</a><span>...</span>";

    for ($i = $start_loop; $i <= $end_loop; $i++) {
        $active = ($i == $page) ? 'active' : '';
        echo "<a class='$active' href='?page=$i$search_url'>$i</a>";
    }

    if ($end_loop < $total_pages) echo "<span>...</span><a href='?page=$total_pages$search_url'>$total_pages</a>";

    if ($page < $total_pages) echo "<a href='?page=" . ($page + 1) . $search_url . "'>Trang sau &raquo;</a>";
}
?>
</div>

<?php else: ?>

<p style='text-align:center;color:red; margin-top: 20px;'>
Không tìm thấy sản phẩm với từ khóa "<b><?= htmlspecialchars($keyword) ?></b>".
</p>

<?php endif; ?>

<div style="text-align:center;margin-top:20px;">
    <a href="index.php" class="btn-back">Quay về trang chủ</a>
</div>

<?php include "footer.php"; ?>
