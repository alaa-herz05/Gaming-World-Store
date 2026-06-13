<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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

$userImage = "";
$imgResult = $conn->query("SELECT image FROM users WHERE id='$user_id'");
if ($imgResult && $imgResult->num_rows > 0) {
    $imgRow = $imgResult->fetch_assoc();
    $userImage = $imgRow['image'];
}

if (isset($_POST['delete_order'])) {
    $order_id = intval($_POST['order_id']);

    $stmt = $conn->prepare("DELETE FROM message_replies WHERE message_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: my_orders.php");
    exit();
}

$orders = $conn->query("
    SELECT * FROM orders
    WHERE user_id = '$user_id'
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Gaming World</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
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

/* Orders Container */
/* Orders Container */
.orders-container{
    max-width:900px;
    margin:40px auto;
    padding:20px;
}

/* Order Card */
.order-card{
    background:#0a0a0a;
    border:1px solid #930505;
    border-radius:20px;
    padding:25px;
    margin-bottom:25px;
    color:#fff;
    transition:.3s;
    position:relative;
}

.order-card:hover{
    transform:translateY(-5px);
    box-shadow:0 0 30px rgba(147,5,5,.2);
}

.order-card h2{
    color:#930505;
    margin-bottom:15px;
    font-size:22px;
    display:flex;
    align-items:center;
    gap:10px;
}

.order-card p{
    line-height:1.6;
    margin:8px 0;
}

.order-details{
    background:#1a1a1a;
    padding:15px;
    border-radius:12px;
    margin:15px 0;
}

.order-date{
    color:#aaa;
    margin-top:15px;
    display:block;
    font-size:12px;
}

.delivered{
    color:#00ff66;
    font-weight:bold;
    margin-top:15px;
    display:inline-block;
    background:rgba(0,255,102,.1);
    padding:5px 15px;
    border-radius:20px;
}

.not-delivered{
    color:#ff6666;
    font-weight:bold;
    margin-top:15px;
    display:inline-block;
    background:rgba(255,0,0,.1);
    padding:5px 15px;
    border-radius:20px;
}

.order-info{
    color:#930505;
    font-weight:bold;
}

/* Delete Button */
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
    margin-top:15px;
}

.delete-btn:hover{
    background:#cc0000;
    transform:scale(1.05);
}

/* No Orders */
.no-orders{
    text-align:center;
    color:#888;
    font-weight:bold;
    padding:60px;
    background:#0a0a0a;
    border-radius:20px;
    border:1px solid #930505;
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

    .orders-container{
        padding:15px;
    }

    .order-card{
        padding:20px;
    }

    .order-card h2{
        font-size:18px;
    }
}
</style>
</head>
<body>

<header>
        <h1 class="site-logo">
    <img src="Icon.png" alt="Icon" class="logo-img">
</h1>
    <h1 class="site-logo">📦 My Orders</h1>
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

<div class="orders-container">
    <?php if($orders && $orders->num_rows > 0): ?>
        <?php while($order = $orders->fetch_assoc()): ?>
            <div class="order-card">
                <h2>
                    <i class="fa-solid fa-receipt"></i> Order #<?php echo $order['id']; ?>
                </h2>
                
                <div class="order-details">
                    <p><strong><i class="fa-solid fa-box"></i> Order Details:</strong></p>
                    <p><?php echo nl2br(htmlspecialchars($order['order_details'])); ?></p>
                </div>

                <p class="order-info">
                    <i class="fa-solid fa-tag"></i> Platform: 
                    <strong><?php echo htmlspecialchars($order['platform'] ?? 'Not specified'); ?></strong>
                </p>

                <p class="order-info">
                    <i class="fa-solid fa-coins"></i> Total: 
                    <strong><?php echo htmlspecialchars($order['total']); ?></strong>
                </p>

                <p class="order-info">
                    <i class="fa-solid fa-credit-card"></i> Payment Method: 
                    <strong><?php echo htmlspecialchars($order['payment_method']); ?></strong>
                </p>

                <?php if($order['status'] == 'Delivered'): ?>
                    <p class="delivered"><i class="fa-solid fa-check-circle"></i> Delivered ✓</p>
                <?php else: ?>
                    <p class="not-delivered"><i class="fa-regular fa-clock"></i> Not Delivered Yet</p>
                <?php endif; ?>

                <span class="order-date">
                    <i class="fa-regular fa-calendar"></i> Order Date: <?php echo htmlspecialchars($order['created_at']); ?>
                </span>

                <form method="POST">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <button type="submit" name="delete_order" class="delete-btn" onclick="return confirm('Are you sure you want to delete this order?');">
                        <i class="fa-solid fa-trash"></i> Delete Order
                    </button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-orders">
            <i class="fa-solid fa-box-open" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
            No Orders Yet
            <p style="margin-top: 10px; font-size: 12px;">
                <a href="index.php" style="color: #c94d06;">Browse games and place your first order!</a>
            </p>
        </div>
    <?php endif; ?>
</div>

<footer>
    <p>&copy; 2024 Gaming World. All rights reserved.</p>
</footer>

<?php $conn->close(); ?>
</body>
</html>