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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
        $user_name = $user['name'];
        
        // Generate unique reset token
        $reset_token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Save token in database
        $stmt2 = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt2->bind_param("iss", $user_id, $reset_token, $expires_at);
        $stmt2->execute();
        $stmt2->close();
        
        // Send reset email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'gamingworld.store.3laa@gmail.com';
            $mail->Password   = 'vhrr bydv nkrz azow';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';
            
            $mail->setFrom('gamingworld.store.3laa@gmail.com', 'Gaming World Store');
            $mail->addAddress($email, $user_name);
            $mail->isHTML(true);
            $mail->Subject = "Reset Your Password - Gaming World";
            
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $reset_token;
            
            $mail->Body = "
            <html>
            <body dir='ltr' style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
                <div style='max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 10px; border: 2px solid #c94d06;'>
                    <div style='text-align: center; margin-bottom: 25px;'>
                        <h1 style='color: #c94d06; font-size: 28px;'>🎮 Gaming World</h1>
                    </div>
                    
                    <h2 style='color: #333;'>Hello $user_name,</h2>
                    
                    <p style='font-size: 16px; color: #555;'>We received a request to reset your password for your Gaming World account.</p>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$reset_link' style='background-color: #c94d06; color: white; padding: 12px 30px; text-decoration: none; border-radius: 40px; font-weight: bold; display: inline-block;'>
                            🔑 Reset My Password
                        </a>
                    </div>
                    
                    <p style='font-size: 14px; color: #777;'>Or copy this link to your browser:</p>
                    <p style='font-size: 12px; color: #999; word-break: break-all;'>$reset_link</p>
                    
                    <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                    
                    <p style='font-size: 12px; color: #999;'>This link will expire in 1 hour. If you didn't request this, please ignore this email.</p>
                    
                    <p style='font-size: 12px; color: #999;'>Best regards,<br>Gaming World Team</p>
                </div>
            </body>
            </html>
            ";
            
            $mail->send();
            $message = "✓ A reset link has been sent to your email address";
            $message_type = "success";
        } catch (Exception $e) {
            $message = "✗ Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            $message_type = "error";
        }
    } else {
        $message = "✗ Email address not found in our records";
        $message_type = "error";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Gaming World</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="Icon.png">
    <link rel="manifest" href="manifest.json">
<link rel="apple-touch-icon" href="icons/icon-192.png">
<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#000;
    color:#fff;
    font-family:'Orbitron',sans-serif;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:40px 20px;
}

.reset-container{
    max-width:450px;
    width:100%;
    margin:0 auto;
}

.reset-card{
    background:#0a0a0a;
    border:2px solid #930505;
    border-radius:30px;
    padding:45px 40px;
    text-align:center;
    transition:.3s;
}

.reset-card:hover{
    transform:translateY(-5px);
    box-shadow:0 0 30px rgba(147,5,5,.2);
}

.site-logo{
    color:#930505;
    font-size:32px;
    font-weight:900;
    margin-bottom:30px;
}
.logo-img{
    width:110px;
    max-width:100%;
    object-fit:contain;
    filter:
        drop-shadow(0 0 10px rgba(147,5,5,.7))
        drop-shadow(0 0 20px rgba(147,5,5,.35));
}
.site-logo i{
    margin-left:10px;
}

.description{
    text-align:center;
    color:#aaa;
    font-size:14px;
    margin-bottom:25px;
    line-height:1.6;
}

.message{
    text-align:center;
    padding:12px;
    border-radius:12px;
    margin-bottom:25px;
    font-weight:bold;
    font-size:14px;
}

.message-success{
    background:rgba(0,255,102,.1);
    border:1px solid #00ff66;
    color:#00ff66;
}

.message-error{
    background:rgba(255,0,0,.1);
    border:1px solid #ff0000;
    color:#ff6666;
}

input[type="email"]{
    width:100%;
    background:#1a1a1a;
    color:#fff;
    border:1px solid #930505;
    padding:14px 18px;
    border-radius:15px;
    font-size:14px;
    margin-bottom:20px;
    transition:.3s;
    font-family:Arial,sans-serif;
}

input[type="email"]:focus{
    outline:none;
    border-color:#b30a0a;
    box-shadow:0 0 15px rgba(147,5,5,.2);
    background:#000;
}

input::placeholder{
    color:#666;
}

.reset-btn{
    width:100%;
    background:#930505;
    color:#fff;
    border:none;
    padding:14px 28px;
    border-radius:40px;
    font-size:16px;
    font-weight:bold;
    font-family:'Orbitron',sans-serif;
    cursor:pointer;
    transition:.3s;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:12px;
    margin-top:10px;
}

.reset-btn:hover{
    background:#000;
    transform:scale(1.02);
    box-shadow:0 5px 25px rgba(147,5,5,.4);
}

.back-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    background:#1a1a1a;
    border:1px solid #930505;
    padding:12px 28px;
    border-radius:40px;
    text-decoration:none;
    font-family:'Orbitron',sans-serif;
    font-weight:bold;
    font-size:14px;
    color:#fff;
    transition:.3s;
    margin-top:20px;
    width:100%;
}

.back-btn:hover{
    background:#930505;
    transform:scale(1.02);
}

.back-btn:hover i,
.back-btn:hover span{
    color:#000;
}

@media(max-width:768px){
    .reset-card{
        padding:30px 25px;
    }

    .site-logo{
        font-size:28px;
    }
}
</style>
</head>
<body>
<div class="reset-container">
    <div class="reset-card">
            <h2 class="site-logo">
    <img src="Icon.png" alt="Icon" class="logo-img">
</h2>
        <h2 class="site-logo">
            <i class="fa-solid fa-key"></i> Reset Password
        </h2>

        <p class="description">
            <i class="fa-regular fa-envelope"></i> Enter your email address and we'll send you a link to reset your password
        </p>

        <?php if(!empty($message)): ?>
            <div class="message <?php echo $message_type === 'success' ? 'message-success' : 'message-error'; ?>">
                <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Email Address" required>
            <button type="submit" class="reset-btn">
                <i class="fa-solid fa-paper-plane"></i> Send Reset Link
            </button>
        </form>

        <a href="login.php" class="back-btn">
            <i class="fa-solid fa-arrow-right"></i> Back to Login
        </a>
    </div>
</div>
    <script>
if ('serviceWorker' in navigator) {

    window.addEventListener('load', () => {

        navigator.serviceWorker.register('sw.js')
        .then(reg => console.log('SW Registered'))
        .catch(err => console.log(err));

    });
}
</script>
</body>
</html>