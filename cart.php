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
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <title>Cart - Gaming World</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="A gaming store offering the best games at competitive prices">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
 <link rel="icon" type="image/png" href="Icon.png">
    <link rel="manifest" href="manifest.json">
<style>

/* Reset & Base */
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
/* Magic Navigation */
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
  overflow:hidden;

  transition:
    transform .45s cubic-bezier(.68,-.55,.265,1.55),
    background .35s ease,
    box-shadow .35s ease,
    color .35s ease;
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

  transition:
    opacity .35s ease,
    transform .35s ease;

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
  overflow:hidden;
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

/* Main Container */
.cart-container{
  max-width:900px;
  margin:40px auto;
  padding:0 20px;
  flex:1;
}

/* Cart Items */
.cart-item{
  display:flex;
  align-items:center;
  gap:20px;
  background:#0a0a0a;
  border:1px solid #930505;
  border-radius:15px;
  padding:15px;
  margin-bottom:15px;
  transition:.3s;
}

.cart-item:hover{
  transform:translateX(5px);
  border-color:#b30a0a;
  box-shadow:0 0 18px rgba(147,5,5,.22);
}

.cart-item img{
  width:80px;
  height:80px;
  object-fit:cover;
  border-radius:10px;
}

.cart-info{
  flex:1;
}

.cart-info h3{
  color:#930505;
  font-size:18px;
  margin-bottom:5px;
}

.cart-info p{
  color:#b30a0a;
  font-weight:bold;
}

.quantity-box{
  display:flex;
  align-items:center;
  gap:10px;
  margin-top:10px;
}

.quantity-box button{
  background:#1a1a1a;
  border:1px solid #930505;
  color:#930505;
  width:30px;
  height:30px;
  border-radius:50%;
  cursor:pointer;
  font-weight:bold;
  transition:.3s;
}

.quantity-box button:hover{
  background:#930505;
  color:#000;
  transform:scale(1.08);
}

.quantity-box span{
  font-size:16px;
  font-weight:bold;
}

/* Buttons */
.btn-modern{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:10px;
  background:#930505;
  padding:12px 28px;
  border-radius:40px;
  transition:all .3s ease;
  text-decoration:none;
  font-family:'Orbitron',sans-serif;
  font-weight:700;
  font-size:14px;
  border:none;
  cursor:pointer;
  color:#fff;
  margin:5px;
  box-shadow:0 4px 15px rgba(147,5,5,.3);
}

.btn-modern i{
  font-size:16px;
  color:#fff;
  transition:.3s;
}

.btn-modern span{
  color:#fff;
  font-weight:600;
}

.btn-modern:hover{
  background:#000;
  transform:scale(1.05);
  box-shadow:0 6px 20px rgba(0,0,0,.5);
}

.btn-modern:hover i,
.btn-modern:hover span{
  color:#930505;
}

.btn-danger{
  background:#ff0000;
}

.btn-danger:hover{
  background:#cc0000;
}

.btn-danger:hover i,
.btn-danger:hover span{
  color:#fff;
}

.btn-home{
  background:#1a1a1a;
  border:1px solid #930505;
}

.btn-home:hover{
  background:#930505;
}

.btn-home:hover i,
.btn-home:hover span{
  color:#fff;
}

.remove-btn{
  background:#ff0000;
  color:#fff;
  border:none;
  padding:8px 16px;
  border-radius:25px;
  cursor:pointer;
  font-family:'Orbitron',sans-serif;
  transition:.3s;
}

.remove-btn:hover{
  background:#cc0000;
  transform:scale(1.05);
}

.empty-cart{
  text-align:center;
  padding:40px;
  color:#888;
  font-size:18px;
}

.cart-total{
  text-align:center;
  font-size:24px;
  font-weight:bold;
  color:#930505;
  margin:20px 0;
  padding:15px;
  background:#0a0a0a;
  border-radius:15px;
  border:1px solid #930505;
  box-shadow:0 0 18px rgba(147,5,5,.14);
}

/* Form Styles */
.form-group{
  margin-bottom:20px;
}

.form-group label{
  display:block;
  color:#930505;
  font-weight:bold;
  margin-bottom:10px;
  font-size:14px;
}

select,
textarea{
  width:100%;
  background:#0a0a0a;
  color:#930505;
  border:1px solid #930505;
  padding:12px 15px;
  border-radius:10px;
  font-size:14px;
  cursor:pointer;
  transition:.3s;
}

select:focus,
textarea:focus{
  outline:none;
  border-color:#b30a0a;
  box-shadow:0 0 10px rgba(147,5,5,.3);
}

option{
  background:#000;
}

.button-group{
  display:flex;
  flex-wrap:wrap;
  justify-content:center;
  gap:15px;
  margin:20px 0;
}

