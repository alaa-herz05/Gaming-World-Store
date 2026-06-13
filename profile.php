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
    die("Connection failed");
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found");
}

// Get followers count
$followers_query = $conn->prepare("SELECT COUNT(*) AS count FROM follows WHERE following_id = ?");
$followers_query->bind_param("i", $user_id);
$followers_query->execute();
$followers_count = $followers_query->get_result()->fetch_assoc()['count'];

// Get following count
$following_query = $conn->prepare("SELECT COUNT(*) AS count FROM follows WHERE follower_id = ?");
$following_query->bind_param("i", $user_id);
$following_query->execute();
$following_count = $following_query->get_result()->fetch_assoc()['count'];

// Get notifications count
$notif_query = $conn->query("SELECT COUNT(*) AS total FROM notifications WHERE user_id = '$user_id' AND is_read = 0");
$notif_count = ($notif_query && $notif_query->num_rows > 0) ? $notif_query->fetch_assoc()['total'] : 0;
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="Icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>My Profile - Gaming World</title>
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

/* Hover Animation فقط */
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

/* Profile Container */
.profile-container{
    max-width:550px;
    margin:50px auto;
    padding:0 20px;
}

/* Profile Card */
.profile-card{
    background:#0a0a0a;
    border:2px solid #930505;
    border-radius:30px;
    padding:40px 35px;
    text-align:center;
    transition:.3s;
    box-shadow:0 0 18px rgba(147,5,5,.12);
}

.profile-card:hover{
    transform:translateY(-5px);
    box-shadow:0 0 30px rgba(147,5,5,.25);
}

/* Profile Image */
.profile-image-section{
    display:flex;
    flex-direction:column;
    align-items:center;
    margin-bottom:25px;
}

.profile-img{
    width:130px;
    height:130px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid #930505;
    box-shadow:0 0 25px rgba(147,5,5,.5);
    transition:.3s;
}

.profile-img:hover{
    transform:scale(1.05);
    box-shadow:0 0 35px rgba(147,5,5,.75);
}

.default-img{
    background:#1a1a1a;
    display:flex;
    align-items:center;
    justify-content:center;
}

.default-img i{
    font-size:60px;
    color:#930505;
}

/* Stats */
.profile-stats{
    display:flex;
    justify-content:center;
    gap:30px;
    margin:25px 0;
    flex-wrap:wrap;
}

.stat-box{
    background:#1a1a1a;
    border:1px solid #930505;
    border-radius:20px;
    padding:15px 25px;
    min-width:120px;
    transition:.3s;
    cursor:pointer;
    text-decoration:none;
    display:inline-block;
}

.stat-box:hover{
    transform:translateY(-3px);
    box-shadow:0 0 15px rgba(147,5,5,.35);
}

.stat-box h3{
    margin:0;
    color:#930505;
    font-size:32px;
}

.stat-box p{
    margin:5px 0 0;
    color:#fff;
    font-size:13px;
}

/* Form */
.form-group{
    margin-bottom:20px;
}

.form-group label{
    display:block;
    color:#930505;
    margin-bottom:8px;
    font-size:14px;
    text-align:right;
}

input{
    width:100%;
    background:#1a1a1a;
    color:#fff;
    border:1px solid #930505;
    padding:14px 18px;
    border-radius:15px;
    font-size:14px;
    transition:.3s;
}

input:focus{
    outline:none;
    border-color:#b30a0a;
    box-shadow:0 0 15px rgba(147,5,5,.2);
    background:#000;
}

/* Upload Button */
.upload-btn{
    background:#930505;
    color:#fff;
    padding:10px 25px;
    border-radius:40px;
    cursor:pointer;
    font-size:13px;
    font-weight:bold;
    font-family:'Orbitron',sans-serif;
    transition:.3s;
    display:inline-block;
    margin-top:10px;
}

.upload-btn:hover{
    background:#000;
    color:#930505;
    transform:scale(1.05);
    box-shadow:0 0 18px rgba(147,5,5,.45);
}

/* Delete Image */
.delete-image-box{
    display:flex;
    align-items:center;
    gap:8px;
    color:#ff6666;
    font-size:13px;
    margin-top:10px;
    cursor:pointer;
    justify-content:center;
}

.delete-image-box input{
    width:auto;
    margin:0;
}

/* Save Button */
.save-btn{
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

.save-btn:hover{
    background:#000;
    color:#930505;
    transform:scale(1.02);
    box-shadow:0 5px 25px rgba(147,5,5,.4);
}

/* Home Button */
.home-btn{
    display:flex;
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
}

.home-btn:hover{
    background:#930505;
    transform:scale(1.02);
}

.home-btn:hover i,
.home-btn:hover span{
    color:#000;
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

    .profile-card{
        padding:25px 20px;
    }

    .profile-stats{
        gap:15px;
    }

    .stat-box{
        padding:10px 15px;
        min-width:90px;
    }

    .stat-box h3{
        font-size:24px;
    }
}
</style>
</head>
<body>

<header>
        <h1 class="site-logo">
    <img src="Icon.png" alt="Icon" class="logo-img">
</h1>
    <h1 class="site-logo"><i class="fa-solid fa-user"></i> My Profile</h1>

    <div class="auth-links">
        <?php if(isset($_SESSION['user_name'])): ?>

            <a href="profile.php" class="magic-nav-item welcome-user-box" title="Profile">
                <span class="magic-icon">
                    <?php if(!empty($user['image'])): ?>
                        <img src="<?php echo $user['image']; ?>" class="nav-profile-img">
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

<div class="profile-container">
    <div class="profile-card">
        <form action="update_profile.php" method="POST" enctype="multipart/form-data">
            <div class="profile-image-section">
                <?php if (!empty($user['image'])): ?>
                    <img src="<?php echo htmlspecialchars($user['image']); ?>" class="profile-img" alt="Profile Picture">
                <?php else: ?>
                    <div class="profile-img default-img">
                        <i class="fa-solid fa-user-astronaut"></i>
                    </div>
                <?php endif; ?>

                <label for="profile_image" class="upload-btn">
                    <i class="fa-solid fa-camera"></i> Choose Image
                </label>

                <label class="delete-image-box">
                    <input type="checkbox" name="delete_image">
                    <i class="fa-solid fa-trash"></i> Delete Image
                </label>

                <input type="file" name="profile_image" id="profile_image" accept="image/*" hidden>
            </div>

            <div class="profile-stats">
                <a href="followers_list.php" class="stat-box">
                    <h3><?php echo $followers_count; ?></h3>
                    <p><i class="fa-solid fa-users"></i> Followers</p>
                </a>
                <a href="following_list.php" class="stat-box">
                    <h3><?php echo $following_count; ?></h3>
                    <p><i class="fa-solid fa-user-plus"></i> Following</p>
                </a>
            </div>

            <div class="form-group">
                <label><i class="fa-solid fa-user"></i> Full Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>

            <div class="form-group">
                <label><i class="fa-solid fa-envelope"></i> Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label><i class="fa-solid fa-lock"></i> New Password (leave empty to keep current)</label>
                <input type="password" name="password" placeholder="Enter new password">
            </div>

            <button type="submit" class="save-btn">
                <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>

            <a href="index.php" class="home-btn">
                <i class="fa-solid fa-home"></i> Home Page
            </a>
        </form>
    </div>
</div>

</body>
</html>
