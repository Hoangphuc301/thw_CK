<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "header.php";
include "db.php";

$error_message = '';
$success_message = '';
$name = '';

if (isset($_POST['register'])) {
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $repassword = $_POST['repassword'] ?? '';

    if (preg_match('~[0-9]~', $name)) {
        $error_message = "Họ tên không được phép chứa số";
    } elseif ($password !== $repassword) {
        $error_message = "Mật khẩu nhập lại không khớp";
    } elseif (strlen($password) < 6) {
        $error_message = "Mật khẩu phải có ít nhất 6 ký tự";
    } else {
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_check->execute([$email]);
        
        if ($stmt_check->rowCount() > 0) {
            $error_message = "Email này đã được đăng ký. Vui lòng sử dụng email khác";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user'; 

            $stmt_insert = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            
            if ($stmt_insert->execute([$name, $email, $hashed_password, $role])) {
                $_SESSION['new_user_email'] = $email;
                $success_message = "Đăng ký thành công! Bạn sẽ được chuyển đến trang đăng nhập sau 3 giây...";
            } else {
                $error_message = "Đăng ký thất bại.";
            }
        }
        $stmt_check = null; 
    }
}
?>

<h2>Đăng ký tài khoản</h2>

<div class="login-container">

    <form method="post" class="login-form">
        <?php if ($error_message) { ?>
            <p style="color:red; text-align:center; padding: 10px; border: 1px solid red; border-radius: 5px;">
                <?= htmlspecialchars($error_message) ?>
            </p>
        <?php } elseif ($success_message) { ?>
            <p id="success-message" style="color:green; text-align:center; padding: 10px; border: 1px solid green; border-radius: 5px;">
                <?= $success_message ?>
            </p>
            <script>
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 3000);
            </script>
        <?php } ?>
        
        <input type="text" name="name" id="register-name" placeholder="Họ tên" required value="<?= htmlspecialchars($name ?? '') ?>" onkeypress="return blockNumbers(event)"><br><br>

        <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($email ?? '') ?>"><br><br>

        <input type="password" name="password" placeholder="Mật khẩu" required><br><br>

        <input type="password" name="repassword" placeholder="Nhập lại mật khẩu" required><br><br>

        <button name="register" type="submit" class="btn-login-submit">
            Đăng ký
        </button>
    </form>

    <div class="cart-actions">
        <a href="login.php" class="btn-back">Quay lại đăng nhập</a>

        <a href="login.php" class="btn-order">
            Tới đăng nhập
        </a>
    </div>

</div>

<script>
function blockNumbers(event) {
    const charCode = (event.which) ? event.which : event.keyCode;
    if (charCode >= 48 && charCode <= 57) {
        return false;
    }
    return true;
}
</script>

<?php 
include "footer.php"; 
?>