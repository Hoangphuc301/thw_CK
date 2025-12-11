<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "header.php"; 
include "db.php"; 

$error_message = '';
$email_prefill = ''; 

if (isset($_SESSION['new_user_email'])) {
    $email_prefill = htmlspecialchars($_SESSION['new_user_user_email']);
    unset($_SESSION['new_user_email']); 
}

$redirect = isset($_GET['redirect']) ? basename($_GET['redirect']) : 'index.php';
if ($redirect === 'login.php' || $redirect === 'register.php' || $redirect === 'logout.php') {
    $redirect = 'index.php';
}

if (isset($_POST['login'])) {
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect_post = trim($_POST['redirect'] ?? 'index.php');

    $email_prefill = htmlspecialchars($email); 

    if (empty($email) || empty($password)) {
        $error_message = "Vui lòng điền đầy đủ Email và Mật khẩu.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        
        if (!$stmt) {
            $error_message = "Lỗi hệ thống: Không thể chuẩn bị truy vấn.";
        } else {
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                
                $hashed_password = $user['password']; 

                if (password_verify($password, $hashed_password)) {
                    
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role']; 
                    
                    if ($user['role'] === 'admin') {
                        header("Location: admin/index.php"); 
                    } else {
                        header("Location: " . $redirect_post); 
                    }
                    exit();

                } else {
                    $error_message = "Mật khẩu không đúng.";
                }

            } else {
                $error_message = "Email này không tồn tại.";
            }
            
            $stmt = null;
        }
    }
}
?>

<h2 style="text-align: center; color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-bottom: 20px;">
    Đăng nhập Tài khoản
</h2>

<div class="login-container" style="max-width: 400px; margin: 0 auto; padding: 20px; background: #fff; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1);">

    <form method="post" class="login-form">
        <?php if ($error_message) { ?>
            <p style="color:#721c24; background-color:#f8d7da; border: 1px solid #f5c6cb; text-align:center; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <?= htmlspecialchars($error_message) ?>
            </p>
        <?php } ?>

        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
        
        <input type="email" name="email" placeholder="Email" value="<?= $email_prefill ?>" required
            style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ced4da; border-radius: 5px; box-sizing: border-box;"
        >
        
        <input type="password" name="password" placeholder="Mật khẩu" required
            style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ced4da; border-radius: 5px; box-sizing: border-box;"
        >
        
        <button name="login" type="submit" class="btn-login-submit"
            style="width: 100%; padding: 12px; border: none; border-radius: 5px; color: white; background:#007bff; font-size: 1.1em; cursor: pointer; transition: background-color 0.3s;"
        >
            Đăng nhập
        </button>
    </form>

    <div class="cart-actions" style="display:flex; justify-content: space-between; margin-top: 20px; padding-top: 15px; border-top: 1px solid #e9ecef;">
        <a href="index.php" class="btn-back" 
            style="padding: 10px 15px; border: 1px solid #6c757d; border-radius: 5px; text-decoration: none; color: #6c757d; background: #f8f9fa;"
        >
            Trang chủ
        </a>
        <a href="register.php" class="btn-order"
            style="padding: 10px 15px; border: none; border-radius: 5px; text-decoration: none; color: white; background:#28a745;"
        >
            Đăng ký ngay
        </a>
    </div>
</div>

<?php include "footer.php"; ?>