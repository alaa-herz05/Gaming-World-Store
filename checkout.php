<?php
session_start();
require "paypal_config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

function safe($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function extract_total_from_order($text) {
    if (preg_match('/Grand Total:\s*([0-9]+(?:\.[0-9]+)?)/i', $text, $m)) {
        return (float)$m[1];
    }
    if (preg_match('/Total:\s*([0-9]+(?:\.[0-9]+)?)/i', $text, $m)) {
        return (float)$m[1];
    }
    return 0;
}

// لو الصفحة انفتحت من cart.php عن طريق POST، خزن بيانات الطلب بالـ session
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? ($_SESSION['user_name'] ?? '');
    $email = $_POST['email'] ?? '';
    $platform = $_POST['platform'] ?? '';
    $order_details = $_POST['order_details'] ?? '';

    if (empty($platform) || empty($order_details)) {
        header("Location: cart.php");
        exit();
    }

    $total = extract_total_from_order($order_details);

    if ($total <= 0) {
        die("Invalid order total");
    }

    $_SESSION['checkout_order'] = [
        'user_id' => $_SESSION['user_id'],
        'name' => $name,
        'email' => $email,
        'platform' => $platform,
        'order_details' => $order_details,
        'total' => number_format($total, 2, '.', '')
    ];
}

// لو عمل Refresh أو رجع للصفحة، خذ البيانات من session
if (!isset($_SESSION['checkout_order'])) {
    header("Location: cart.php");
    exit();
}

$order = $_SESSION['checkout_order'];
$name = $order['name'] ?? '';
$email = $order['email'] ?? '';
$platform = $order['platform'] ?? '';
$order_details = $order['order_details'] ?? '';
$total = (float)($order['total'] ?? 0);

if ($total <= 0) {
    header("Location: cart.php");
    exit();
}

$userImage = "";

$conn = new mysqli(
    "sql213.infinityfree.com",
    "if0_41900150",
    "Rany9NH3lawi",
    "if0_41900150_my_first_project"
);

if (!$conn->connect_error) {
    $conn->set_charset("utf8mb4");
    $current_user = intval($_SESSION['user_id']);
    $imgResult = $conn->query("SELECT image FROM users WHERE id='$current_user'");
    if ($imgResult && $imgResult->num_rows > 0) {
        $imgRow = $imgResult->fetch_assoc();
        $userImage = $imgRow['image'];
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <title>Checkout - Gaming World</title>
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
    display:flex;
    flex-direction:column;
}

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

.checkout-container{
    max-width:900px;
    width:100%;
    margin:40px auto;
    padding:0 20px;
    flex:1;
}

.checkout-card{
    background:#0a0a0a;
    border:1px solid #930505;
    border-radius:18px;
    padding:25px;
    box-shadow:0 0 25px rgba(147,5,5,.2);
}

.checkout-title{
    color:#930505;
    text-align:center;
    margin-bottom:25px;
    font-size:26px;
}

.info-box{
    background:#111;
    border:1px solid rgba(147,5,5,.6);
    border-radius:12px;
    padding:14px;
    margin-bottom:15px;
    color:#fff;
    line-height:1.7;
}

.info-box strong{
    color:#930505;
}

.order-preview{
    white-space:pre-wrap;
    direction:ltr;
    text-align:left;
    color:#fff;
    background:#000;
    border:1px solid #333;
    border-radius:12px;
    padding:15px;
    margin:20px 0;
    line-height:1.7;
    overflow-x:auto;
}

.paypal-box{
    background:#111;
    border:1px solid #930505;
    border-radius:15px;
    padding:20px;
    margin-top:20px;
}

.warning{
    color:#ff8c8c;
    text-align:center;
    margin-bottom:15px;
    line-height:1.7;
}

.btn-back{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    background:#1a1a1a;
    color:#fff;
    border:1px solid #930505;
    padding:12px 24px;
    border-radius:40px;
    text-decoration:none;
    margin-top:20px;
    transition:.3s;
}

.btn-back:hover{
    background:#930505;
}

footer{
    text-align:center;
    padding:20px;
    background:#000;
    border-top:1px solid #930505;
    margin-top:40px;
}

footer p{
    color:#930505;
    font-size:12px;
}

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
}

</style>
</head>
<body>
<header>
        <h1 class="site-logo">
    <img src="Icon.png" alt="Icon" class="logo-img">
</h1>
  <h1 class="site-logo">Checkout</h1>
    <div class="auth-links">
        <?php if(isset($_SESSION['user_name'])): ?>

            <a href="profile.php" class="magic-nav-item welcome-user-box" title="Profile">
                <span class="magic-icon">
                    <?php if(!empty($userImage)): ?>
                        <img src="<?php echo safe($userImage); ?>" class="nav-profile-img">
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
                if(isset($conn) && !$conn->connect_error && isset($_SESSION['user_id'])) {
                    $current_user = intval($_SESSION['user_id']);
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

<main class="checkout-container">
  <div class="checkout-card">
    <h2 class="checkout-title">Complete Your Payment</h2>

    <div class="info-box"><strong>Name:</strong> <?= safe($name) ?></div>
    <div class="info-box"><strong>Email:</strong> <?= safe($email) ?></div>
    <div class="info-box"><strong>Platform:</strong> <?= safe($platform) ?></div>
    <div class="info-box"><strong>Total:</strong> <?= safe(number_format($total, 2)) ?> <?= safe(PAYPAL_CURRENCY) ?></div>

    <div class="order-preview"><?= safe($order_details) ?></div>

    <div class="paypal-box">
      <p class="warning">PayPal secure checkout. The order will be sent to admin only after payment is completed.</p>
      <div id="paypal-button-container"></div>
    </div>

    <a href="cart.php" class="btn-back"><i class="fa-solid fa-arrow-right"></i> Back to Cart</a>
  </div>
</main>

<footer><p>&copy; Gaming World rights reserved</p></footer>

<script src="https://www.paypal.com/sdk/js?client-id=<?= urlencode(PAYPAL_CLIENT_ID) ?>&currency=<?= urlencode(PAYPAL_CURRENCY) ?>"></script>
<script>
if (typeof paypal === "undefined") {
  alert("PayPal SDK did not load. Check your Client ID in paypal_config.php");
} else {
  paypal.Buttons({
    createOrder: function() {
      return fetch("paypal_create_order.php", { method: "POST" })
        .then(function(response) { return response.json(); })
        .then(function(order) {
          if (order.error) {
            throw new Error(order.error);
          }
          return order.id;
        });
    },

    onApprove: function(data) {
      return fetch("paypal_capture_order.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ orderID: data.orderID })
      })
      .then(function(response) { return response.json(); })
      .then(function(details) {
        if (details.status === "COMPLETED") {
          localStorage.removeItem("cart");
          window.location.href = "payment_success.php";
        } else {
          alert("Payment was not completed. Please try again.");
        }
      })
      .catch(function(error) {
        alert("Payment error: " + error.message);
      });
    },

    onCancel: function() {
      alert("Payment cancelled.");
    },

    onError: function(err) {
      alert("PayPal error. Check Console for details.");
      console.error(err);
    }
  }).render("#paypal-button-container");
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
