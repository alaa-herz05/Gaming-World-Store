<?php
session_start();

if (!isset($_SESSION['user_id'])) exit();

$conn = new mysqli(
    "sql213.infinityfree.com",
    "if0_41900150",
    "Rany9NH3lawi",
    "if0_41900150_my_first_project"
);

$conn->set_charset("utf8mb4");

$message_id = intval($_POST['message_id'] ?? 0);
$user_id = intval($_SESSION['user_id']);

$msg_q = $conn->query("
    SELECT *
    FROM user_messages
    WHERE id='$message_id'
    LIMIT 1
");

if (!$msg_q || $msg_q->num_rows == 0) exit();

$msg = $msg_q->fetch_assoc();

$friend_id =
    $msg['sender_id'] == $user_id
    ? $msg['receiver_id']
    : $msg['sender_id'];

$conn->query("
    UPDATE user_messages
    SET pinned = 0
    WHERE
    (sender_id='$user_id' AND receiver_id='$friend_id')
    OR
    (sender_id='$friend_id' AND receiver_id='$user_id')
");

$conn->query("
    UPDATE user_messages
    SET pinned = 1
    WHERE id='$message_id'
");

echo "OK";
?>