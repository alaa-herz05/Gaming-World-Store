<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = "";
$userImage = "";
$status = "";

$conn = new mysqli(
    "sql213.infinityfree.com",
    "if0_41900150",
    "Rany9NH3lawi",
    "if0_41900150_my_first_project"
);

if ($conn->connect_error) {
    die("Database Error");
}

$conn->set_charset("utf8mb4");

$sql = "SELECT email, image FROM users WHERE id='$user_id'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user_email = $user['email'];
    $userImage = $user['image'];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (!empty($message)) {
        $stmt = $conn->prepare("
           INSERT INTO messages 
        (user_id, user_name, user_email, subject, message)
        VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "issss",
            $user_id,
            $user_name,
            $user_email,
            $subject,
            $message
        );

        if ($stmt->execute()) {

            $message_id = $conn->insert_id;

            $auto_reply = "Hello,\n\nThank you for contacting Gaming World.\n\nWe received your message and will respond as soon as possible.\n\nBest regards,\nGaming World Team";

            $reply_stmt = $conn->prepare("
                INSERT INTO message_replies
                (message_id, user_id, reply)
                VALUES (?, ?, ?)
            ");

            $reply_stmt->bind_param(
                "iis",
                $message_id,
                $user_id,
                $auto_reply
            );

            $reply_stmt->execute();
            $reply_stmt->close();

            $status = "✓ Message sent successfully";
        } else {
            $status = "✗ An error occurred while sending";
        }

        $stmt->close();
    } else {
        $status = "✗ Please write a message";
    }
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A gaming store offering the best games at competitive prices">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="Icon.png">
    <title>Contact Us - Gaming World</title>
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
    flex-direction:column;
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
    gap:18px;
}

.site-logo{
    color:#930505;
    font-family:'Orbitron',sans-serif;
    font-size:28px;
    font-weight:900;
    text-shadow:0 0 12px rgba(147,5,5,.65);
}
.logo-img{
    width:110px;
    max-width:100%;
    object-fit:contain;
    filter:
        drop-shadow(0 0 10px rgba(147,5,5,.7))
        drop-shadow(0 0 20px rgba(147,5,5,.35));
}
.auth-links{
    position:relative;
    min-height:76px;
    background:#070707;
    border:1px solid rgba(147,5,5,.75);
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:0;
    padding:0 14px;
    box-shadow:0 0 18px rgba(147,5,5,.25), inset 0 0 18px rgba(147,5,5,.08);
}

.auth-links a{
    color:#fff;
    text-decoration:none;
    transition:.45s cubic-bezier(.68,-.55,.265,1.55);
    font-size:18px;
}

/* Magic Navigation */
.magic-nav-item{
    position:relative;
    width:74px;
    height:74px;
    display:flex;
    align-items:center;
    justify-content:center;
    z-index:2;
}

.magic-nav-item .magic-icon{
    position:relative;
    display:flex;
    align-items:center;
    justify-content:center;
    width:42px;
    height:42px;
    border-radius:50%;
    color:#fff;
    transition:.45s cubic-bezier(.68,-.55,.265,1.55);
    overflow:hidden;
}

.magic-nav-item .magic-icon i{
    font-size:20px;
    position:relative;
    z-index:2;
    transition:.35s ease;
}

.magic-nav-item .magic-text{
    position:absolute;
    bottom:8px;
    left:50%;
    transform:translate(-50%,18px);
    color:#930505;
    font-size:10px;
    font-weight:700;
    letter-spacing:.03em;
    opacity:0;
    pointer-events:none;
    white-space:nowrap;
    transition:.35s ease;
    text-shadow:0 0 8px rgba(147,5,5,.65);
}

/* Hover Animation فقط عند الماوس */
.magic-nav-item:hover .magic-icon{
    transform:translateY(-34px) scale(1.08);
    background:#930505;
    color:#000;
    box-shadow:
        0 0 20px rgba(147,5,5,.95),
        0 0 40px rgba(147,5,5,.45);
}

.magic-nav-item:hover .magic-icon i{
    transform:scale(1.08);
}

.magic-nav-item:hover .magic-text{
    opacity:1;
    transform:translate(-50%,0);
}

.welcome-user-box{
    padding:0;
    border:none;
    background:transparent;
}

.welcome-user-box .magic-icon{
    border:1px solid rgba(147,5,5,.8);
}

.nav-profile-img{
    width:100%;
    height:100%;
    border-radius:50%;
    object-fit:cover;
}

.default-nav-icon{
    background:#930505;
    display:flex;
    align-items:center;
    justify-content:center;
}

.default-nav-icon i{
    color:#000;
    font-size:16px;
}

.nav-username{
    display:none;
}

.bell-link{
    position:relative;
}

