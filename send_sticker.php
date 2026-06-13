<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit();
}

$conn = new mysqli("sql213.infinityfree.com", "if0_41900150", "Rany9NH3lawi", "if0_41900150_my_first_project");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Database Error"]);
    exit();
}

$conn->set_charset("utf8mb4");

$sender_id = intval($_SESSION['user_id']);
$sender_name = $_SESSION['user_name'] ?? "User";
$receiver_id = intval($_POST['receiver_id'] ?? 0);
$sticker_path = trim($_POST['sticker_path'] ?? "");

if ($receiver_id <= 0 || $sticker_path === "") {
    echo json_encode(["success" => false, "error" => "Invalid sticker"]);
    exit();
}

$owner_check = $conn->prepare("
    SELECT id
    FROM user_stickers
    WHERE user_id = ?
    AND sticker_path = ?
    LIMIT 1
");

$owner_check->bind_param("is", $sender_id, $sticker_path);
$owner_check->execute();
$owner_result = $owner_check->get_result();

if (!$owner_result || $owner_result->num_rows == 0) {
    echo json_encode(["success" => false, "error" => "Sticker not found"]);
    exit();
}

$getUser = $conn->query("SELECT name FROM users WHERE id = '$receiver_id'");
$userData = $getUser ? $getUser->fetch_assoc() : null;
$receiver_name = $userData['name'] ?? "User";

$stmt = $conn->prepare("
    INSERT INTO user_messages
    (sender_id, sender_name, receiver_id, receiver_name, message, message_type, file_path, media_type, media_path, created_at)
    VALUES (?, ?, ?, ?, '💟 Sticker', 'sticker', ?, 'sticker', ?, NOW())
");

$stmt->bind_param(
    "isisss",
    $sender_id,
    $sender_name,
    $receiver_id,
    $receiver_name,
    $sticker_path,
    $sticker_path
);

if (!$stmt->execute()) {
    echo json_encode(["success" => false, "error" => "Database insert failed: " . $conn->error]);
    exit();
}

echo json_encode(["success" => true]);
?>
