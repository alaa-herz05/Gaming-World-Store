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

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$sql = "SELECT email FROM users WHERE id='$user_id'";
$result = $conn->query($sql);

$user_email = "";

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user_email = $user['email'];
}

$order_details = $_POST['order_details'];
$total = $_POST['total'];
$payment_method = $_POST['payment_method'];

$stmt = $conn->prepare("
    INSERT INTO orders
    (user_id, user_name, user_email, order_details, total, payment_method)
    VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "isssss",
    $user_id,
    $user_name,
    $user_email,
    $order_details,
    $total,
    $payment_method
);

if ($stmt->execute()) {

    
     $subject = "New Order";

$message = "New order from " . $user_name . "\n\n";
$message .= $order_details . "\n\n";
$message .= "Total: " . $total . "\n";
$message .= "Payment Method: " . $payment_method;

$stmtMsg = $conn->prepare("
    INSERT INTO messages
    (user_id, user_name, user_email, subject, message)
    VALUES (?, ?, ?, ?, ?)
");

$stmtMsg->bind_param(
    "issss",
    $user_id,
    $user_name,
    $user_email,
    $subject,
    $message
);

$stmtMsg->execute();
$stmtMsg->close();
    echo "
    <script>
        alert('Order Sent Successfully');
        localStorage.removeItem('cart');
        window.location.href='cart.php';
    </script>
    ";

} else {

    echo "Database Error";

}

$stmt->close();
$conn->close();

?>