.bell-count{
    position:absolute;
    top:7px;
    right:13px;
    background:#ff0000;
    color:#fff;
    font-size:10px;
    padding:2px 5px;
    border-radius:50%;
    z-index:4;
    box-shadow:0 0 10px rgba(255,0,0,.8);
    transition:.35s ease;
}

.magic-nav-item:hover .bell-count{
    top:-25px;
    transform:scale(1.08);
}

nav{
    display:flex;
    gap:20px;
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

/* Hero Section */
.hero-contact{
    text-align:center;
    padding:40px 20px 20px;
}

.hero-contact h1{
    font-size:48px;
    color:#930505;
    margin-bottom:10px;
    text-shadow:0 0 20px rgba(147,5,5,.3);
}

.hero-contact p{
    color:#aaa;
    font-size:16px;
}

/* Main Container */
.contact-container{
    max-width:900px;
    margin:0 auto 60px;
    padding:0 20px;
    flex:1;
}

/* Contact Card */
.contact-card{
    background:rgba(10,10,10,.95);
    border:2px solid #930505;
    border-radius:25px;
    padding:50px 45px;
    transition:.3s;
}

.contact-card:hover{
    transform:translateY(-5px);
    box-shadow:0 0 30px rgba(147,5,5,.2);
}

/* User Info */
.user-info{
    background:linear-gradient(135deg,rgba(147,5,5,.15),rgba(147,5,5,.05));
    border:1px solid #930505;
    border-radius:25px;
    padding:30px;
    text-align:center;
    margin-bottom:40px;
}

.user-info .user-icon{
    width:90px;
    height:90px;
    background:#930505;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:0 auto 20px;
}

.user-info .user-icon i{
    font-size:45px;
    color:#000;
}

.user-info .user-name{
    color:#930505;
    font-size:28px;
    font-weight:bold;
    margin-bottom:8px;
}

.user-info .user-role{
    color:#aaa;
    font-size:15px;
}

/* Form Groups */
.form-group{
    margin-bottom:30px;
}

.form-group label{
    display:block;
    color:#930505;
    font-weight:bold;
    margin-bottom:12px;
    font-size:16px;
}

.form-group label i{
    margin-left:10px;
    font-size:18px;
}

.form-group input,
.form-group textarea{
    width:100%;
    background:#1a1a1a;
    color:#fff;
    border:1px solid #930505;
    padding:16px 20px;
    border-radius:15px;
    font-size:16px;
    font-family:Arial,sans-serif;
    transition:.3s;
}

.form-group input:focus,
.form-group textarea:focus{
    outline:none;
    border-color:#b30a0a;
    box-shadow:0 0 15px rgba(147,5,5,.2);
    background:#000;
}

.form-group textarea{
    resize:vertical;
    min-height:160px;
}

/* Send Button */
.send-btn{
    width:100%;
    background:#930505;
    color:#fff;
    border:none;
    padding:16px 32px;
    border-radius:50px;
    font-size:18px;
    font-weight:bold;
    font-family:'Orbitron',sans-serif;
    cursor:pointer;
    transition:.3s;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:12px;
    margin-top:20px;
}

.send-btn i{
    font-size:20px;
}

.send-btn:hover{
    background:#000;
    transform:scale(1.02);
    box-shadow:0 5px 25px rgba(147,5,5,.4);
}

/* Status Message */
.status-message{
    text-align:center;
    padding:15px;
    border-radius:12px;
    margin-bottom:30px;
    font-weight:bold;
    font-size:15px;
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

/* Footer */
footer{
    text-align:center;
    padding:30px;
    background:#000;
    border-top:1px solid #930505;
    margin-top:40px;
}

footer p{
    color:#930505;
    font-family:'Orbitron',sans-serif;
    font-size:12px;
    margin:5px 0;
}

footer a{
    color:#930505;
    text-decoration:none;
    transition:.3s;
}

footer a:hover{
    color:#b30a0a;
}

.footer-logo{
    color:#930505;
    font-size:18px;
    font-weight:bold;
    margin-bottom:15px;
}

.footer-links{
    display:flex;
    justify-content:center;
    gap:20px;
    margin-top:15px;
}

.footer-links a{
    color:#666;
    font-size:11px;
}

.footer-links a:hover{
    color:#930505;
}

/* Responsive */
@media(max-width:768px){

    header{
        flex-direction:column;
        text-align:center;
    }

    .auth-links{
        width:100%;
        max-width:430px;
        overflow-x:auto;
        justify-content:flex-start;
        padding:0 10px;
        scrollbar-width:none;
    }

    .auth-links::-webkit-scrollbar{
        display:none;
    }

    .magic-nav-item{
        min-width:64px;
        width:64px;
    }

    .magic-nav-item .magic-text{
        font-size:9px;
    }

    .hero-contact h1{
        font-size:32px;
    }

    .contact-card{
        padding:25px 20px;
    }

    .user-info .user-name{
        font-size:22px;
    }

    .user-info .user-icon{
        width:70px;
        height:70px;
    }

    .user-info .user-icon i{
        font-size:35px;
    }

    .send-btn{
        padding:12px 20px;
        font-size:14px;
    }

    .form-group input,
    .form-group textarea{
        padding:12px 15px;
        font-size:14px;
    }
}
</style>
</head>
<body>

<header>
        <h1 class="site-logo">
    <img src="Icon.png" alt="Icon" class="logo-img">
</h1>
    <div class="auth-links">
        <?php if(isset($_SESSION['user_name'])): ?>

            <a href="profile.php" class="magic-nav-item welcome-user-box" title="Profile">
                <span class="magic-icon">
                    <?php if(!empty($userImage)): ?>
                        <img src="<?php echo $userImage; ?>" class="nav-profile-img">
                    <?php else: ?>
                        <span class="nav-profile-img default-nav-icon">
                            <i class="fa-solid fa-user"></i>
                        </span>
                    <?php endif; ?>
                </span>
                <span class="magic-text">Profile</span>
            </a>

            <a href="logout.php" class="magic-nav-item" title="Logout">
                <span class="magic-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
                <span class="magic-text">Logout</span>
            </a>

            <a href="my_messages.php" class="magic-nav-item" title="Messages">
                <span class="magic-icon"><i class="fa-solid fa-envelope"></i></span>
                <span class="magic-text">Messages</span>
            </a>

            <a href="friends.php" class="magic-nav-item" title="Friends">
                <span class="magic-icon"><i class="fa-solid fa-user-group"></i></span>
                <span class="magic-text">Friends</span>
            </a>

            <a href="my_orders.php" class="magic-nav-item" title="My Orders">
                <span class="magic-icon"><i class="fa-solid fa-box-open"></i></span>
                <span class="magic-text">Orders</span>
            </a>

            <a href="notifications.php" class="magic-nav-item bell-link" title="Notifications">
                <span class="magic-icon"><i class="fa-solid fa-bell"></i></span>
                <span class="magic-text">Notifications</span>
                <?php
                if(isset($_SESSION['user_id'])) {
                    $current_user = $_SESSION['user_id'];
                    $notif_query = $conn->query("SELECT COUNT(*) AS total FROM notifications WHERE user_id = '$current_user' AND is_read = 0");
                    if($notif_query){
                        $notif_data = $notif_query->fetch_assoc();
                        if($notif_data['total'] > 0):
                ?>
                        <span class="bell-count"><?php echo $notif_data['total']; ?></span>
                <?php
                        endif;
                    }
                }
                ?>
            </a>

        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="signup.php">Create Account</a>
        <?php endif; ?>
    </div>
<nav>
    <a href="index.php" >
        <i class="fa-solid fa-house"></i> Home
    </a>

    <a href="cart.php">
        <i class="fa-solid fa-cart-shopping"></i> Cart
    </a>

    <a href="P2.php" style="color:#930505;">
        <i class="fa-solid fa-headset"></i> Contact Us
    </a>
</nav>
</header>

<div class="hero-contact">
    <h1><i class="fa-solid fa-headset"></i> Contact Us</h1>
    <p>We're here to help and answer any question you might have</p>
</div>

<main class="contact-container">
    <div class="contact-card">
        <div class="user-info">
            <div class="user-icon">
                <i class="fa-solid fa-user-astronaut"></i>
            </div>
            <div class="user-name"><?php echo $user_name; ?></div>
            <div class="user-role">Gaming Enthusiast</div>
        </div>

        <?php if(!empty($status)): ?>
            <div class="status-message <?php echo (strpos($status, '✓') !== false) ? 'status-success' : 'status-error'; ?>">
                <i class="fas <?php echo (strpos($status, '✓') !== false) ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $status; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label><i class="fa-solid fa-tag"></i> Subject</label>
                <input 
                    type="text"
                    name="subject"
                    placeholder="Enter subject..."
                    required
                >
            </div>

            <div class="form-group">
                <label><i class="fa-solid fa-pen"></i> Message</label>
                <textarea 
                    name="message" 
                    rows="5" 
                    placeholder="Write your message here..."
                    required
                ></textarea>
            </div>

            <button type="submit" class="send-btn">
                <i class="fa-solid fa-paper-plane"></i>
                Send Message
            </button>
        </form>
    </div>
</main>

<footer>
    <div class="footer-logo">
        <i class="fa-solid fa-gamepad"></i> GAMING WORLD
    </div>
    <p>&copy;  Gaming World rights reserved</p>
    <div class="footer-links">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Support</a>
    </div>
</footer>

<?php $conn->close(); ?>
</body>
</html>