<?php
/*
  PayPal Settings
  IMPORTANT: Do not share the Secret Key publicly.
  Use Sandbox while testing, then switch PAYPAL_MODE to "live".
*/

define("PAYPAL_MODE", "sandbox"); // sandbox OR live

define("PAYPAL_CLIENT_ID", "AZhq6G8pM8jZRYA5J-ngT-Nj-3zYZ0daLNIORv4gxp5Wr-YelSUQM00Fc9IuY0k9KnWTvtUOimtC5BVH");
define("PAYPAL_SECRET", "ELYj-S78zUjB9MH7270AMiOFPCdRszj3FcWTD6wrq4IChPy1eRPY-KMQMyNuiK6Xfu5Wd-jgLi0h2kbp");
define("PAYPAL_CURRENCY", "USD"); // PayPal does not support JOD in all accounts. Use USD unless your account supports another currency.

if (PAYPAL_MODE === "live") {
    define("PAYPAL_BASE_URL", "https://api-m.paypal.com");
} else {
    define("PAYPAL_BASE_URL", "https://api-m.sandbox.paypal.com");
}

function db_connection() {
    $conn = new mysqli(
        "sql213.infinityfree.com",
        "if0_41900150",
        "Rany9NH3lawi",
        "if0_41900150_my_first_project"
    );

    if ($conn->connect_error) {
        http_response_code(500);
        die("Database Error");
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

function paypal_access_token() {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, PAYPAL_BASE_URL . "/v1/oauth2/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ":" . PAYPAL_SECRET);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/json",
        "Accept-Language: en_US"
    ]);

    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        http_response_code(500);
        echo json_encode(["error" => "PayPal token error", "details" => $error]);
        exit();
    }

    $json = json_decode($result, true);

    if (!isset($json["access_token"])) {
        http_response_code(500);
        echo json_encode(["error" => "Could not get PayPal access token", "paypal_response" => $json]);
        exit();
    }

    return $json["access_token"];
}
?>
