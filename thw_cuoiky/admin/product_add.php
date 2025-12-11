<?php
include "../db.php";
include "auth_check.php";

if (isset($_POST['add'])) {
    
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $desc = trim($_POST['description']);
    
    $target_dir = __DIR__ . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR; 
    $image_filename = null; 

    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $file_name = $_FILES['image_file']['name'];
        $file_tmp = $_FILES['image_file']['tmp_name'];
        $file_type = $_FILES['image_file']['type'];
        
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $image_filename = uniqid('shoe_', true) . '.' . $file_extension;
        $target_file = $target_dir . $image_filename;

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file_type, $allowed_types)) {
            die("Lỗi: Chỉ cho phép tệp JPG, PNG, hoặc GIF.");
        }
        
        if (move_uploaded_file($file_tmp, $target_file)) {
        } else {
            die("Lỗi: Không thể di chuyển tệp đã tải lên vào thư mục đích. Vui lòng kiểm tra **Quyền truy cập ghi** của thư mục admin/images/");
        }
    } else {
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'Kích thước tệp vượt quá giới hạn cho phép.',
            UPLOAD_ERR_FORM_SIZE => 'Kích thước tệp vượt quá giới hạn FORM.',
            UPLOAD_ERR_PARTIAL => 'Tệp chỉ được tải lên một phần.',
            UPLOAD_ERR_NO_FILE => 'Chưa chọn tệp để tải lên.',
        ];
        $error_code = $_FILES['image_file']['error'];
        $error_message = isset($upload_errors[$error_code]) ? $upload_errors[$error_code] : 'Lỗi không xác định khi tải tệp.';
        die("Lỗi upload: " . $error_message);
    }
    
    $sql_insert = "INSERT INTO products(name, price, image, description) VALUES(?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    
    if ($stmt->execute([$name, $price, $image_filename, $desc])) {
        header("Location: products.php"); 
        exit();
    } else {
        $error_info = $stmt->errorInfo();
        die("Lỗi truy vấn SQL: " . $error_info[2]);
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Thêm sản phẩm</title>
        <link rel="stylesheet" href="css/admin.css"> 
        <link rel="stylesheet" href="css/product_form.css">
    </head>
<body>
    <h2 style="text-align:center; margin-top: 30px; color: #333;">Thêm sản phẩm mới</h2>
    
    <div class="form-container">
        <form method="post" enctype="multipart/form-data">
            
            <div class="form-group">
                <label for="name">Tên giày</label>
                <input type="text" name="name" id="name" placeholder="Ví dụ: Air Jordan 1 Low" required>
            </div>

            <div class="form-group">
                <label for="price">Giá (VNĐ)</label>
                <input type="number" name="price" id="price" placeholder="Ví dụ: 3500000" required>
            </div>

            <div class="form-group">
                <label for="image_file">Ảnh sản phẩm</label>
                <input type="file" name="image_file" id="image_file" accept="image/*" required>
            </div>
            
            <div class="form-group">
                <label for="description">Mô tả sản phẩm</label>
                <textarea name="description" id="description" placeholder="Nhập mô tả chi tiết sản phẩm" required></textarea>
            </div>
            
            <button name="add">Thêm sản phẩm</button>
        </form>
    </div>
    </body>
</html>