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

$search = trim($_GET['search'] ?? '');
$users = null;

if (!empty($search)) {
    $stmt = $conn->prepare("
        SELECT id, name, email, image 
        FROM users 
        WHERE id != ? AND name LIKE ?
    ");
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("is", $current_user, $searchTerm);
    $stmt->execute();
    $users = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>Add Friends - Gaming World</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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

/* Search Box - مثل تصميم Contact Me */
/* Search Box - مثل تصميم Contact Me */
    .search-users-box {
        max-width: 550px;
        margin: 40px auto 30px;
        display: flex;
        gap: 12px;
        padding: 0 20px;
    }

    .search-users-box input {
        flex: 1;
        background: #1a1a1a;
        color: white;
        border: 1px solid #930505;
        border-radius: 40px;
        padding: 14px 20px;
        font-size: 14px;
        font-family: 'Orbitron', sans-serif;
        outline: none;
        transition: 0.3s;
    }

    .search-users-box input:focus {
        box-shadow: 0 0 15px rgba(201, 77, 6, 0.3);
        border-color: #ff6a1a;
    }

    .search-users-box input::placeholder {
        color: #666;
    }

    .search-users-box button {
        background: #930505;
        color: white;
        border: none;
        border-radius: 40px;
        padding: 14px 28px;
        cursor: pointer;
        font-weight: bold;
        font-family: 'Orbitron', sans-serif;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .search-users-box button:hover {
        background: #000;
        transform: scale(1.02);
    }

    /* Users Container */
    .users-container {
        max-width: 700px;
        margin: 0 auto;
        padding: 0 20px 40px;
    }

    /* User Card - مثل تصميم Contact Me */
    .user-card {
        background: #0a0a0a;
        border: 1px solid #930505;
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        color: white;
        transition: 0.3s;
    }

    .user-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0 20px rgba(201, 77, 6, 0.2);
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .user-img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #930505;
    }

    .default-user {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 2px solid #930505;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #1a1a1a;
    }

    .default-user i {
        font-size: 28px;
        color: #930505;
    }

    .user-info h3 {
        margin: 0;
    }

    .user-info a {
        color: #930505;
        text-decoration: none;
        font-size: 18px;
        font-weight: bold;
        transition: 0.3s;
    }

    .user-info a:hover {
        color: #ff6a1a;
    }

    /* Follow Button - مثل تصميم Contact Me */
    .follow-btn {
        background: #930505;
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 40px;
        cursor: pointer;
        font-weight: bold;
        font-family: 'Orbitron', sans-serif;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .follow-btn:hover {
        background: #000;
        transform: scale(1.05);
    }

    .unfollow-btn {
        background: #555;
    }

    .unfollow-btn:hover {
        background: #ff0000;
    }

    /* No Results Message */
    .no-results {
        text-align: center;
        color: #888;
        font-weight: bold;
        margin-top: 60px;
        padding: 60px;
        background: #0a0a0a;
        border-radius: 20px;
        border: 1px solid #930505;
    }

    .no-results i {
        font-size: 48px;
        margin-bottom: 15px;
        display: block;
        color:#930505;
    }

    /* Responsive */
   @media (max-width: 768px) {

    body {
        overflow-x: hidden;
    }

    header {
        padding: 22px 16px 28px;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        gap: 18px;
    }

    .logo-img {
        width: 105px;
    }

    .site-logo {
        font-size: 28px;
        line-height: 1.2;
    }

    .auth-links {
        width: 100%;
        max-width: 380px;
        min-height: 70px;
        padding: 0 8px;
        border-radius: 18px;
        justify-content: space-around;
        gap: 0;
    }

    .magic-nav-item {
        width: 52px;
        height: 62px;
    }

    .magic-nav-item .magic-icon {
        width: 40px;
        height: 40px;
    }

    .magic-nav-item .magic-icon i {
        font-size: 18px;
    }

    .magic-nav-item .magic-text {
        display: none;
    }

    .magic-nav-item:hover .magic-icon {
        transform: none;
        background: transparent;
        color: #fff;
        box-shadow: none;
    }

    .magic-nav-item:hover .bell-count {
        top: 6px;
        transform: none;
    }

    .bell-count {
        top: 6px;
        right: 8px;
    }

    nav {
        width: 100%;
        justify-content: center;
        gap: 18px;
        flex-wrap: wrap;
    }

    nav a {
        font-size: 15px;
    }

    .search-users-box {
        width: 100%;
        max-width: 420px;
        margin: 32px auto 28px;
        padding: 0 18px;
        flex-direction: column;
        gap: 16px;
    }

    .search-users-box input,
    .search-users-box button {
        width: 100%;
        height: 58px;
        font-size: 14px;
    }

    .search-users-box button {
        justify-content: center;
    }

    .users-container {
        width: 100%;
        max-width: 420px;
        padding: 0 18px 50px;
    }

    .no-results {
        width: 100%;
        margin-top: 40px;
        padding: 55px 20px;
        border-radius: 20px;
        font-size: 15px;
    }

    .no-results i {
        font-size: 54px;
    }

    .no-results p {
        line-height: 1.5;
    }

    .user-card {
        flex-direction: column;
        text-align: center;
        padding: 22px 18px;
    }

    .user-info {
        flex-direction: column;
    }

    .follow-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
</head>
<body>

<header>
        <h1 class="site-logo">
    <img src="Icon.png" alt="Icon" class="logo-img">
</h1>
    <h1 class="site-logo"><i class="fa-solid fa-user-plus"></i> Add Friends</h1>
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
                $notif_query = $conn->query("
                    SELECT COUNT(*) AS total
                    FROM notifications
                    WHERE user_id = '$current_user'
                    AND is_read = 0
                ");
                if($notif_query){
                    $notif_data = $notif_query->fetch_assoc();
                    if($notif_data['total'] > 0):
                ?>
                    <span class="bell-count"><?php echo $notif_data['total']; ?></span>
                <?php
                    endif;
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

<form method="GET" class="search-users-box">
    <input 
        type="text" 
        name="search" 
        placeholder="🔍 Search for a friend by name..."
        value="<?php echo htmlspecialchars($search); ?>"
        required>
    <button type="submit">
        <i class="fa-solid fa-magnifying-glass"></i> Search
    </button>
</form>

<div class="users-container">
    <?php if(!empty($search)): ?>
        <?php if($users && $users->num_rows > 0): ?>
            <?php while($user = $users->fetch_assoc()): ?>
                <?php
                $user_id = $user['id'];

                $check = $conn->query("
                    SELECT id FROM follows 
                    WHERE follower_id='$current_user' 
                    AND following_id='$user_id'
                ");
                $isFollowing = $check && $check->num_rows > 0;

                $followBackCheck = $conn->query("
                    SELECT id FROM follows
                    WHERE follower_id='$user_id'
                    AND following_id='$current_user'
                ");
                $followBack = $followBackCheck && $followBackCheck->num_rows > 0;
                ?>

                <div class="user-card">
                    <div class="user-info">
                        <?php if(!empty($user['image'])): ?>
                            <img src="<?php echo $user['image']; ?>" class="user-img">
                        <?php else: ?>
                            <div class="default-user">
                                <i class="fa-solid fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h3>
                                <a href="user_profile.php?id=<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['name']); ?>
                                </a>
                            </h3>
                        </div>
                    </div>
                    <form action="follow.php" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" class="follow-btn <?php echo $isFollowing ? 'unfollow-btn' : ''; ?>">
                            <i class="fa-solid <?php echo $isFollowing ? 'fa-user-minus' : ($followBack ? 'fa-user-check' : 'fa-user-plus'); ?>"></i>
                            <?php
                            if($isFollowing){
                                echo ' Unfollow';
                            } elseif($followBack){
                                echo ' Follow Back';
                            } else {
                                echo ' Follow';
                            }
                            ?>
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-results">
                <i class="fa-solid fa-user-slash"></i>
                No users found for "<strong><?php echo htmlspecialchars($search); ?></strong>"
                <p style="margin-top: 10px; font-size: 12px;">Try searching with a different name</p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="no-results">
            <i class="fa-solid fa-search"></i>
            Search for friends to connect with!
            <p style="margin-top: 10px; font-size: 12px;">Enter a name in the search box above</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>