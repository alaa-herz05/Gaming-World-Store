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
$group_id = intval($_POST['group_id'] ?? 0);

if ($group_id <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid group"]);
    exit();
}

$member_check = $conn->query("
    SELECT id
    FROM group_members
    WHERE group_id = '$group_id'
    AND user_id = '$sender_id'
    LIMIT 1
");

if (!$member_check || $member_check->num_rows == 0) {
    echo json_encode(["success" => false, "error" => "Not a group member"]);
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

$message_text = $media_type === "image" ? "📷 Image" : "🎬 Video";

$stmt = $conn->prepare("
    INSERT INTO group_messages
    (group_id, sender_id, sender_name, message, message_type, file_path, media_type, media_path, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

$stmt->bind_param(
    "iissssss",
    $group_id,
    $sender_id,
    $sender_name,
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

$group_q = $conn->query("SELECT group_name FROM groups_list WHERE id='$group_id'");
$group = $group_q ? $group_q->fetch_assoc() : null;
$group_name = $group ? $group['group_name'] : "Group";

$members = $conn->query("
    SELECT user_id
    FROM group_members
    WHERE group_id = '$group_id'
    AND user_id != '$sender_id'
");

while ($member = $members->fetch_assoc()) {
    $receiver_id = intval($member['user_id']);

    $active = $conn->query("
        SELECT id
        FROM group_activity
        WHERE group_id = '$group_id'
        AND user_id = '$receiver_id'
        AND updated_at > NOW() - INTERVAL 10 SECOND
        LIMIT 1
    ");

    if (!$active || $active->num_rows == 0) {
        $notification_text = $sender_name . " " . (
    $media_type === "image"
        ? "sent a photo in "
        : "sent a video in "
) . $group_name;

        $notif = $conn->prepare("
            INSERT INTO notifications (user_id, sender_id, text, group_id)
            VALUES (?, ?, ?, ?)
        ");

        if ($notif) {
            $notif->bind_param("iisi", $receiver_id, $sender_id, $notification_text, $group_id);
            $notif->execute();
            $notif->close();
        } else {
            $notif2 = $conn->prepare("
                INSERT INTO notifications (user_id, sender_id, text)
                VALUES (?, ?, ?)
            ");

            if ($notif2) {
                $notif2->bind_param("iis", $receiver_id, $sender_id, $notification_text);
                $notif2->execute();
                $notif2->close();
            }
        }
    }
}

echo json_encode(["success" => true, "type" => $media_type, "path" => $target]);
?>
