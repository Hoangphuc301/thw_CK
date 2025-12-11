<?php
include "../db.php";
include "auth_check.php";

if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    if ($delete_id > 0) { 
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        
        $stmt->execute([$delete_id]);

        $success_message = "Người dùng ID #$delete_id đã bị xóa.";
    }
}

$stmt_users = $conn->prepare("SELECT id, name, email, role FROM users ORDER BY id ASC");
$stmt_users->execute();
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quản lý người dùng</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/product.css"> 
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
            <h2>Quản lý Người dùng</h2>
        </div>

        <?php if (isset($success_message)): ?>
            <p class="success-message" style="margin-top: 15px;"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>
        
        <table>
            <tr>
                <th>ID</th>
                <th>Tên người dùng</th> <th>Email</th>
                <th>Vai trò</th>
                <th>Hành động</th>
            </tr>
            <?php
            foreach ($users as $row) { 
            ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td> <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['role'] ?? 'user') ?></td>
                <td>
                    <a href='?delete=<?= $row['id'] ?>' 
                        onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng <?= htmlspecialchars($row['name']) ?> không?')" 
                        class='btn-action btn-delete'>Xóa</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>
</body>
</html>