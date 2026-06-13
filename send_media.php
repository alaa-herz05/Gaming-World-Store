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

if ($receiver_id <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid receiver"]);
    exit();
}

if (!isset($_FILES['media']) || $_FILES['media']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["success" => false, "error" => "No media uploaded"]);
    exit();
}

$allowed_images = ["jpg", "jpeg", "png", "gif", "webp"];
$allowed_videos = ["mp4", "webm", "mov"];
$ext = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));

if (in_array($ext, $allowed_images)) {
    $media_type = "image";
} elseif (in_array($ext, $allowed_videos)) {
    $media_type = "video";
} else {
    echo json_encode(["success" => false, "error" => "Unsupported file type"]);
    exit();
}

if ($_FILES['media']['size'] > 40 * 1024 * 1024) {
    echo json_encode(["success" => false, "error" => "File too large. Max 40MB"]);
    exit();
}

$upload_dir = "uploads/media/";

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$filename = time() . "_" . rand(10000, 99999) . "." . $ext;
$target = $upload_dir . $filename;

if (!move_uploaded_file($_FILES['media']['tmp_name'], $target)) {
    echo json_encode(["success" => false, "error" => "Failed to upload file"]);
    exit();
}

$getUser = $conn->query("SELECT name FROM users WHERE id = '$receiver_id'");
$userData = $getUser ? $getUser->fetch_assoc() : null;
$receiver_name = $userData['name'] ?? "User";
$message_text = $media_type === "image" ? "📷 Image" : "🎬 Video";

$stmt = $conn->prepare("
    INSERT INTO user_messages
    (sender_id, sender_name, receiver_id, receiver_name, message, message_type, file_path, media_type, media_path, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

$stmt->bind_param(
    "isissssss",
    $sender_id,
    $sender_name,
    $receiver_id,
    $receiver_name,
    $message_text,
    $media_type,
    $target,
    $media_type,
    $target
);

if (!$stmt->execute()) {
    echo json_encode(["success" => false, "error" => "Database insert failed: " . $conn->error]);
    exit();
}

$is_active = $conn->query("
    SELECT id
    FROM user_activity
    WHERE user_id = '$receiver_id'
    AND friend_id = '$sender_id'
    AND updated_at > NOW() - INTERVAL 10 SECOND
    LIMIT 1
");

if (!$is_active || $is_active->num_rows == 0) {
   $notification_text = $sender_name . " " . (
    $media_type === "image"
        ? "sent you a photo"
        : "sent you a video"
);

    $notif = $conn->prepare("
        INSERT INTO notifications (user_id, sender_id, text)
        VALUES (?, ?, ?)
    ");

    if ($notif) {
        $notif->bind_param("iis", $receiver_id, $sender_id, $notification_text);
        $notif->execute();
        $notif->close();
    }
}

echo json_encode(["success" => true, "type" => $media_type, "path" => $target]);
?>
