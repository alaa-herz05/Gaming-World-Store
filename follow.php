<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

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

if ($conn->connect_error) {
    die("Database Error: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$current_user = $_SESSION['user_id'];
$current_name = $_SESSION['user_name'];
$following_id = $_POST['user_id'];

$check = $conn->prepare("
    SELECT id FROM follows 
    WHERE follower_id = ? 
    AND following_id = ?
");

$check->bind_param("ii", $current_user, $following_id);
$check->execute();
$result = $check->get_result();

if ($result && $result->num_rows > 0) {

    $stmt = $conn->prepare("
        DELETE FROM follows 
        WHERE follower_id = ? 
        AND following_id = ?
    ");

    $stmt->bind_param("ii", $current_user, $following_id);
    $stmt->execute();
    $stmt->close();

} else {

    $stmt = $conn->prepare("
        INSERT INTO follows (follower_id, following_id)
        VALUES (?, ?)
    ");

    $stmt->bind_param("ii", $current_user, $following_id);
    $stmt->execute();
    $stmt->close();

$notification_text = $current_name . " started following you";

$notif = $conn->prepare("
    INSERT INTO notifications (user_id, sender_id, text)
    VALUES (?, ?, ?)
");

$notif->bind_param("iis", $following_id, $current_user, $notification_text);
$notif->execute();
$notif->close();
}

$check->close();
$conn->close();

header("Location: users.php");
exit();
?>