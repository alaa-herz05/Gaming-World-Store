<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$order_id = $_SESSION['last_paid_order_id'] ?? '';
$paypal_id = $_SESSION['paypal_order_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>Payment Success - Gaming World</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { background:#000; color:#fff; font-family:'Orbitron',sans-serif; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
    .card { max-width:600px; width:100%; background:#0a0a0a; border:1px solid #c94d06; border-radius:20px; padding:35px; text-align:center; box-shadow:0 0 25px rgba(201,77,6,.25); }
    .icon { font-size:55px; color:#c94d06; margin-bottom:20px; }
    h1 { color:#c94d06; margin-bottom:15px; }
    p { line-height:1.8; margin-bottom:12px; }
    .btn { display:inline-flex; margin:10px; align-items:center; justify-content:center; gap:8px; background:#c94d06; color:#fff; text-decoration:none; padding:13px 25px; border-radius:40px; transition:.3s; }
    .btn:hover { background:#ff6a1a; transform:scale(1.03); }
  </style>
</head>
<body>
  <div class="card">
    <div class="icon"><i class="fa-solid fa-circle-check"></i></div>
    <h1>Payment Completed</h1>
    <p>Your order has been paid successfully and sent to admin.</p>
    <?php if($order_id): ?><p>Order ID: <?= htmlspecialchars($order_id) ?></p><?php endif; ?>
    <?php if($paypal_id): ?><p>PayPal ID: <?= htmlspecialchars($paypal_id) ?></p><?php endif; ?>
    <a class="btn" href="index.php"><i class="fa-solid fa-home"></i> Home</a>
    <a class="btn" href="my_orders.php"><i class="fa-solid fa-box-open"></i> My Orders</a>
  </div>
</body>
</html>
