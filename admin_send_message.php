<?php
session_start();

$admin_password = "3laa25Herz";

if (isset($_POST['admin_pass'])) {
    if ($_POST['admin_pass'] === $admin_password) {
        $_SESSION['admin_logged'] = true;
    } else {
        $error = "Wrong Password";
    }
}

if (!isset($_SESSION['admin_logged'])):
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>Admin Login - Gaming World</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="Icon.png">
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
        padding:20px;
    }

    .login-container{
        max-width:400px;
        width:100%;
    }

    .login-card{
        background:#0a0a0a;
        border:2px solid #930505;
        border-radius:30px;
        padding:40px 35px;
        text-align:center;
        transition:.3s;
        box-shadow:0 0 20px rgba(147,5,5,.12);
    }

    .login-card:hover{
        transform:translateY(-5px);
        box-shadow:0 0 30px rgba(147,5,5,.25);
    }

    .site-logo{
        color:#930505;
        font-size:28px;
        margin-bottom:25px;
    }
    .logo-img{
        width:110px;
        max-width:100%;
        object-fit:contain;
        filter:
            drop-shadow(0 0 10px rgba(147,5,5,.7))
            drop-shadow(0 0 20px rgba(147,5,5,.35));
    }
    .error{
        color:#ff6666;
        text-align:center;
        margin-bottom:15px;
        background:rgba(255,0,0,.1);
        padding:10px;
        border-radius:10px;
        border:1px solid #ff0000;
    }

    input{
        width:100%;
        background:#1a1a1a;
        color:#fff;
        border:1px solid #930505;
        padding:14px 18px;
        border-radius:40px;
        font-size:14px;
        margin-bottom:20px;
        outline:none;
        transition:.3s;
    }

    input:focus{
        box-shadow:0 0 15px rgba(147,5,5,.3);
        border-color:#b30a0a;
        background:#000;
    }

    input::placeholder{
        color:#777;
    }

    button{
        width:100%;
        background:#930505;
        color:#fff;
        border:none;
        padding:14px;
        border-radius:40px;
        font-size:16px;
        font-weight:bold;
        font-family:'Orbitron',sans-serif;
        cursor:pointer;
        transition:.3s;
    }

    button:hover{
        background:#000;
        color:#930505;
        transform:scale(1.02);
        box-shadow:0 0 0 1px #930505;
    }
</style>
</head>
<body>
<div class="login-container">
    <div class="login-card">
                <h2 class="site-logo">
    <img src="Icon.png" alt="Icon" class="logo-img">
</h2>
        <h2 class="site-logo"><i class="fa-solid fa-lock"></i> Admin Login</h2>
        <?php if(!empty($error)): ?>
            <p class="error"><i class="fa-solid fa-exclamation-triangle"></i> <?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="password" name="admin_pass" placeholder="🔒 Admin Password" required>
            <button type="submit"><i class="fa-solid fa-key"></i> Login</button>
        </form>
    </div>
</div>
</body>
</html>
<?php
exit();
endif;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

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

$status = "";

