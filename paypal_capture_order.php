<?php
session_start();
require "paypal_config.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id']) || !isset($_SESSION['checkout_order'])) {
    http_response_code(400);
    echo json_encode(["error" => "No checkout order found"]);
    exit();
}

$input = json_decode(file_get_contents("php://input"), true);
$orderID = $input['orderID'] ?? '';

if (empty($orderID)) {
    http_response_code(400);
    echo json_encode(["error" => "Missing PayPal order ID"]);
    exit();
}

$accessToken = paypal_access_token();

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, PAYPAL_BASE_URL . "/v2/checkout/orders/" . urlencode($orderID) . "/capture");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $accessToken
]);

$result = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(500);
    echo json_encode(["error" => "Capture failed", "details" => $error]);
    exit();
}

$response = json_decode($result, true);

if (isset($response['status']) && $response['status'] === 'COMPLETED') {
    $checkout = $_SESSION['checkout_order'];
    $conn = db_connection();

    $user_id = (int)$checkout['user_id'];
    $name = $conn->real_escape_string($checkout['name']);
    $email = $conn->real_escape_string($checkout['email']);
    $platform = $conn->real_escape_string($checkout['platform']);
    $details = $conn->real_escape_string($checkout['order_details']);
    $total = (float)$checkout['total'];
    $paypal_id = $conn->real_escape_string($orderID);

    // Create table if it does not exist. If you already have an orders table, keep your admin panel using this table.
    $conn->query("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        platform VARCHAR(100) NOT NULL,
        order_details TEXT NOT NULL,
        total DECIMAL(10,2) NOT NULL DEFAULT 0,
        payment_method VARCHAR(50) NOT NULL DEFAULT 'PayPal',
        payment_status VARCHAR(50) NOT NULL DEFAULT 'paid',
        paypal_order_id VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $stmt = $conn->prepare("INSERT INTO orders (user_id, name, email, platform, order_details, total, payment_method, payment_status, paypal_order_id) VALUES (?, ?, ?, ?, ?, ?, 'PayPal', 'paid', ?)");
    $stmt->bind_param("issssds", $user_id, $name, $email, $platform, $details, $total, $paypal_id);
    $stmt->execute();
    $order_db_id = $stmt->insert_id;
    $stmt->close();

    // Optional admin notification table. It will not break if your admin panel does not use it.
    $conn->query("CREATE TABLE IF NOT EXISTS admin_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $title = $conn->real_escape_string("New Paid Order");
    $message = $conn->real_escape_string("New paid PayPal order #" . $order_db_id . " from " . $checkout['name'] . " | Platform: " . $checkout['platform'] . " | Total: " . $checkout['total'] . " " . PAYPAL_CURRENCY);
    $conn->query("INSERT INTO admin_notifications (title, message) VALUES ('$title', '$message')");

    $conn->close();

    $_SESSION['last_paid_order_id'] = $order_db_id;
    $_SESSION['paypal_order_id'] = $orderID;
    unset($_SESSION['checkout_order']);
}

echo json_encode($response);
?>
