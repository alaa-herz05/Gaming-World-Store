<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli(
    "sql213.infinityfree.com",
    "if0_41900150",
    "Rany9NH3lawi",
    "if0_41900150_my_first_project"
);

$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Database Error");
}

$sender_id = $_SESSION['user_id'];
$sender_name = $_SESSION['user_name'];

$receiver_id = intval($_POST['receiver_id']);
$message = trim($_POST['message'] ?? '');
$reply_message_id = !empty($_POST['reply_message_id']) ? intval($_POST['reply_message_id']) : null;

if (!empty($message)) {

    $check = $conn->query("
        SELECT 
            (SELECT COUNT(*) FROM follows WHERE follower_id='$sender_id' AND following_id='$receiver_id') AS i_follow,
            (SELECT COUNT(*) FROM follows WHERE follower_id='$receiver_id' AND following_id='$sender_id') AS follows_me
    ");

    $row = $check->fetch_assoc();

    if ($row['i_follow'] > 0 && $row['follows_me'] > 0) {

        $getUser = $conn->query("
            SELECT name
            FROM users
            WHERE id = '$receiver_id'
        ");

        $userData = $getUser->fetch_assoc();
        $receiver_name = $userData['name'];

        $stmt = $conn->prepare("
            INSERT INTO user_messages
            (sender_id, sender_name, receiver_id, receiver_name, message, reply_message_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "isissi",
            $sender_id,
            $sender_name,
            $receiver_id,
            $receiver_name,
            $message,
            $reply_message_id
        );

        $stmt->execute();
        $stmt->close();

        $is_active = $conn->query("
            SELECT id
            FROM user_activity
            WHERE user_id = '$receiver_id'
            AND friend_id = '$sender_id'
            AND updated_at > NOW() - INTERVAL 10 SECOND
            LIMIT 1
        ");

        if (!$is_active || $is_active->num_rows == 0) {

            $notification_text = $sender_name . " sent you a message";

            $notif = $conn->prepare("
                INSERT INTO notifications (user_id, sender_id, text)
                VALUES (?, ?, ?)
            ");

            $notif->bind_param("iis", $receiver_id, $sender_id, $notification_text);
            $notif->execute();
            $notif->close();
        }
    }
}

$conn->close();

echo "OK";
exit();
?>