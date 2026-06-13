<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

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

$user_id = $_SESSION['user_id'];
$userImage = "";
$imgResult = $conn->query("SELECT image FROM users WHERE id='$user_id'");
if ($imgResult && $imgResult->num_rows > 0) {
    $imgRow = $imgResult->fetch_assoc();
    $userImage = $imgRow['image'];
}
$notif_count = 0;

$notif_query = $conn->query("
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = '$user_id'
    AND is_read = 0
");

if ($notif_query && $notif_query->num_rows > 0) {
    $notif_data = $notif_query->fetch_assoc();
    $notif_count = $notif_data['total'];
}

$notifications = $conn->query("
    SELECT *
    FROM notifications
    WHERE user_id = '$user_id'
    ORDER BY created_at DESC
");

$conn->query("
    UPDATE notifications
    SET is_read = 1
    WHERE user_id = '$user_id'
");
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A gaming store offering the best games at competitive prices">
    <title>Notifications - Gaming World</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="Icon.png">
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

/* Notifications Container */
/* Notifications Container */
.notifications-container{
    max-width:800px;
    margin:40px auto;
    padding:20px;
}

/* Notification Card */
.notification-card{
    background:#0a0a0a;
    border:1px solid #930505;
    border-radius:20px;
    padding:22px 25px;
    margin-bottom:18px;
    color:#fff;
    transition:.3s;
    position:relative;
    overflow:hidden;
}

.notification-card:hover{
    transform:translateX(5px);
    box-shadow:0 0 25px rgba(147,5,5,.2);
}

.notification-card::before{
    content:'';
    position:absolute;
    right:0;
    top:0;
    width:4px;
    height:100%;
    background:#930505;
}

.notification-card p{
    line-height:1.6;
    font-size:15px;
    margin-bottom:10px;
}

.notification-card a{
    color:#930505;
    text-decoration:none;
    font-weight:bold;
    transition:.3s;
}

.notification-card a:hover{
    color:#b30a0a;
    text-decoration:underline;
}

.notification-date{
    color:#888;
    font-size:12px;
    display:block;
    margin-top:8px;
}

.notification-date i{
    margin-left:5px;
}

/* No Notifications */
.no-notifications{
    text-align:center;
    color:#888;
    font-weight:bold;
    margin-top:60px;
    padding:60px;
    background:#0a0a0a;
    border-radius:20px;
    border:1px solid #930505;
}

.no-notifications i{
    font-size:48px;
    margin-bottom:15px;
    display:block;
    color:#930505;
}

/* Notification Icon */
.notif-icon{
    display:inline-block;
    width:32px;
    text-align:center;
    margin-left:10px;
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

    .notifications-container{
        padding:15px;
    }

    .notification-card{
        padding:18px;
    }

    .notification-card p{
        font-size:13px;
    }
}
</style>
</head>
<body>

<header>
        <h1 class="site-logo">
    <img src="Icon.png" alt="Icon" class="logo-img">
</h1>
    <h1 class="site-logo"><i class="fa-regular fa-bell"></i> Notifications</h1>
    <div class="auth-links">
        <?php if(isset($_SESSION['user_name'])): ?>

            <a href="profile.php" class="magic-nav-item welcome-user-box" title="Profile">
                <span class="magic-icon">
                <?php if(!empty($userImage)): ?>
                    <img src="<?php echo htmlspecialchars($userImage); ?>" class="nav-profile-img">
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

            <a href="my_orders.php" class="magic-nav-item" title="Orders">
                <span class="magic-icon"><i class="fa-solid fa-box-open"></i></span>
                <span class="magic-text">Orders</span>
            </a>

            <a href="notifications.php" class="magic-nav-item bell-link" title="Notifications">
                <span class="magic-icon"><i class="fa-solid fa-bell"></i></span>
                <span class="magic-text">Notifications</span>
                <?php if($notif_count > 0): ?>
                    <span class="bell-count"><?php echo $notif_count; ?></span>
                <?php endif; ?>
            </a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="signup.php">Create Account</a>
        <?php endif; ?>
    </div>
<nav>
    <a href="index.php">
        <i class="fa-solid fa-house"></i> Home
    </a>

    <a href="cart.php">
        <i class="fa-solid fa-cart-shopping"></i> Cart
    </a>

    <a href="P2.php">
        <i class="fa-solid fa-envelope"></i> Contact Us
    </a>
</nav>
</header>

<div class="notifications-container">
    <?php if($notifications && $notifications->num_rows > 0): ?>
        <?php while($notification = $notifications->fetch_assoc()): ?>
            <div class="notification-card">
                <p>
                    <?php
                    $raw_text = $notification['text'];
                    $clean_text = trim(strip_tags($raw_text));
                    $sender_id = $notification['sender_id'] ?? null;

                    $link = "#";
                    $name_text = $clean_text;
                    $action_text = "";
                    $icon = '<i class="fa-regular fa-bell"></i>';

                    if (
                        (
                            strpos($clean_text, "sent a message in") !== false ||
                            strpos($clean_text, "sent a text message in") !== false ||
                            strpos($clean_text, "sent a photo in") !== false ||
                            strpos($clean_text, "sent an image in") !== false ||
                            strpos($clean_text, "sent a video in") !== false ||
                            strpos($clean_text, "sent a voice message in") !== false ||
                            strpos($clean_text, "sent a sticker in") !== false
                        )
                        && !empty($notification['group_id'])
                    ) {
                        $link = "group_room.php?id=" . intval($notification['group_id']);
                        $name_text = $clean_text;
                        $action_text = "";

                        if (strpos($clean_text, "photo") !== false || strpos($clean_text, "image") !== false) {
                            $icon = '<i class="fa-regular fa-image"></i>';
                        }
                        elseif (strpos($clean_text, "video") !== false) {
                            $icon = '<i class="fa-solid fa-video"></i>';
                        }
                        elseif (strpos($clean_text, "voice") !== false) {
                            $icon = '<i class="fa-solid fa-microphone"></i>';
                        }
                        elseif (strpos($clean_text, "sticker") !== false) {
                            $icon = '<i class="fa-regular fa-face-smile"></i>';
                        }
                        else {
                            $icon = '<i class="fa-solid fa-users"></i>';
                        }
                    }
                    elseif (
                        (
                            strpos($clean_text, "sent you a message") !== false ||
                            strpos($clean_text, "sent you a photo") !== false ||
                            strpos($clean_text, "sent you an image") !== false ||
                            strpos($clean_text, "sent you a video") !== false ||
                            strpos($clean_text, "sent you a voice message") !== false ||
                            strpos($clean_text, "sent you a sticker") !== false
                        )
                        && strpos($clean_text, "Admin sent you a message") === false
                    ) {
                        $link = "messages_room.php?id=" . intval($sender_id);

                        $name_text = explode(" sent", $clean_text)[0];
                        $action_text = str_replace($name_text, "", $clean_text);

                        if (strpos($clean_text, "photo") !== false || strpos($clean_text, "image") !== false) {
                            $icon = '<i class="fa-regular fa-image"></i>';
                        }
                        elseif (strpos($clean_text, "video") !== false) {
                            $icon = '<i class="fa-solid fa-video"></i>';
                        }
                        elseif (strpos($clean_text, "voice") !== false) {
                            $icon = '<i class="fa-solid fa-microphone"></i>';
                        }
                        elseif (strpos($clean_text, "sticker") !== false) {
                            $icon = '<i class="fa-regular fa-face-smile"></i>';
                        }
                        else {
                            $icon = '<i class="fa-regular fa-envelope"></i>';
                        }
                    }
                    elseif (strpos($clean_text, "Admin sent you a message") !== false) {
                        $link = "my_messages.php";
                        $name_text = "Admin";
                        $action_text = " sent you a message";
                        $icon = '<i class="fa-solid fa-user-tie"></i>';
                    }
                    elseif (strpos($clean_text, "started following you") !== false) {
                        $link = "user_profile.php?id=" . $sender_id;
                        $name_text = trim(str_replace("started following you", "", $clean_text));
                        $action_text = " started following you";
                        $icon = '<i class="fa-solid fa-user-plus"></i>';
                    }
                    elseif (strpos($clean_text, "Order Has Been Delivered") !== false) {
                        $link = "my_messages.php";
                        $name_text = "Order";
                        $action_text = " Has Been Delivered";
                        $icon = '<i class="fa-solid fa-truck"></i>';
                    }
                    elseif (strpos($clean_text, "Your order has been confirmed") !== false) {
                        $link = "my_orders.php";
                        $icon = '<i class="fa-solid fa-check-circle"></i>';
                    }

                    echo '<span class="notif-icon">' . $icon . '</span>';
                    
                    if ($link !== "#"):
                    ?>
                        <a href="<?php echo htmlspecialchars($link); ?>">
                            <?php echo htmlspecialchars($name_text); ?>
                        </a>
                        <?php echo htmlspecialchars($action_text); ?>
                    <?php else: ?>
                        <?php echo htmlspecialchars($clean_text); ?>
                    <?php endif; ?>
                </p>
                <span class="notification-date">
                    <i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($notification['created_at']); ?>
                </span>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-notifications">
            <i class="fa-regular fa-bell-slash"></i>
            No Notifications Yet
            <p style="margin-top: 10px; font-size: 12px;">
                <a href="index.php" style="color: #c94d06;">Browse games and stay updated!</a>
            </p>
        </div>
    <?php endif; ?>
</div>

<script>
function loadNotifications(){
    fetch("load_notifications.php")
    .then(response => response.text())
    .then(data => {
        const notif = document.getElementById("notif-count");
        if(notif){
            if(data > 0){
                notif.innerText = data;
                notif.style.display = "flex";
            } else {
                notif.style.display = "none";
            }
        }
    });
}

setInterval(loadNotifications, 2000);
</script>

</body>
</html>