if (isset($_POST['delete_sent_message'])) {
    $sent_id = intval($_POST['sent_id']);
    $stmt = $conn->prepare("DELETE FROM admin_messages WHERE id = ?");
    $stmt->bind_param("i", $sent_id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_send_message.php");
    exit();
}

if (isset($_POST['send_admin_message'])) {
    $username = trim($_POST['username']);
    $message = trim($_POST['message']);

    if (!empty($username) && !empty($message)) {

        $stmtUser = $conn->prepare("SELECT id, name, email FROM users WHERE name = ?");
        $stmtUser->bind_param("s", $username);
        $stmtUser->execute();
        $userResult = $stmtUser->get_result();

        if ($userResult && $userResult->num_rows > 0) {
            $user = $userResult->fetch_assoc();
            $user_id = $user['id'];
            $real_username = $user['name'];
            $user_email = $user['email'] ?? null;

            $stmt = $conn->prepare("INSERT INTO admin_messages (user_id, username, message) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $real_username, $message);

            if ($stmt->execute()) {
            
                if (!empty($user_email)) {
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
                        $mail->addAddress($user_email);

                        $mail->isHTML(true);
                        $mail->Subject = "New message from Admin";
                        
                        $mail->Body = "
                        <html>
                        <body dir='ltr' style='font-family: Arial, sans-serif; background-color:#000; padding:20px;'>
                            <div style='max-width:600px; margin:0 auto; background:#0a0a0a; border:1px solid #ff0000; border-radius:16px; overflow:hidden; box-shadow:0 0 30px rgba(255,0,0,.25);'>

                                <div style='background:#111; padding:30px 20px; text-align:center; border-bottom:1px solid #ff0000;'>
                                    <img src='https://gaming-world-store.66ghz.com/Icon.png' alt='Gaming World' style='width:120px; max-width:100%; margin-bottom:15px;'>
                                    <h1 style='margin:0; font-size:26px; color:#ff0000;'>Gaming World Store</h1>
                                </div>

                                <div style='padding:25px; color:#ddd;'>
                                    <h2 style='color:#ff0000; text-align:center; margin-bottom:20px;'>
                                        New message from Gaming World Team
                                    </h2>

                                    <p style='font-size:16px; color:#ccc;'>Message content:</p>

                                    <div style='background:#111; padding:18px; border-left:4px solid #ff0000; border-radius:10px; margin:20px 0;'>
                                        <p style='margin:0; font-size:15px; color:#fff; white-space:pre-line; line-height:1.7;'>
                                            $message
                                        </p>
                                    </div>

                                    <hr style='border:0; border-top:1px solid #222; margin:25px 0;'>

                                    <p style='font-size:12px; color:#777; text-align:center;'>
                                        This is an automated email, please do not reply to it.
                                    </p>
                                </div>

                            </div>
                        </body>
                        </html>
                        ";

                        $mail->send();
                    } catch (Exception $e) {
                    }
                }

                $notification_text = "Admin sent you a message";
                $notif = $conn->prepare("INSERT INTO notifications (user_id, text) VALUES (?, ?)");
                $notif->bind_param("is", $user_id, $notification_text);
                $notif->execute();
                $notif->close();

                $status = "✓ Message sent successfully";
            } else {
                $status = "✗ Message was not sent";
            }
            $stmt->close();
        } else {
            $status = "✗ Username not found";
        }
        $stmtUser->close();
    } else {
        $status = "✗ Please fill all fields";
    }
}

$all_users = $conn->query("SELECT id, name, email, image FROM users ORDER BY name ASC");
$sent_messages = $conn->query("SELECT * FROM admin_messages ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>Admin Send Message - Gaming World</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="Icon.png">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
    }

    /* Header */
    header{
        background:#000;
        border-bottom:2px solid #930505;
        padding:15px 30px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        flex-wrap:wrap;
        gap:15px;
    }

    .site-logo{
        color:#930505;
        font-family:'Orbitron',sans-serif;
        font-size:28px;
        font-weight:900;
    }

    .logo-img{
        width:110px;
        max-width:100%;
        object-fit:contain;
        filter:
            drop-shadow(0 0 10px rgba(147,5,5,.7))
            drop-shadow(0 0 20px rgba(147,5,5,.35));
    }

    nav{
        display:flex;
        gap:20px;
        flex-wrap:wrap;
    }

    nav a{
        color:#fff;
        text-decoration:none;
        font-family:'Orbitron',sans-serif;
        transition:.3s;
    }

    nav a:hover{
        color:#930505;
    }

    /* Layout */
    .admin-layout{
        display:flex;
        gap:30px;
        padding:40px 30px;
        max-width:1600px;
        margin:0 auto;
        flex-wrap:wrap;
    }

    /* Users Box */
    .users-box{
        flex:1;
        min-width:280px;
        background:#0a0a0a;
        border:1px solid #930505;
        border-radius:25px;
        padding:20px;
        height:600px;
        overflow-y:auto;
    }

    .users-box h2{
        color:#930505;
        text-align:center;
        margin-bottom:20px;
        font-size:22px;
    }

    /* User Card */
    .user-card{
        background:#1a1a1a;
        border:1px solid #930505;
        border-radius:20px;
        padding:15px;
        margin-bottom:15px;
        transition:.3s;
    }

    .user-card:hover{
        transform:translateY(-2px);
        box-shadow:0 0 15px rgba(147,5,5,.2);
    }

    .user-card-content{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:15px;
    }

    .user-info{
        display:flex;
        align-items:center;
        gap:12px;
        flex:1;
    }

    .user-img{
        width:50px;
        height:50px;
        border-radius:50%;
        object-fit:cover;
        border:2px solid #930505;
    }

    .default-user-img{
        width:50px;
        height:50px;
        border-radius:50%;
        background:#222;
        border:2px solid #930505;
        display:flex;
        align-items:center;
        justify-content:center;
        color:#930505;
        font-size:20px;
    }

    .user-name{
        color:#930505;
        font-size:18px;
        font-weight:bold;
    }

    /* Select Button */
    .select-btn{
        background:#930505;
        color:#fff;
        border:none;
        padding:8px 20px;
        border-radius:40px;
        cursor:pointer;
        font-weight:bold;
        font-family:'Orbitron',sans-serif;
        transition:.3s;
        font-size:13px;
    }

    .select-btn:hover{
        background:#000;
        transform:scale(1.05);
        box-shadow:0 0 0 1px #930505;
    }

    /* Send Message Box */
    .admin-send-box{
        flex:1;
        min-width:350px;
        background:#0a0a0a;
        border:1px solid #930505;
        border-radius:25px;
        padding:30px;
    }

    .admin-send-box h2{
        color:#930505;
        text-align:center;
        margin-bottom:25px;
        font-size:22px;
    }

    .admin-send-box input,
    .admin-send-box textarea{
        width:100%;
        background:#1a1a1a;
        color:#fff;
        border:1px solid #930505;
        border-radius:15px;
        padding:14px;
        margin-bottom:15px;
        font-size:14px;
        font-family:Arial,sans-serif;
        transition:.3s;
    }

    .admin-send-box input:focus,
    .admin-send-box textarea:focus{
        outline:none;
        border-color:#b30a0a;
        box-shadow:0 0 15px rgba(147,5,5,.2);
    }

    .admin-send-box textarea{
        min-height:180px;
        resize:vertical;
    }

    .send-btn{
        width:100%;
        background:#930505;
        color:#fff;
        border:none;
        padding:14px;
        border-radius:40px;
        cursor:pointer;
        font-weight:bold;
        font-family:'Orbitron',sans-serif;
        transition:.3s;
        display:flex;
        align-items:center;
        justify-content:center;
        gap:10px;
    }

    .send-btn:hover{
        background:#000;
        transform:scale(1.02);
        box-shadow:0 0 0 1px #930505;
    }

    /* Sent Messages Box */
    .sent-box{
        flex:1;
        min-width:280px;
        background:#0a0a0a;
        border:1px solid #930505;
        border-radius:25px;
        padding:20px;
        height:600px;
        overflow-y:auto;
    }

    .sent-box h2{
        color:#930505;
        text-align:center;
        margin-bottom:20px;
        font-size:22px;
    }

    /* Sent Card */
    .sent-card{
        background:#1a1a1a;
        border:1px solid #930505;
        border-radius:20px;
        padding:15px;
        margin-bottom:15px;
        transition:.3s;
    }

    .sent-card:hover{
        transform:translateY(-2px);
        box-shadow:0 0 15px rgba(147,5,5,.2);
    }

    .sent-card p{
        margin:8px 0;
        line-height:1.5;
    }

    .sent-card strong{
        color:#930505;
    }

    .sent-date{
        color:#aaa;
        font-size:11px;
        display:block;
        margin-top:10px;
    }

    /* Delete Button */
    .delete-btn{
        background:#ff0000;
        color:#fff;
        border:none;
        padding:8px 20px;
        border-radius:40px;
        cursor:pointer;
        font-weight:bold;
        font-family:'Orbitron',sans-serif;
        transition:.3s;
        display:inline-flex;
        align-items:center;
        gap:8px;
        margin-top:10px;
        font-size:13px;
    }

    .delete-btn:hover{
        background:#cc0000;
        transform:scale(1.05);
    }

    /* Status Message */
    .status-message{
        text-align:center;
        padding:12px;
        border-radius:10px;
        margin-bottom:20px;
        font-weight:bold;
    }

    .status-success{
        background:rgba(0,255,102,.1);
        border:1px solid #00ff66;
        color:#00ff66;
    }

    .status-error{
        background:rgba(255,0,0,.1);
        border:1px solid #ff0000;
        color:#ff6666;
    }

    /* Scrollbar */
    .users-box::-webkit-scrollbar,
    .sent-box::-webkit-scrollbar{
        width:5px;
    }

    .users-box::-webkit-scrollbar-track,
    .sent-box::-webkit-scrollbar-track{
        background:#1a1a1a;
    }

    .users-box::-webkit-scrollbar-thumb,
    .sent-box::-webkit-scrollbar-thumb{
        background:#930505;
        border-radius:5px;
    }

    /* Responsive */
    @media(max-width:1024px){

        .admin-layout{
            flex-direction:column;
        }

        .users-box,
        .admin-send-box,
        .sent-box{
            height:auto;
            max-height:500px;
        }
    }

    @media(max-width:768px){

        header{
            flex-direction:column;
            text-align:center;
        }

        .user-card-content{
            flex-direction:column;
            text-align:center;
        }

        .user-info{
            flex-direction:column;
        }
    }
</style>
</head>
<body>

<header>
        <h1 class="site-logo">
    <img src="Icon.png" alt="Icon" class="logo-img">
</h1>
    <h1 class="site-logo"><i class="fa-solid fa-envelope"></i> Admin Panel</h1>
    <nav>
        <a href="index.php"><i class="fa-solid fa-home"></i> Home</a>
        <a href="admin_send_message.php" style="color:#930505;"><i class="fa-solid fa-paper-plane"></i> Send Messages</a>
        <a href="admin.php"><i class="fa-solid fa-envelope"></i> Customer Messages</a>
    </nav>
</header>

<div class="admin-layout">
    <!-- Users List -->
    <div class="users-box">
        <h2><i class="fa-solid fa-users"></i> All Users</h2>
        <?php if($all_users && $all_users->num_rows > 0): ?>
            <?php while($user = $all_users->fetch_assoc()): ?>
                <div class="user-card">
                    <div class="user-card-content">
                        <div class="user-info">
                            <?php if(!empty($user['image'])): ?>
                                <img src="<?php echo htmlspecialchars($user['image']); ?>" class="user-img">
                            <?php else: ?>
                                <div class="default-user-img">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                            </div>
                        </div>
                        <button type="button" onclick="document.querySelector('input[name=username]').value='<?php echo htmlspecialchars($user['name']); ?>';" class="select-btn">
                            <i class="fa-solid fa-check"></i> Select
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center; color:#888;">No users found</p>
        <?php endif; ?>
    </div>

    <!-- Send Message Form -->
    <div class="admin-send-box">
        <h2><i class="fa-solid fa-paper-plane"></i> Send Message</h2>
        <?php if(!empty($status)): ?>
            <div class="status-message <?php echo (strpos($status, '✓') !== false) ? 'status-success' : 'status-error'; ?>">
                <i class="fas <?php echo (strpos($status, '✓') !== false) ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $status; ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="👤 Username" required>
            <textarea name="message" placeholder="✏️ Write message to user..." required></textarea>
            <button type="submit" name="send_admin_message" class="send-btn">
                <i class="fa-solid fa-paper-plane"></i> Send Message
            </button>
        </form>
    </div>

    <!-- Sent Messages -->
    <div class="sent-box">
        <h2><i class="fa-regular fa-clock"></i> Sent Messages</h2>
        <?php if($sent_messages && $sent_messages->num_rows > 0): ?>
            <?php while($msg = $sent_messages->fetch_assoc()): ?>
                <div class="sent-card">
                    <p><strong><i class="fa-solid fa-user"></i> Username:</strong> <?php echo htmlspecialchars($msg['username']); ?></p>
                    <p><strong><i class="fa-regular fa-message"></i> Message:</strong> <?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                    <span class="sent-date"><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($msg['created_at']); ?></span>
                    <form method="POST">
                        <input type="hidden" name="sent_id" value="<?php echo $msg['id']; ?>">
                        <button type="submit" name="delete_sent_message" class="delete-btn" onclick="return confirm('Delete this sent message?');">
                            <i class="fa-solid fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center; color:#888;">No sent messages yet</p>
        <?php endif; ?>
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