<?php
include "../db.php"; 
include "auth_check.php";

$success_message = "";
$error_message = "";

$status_order = [
    'Mới'       => 1,
    'Đang giao' => 2,
    'Đã giao'   => 3,
    'Đã hủy'    => 4
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    
    $order_id = intval($_POST['order_id']);
    $action = $_POST['action'] ?? '';
    $new_status = "";
    
    $stmt_current = $conn->prepare("SELECT status FROM orders WHERE id = ?");
    $stmt_current->execute([$order_id]);
    $current_order = $stmt_current->fetch(PDO::FETCH_ASSOC);
    
    if ($current_order) {
        $current_status = $current_order['status'];
        $current_priority = $status_order[$current_status] ?? 0;
        
        $can_update = true;
        $target_priority = $current_priority;

        if ($action === 'update_status') {
            $selected_status_value = $_POST['selected_status'] ?? $current_status; 
            
            if (isset($status_order[$selected_status_value])) {
                $target_priority = $status_order[$selected_status_value];
                $new_status = $selected_status_value;

                if ($target_priority < $current_priority) {
                    $can_update = false;
                    $error_message = "Cập nhật thất bại: Không thể chuyển trạng thái lùi lại từ '$current_status' về '$new_status'";
                } 
                elseif ($current_status == 'Đã hủy' || $current_status == 'Đã giao') {
                     $can_update = false;
                     $error_message = "Đơn hàng đã ở trạng thái '$current_status'. Không thể thay đổi thêm";
                }
            } else {
                 $can_update = false;
                 $error_message = "Trạng thái không hợp lệ.";
            }

        } elseif ($action === 'cancel') {
            $new_status = "Đã hủy";
            $target_priority = $status_order[$new_status];

            if ($current_status !== 'Mới') {
                $can_update = false;
                $error_message = "Không thể hủy đơn hàng đang ở trạng thái $current_status";
            }
        }

        if ($can_update && !empty($new_status)) {
            $sql_update = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql_update);
            
            if ($stmt->execute([$new_status, $order_id])) {
                 $success_message = "Đơn hàng #$order_id đã được cập nhật thành: $new_status";
            } else {
                 $error_message = "Không thể cập nhật trạng thái";
            }
            $stmt = null;
        }

    } else {
        $error_message = "Lỗi: Không tìm thấy đơn hàng #$order_id.";
    }
}

$sql = "
    SELECT o.*, u.name AS user_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.order_date DESC
";
$stmt_select = $conn->prepare($sql);
$stmt_select->execute();
$orders = $stmt_select->fetchAll(PDO::FETCH_ASSOC); 
$stmt_select = null; 
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quản lý Đơn hàng</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/product.css"> 
    <link rel="stylesheet" href="css/order.css"> 
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
            <h2>Quản lý đơn hàng</h2>
        </div>
        
        <?php if (!empty($success_message)): ?>
            <p class="success-message" style="margin-top: 15px;"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <p class="error-message" style="margin-top: 15px;"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <table>
            <tr>
                <th>ID</th>
                <th>Ngày đặt</th>
                <th>Tổng tiền</th>
                <th class="status-cell">Trạng thái</th> <th>Người dùng</th> 
                <th>Hành động</th>
            </tr>
            <?php
            foreach ($orders as $row) { 
                $order_status = htmlspecialchars($row['status'] ?? 'Mới');
                $status_class = 'status-' . strtolower(str_replace(' ', '-', $order_status));
                
                if (!empty($row['user_name'])) {
                    $user_display = htmlspecialchars($row['user_name']);
                } else {
                    $user_display = "Khách vãng lai";
                }
                
                $can_be_cancelled = ($order_status == 'Mới');
                $is_final_status = ($order_status == 'Đã hủy' || $order_status == 'Đã giao');
            ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= date('d/m/Y', strtotime($row['order_date'])) ?></td>
                <td><?= number_format($row['total']) ?> VNĐ</td> 
                <td class="status-cell">
                    <span class="<?= $status_class ?>">
                        <?= $order_status ?>
                    </span>
                </td>
                <td><?= $user_display ?></td> 
                <td>
                    <div style="display: flex; align-items: center; gap: 5px;">
                        
                        <?php if (!$is_final_status): ?>
                        <form method="post" style="display:inline-flex; align-items: center; gap: 5px;">
                            <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="action" value="update_status">
                            
                            <select name="selected_status" class="status-select">
                                <?php 
                                $current_prio = $status_order[$order_status] ?? 0;
                                foreach ($status_order as $status_name => $priority): 
                                    if ($status_name == 'Đã hủy') 
                                        continue;                                    
                                    if ($priority >= $current_prio): 
                                ?>
                                <option value="<?= $status_name ?>" <?= $order_status == $status_name ? 'selected' : '' ?>>
                                    <?= $status_name ?>
                                </option>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </select>
                            <button type="submit" class="btn-action btn-edit">Cập nhật</button>
                        </form>
                        <?php else: ?>
                            <span style="color: gray; font-style: italic; font-size: 0.9em;">Hoàn tất</span>
                        <?php endif; ?>
                        
                        <?php if ($can_be_cancelled): ?>
                        <form method="post" onsubmit="return confirm('Bạn có chắc chắn muốn HỦY đơn hàng #<?= $row['id'] ?>? Hành động này không thể hoàn tác.');">
                            <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="action" value="cancel">
                            <button type="submit" class="btn-action btn-delete" style="background-color: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">Hủy đơn</button>
                        </form>
                        <?php endif; ?>
                        
                        <a href="order_details.php?id=<?= $row['id'] ?>" class="btn-action btn-view" style="background-color: #6c757d; color: white; border: none; text-decoration: none; border-radius: 4px;">Chi tiết</a>
                    </div>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>
</body>
</html>