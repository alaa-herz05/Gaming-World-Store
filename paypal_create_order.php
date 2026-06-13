<?php
session_start();
require "paypal_config.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id']) || !isset($_SESSION['checkout_order'])) {
    http_response_code(400);
    echo json_encode(["error" => "No checkout order found"]);
    exit();
}

$order = $_SESSION['checkout_order'];
$accessToken = paypal_access_token();

$data = [
    "intent" => "CAPTURE",
    "purchase_units" => [[
        "reference_id" => "GW-" . $_SESSION['user_id'] . "-" . time(),
        "description" => "Gaming World Order - " . $order['platform'],
        "amount" => [
            "currency_code" => PAYPAL_CURRENCY,
            "value" => number_format((float)$order['total'], 2, '.', '')
        ]
    ]],
    "application_context" => [
        "brand_name" => "Gaming World",
        "shipping_preference" => "NO_SHIPPING",
        "user_action" => "PAY_NOW"
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, PAYPAL_BASE_URL . "/v2/checkout/orders");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $accessToken
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$result = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(500);
    echo json_encode(["error" => "Create order failed", "details" => $error]);
    exit();
}

echo $result;
?>