.send-btn{
  background:#930505;
  color:#fff;
  border:none;
  padding:14px 30px;
  border-radius:40px;
  font-size:16px;
  font-weight:bold;
  cursor:pointer;
  transition:.3s;
  width:100%;
  margin-top:20px;
  box-shadow:0 4px 15px rgba(147,5,5,.3);
}

.send-btn:hover{
  background:#b30a0a;
  transform:scale(1.02);
  box-shadow:0 0 20px rgba(147,5,5,.45);
}

.order-info{
  color:#930505;
  font-weight:bold;
  text-align:center;
  margin:15px 0;
  padding:10px;
  background:#0a0a0a;
  border-radius:10px;
  border:1px solid rgba(147,5,5,.45);
}

.payment-link{
  color:#fff;
  text-decoration:underline;
  transition:.3s;
}

.payment-link:hover{
  color:#930505;
}

/* Footer */
footer{
  position:relative;
  overflow:hidden;
  text-align:center;
  padding:30px;
  background:#000;
  border-top:1px solid #930505;
  margin-top:40px;
}

footer::before{
  content:'';
  position:absolute;
  top:-120%;
  left:-40%;
  width:180%;
  height:300%;
  background:linear-gradient(
    120deg,
    transparent,
    rgba(147,5,5,.12),
    transparent
  );
  transform:rotate(25deg);
  animation:footerLight 6s linear infinite;
}

.footer-content{
  position:relative;
  z-index:2;
  animation:footerFloat 4s ease-in-out infinite;
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
  transition:.35s ease;
}

footer a:hover{
  color:#ff2b2b;
  text-shadow:0 0 10px rgba(147,5,5,.9), 0 0 20px rgba(147,5,5,.6);
}

@keyframes footerFloat{
  0%{
    transform:translateY(0);
  }

  50%{
    transform:translateY(-4px);
  }

  100%{
    transform:translateY(0);
  }
}

@keyframes footerLight{
  0%{
    transform:translateX(-30%) rotate(25deg);
  }

  100%{
    transform:translateX(30%) rotate(25deg);
  }
}

