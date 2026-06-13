<?php
session_start();

$conn = new mysqli(
    "sql213.infinityfree.com",
    "if0_41900150",
    "Rany9NH3lawi",
    "if0_41900150_my_first_project"
);
$conn->set_charset("utf8mb4");
if ($conn->connect_error) {
    die("Database Error");
}

$message = "";
$message_type = "";
$valid_token = false;
$user_id = null;

// Get token from URL
$token = $_GET['token'] ?? '';

if (!empty($token)) {
    // Check if token is valid
    $stmt = $conn->prepare("SELECT user_id, expires_at FROM password_resets WHERE token = ? AND used = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $reset = $result->fetch_assoc();
        $expires_at = strtotime($reset['expires_at']);
        $now = time();
        
        if ($now < $expires_at) {
            $valid_token = true;
            $user_id = $reset['user_id'];
        } else {
            $message = "✗ This reset link has expired. Please request a new one.";
            $message_type = "error";
        }
    } else {
        $message = "✗ Invalid reset link. Please request a new one.";
        $message_type = "error";
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['token'])) {
    $token = $_POST['token'];
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $message = "✗ Passwords do not match";
        $message_type = "error";
    } elseif (strlen($new_password) < 6) {
        $message = "✗ Password must be at least 6 characters";
        $message_type = "error";
    } else {
        // Verify token again
        $stmt = $conn->prepare("SELECT user_id FROM password_resets WHERE token = ? AND used = 0");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $reset = $result->fetch_assoc();
            $user_id = $reset['user_id'];
            
            // Update password
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateStmt->bind_param("si", $hashedPassword, $user_id);
            $updateStmt->execute();
            $updateStmt->close();
            
            // Mark token as used
            $usedStmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
            $usedStmt->bind_param("s", $token);
            $usedStmt->execute();
            $usedStmt->close();
            
            $message = "✓ Password changed successfully! You can now login.";
            $message_type = "success";
        } else {
            $message = "✗ Invalid or expired reset link";
            $message_type = "error";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Gaming World</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="icon.png">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #000; color: #fff; font-family: 'Orbitron', sans-serif; min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 40px 20px; }
        .reset-container { max-width: 450px; width: 100%; margin: 0 auto; }
        .reset-card { background: #0a0a0a; border: 2px solid #c94d06; border-radius: 30px; padding: 45px 40px; text-align: center; transition: 0.3s; }
        .reset-card:hover { transform: translateY(-5px); box-shadow: 0 0 30px rgba(201, 77, 6, 0.2); }
        .site-logo { color: #c94d06; font-size: 32px; font-weight: 900; margin-bottom: 30px; }
        .site-logo i { margin-left: 10px; }
        .description { text-align: center; color: #aaa; font-size: 14px; margin-bottom: 25px; line-height: 1.6; }
        .message { text-align: center; padding: 12px; border-radius: 12px; margin-bottom: 25px; font-weight: bold; font-size: 14px; }
        .message-success { background: rgba(0, 255, 102, 0.1); border: 1px solid #00ff66; color: #00ff66; }
        .message-error { background: rgba(255, 0, 0, 0.1); border: 1px solid #ff0000; color: #ff6666; }
        input[type="password"] { width: 100%; background: #1a1a1a; color: #fff; border: 1px solid #c94d06; padding: 14px 18px; border-radius: 15px; font-size: 14px; margin-bottom: 20px; transition: 0.3s; font-family: Arial, sans-serif; }
        input[type="password"]:focus { outline: none; border-color: #ff6a1a; box-shadow: 0 0 15px rgba(201, 77, 6, 0.2); background: #000; }
        input::placeholder { color: #666; }
        .password-box { position: relative; width: 100%; margin-bottom: 20px; }
        .password-box input { margin-bottom: 0; padding-left: 50px; }
        .toggle-password { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 18px; color: #c94d06; transition: 0.3s; }
        .toggle-password:hover { color: #ff6a1a; }
        .reset-btn { width: 100%; background: #c94d06; color: #fff; border: none; padding: 14px 28px; border-radius: 40px; font-size: 16px; font-weight: bold; font-family: 'Orbitron', sans-serif; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 12px; margin-top: 10px; }
        .reset-btn:hover { background: #000; transform: scale(1.02); box-shadow: 0 5px 25px rgba(201, 77, 6, 0.4); }
        .login-btn { display: inline-flex; align-items: center; justify-content: center; gap: 10px; background: #1a1a1a; border: 1px solid #c94d06; padding: 12px 28px; border-radius: 40px; text-decoration: none; font-family: 'Orbitron', sans-serif; font-weight: bold; font-size: 14px; color: #fff; transition: 0.3s; margin-top: 20px; width: 100%; }
        .login-btn:hover { background: #c94d06; transform: scale(1.02); }
        .login-btn:hover i, .login-btn:hover span { color: #000; }
        @media (max-width: 768px) { .reset-card { padding: 30px 25px; } .site-logo { font-size: 28px; } }
    </style>
</head>
<body>
<div class="reset-container">
    <div class="reset-card">
        <h2 class="site-logo"><i class="fa-solid fa-lock"></i> Set New Password</h2>

        <?php if(!empty($message)): ?>
            <div class="message <?php echo $message_type === 'success' ? 'message-success' : 'message-error'; ?>">
                <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if($message_type === 'success'): ?>
            <a href="login.php" class="login-btn">
                <i class="fa-solid fa-right-to-bracket"></i> Login Now
            </a>
        <?php elseif($valid_token): ?>
            <p class="description">Please enter your new password below</p>
            <form method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="password-box">
                    <span class="toggle-password" onclick="togglePassword('password')">
                        <i class="fa-regular fa-eye"></i>
                    </span>
                    <input type="password" name="password" id="password" placeholder="🔒 New Password" required>
                </div>
                <div class="password-box">
                    <span class="toggle-password" onclick="togglePassword('confirm_password')">
                        <i class="fa-regular fa-eye"></i>
                    </span>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="🔒 Confirm New Password" required>
                </div>
                <button type="submit" class="reset-btn">
                    <i class="fa-solid fa-floppy-disk"></i> Save New Password
                </button>
            </form>
        <?php elseif(empty($token)): ?>
            <p class="description">No reset token provided . Please use the link from your email</p>
            <a href="forgot_password.php" class="login-btn">
                <i class="fa-solid fa-key"></i> Request New Reset Link
            </a>
        <?php endif; ?>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const toggleIcon = passwordInput.parentElement.querySelector(".toggle-password i");
    
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleIcon.classList.remove("fa-eye");
        toggleIcon.classList.add("fa-eye-slash");
    } else {
        passwordInput.type = "password";
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");
    }
}
</script>
</body>
</html>