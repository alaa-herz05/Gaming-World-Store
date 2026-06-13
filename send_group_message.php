<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit("Not logged in");
}

$conn = new mysqli("sql213.infinityfree.com", "if0_41900150", "Rany9NH3lawi", "if0_41900150_my_first_project");
if ($conn->connect_error) {
    http_response_code(500);
    exit("Database Error");
}
$conn->set_charset("utf8mb4");

$sender_id = intval($_SESSION['user_id']);
$sender_name = $_SESSION['user_name'] ?? 'User';
$group_id = intval($_POST['group_id'] ?? 0);
$message = trim($_POST['message'] ?? '');
$reply_message_id = !empty($_POST['reply_message_id']) ? intval($_POST['reply_message_id']) : null;

if ($group_id <= 0 || $message === '') {
    exit("Invalid data");
}

$member_check = $conn->query("SELECT id FROM group_members WHERE group_id='$group_id' AND user_id='$sender_id' LIMIT 1");
if (!$member_check || $member_check->num_rows == 0) {
    http_response_code(403);
    exit("Not a member");
}

$stmt = $conn->prepare("INSERT INTO group_messages (group_id, sender_id, sender_name, message, message_type, reply_message_id, created_at)
                        VALUES (?, ?, ?, ?, 'text', ?, NOW())");
$stmt->bind_param("iissi", $group_id, $sender_id, $sender_name, $message, $reply_message_id);
$stmt->execute();
$stmt->close();

$group_q = $conn->query("SELECT group_name FROM groups_list WHERE id='$group_id'");
$group = $group_q ? $group_q->fetch_assoc() : null;
$group_name = $group ? $group['group_name'] : "Group";

$members = $conn->query("SELECT user_id FROM group_members WHERE group_id='$group_id' AND user_id != '$sender_id'");

while ($member = $members->fetch_assoc()) {
    $receiver_id = intval($member['user_id']);

    $active = $conn->query("SELECT id FROM group_activity
                            WHERE group_id='$group_id'
                            AND user_id='$receiver_id'
                            AND updated_at > NOW() - INTERVAL 10 SECOND
                            LIMIT 1");

    if (!$active || $active->num_rows == 0) {
        $notification_text = $sender_name . " sent a text message in " . $group_name;

        $notif = $conn->prepare("INSERT INTO notifications (user_id, sender_id, text, group_id)
                                 VALUES (?, ?, ?, ?)");
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

echo "OK";
?>
