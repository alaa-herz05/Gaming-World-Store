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

$current_user = $_SESSION['user_id'];

// Get user image for header
$userImage = "";
$imgResult = $conn->query("SELECT image FROM users WHERE id='$current_user'");
if ($imgResult && $imgResult->num_rows > 0) {
    $imgRow = $imgResult->fetch_assoc();
    $userImage = $imgRow['image'];
}

// Get followers count
$count_query = $conn->prepare("SELECT COUNT(*) AS total FROM follows WHERE following_id = ?");
$count_query->bind_param("i", $current_user);
$count_query->execute();
$followers_count = $count_query->get_result()->fetch_assoc()['total'];

// Get followers (people who follow me)
$followers = $conn->prepare("
    SELECT users.id, users.name, users.email, users.image
    FROM follows
    INNER JOIN users ON follows.follower_id = users.id
    WHERE follows.following_id = ?
    ORDER BY users.name ASC
");
$followers->bind_param("i", $current_user);
$followers->execute();
$followers_result = $followers->get_result();

// Get notifications count
$notif_query = $conn->query("SELECT COUNT(*) AS total FROM notifications WHERE user_id = '$current_user' AND is_read = 0");
$notif_count = ($notif_query && $notif_query->num_rows > 0) ? $notif_query->fetch_assoc()['total'] : 0;
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>My Followers - Gaming World</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="icon.png">
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
            box-shadow:0 0 20px rgba(147,5,5,.95), 0 0 40px rgba(147,5,5,.45);
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
            display:block;
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

        /* Container */
        .container{
            max-width:800px;
            margin:40px auto;
            padding:0 20px;
        }

        /* Stats Bar */
        .stats-bar{
            background:#0a0a0a;
            border:1px solid #930505;
            border-radius:20px;
            padding:20px;
            text-align:center;
            margin-bottom:30px;
            box-shadow:0 0 20px rgba(147,5,5,.12);
        }

        .stats-bar h2{
            color:#930505;
            font-size:36px;
            margin-bottom:5px;
        }

        .stats-bar p{
            color:#aaa;
            font-size:14px;
        }

        /* Back Button */
        .back-btn{
            display:inline-flex;
            align-items:center;
            gap:8px;
            background:#1a1a1a;
            border:1px solid #930505;
            padding:10px 25px;
            border-radius:40px;
            text-decoration:none;
            color:#fff;
            margin-bottom:20px;
            transition:.3s;
            font-family:'Orbitron',sans-serif;
            font-weight:bold;
        }

        .back-btn:hover{
            background:#930505;
            color:#000;
            transform:scale(1.03);
        }

        /* User Card */
        .user-card{
            background:#0a0a0a;
            border:1px solid #930505;
            border-radius:20px;
            padding:20px;
            margin-bottom:15px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            transition:.3s;
        }

        .user-card:hover{
            transform:translateY(-3px);
            box-shadow:0 0 20px rgba(147,5,5,.2);
        }

        .user-info{
            display:flex;
            align-items:center;
            gap:15px;
        }

        .user-img{
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
            background:#1a1a1a;
            border:2px solid #930505;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .default-user i{
            font-size:28px;
            color:#930505;
        }

        .user-details{
            flex:1;
        }

        .user-name{
            margin:0 0 5px;
        }

        .user-name a{
            color:#930505;
            text-decoration:none;
            font-size:18px;
            font-weight:bold;
            transition:.3s;
        }

        .user-name a:hover{
            color:#b30a0a;
        }

        .user-email{
            color:#888;
            font-size:12px;
        }

        /* Action Buttons */
        .action-buttons{
            display:flex;
            gap:10px;
        }

        .chat-btn{
            background:#930505;
            color:#fff;
            border:none;
            padding:10px 20px;
            border-radius:40px;
            cursor:pointer;
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
            box-shadow:0 0 0 1px #930505;
        }

        .unfollow-btn{
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

        .unfollow-btn:hover{
            background:#cc0000;
            transform:scale(1.05);
        }

        /* No Results */
        .no-results{
            text-align:center;
            padding:60px;
            background:#0a0a0a;
            border-radius:20px;
            border:1px solid #930505;
            color:#888;
        }

        .no-results i{
            font-size:48px;
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

            .user-card{
                flex-direction:column;
                text-align:center;
                gap:15px;
            }

            .user-info{
                flex-direction:column;
            }

            .action-buttons{
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
    <h1 class="site-logo"><i class="fa-solid fa-users"></i> My Followers</h1>
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
        <a href="index.php"><i class="fa-solid fa-home"></i> Home</a>
        <a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a>
    </nav>
</header>

<div class="container">
    <a href="profile.php" class="back-btn">
        <i class="fa-solid fa-arrow-right"></i> Back to Profile
    </a>

    <div class="stats-bar">
        <h2><?php echo $followers_count; ?></h2>
        <p><i class="fa-solid fa-users"></i> People follow you</p>
    </div>

    <?php if($followers_result && $followers_result->num_rows > 0): ?>
        <?php while($follower = $followers_result->fetch_assoc()): ?>
            <?php
            // Check if current user follows this person back
            $follow_back_check = $conn->prepare("SELECT id FROM follows WHERE follower_id = ? AND following_id = ?");
            $follow_back_check->bind_param("ii", $current_user, $follower['id']);
            $follow_back_check->execute();
            $follow_back = $follow_back_check->get_result()->num_rows > 0;
            ?>
            <div class="user-card">
                <div class="user-info">
                    <?php if(!empty($follower['image'])): ?>
                        <img src="<?php echo htmlspecialchars($follower['image']); ?>" class="user-img">
                    <?php else: ?>
                        <div class="default-user">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    <?php endif; ?>
                    <div class="user-details">
                        <h3 class="user-name">
                            <a href="user_profile.php?id=<?php echo $follower['id']; ?>">
                                <?php echo htmlspecialchars($follower['name']); ?>
                            </a>
                        </h3>
                        <p class="user-email"><?php echo htmlspecialchars($follower['email']); ?></p>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="messages_room.php?id=<?php echo $follower['id']; ?>" class="chat-btn">
                        <i class="fa-regular fa-message"></i> Chat
                    </a>
                    <?php if($follow_back): ?>
                        <form action="unfollow.php" method="POST" style="margin:0;">
                            <input type="hidden" name="user_id" value="<?php echo $follower['id']; ?>">
                            <button type="submit" class="unfollow-btn" onclick="return confirm('Unfollow this user?')">
                                <i class="fa-solid fa-user-minus"></i> Unfollow
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-results">
            <i class="fa-regular fa-face-frown"></i>
            No followers yet
            <p style="margin-top: 10px; font-size: 12px;">
                Share your profile to get more followers!
            </p>
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