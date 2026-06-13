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

$user_id = intval($_SESSION['user_id']);

if (!isset($_FILES['sticker']) || $_FILES['sticker']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["success" => false, "error" => "No sticker uploaded"]);
    exit();
}

$allowed_images = ["jpg", "jpeg", "png", "gif", "webp"];
$allowed_videos = ["mp4", "webm"];
$allowed = array_merge($allowed_images, $allowed_videos);

$ext = strtolower(pathinfo($_FILES['sticker']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    echo json_encode(["success" => false, "error" => "Only JPG, PNG, GIF, WEBP, MP4, WEBM are allowed"]);
    exit();
}

$max_size = in_array($ext, $allowed_videos) ? 25 * 1024 * 1024 : 10 * 1024 * 1024;

if ($_FILES['sticker']['size'] > $max_size) {
    echo json_encode(["success" => false, "error" => "Sticker file is too large"]);
    exit();
}

$upload_dir = "uploads/stickers/";

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$filename = time() . "_sticker_" . rand(10000, 99999) . "." . $ext;
$target = $upload_dir . $filename;

if (!move_uploaded_file($_FILES['sticker']['tmp_name'], $target)) {
    echo json_encode(["success" => false, "error" => "Failed to create sticker"]);
    exit();
}

$stmt = $conn->prepare("INSERT INTO user_stickers (user_id, sticker_path) VALUES (?, ?)");
$stmt->bind_param("is", $user_id, $target);

if (!$stmt->execute()) {
    echo json_encode(["success" => false, "error" => "Failed to save sticker"]);
    exit();
}

echo json_encode(["success" => true, "path" => $target]);
?>
