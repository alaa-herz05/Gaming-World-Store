<?php
session_start();

$admin_password = "3laa25Herz";

if (isset($_POST['admin_pass'])) {
    if ($_POST['admin_pass'] === $admin_password) {
        $_SESSION['admin_logged'] = true;
    } else {
        $error = "كلمة المرور خاطئة";
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

    .error{
        color:#ff6666;
        text-align:center;
        margin-bottom:15px;
        background:rgba(255,0,0,.1);
        border:1px solid #ff0000;
        padding:10px;
        border-radius:10px;
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

$conn = new mysqli("sql213.infinityfree.com", "if0_41900150", "Rany9NH3lawi", "if0_41900150_my_first_project");
$conn->set_charset("utf8mb4");

if ($conn->connect_error) { die("Database Error"); }

if (isset($_POST['send_reply'])) {
    $message_id = $_POST["message_id"];
    $user_id = $_POST["user_id"];
    $reply = $_POST["reply"];

    $msg_query = $conn->prepare("SELECT user_email, user_name, subject, message FROM messages WHERE id = ?");
    $msg_query->bind_param("i", $message_id);
    $msg_query->execute();
    $msg_data = $msg_query->get_result()->fetch_assoc();
    $to_email = $msg_data['user_email'] ?? null;
    $user_name = $msg_data['user_name'] ?? 'Customer';
    $user_subject = $msg_data['subject'] ?? 'No Subject';
    $user_message = $msg_data['message'] ?? '';
    $msg_query->close();

    $stmt = $conn->prepare("INSERT INTO message_replies (message_id, user_id, reply) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $message_id, $user_id, $reply);
    $stmt->execute();
    $stmt->close();

        if (!empty($to_email)) {
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
            $mail->addAddress($to_email);
            $mail->isHTML(true);
            $mail->Subject = "Re: " . $user_subject;

            $mail->Body = "
            <div dir='ltr' style='font-family: Arial, sans-serif; background-color:#000; padding:20px;'>
                <div style='max-width:600px; margin:0 auto; background:#0a0a0a; border:1px solid #ff0000; border-radius:16px; overflow:hidden; box-shadow:0 0 30px rgba(255,0,0,.25);'>
                    <div style='background:#111; padding:30px 20px; text-align:center; border-bottom:1px solid #ff0000;'>
                        <img src='https://gaming-world-store.66ghz.com/Icon.png' alt='Gaming World' style='width:120px; max-width:100%; margin-bottom:15px;'>
                        <h1 style='margin:0; font-size:26px; color:#ff0000;'>Gaming World Store</h1>
                    </div>

                    <div style='padding:25px; color:#ddd;'>
                        <p style='font-size:16px;'>Hello <strong style='color:#ff0000;'>$user_name</strong>,</p>
                        <p style='font-size:16px; line-height:1.7;'>The admin has replied to your inquiry regarding: <strong style='color:#ff0000;'>$user_subject</strong></p>

                        <div style='background:#111; border-left:4px solid #ff0000; padding:18px; border-radius:12px; margin:20px 0;'>
                            <p style='margin:0 0 10px; font-weight:bold; color:#ff0000;'>Admin Reply:</p>
                            <p style='margin:0; color:#fff; line-height:1.8;'>" . nl2br(htmlspecialchars($reply)) . "</p>
                        </div>

                        <hr style='border:0; border-top:1px solid #222; margin:25px 0;'>

                        <p style='font-size:14px; color:#ff0000; font-weight:bold;'>Your Original Message:</p>
                        <p style='font-size:14px; color:#999; font-style:italic; line-height:1.7;'>" . nl2br(htmlspecialchars($user_message)) . "</p>
                    </div>

                    <div style='background:#111; padding:15px; text-align:center; color:#777; font-size:12px; border-top:1px solid #222;'>
                        &copy; " . date('Y') . " Gaming World Store. All rights reserved.
                    </div>
                </div>
            </div>";

            $mail->send();

        } catch (Exception $e) {
            // Email failed but page will continue
        }
    }

    $conn->query("UPDATE orders SET status = 'Delivered' WHERE user_id = '$user_id' ORDER BY id DESC LIMIT 1");

    $notification_text = "The admin has replied to your message: <a href='my_messages.php' style='color:#930505; text-decoration:none; font-weight:bold;'>View Reply</a>. Your <a href='my_orders.php' style='color:#930505; text-decoration:none; font-weight:bold;'>Order</a> Has Been Delivered.";

    $notif = $conn->prepare("INSERT INTO notifications (user_id, text) VALUES (?, ?)");
    $notif->bind_param("is", $user_id, $notification_text);
    $notif->execute();
    $notif->close();

    header("Location: admin.php");
    exit();
}
if (isset($_POST['delete_message'])) {
    $delete_id = $_POST['delete_id'];
    $conn->query("DELETE FROM message_replies WHERE message_id = ".intval($delete_id));
    $conn->query("DELETE FROM messages WHERE id = ".intval($delete_id));
    header("Location: admin.php");
    exit();
}

$messages = $conn->query("SELECT * FROM messages ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>Admin Panel - Gaming World</title>
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
        gap:25px;
        flex-wrap:wrap;
    }

    nav a{
        color:#fff;
        text-decoration:none;
        font-family:'Orbitron',sans-serif;
        transition:.3s;
        padding:8px 0;
    }

    nav a:hover{
        color:#930505;
    }

    /* Container */
    .messages-container{
        max-width:1000px;
        margin:40px auto;
        padding:0 20px;
    }

    /* Message Card */
    .message-card{
        background:#0a0a0a;
        border:1px solid #930505;
        border-radius:20px;
        padding:25px;
        margin-bottom:25px;
        transition:.3s;
        box-shadow:0 0 18px rgba(147,5,5,.12);
    }

    .message-card:hover{
        transform:translateY(-3px);
        box-shadow:0 0 20px rgba(147,5,5,.2);
    }

    .msg-info{
        color:#fff;
        margin-bottom:15px;
        line-height:1.6;
    }

    .msg-info strong{
        color:#930505;
    }

    /* Reply Box */
    .reply-box{
        background:#1a1a1a;
        padding:15px;
        margin-top:15px;
        border-radius:15px;
        border-right:3px solid #00ff66;
    }

    .reply-text{
        color:#00ff66;
        margin:0;
    }

    /* Textarea */
    textarea{
        width:100%;
        background:#1a1a1a;
        color:#fff;
        border:1px solid #930505;
        border-radius:15px;
        padding:15px;
        font-size:14px;
        font-family:Arial,sans-serif;
        resize:vertical;
        margin-top:15px;
        transition:.3s;
    }

    textarea:focus{
        outline:none;
        border-color:#b30a0a;
        box-shadow:0 0 15px rgba(147,5,5,.2);
        background:#000;
    }

    textarea::placeholder{
        color:#777;
    }

    /* Buttons */
    .send-btn{
        background:#930505;
        color:#fff;
        border:none;
        padding:12px 28px;
        border-radius:40px;
        cursor:pointer;
        font-weight:bold;
        font-family:'Orbitron',sans-serif;
        transition:.3s;
        display:inline-flex;
        align-items:center;
        gap:8px;
        margin-top:10px;
    }

    .send-btn:hover{
        background:#000;
        color:#930505;
        transform:scale(1.02);
        box-shadow:0 0 0 1px #930505;
    }

    .delete-btn{
        background:#ff0000;
        color:#fff;
        border:none;
        padding:10px 25px;
        border-radius:40px;
        cursor:pointer;
        font-weight:bold;
        font-family:'Orbitron',sans-serif;
        transition:.3s;
        display:inline-flex;
        align-items:center;
        gap:8px;
        margin-right:10px;
    }

    .delete-btn:hover{
        background:#cc0000;
        transform:scale(1.02);
    }

    .action-buttons{
        display:flex;
        gap:10px;
        margin-top:15px;
        flex-wrap:wrap;
    }

    /* No Messages */
    .no-messages{
        text-align:center;
        padding:60px;
        background:#0a0a0a;
        border-radius:20px;
        border:1px solid #930505;
        color:#888;
    }

    .no-messages i{
        font-size:50px;
        margin-bottom:15px;
        display:block;
        color:#930505;
    }

    /* Responsive */
    @media(max-width:768px){

        header{
            flex-direction:column;
            text-align:center;
        }

        .message-card{
            padding:20px;
        }

        .action-buttons{
            flex-direction:column;
        }

        .send-btn,
        .delete-btn{
            width:100%;
            justify-content:center;
        }
    }
</style>
</head>
<body>

<header>
        <h1 class="site-logo">
    <img src="Icon.png" alt="Icon" class="logo-img">
</h1>
    <h1 class="site-logo"><i class="fa-solid fa-gavel"></i> Admin Panel</h1>
    <nav>
        <a href="index.php"><i class="fa-solid fa-home"></i> Home</a>
        <a href="admin_send_message.php"><i class="fa-solid fa-paper-plane"></i> Send Messages</a>
        <a href="admin.php"style="color:#930505;"><i class="fa-solid fa-paper-plane"> </i> Customer Messages</a>
    </nav>
</header>

<div class="messages-container">
    <?php if($messages && $messages->num_rows > 0): ?>
        <?php while($msg = $messages->fetch_assoc()): ?>
            <div class="message-card">
                <div class="msg-info">
                    <p><strong><i class="fa-solid fa-user"></i> Name:</strong> <?php echo htmlspecialchars($msg["user_name"]); ?></p>
                    <p><strong><i class="fa-solid fa-envelope"></i> Email:</strong> <?php echo htmlspecialchars($msg["user_email"]); ?></p>
                    <p><strong><i class="fa-solid fa-tag"></i> Subject:</strong> <?php echo htmlspecialchars($msg["subject"]); ?></p>
                    <p><strong><i class="fa-regular fa-message"></i> Message:</strong> <?php echo nl2br(htmlspecialchars($msg["message"])); ?></p>
                </div>

                <?php
                $replies = $conn->query("SELECT * FROM message_replies WHERE message_id = ".intval($msg['id'])." ORDER BY created_at ASC");
                while($r = $replies->fetch_assoc()): ?>
                    <div class="reply-box">
                        <p class="reply-text"><strong><i class="fa-solid fa-reply"></i> Reply:</strong> <?php echo nl2br(htmlspecialchars($r['reply'])); ?></p>
                    </div>
                <?php endwhile; ?>

                <form method="POST">
                    <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                    <input type="hidden" name="user_id" value="<?php echo $msg['user_id']; ?>">
                    <textarea name="reply" rows="4" placeholder="✏️ Write your reply here..." required></textarea>
                    
                    <div class="action-buttons">
                        <button type="submit" name="send_reply" class="send-btn">
                            <i class="fa-solid fa-paper-plane"></i> Send Reply
                        </button>
                    </div>
                </form>

                <form method="POST" style="margin-top: 10px;">
                    <input type="hidden" name="delete_id" value="<?php echo $msg['id']; ?>">
                    <button type="submit" name="delete_message" class="delete-btn" onclick="return confirm('Delete this message?')">
                        <i class="fa-solid fa-trash"></i> Delete Message
                    </button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-messages">
            <i class="fa-regular fa-envelope" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
            No Messages Yet
        </div>
    <?php endif; ?>
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