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

$current_user = $_SESSION['user_id'];
$userImage = "";
$imgResult = $conn->query("SELECT image FROM users WHERE id='$current_user'");
if ($imgResult && $imgResult->num_rows > 0) {
    $imgRow = $imgResult->fetch_assoc();
    $userImage = $imgRow['image'];
}
$friends = $conn->query("
SELECT users.id, users.name, users.email, users.image
FROM follows
INNER JOIN users
ON follows.following_id = users.id
WHERE follows.follower_id = '$current_user'
");
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>Friends - Gaming World</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="Icon.png">
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

/* Search Container */
/* Search Container */
.search-container{
    max-width:700px;
    margin:30px auto 20px;
    padding:0 20px;
}

#friendSearch{
    width:100%;
    padding:14px 20px;
    background:#1a1a1a;
    border:1px solid #930505;
    border-radius:40px;
    color:#fff;
    font-size:14px;
    font-family:'Orbitron',sans-serif;
    outline:none;
    transition:.3s;
}

#friendSearch:focus{
    box-shadow:0 0 15px rgba(147,5,5,.3);
    border-color:#b30a0a;
}

#friendSearch::placeholder{
    color:#666;
}

/* Groups Shortcut */
.groups-shortcut{
    max-width:700px;
    margin:0 auto 25px;
    padding:0 20px;
}

.groups-card{
    width:100%;
    background:#0a0a0a;
    border:1px solid #930505;
    border-radius:20px;
    padding:18px 22px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:15px;
    color:#fff;
    text-decoration:none;
    transition:.3s;
    box-shadow:0 0 15px rgba(147,5,5,.12);
}

.groups-card:hover{
    transform:translateY(-3px);
    box-shadow:0 0 25px rgba(147,5,5,.25);
    border-color:#b30a0a;
}

.groups-left{
    display:flex;
    align-items:center;
    gap:15px;
}

.groups-icon{
    width:52px;
    height:52px;
    border-radius:50%;
    background:#1a1a1a;
    border:2px solid #930505;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#930505;
    font-size:24px;
    flex-shrink:0;
}

.groups-title{
    color:#930505;
    font-size:18px;
    font-weight:bold;
    margin-bottom:5px;
}

.groups-subtitle{
    color:#888;
    font-size:12px;
}

.groups-arrow{
    color:#930505;
    font-size:20px;
    flex-shrink:0;
}

/* Friends Container */
.friends-container{
    max-width:700px;
    margin:0 auto;
    padding:0 20px 40px;
}

/* Friend Card */
.friend-card{
    background:#0a0a0a;
    border:1px solid #930505;
    border-radius:20px;
    padding:20px;
    margin-bottom:20px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    color:#fff;
    transition:.3s;
}

.friend-card:hover{
    transform:translateY(-3px);
    box-shadow:0 0 20px rgba(147,5,5,.2);
}

.friend-info{
    display:flex;
    align-items:center;
    gap:15px;
}

.friend-img{
    width:60px;
    height:60px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid #930505;
}

.default-user{
    width:60px;
    height:60px;
    border-radius:50%;
    border:2px solid #930505;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#1a1a1a;
}

.default-user i{
    font-size:28px;
    color:#930505;
}

.friend-name{
    margin:0;
}

.friend-name a{
    color:#930505;
    text-decoration:none;
    font-size:18px;
    font-weight:bold;
    transition:.3s;
}

.friend-name a:hover{
    color:#b30a0a;
}

/* Action Buttons */
.action-buttons{
    display:flex;
    align-items:center;
    gap:10px;
}

.chat-btn{
    background:#930505;
    color:#fff;
    border:none;
    padding:10px 20px;
    border-radius:40px;
    text-decoration:none;
    font-weight:bold;
    font-family:'Orbitron',sans-serif;
    transition:.3s;
    display:inline-flex;
    align-items:center;
    gap:8px;
    font-size:13px;
}

.chat-btn:hover{
    background:#000;
    transform:scale(1.05);
}

.remove-btn{
    background:#ff0000;
    color:#fff;
    border:none;
    padding:10px 20px;
    border-radius:40px;
    cursor:pointer;
    font-weight:bold;
    font-family:'Orbitron',sans-serif;
    transition:.3s;
    display:inline-flex;
    align-items:center;
    gap:8px;
    font-size:13px;
}

