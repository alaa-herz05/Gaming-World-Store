<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$conn = new mysqli("sql213.infinityfree.com", "if0_41900150", "Rany9NH3lawi", "if0_41900150_my_first_project");
$conn->set_charset("utf8mb4");

$sender_id = intval($_SESSION['user_id']);
$sender_name = $_SESSION['user_name'] ?? 'User';
$group_id = intval($_POST['group_id'] ?? 0);

if ($group_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid group']);
    exit();
}

$member_check = $conn->query("SELECT id FROM group_members WHERE group_id='$group_id' AND user_id='$sender_id' LIMIT 1");
if (!$member_check || $member_check->num_rows == 0) {
    echo json_encode(['success' => false, 'error' => 'Not a member']);
    exit();
}

if (!isset($_FILES['voice']) || $_FILES['voice']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No voice file received']);
    exit();
}

if ($_FILES['voice']['size'] > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'File too large max 10MB']);
    exit();
}

$audio_dir = 'uploads/audio/';
if (!file_exists($audio_dir)) {
    mkdir($audio_dir, 0777, true);
}

$filename = time() . '_group_voice_' . rand(10000, 99999) . '.webm';
$file_path = $audio_dir . $filename;

if (!move_uploaded_file($_FILES['voice']['tmp_name'], $file_path)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save voice']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO group_messages (group_id, sender_id, sender_name, message, message_type, file_path, created_at)
                        VALUES (?, ?, ?, '🎤 Voice message', 'voice', ?, NOW())");
$stmt->bind_param("iiss", $group_id, $sender_id, $sender_name, $file_path);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit();
}
$stmt->close();

$group_q = $conn->query("SELECT group_name FROM groups_list WHERE id='$group_id'");
$group = $group_q ? $group_q->fetch_assoc() : null;
$group_name = $group ? $group['group_name'] : "Group";

$members = $conn->query("SELECT user_id FROM group_members WHERE group_id='$group_id' AND user_id != '$sender_id'");
while ($member = $members->fetch_assoc()) {
    $receiver_id = intval($member['user_id']);
    $active = $conn->query("SELECT id FROM group_activity WHERE group_id='$group_id' AND user_id='$receiver_id' AND updated_at > NOW() - INTERVAL 10 SECOND LIMIT 1");
    if (!$active || $active->num_rows == 0) {
        $notification_text = $sender_name . " sent a voice message in " . $group_name;
        $notif = $conn->prepare("INSERT INTO notifications (user_id, sender_id, text, group_id) VALUES (?, ?, ?, ?)");
        if ($notif) {
            $notif->bind_param("iisi", $receiver_id, $sender_id, $notification_text, $group_id);
            $notif->execute();
            $notif->close();
        } else {
            $notif2 = $conn->prepare("INSERT INTO notifications (user_id, sender_id, text) VALUES (?, ?, ?)");
            $notif2->bind_param("iis", $receiver_id, $sender_id, $notification_text);
            $notif2->execute();
            $notif2->close();
        }
    }
}

echo json_encode(['success' => true, 'file_size' => filesize($file_path)]);
?>
