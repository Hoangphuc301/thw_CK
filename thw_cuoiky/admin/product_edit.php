<?php
include "../db.php";
include "auth_check.php";

$id = intval($_GET['id']);

$sql_select = "SELECT * FROM products WHERE id=?";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->execute([$id]);

$product = $stmt_select->fetch(PDO::FETCH_ASSOC);
$stmt_select = null;

if (!$product) {
    die("Không tìm thấy sản phẩm.");
}
$old_image_filename = $product['image'];

if (isset($_POST['edit'])) {
    
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $desc = trim($_POST['description']);
    $image_filename = $old_image_filename; 
    
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        
        $target_dir = __DIR__ . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR; 
        
        $file_name = $_FILES['image_file']['name'];
        $file_tmp = $_FILES['image_file']['tmp_name'];
        
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_image_filename = uniqid('shoe_', true) . '.' . $file_extension;
        $target_file = $target_dir . $new_image_filename;

        if (move_uploaded_file($file_tmp, $target_file)) {
            $image_filename = $new_image_filename;

            $old_file_path = $target_dir . $old_image_filename;
            if ($old_image_filename && $old_image_filename !== $image_filename && file_exists($old_file_path)) {
                unlink($old_file_path);
            }
        } else {
            die("Lỗi: Không thể di chuyển tệp mới đã tải lên. Kiểm tra Quyền Ghi của thư mục admin/images/");
        }
    }

    $sql_update = "UPDATE products SET name=?, price=?, image=?, description=? WHERE id=?";
    $stmt_update = $conn->prepare($sql_update);
    
    if ($stmt_update->execute([$name, $price, $image_filename, $desc, $id])) {
        header("Location: products.php");
        exit();
    } else {
        $error_info = $stmt_update->errorInfo();
        die("Lỗi truy vấn SQL: " . $error_info[2]);
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Sửa sản phẩm</title>
        <link rel="stylesheet" href="css/admin.css">
        <link rel="stylesheet" href="css/product_form.css">
        </head>
<body>
    <h2 style="text-align:center; margin-top: 30px; color: #333;">Sửa sản phẩm: <?= htmlspecialchars($product['name']) ?></h2>
    
    <div class="form-container">
        <div class="current-image">
            <p>Ảnh hiện tại:</p>
            <?php 
            $image_url = 'images/' . urlencode(htmlspecialchars($product['image']));
            ?>
            <img src="<?= $image_url ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <small><?= htmlspecialchars($product['image']) ?></small>
        </div>
        
        <form method="post" enctype="multipart/form-data">
            
            <div class="form-group">
                <label for="name">Tên giày</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="price">Giá (VNĐ)</label>
                <input type="number" name="price" id="price" value="<?= htmlspecialchars($product['price']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="image_file">Tải lên Ảnh mới (Không bắt buộc)</label>
                <input type="file" name="image_file" id="image_file" accept="image/*">
                <small style="color: gray;">Nếu chọn, ảnh cũ sẽ bị thay thế.</small>
            </div>
            
            <div class="form-group">
                <label for="description">Mô tả sản phẩm</label>
                <textarea name="description" id="description" required><?= htmlspecialchars($product['description']) ?></textarea>
            </div>
            
            <button name="edit">Cập nhật sản phẩm</button>
        </form>
    </div>
</body>
</html>