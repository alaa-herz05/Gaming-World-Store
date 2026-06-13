<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$conn = new mysqli("sql213.infinityfree.com", "if0_41900150", "Rany9NH3lawi", "if0_41900150_my_first_project");
$conn->set_charset("utf8mb4");

$user_id = $_SESSION['user_id'];
$friend_id = isset($_POST['friend_id']) ? intval($_POST['friend_id']) : 0;
$is_typing = isset($_POST['is_typing']) ? intval($_POST['is_typing']) : 0;

// Insert or update typing status
$stmt = $conn->prepare("INSERT INTO typing_status (user_id, friend_id, is_typing, updated_at) 
                        VALUES (?, ?, ?, NOW()) 
                        ON DUPLICATE KEY UPDATE is_typing = ?, updated_at = NOW()");
$stmt->bind_param("iiii", $user_id, $friend_id, $is_typing, $is_typing);
$stmt->execute();

echo json_encode(['success' => true]);
?>