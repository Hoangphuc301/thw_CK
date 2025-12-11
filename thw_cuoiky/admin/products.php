<?php
include "../db.php";
include "auth_check.php";

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    
    $stmt->execute([$id]);
    
    header("Location: products.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quản lý sản phẩm</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/product.css">
</head>
<body>
<div class="admin-container">
    <div class="sidebar">
        <h3 style="text-align:center;color:white">ADMIN</h3>
        <ul class="menu">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="products.php">Sản phẩm</a></li>
            <li><a href="../index.php">Về shop</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="admin-header">
            <h2>Quản lý sản phẩm</h2>
            <a href="product_add.php" class="btn-order">Thêm sản phẩm</a>
        </div>
        <table>
            <tr>
                <th>ID</th>
                <th>Hình</th>
                <th>Tên</th>
                <th>Giá</th>
                <th>Mô tả</th>
                <th>Hành động</th>
            </tr>
            <?php
            $stmt = $conn->prepare("SELECT id, name, price, image, description FROM products ORDER BY id DESC");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($result as $row) {
                
                $image_url = 'images/' . urlencode(htmlspecialchars($row['image'])); 
                $product_name = htmlspecialchars($row['name']);
                $product_description = htmlspecialchars($row['description']);
                
                echo "<tr>
                    <td>{$row['id']}</td>
                    <td><img src='{$image_url}' width='60' alt='{$product_name}'></td>
                    <td>{$product_name}</td>
                    <td>".number_format($row['price'])." VNĐ</td>
                    <td>{$product_description}</td>
                    <td>
                        <a href='product_edit.php?id={$row['id']}' class='btn-action btn-edit'>Sửa</a>
                        
                        <a href='?delete={$row['id']}' 
                            onclick=\"return confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?')\" 
                            class='btn-action btn-delete'>Xóa</a>
                    </td>
                </tr>";
            }
            ?>
        </table>
    </div>
</div>
</body>
</html>