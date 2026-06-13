<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit();
}

$conn = new mysqli(
    "sql213.infinityfree.com",
    "if0_41900150",
    "Rany9NH3lawi",
    "if0_41900150_my_first_project"
);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Database Error"]);
    exit();
}

$conn->set_charset("utf8mb4");

$user_id = intval($_SESSION['user_id']);
$sticker_path = trim($_POST['sticker_path'] ?? '');

if ($sticker_path === '') {
    echo json_encode(["success" => false, "error" => "Invalid sticker"]);
    exit();
}

$stmt = $conn->prepare("
    DELETE FROM user_stickers
    WHERE user_id = ?
    AND sticker_path = ?
");

$stmt->bind_param("is", $user_id, $sticker_path);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Delete failed"]);
}
?>