.remove-btn:hover{
    background:#cc0000;
    transform:scale(1.05);
}

/* No Friends */
.no-friends{
    text-align:center;
    color:#888;
    font-weight:bold;
    margin-top:60px;
    padding:60px;
    background:#0a0a0a;
    border-radius:20px;
    border:1px solid #930505;
}

.no-friends i{
    font-size:48px;
    margin-bottom:15px;
    display:block;
    color:#930505;
}

/* No Result */
.no-result{
    text-align:center;
    color:#b30a0a;
    margin-top:20px;
    padding:20px;
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

    .friend-card{
        flex-direction:column;
        gap:15px;
        text-align:center;
    }

    .friend-info{
        flex-direction:column;
    }

    .action-buttons{
        width:100%;
        justify-content:center;
    }

    .chat-btn,
    .remove-btn{
        padding:8px 16px;
        font-size:12px;
    }
}
</style>
</head>
<body>

<header>
        <h1 class="site-logo">
    <img src="Icon.png" alt="Icon" class="logo-img">
</h1>
    <h1 class="site-logo"><i class="fa-solid fa-user-group"></i> My Friends</h1>
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

<div class="search-container">
    <input type="text" id="friendSearch" placeholder="🔍 Search friends by name..." onkeyup="filterFriends()">
</div>

<div class="groups-shortcut">
    <a href="groups.php" class="groups-card">
        <div class="groups-left">
            <div class="groups-icon">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <div class="groups-title">Groups</div>
                <div class="groups-subtitle">Create groups and chat with your friends</div>
            </div>
        </div>
        <div class="groups-arrow">
            <i class="fa-solid fa-chevron-left"></i>
        </div>
    </a>
</div>

<div class="friends-container" id="friendsList">
    <?php if($friends && $friends->num_rows > 0): ?>
        <?php while($friend = $friends->fetch_assoc()): ?>
            <div class="friend-card">
                <div class="friend-info">
                    <?php if(!empty($friend['image'])): ?>
                        <img src="<?php echo $friend['image']; ?>" class="friend-img">
                    <?php else: ?>
                        <div class="default-user"><i class="fa-solid fa-user"></i></div>
                    <?php endif; ?>
                    <div>
                        <h3 class="friend-name">
                            <a href="user_profile.php?id=<?php echo $friend['id']; ?>">
                                <?php echo htmlspecialchars($friend['name']); ?>
                            </a>
                        </h3>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="messages_room.php?id=<?php echo $friend['id']; ?>" class="chat-btn">
                        <i class="fa-regular fa-message"></i> Chat
                    </a>
                    <form action="remove_friend.php" method="POST" style="margin:0;">
                        <input type="hidden" name="friend_id" value="<?php echo $friend['id']; ?>">
                        <button type="submit" class="remove-btn" onclick="return confirm('Remove this friend?')">
                            <i class="fa-solid fa-user-minus"></i> Remove
                        </button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-friends">
            <i class="fa-regular fa-face-frown"></i>
            No Friends Yet
            <p style="margin-top: 10px; font-size: 12px;">
                <a href="users.php" style="color: #c94d06;">Add friends to start chatting!</a>
            </p>
        </div>
    <?php endif; ?>
</div>

<script>
function filterFriends() {
    let input = document.getElementById('friendSearch').value.toLowerCase().trim();
    let cards = document.getElementsByClassName('friend-card');
    let hasVisible = false;

    for (let i = 0; i < cards.length; i++) {
        let name = cards[i].querySelector('.friend-name').innerText.toLowerCase();
        
        if (name.includes(input)) {
            cards[i].style.display = "flex";
            hasVisible = true;
        } else {
            cards[i].style.display = "none";
        }
    }

    let listContainer = document.getElementById('friendsList');
    let existingNoResult = document.getElementById('noResult');
    
    if (!hasVisible && input !== "") {
        if (!existingNoResult) {
            let p = document.createElement('p');
            p.id = "noResult";
            p.className = "no-result";
            p.innerHTML = '<i class="fa-solid fa-search"></i> No matching friends found';
            listContainer.appendChild(p);
        }
    } else {
        if (existingNoResult) existingNoResult.remove();
    }
}
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