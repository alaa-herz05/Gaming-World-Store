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

if ($conn->connect_error) {
    die("Database Error");
}

$conn->set_charset("utf8mb4");

$user_id = intval($_SESSION['user_id']);
$userImage = "";

$getUserImg = $conn->query("
    SELECT image 
    FROM users 
    WHERE id = '$user_id'
");

if($getUserImg && $getUserImg->num_rows > 0){
    $imgData = $getUserImg->fetch_assoc();
    $userImage = $imgData['image'];
}
$groups = $conn->query("
    SELECT g.*
    FROM groups_list g
    INNER JOIN group_members gm ON g.id = gm.group_id
    WHERE gm.user_id = '$user_id'
    ORDER BY g.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>Groups - Gaming World</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="Icon.png">
    <link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#c94d06">
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
    box-shadow:
        0 0 20px rgba(147,5,5,.95),
        0 0 40px rgba(147,5,5,.45);
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
    width:32px;
    height:32px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid #930505;
    display:block;
}

.default-nav-icon{
    width:32px;
    height:32px;
    border-radius:50%;
    background:#1a1a1a;
    border:2px solid #930505;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
}

.default-nav-icon i{
    color:#930505;
    font-size:14px;
}

.nav-username{
    color:#930505;
    font-size:14px;
}

.bell-link{
    position:relative;
}

.bell-count{
    position:absolute;
    top:-8px;
    right:-8px;
    background:#ff0000;
    color:#fff;
    font-size:10px;
    padding:2px 5px;
    border-radius:50%;
}

nav{
    display:flex;
    gap:18px;
    flex-wrap:wrap;
}

nav a{
    color:#fff;
    text-decoration:none;
    transition:.3s;
}

nav a:hover{
    color:#930505;
}

/* Main Container */
.container{
    max-width:900px;
    margin:35px auto;
    padding:0 18px;
}

/* Top Actions */
.top-actions{
    display:flex;
    justify-content:center;
    margin-bottom:25px;
}

/* Buttons */
.btn{
    background:#930505;
    color:#fff;
    border:none;
    text-decoration:none;
    padding:12px 24px;
    border-radius:40px;
    font-weight:bold;
    display:inline-flex;
    align-items:center;
    gap:8px;
    transition:.3s;
}

.btn:hover{
    background:#000;
    color:#930505;
    box-shadow:0 0 0 1px #930505;
    transform:scale(1.03);
}

/* Group Card */
.group-card{
    background:#0a0a0a;
    border:1px solid #930505;
    border-radius:18px;
    padding:18px;
    margin-bottom:15px;
    display:flex;
    align-items:center;
    gap:15px;
    text-decoration:none;
    color:#fff;
    transition:.3s;
}

.group-card:hover{
    transform:translateX(-5px);
    box-shadow:0 0 25px rgba(147,5,5,.25);
}

.group-img{
    width:58px;
    height:58px;
    border-radius:50%;
    border:2px solid #930505;
    object-fit:cover;
    background:#111;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#930505;
    font-size:25px;
    flex-shrink:0;
}

.group-name{
    color:#930505;
    font-size:18px;
    margin-bottom:5px;
}

.group-card small{
    color:#aaa;
}

/* Empty State */
.empty{
    text-align:center;
    color:#888;
    border:1px solid #930505;
    border-radius:18px;
    padding:45px;
    background:#0a0a0a;
}

.empty i{
    font-size:45px;
    color:#930505;
    margin-bottom:12px;
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

    .group-card{
        flex-direction:column;
        text-align:center;
    }

    .group-name{
        font-size:16px;
    }

    .btn{
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
    <h1 class="site-logo"><i class="fa-solid fa-users"></i> Groups</h1>
 <div class="auth-links">
        <?php if(isset($_SESSION['user_name'])): ?>
            <a href="profile.php" class="magic-nav-item welcome-user-box" title="Profile">
                <span class="magic-icon">
            <?php if(!empty($userImage)): ?>
                <img src="<?php echo htmlspecialchars($userImage); ?>" class="nav-profile-img" alt="Profile">
            <?php else: ?>
                <div class="nav-profile-img default-nav-icon">
                    <i class="fa-solid fa-user"></i>
                </div>
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

<div class="container">
    <div class="top-actions">
        <a href="create_group.php" class="btn"><i class="fa-solid fa-plus"></i> Create Group</a>
    </div>

    <?php if($groups && $groups->num_rows > 0): ?>
        <?php while($g = $groups->fetch_assoc()): ?>
            <a href="group_room.php?id=<?php echo $g['id']; ?>" class="group-card">
                <?php if(!empty($g['group_image'])): ?>
                    <img src="<?php echo htmlspecialchars($g['group_image']); ?>" class="group-img">
                <?php else: ?>
                    <div class="group-img"><i class="fa-solid fa-users"></i></div>
                <?php endif; ?>
                <div>
                    <div class="group-name"><?php echo htmlspecialchars($g['group_name']); ?></div>
                    <small>Open group conversation</small>
                </div>
            </a>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty">
            <i class="fa-regular fa-comments" style="font-size:45px;color:#c94d06;margin-bottom:12px;"></i>
            <p>No groups yet</p>
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
