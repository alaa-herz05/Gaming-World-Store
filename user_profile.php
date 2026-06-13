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

if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$profile_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT id, name, email, image FROM users WHERE id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$user_result = $stmt->get_result();

if (!$user_result || $user_result->num_rows === 0) {
    die("User not found");
}

$user = $user_result->fetch_assoc();

$followers_result = $conn->query("
    SELECT COUNT(*) AS total 
    FROM follows 
    WHERE following_id = '$profile_id'
");

$following_result = $conn->query("
    SELECT COUNT(*) AS total 
    FROM follows 
    WHERE follower_id = '$profile_id'
");

$followers_count = $followers_result->fetch_assoc()['total'];
$following_count = $following_result->fetch_assoc()['total'];
$current_user = $_SESSION['user_id'];
$userImage = "";
$imgResult = $conn->query("SELECT image FROM users WHERE id='$current_user'");
if ($imgResult && $imgResult->num_rows > 0) {
    $imgRow = $imgResult->fetch_assoc();
    $userImage = $imgRow['image'];
}
$follow_check = $conn->prepare("
SELECT id FROM follows
WHERE follower_id = ?
AND following_id = ?
");

$follow_check->bind_param("ii", $current_user, $profile_id);
$follow_check->execute();

$is_following = $follow_check->get_result()->num_rows > 0;
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>User Profile - Gaming World</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="A gaming store offering the best games at competitive prices">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="Icon.png">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: #000;
        color: #fff;
        font-family: 'Orbitron', sans-serif;
        min-height: 100vh;
    }

    /* Header */
    header {
        background: #000;
        border-bottom: 2px solid #930505;
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .site-logo {
        color: #930505;
        font-family: 'Orbitron', sans-serif;
        font-size: 28px;
        font-weight: 900;
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
        box-shadow:0 0 20px rgba(147,5,5,.95),0 0 40px rgba(147,5,5,.45);
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

    .nav-profile-img {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
    }

    .default-nav-icon {
        background: #930505;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .default-nav-icon i {
        color: #000;
        font-size: 16px;
    }

    .nav-username {
        color: #930505;
        font-size: 14px;
    }

    .bell-link {
        position: relative;
    }

    .bell-count {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #ff0000;
        color: #fff;
        font-size: 10px;
        padding: 2px 5px;
        border-radius: 50%;
    }

    nav {
        display: flex;
        gap: 20px;
    }

    nav a {
        color: #fff;
        text-decoration: none;
        font-family: 'Orbitron', sans-serif;
        transition: 0.3s;
    }

    nav a:hover {
        color: #930505;
    }

    /* Profile Card - مثل تصميم Contact Me */
    .profile-card {
        max-width: 550px;
        margin: 50px auto;
        background: #0a0a0a;
        border: 2px solid #930505;
        border-radius: 30px;
        padding: 40px 30px;
        text-align: center;
        color: white;
        transition: 0.3s;
    }

    .profile-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0 30px rgba(147, 5, 5, 0.2);
    }

    /* Profile Photo */
    .profile-photo {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #930505;
        box-shadow: 0 0 25px rgba(147, 5, 5, 0.5);
        transition: 0.3s;
    }

    .profile-photo:hover {
        transform: scale(1.05);
        box-shadow: 0 0 35px rgba(147, 5, 5, 0.7);
    }

    .default-profile {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        border: 3px solid #930505;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #1a1a1a;
        transition: 0.3s;
    }

    .default-profile i {
        font-size: 60px;
        color: #930505;
    }

    .default-profile:hover {
        transform: scale(1.05);
        box-shadow: 0 0 25px rgba(147, 5, 5, 0.5);
    }

    /* Profile Info */
    .profile-name {
        color: #930505;
        margin-top: 20px;
        font-size: 28px;
    }

    .profile-email {
        color: #aaa;
        margin-bottom: 25px;
        font-size: 14px;
    }

    /* Stats Box - مثل تصميم Contact Me */
    .profile-stats {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 25px;
        flex-wrap: wrap;
    }

    .stat-box {
        background: #1a1a1a;
        border: 1px solid #930505;
        border-radius: 20px;
        padding: 15px 25px;
        min-width: 100px;
        transition: 0.3s;
    }

    .stat-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 0 15px rgba(147, 5, 5, 0.2);
    }

    .stat-box h3 {
        margin: 0;
        color: #930505;
        font-size: 28px;
    }

    .stat-box p {
        margin: 5px 0 0;
        color: white;
        font-size: 12px;
    }

    /* Follow Button - مثل أزرار Contact Me */
    .follow-btn {
        background: #930505;
        color: white;
        border: none;
        padding: 12px 35px;
        border-radius: 40px;
        cursor: pointer;
        font-weight: bold;
        font-family: 'Orbitron', sans-serif;
        font-size: 16px;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
    }

    .follow-btn:hover {
        background: #000;
        transform: scale(1.05);
    }

    .following-btn {
        background: #555;
    }

    .following-btn:hover {
        background: #ff0000;
    }

    /* Chat Button - مثل أزرار Contact Me */
    .chat-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: #930505;
        color: white;
        padding: 12px 35px;
        border-radius: 40px;
        text-decoration: none;
        font-weight: bold;
        font-size: 16px;
        font-family: 'Orbitron', sans-serif;
        transition: 0.3s;
        margin-top: 20px;
    }

    .chat-btn:hover {
        background: #000;
        transform: scale(1.05);
    }

    /* Friend Badge */
    .friend-badge {
        background: rgba(0, 255, 102, 0.1);
        border: 1px solid #00ff66;
    }

    .friend-badge h3 {
        color: #00ff66 !important;
    }

    /* Responsive */
    @media (max-width: 768px) {
        header {
            flex-direction: column;
            text-align: center;
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
        
        .profile-card {
            margin: 30px 20px;
            padding: 30px 20px;
        }
        
        .profile-stats {
            gap: 15px;
        }
        
        .stat-box {
            padding: 10px 15px;
            min-width: 80px;
        }
        
        .stat-box h3 {
            font-size: 22px;
        }
    }
</style>
</head>
<body>

<header>
        <h1 class="site-logo">
    <img src="Icon.png" alt="Icon" class="logo-img">
</h1>
    <h1 class="site-logo"><i class="fa-solid fa-user"></i> User Profile</h1>
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

            <a href="my_orders.php" class="magic-nav-item" title="Orders">
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
        <a href="users.php"><i class="fa-solid fa-user-plus"></i> Add Friends</a>
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

<div class="profile-card">
    <?php if(!empty($user['image'])): ?>
        <img src="<?php echo htmlspecialchars($user['image']); ?>" class="profile-photo">
    <?php else: ?>
        <div class="default-profile">
            <i class="fa-solid fa-user-astronaut"></i>
        </div>
    <?php endif; ?>

    <h2 class="profile-name"><?php echo htmlspecialchars($user['name']); ?></h2>
    <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>

    <?php if($current_user != $profile_id): ?>
        <form action="follow.php" method="POST" style="margin-top:10px;">
            <input type="hidden" name="user_id" value="<?php echo $profile_id; ?>">
            <button type="submit" class="follow-btn <?php echo $is_following ? 'following-btn' : ''; ?>">
                <i class="fa-solid <?php echo $is_following ? 'fa-user-minus' : 'fa-user-plus'; ?>"></i>
                <?php echo $is_following ? ' Unfollow' : ' Follow'; ?>
            </button>
        </form>
    <?php endif; ?>

    <?php
    $mutual_follow = false;
    if($current_user != $profile_id){
        $mutualCheck = $conn->query("
            SELECT 
                (SELECT COUNT(*) FROM follows WHERE follower_id='$current_user' AND following_id='$profile_id') AS i_follow,
                (SELECT COUNT(*) FROM follows WHERE follower_id='$profile_id' AND following_id='$current_user') AS follows_me
        ");
        $mutualData = $mutualCheck->fetch_assoc();
        if($mutualData['i_follow'] > 0 && $mutualData['follows_me'] > 0){
            $mutual_follow = true;
        }
    }
    ?>

    <div class="profile-stats">
        <div class="stat-box">
            <h3><?php echo $followers_count; ?></h3>
            <p><i class="fa-solid fa-users"></i> Followers</p>
        </div>
        <div class="stat-box">
            <h3><?php echo $following_count; ?></h3>
            <p><i class="fa-solid fa-user-plus"></i> Following</p>
        </div>

        <?php
        $is_friend = false;
        if($current_user != $profile_id){
            $friend_check = $conn->query("
                SELECT 
                    (SELECT COUNT(*) FROM follows WHERE follower_id='$current_user' AND following_id='$profile_id') AS i_follow,
                    (SELECT COUNT(*) FROM follows WHERE follower_id='$profile_id' AND following_id='$current_user') AS follows_me
            ");
            $friend_data = $friend_check->fetch_assoc();
            if($friend_data['i_follow'] > 0 && $friend_data['follows_me'] > 0){
                $is_friend = true;
            }
        }
        ?>

        <?php if($is_friend): ?>
            <div class="stat-box friend-badge">
                <h3><i class="fa-solid fa-handshake"></i></h3>
                <p>Friend</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if($mutual_follow): ?>
        <a href="messages_room.php?id=<?php echo $profile_id; ?>" class="chat-btn">
            <i class="fa-regular fa-message"></i> Chat Now
        </a>
    <?php endif; ?>
</div>

</body>
</html>