/* Responsive */
@media(max-width:768px){

  header{
    flex-direction:column;
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

  .cart-item{
    flex-direction:column;
    text-align:center;
  }

  .quantity-box{
    justify-content:center;
  }

  .button-group{
    flex-direction:column;
    align-items:center;
  }

  .btn-modern{
    width:200px;
  }
}

@media(max-width:600px){

  .footer-content{
    text-align:center;
  }

}

</style>
</head>
<body>

<header>
        <h1 class="site-logo" >
    <img src="Icon.png" alt="Icon" class="logo-img">
</h1>
  <div class="auth-links">
    <?php if(isset($_SESSION['user_name'])): ?>
      <a href="profile.php" class="welcome-user-box magic-nav-item" title="Profile">
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
        <span class="nav-username"><?php echo $_SESSION['user_name']; ?></span>
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
      <a href="login.php" class="magic-nav-item" title="Login">
        <span class="magic-icon"><i class="fa-solid fa-right-to-bracket"></i></span>
        <span class="magic-text">Login</span>
      </a>

      <a href="signup.php" class="magic-nav-item" title="Create Account">
        <span class="magic-icon"><i class="fa-solid fa-user-plus"></i></span>
        <span class="magic-text">Sign Up</span>
      </a>
    <?php endif; ?>
  </div>
<nav>
    <a href="index.php" >
        <i class="fa-solid fa-house"></i> Home
    </a>

    <a href="cart.php" style="color:#930505;">
        <i class="fa-solid fa-cart-shopping"></i> Cart
    </a>

    <a href="P2.php">
        <i class="fa-solid fa-headset"></i> Contact Us
    </a>
</nav>
</header>

<main class="cart-container">
  <div id="cartItems"></div>
  <div class="cart-total" id="cartTotal"></div>

  <div class="button-group">
    <a href="index.php" class="btn-modern btn-home">
      <i class="fa-solid fa-home"></i>
      <span>Home Page</span>
    </a>
    <button class="btn-modern btn-danger" onclick="clearCart()">
      <i class="fa-solid fa-trash-alt"></i>
      <span>Clear Cart</span>
    </button>
  </div>

  <form id="orderForm" action="checkout.php" method="POST">
    <input type="hidden" name="name" value="<?php echo $user_name; ?>">
    <input type="hidden" name="email" value="<?php echo $user_email; ?>">
    
    <div class="order-info">
      <i class="fa-solid fa-user"></i> Order for: <?php echo $user_name; ?>
    </div>

    <div class="form-group">
      <label><i class="fa-solid fa-gamepad"></i> Platform</label>
      <select name="platform" required>
        <option value="">Choose Platform</option>
        <option value="PlayStation">🎮 PlayStation</option>
        <option value="Xbox">🟢 Xbox</option>
        <option value="Steam">🖥️ Steam</option>
      </select>
    </div>

    <textarea name="order_details" id="orderData" hidden></textarea>
    
    <div class="order-info">
      <i class="fa-brands fa-paypal"></i> You will continue to PayPal secure checkout after submitting your cart.
    </div>

    <button type="submit" class="send-btn">
      <i class="fa-brands fa-paypal"></i> Continue to Checkout
    </button>
  </form>
</main>

<footer>
  <div class="footer-content">
    <p>&copy; Gaming World rights reserved</p>
    <br>
    <p>My Social</p>
    <p>
      <a href="https://3laa.66ghz.com/" target="_blank">
        <i class="fa-solid fa-user"></i> Alaa Herzallah
      </a>
    </p>
  </div>
</footer>

<script>
let cart = JSON.parse(localStorage.getItem("cart")) || [];
const cartItems = document.getElementById("cartItems");
const cartTotal = document.getElementById("cartTotal");

function groupCart() {
  const grouped = [];
  cart.forEach(item => {
    const found = grouped.find(p => p.name === item.name);
    if (found) {
      found.quantity++;
    } else {
      grouped.push({ ...item, quantity: 1 });
    }
  });
  return grouped;
}

function displayCart() {
  cartItems.innerHTML = "";
  
  if (cart.length === 0) {
    cartItems.innerHTML = `<div class="empty-cart"><i class="fa-solid fa-cart-shopping"></i> The cart is currently empty</div>`;
    cartTotal.textContent = "";
    return;
  }
  
  let total = 0;
  const groupedCart = groupCart();
  
  groupedCart.forEach((item) => {
    total += Number(item.price) * item.quantity;
    cartItems.innerHTML += `
      <div class="cart-item">
        <img src="${item.image}" alt="${item.name}">
        <div class="cart-info">
          <h3>${item.name}</h3>
          <p>${item.price} JD</p>
          <div class="quantity-box">
            <button onclick="decreaseQuantity('${item.name}')">-</button>
            <span>${item.quantity}</span>
            <button onclick="increaseQuantity('${item.name}')">+</button>
          </div>
        </div>
        <button class="remove-btn" onclick="removeAll('${item.name}')"><i class="fa-solid fa-trash"></i> Remove</button>
      </div>
    `;
  });
  
  cartTotal.textContent = "💰 Total: " + total + " JD";
}

function increaseQuantity(name) {
  const item = cart.find(p => p.name === name);
  if (item) {
    cart.push(item);
    localStorage.setItem("cart", JSON.stringify(cart));
    displayCart();
  }
}

function decreaseQuantity(name) {
  const index = cart.findIndex(p => p.name === name);
  if (index !== -1) {
    cart.splice(index, 1);
    localStorage.setItem("cart", JSON.stringify(cart));
    displayCart();
  }
}

function removeAll(name) {
  cart = cart.filter(item => item.name !== name);
  localStorage.setItem("cart", JSON.stringify(cart));
  displayCart();
}

function clearCart() {
  if (confirm("Are you sure you want to clear your entire cart?")) {
    localStorage.removeItem("cart");
    cart = [];
    displayCart();
  }
}

document.getElementById("orderForm").addEventListener("submit", function (event) {
  let orderText = "";
  let total = 0;
  
  if (cart.length === 0) {
    alert("The cart is empty");
    event.preventDefault();
    return;
  }
  
  const groupedCart = groupCart();
  const platform = document.querySelector('select[name="platform"]').value;
  
  if (!platform) {
    alert("Please select a platform");
    event.preventDefault();
    return;
  }
  
  orderText += `Platform: ${platform}\n`;
  orderText += `=======================\n\n`;
  
  groupedCart.forEach(item => {
    const itemTotal = Number(item.price) * item.quantity;
    orderText += `🎮 Product: ${item.name}\n`;
    orderText += `💰 Price: ${item.price} JD\n`;
    orderText += `🔢 Quantity: ${item.quantity}\n`;
    orderText += `💵 Total: ${itemTotal} JD\n`;
    orderText += `-----------------------\n`;
    total += itemTotal;
  });
  
  orderText += `\n⭐ Grand Total: ${total} JD\n`;
  orderText += `=======================`;
  
  document.getElementById("orderData").value = orderText;
});

displayCart();
    if ('serviceWorker' in navigator) {

    window.addEventListener('load', () => {

        navigator.serviceWorker.register('sw.js')
        .then(reg => console.log('SW Registered'))
        .catch(err => console.log(err));

    });
}
</script>

<?php $conn->close(); ?>
</body>
</html>