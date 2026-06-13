<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$conn = new mysqli("sql213.infinityfree.com", "if0_41900150", "Rany9NH3lawi", "if0_41900150_my_first_project");
$conn->set_charset("utf8mb4");

$current_user = $_SESSION['user_id'];
$friend_id = isset($_GET['friend_id']) ? intval($_GET['friend_id']) : 0;

// Check if friend is typing (within last 3 seconds)
$stmt = $conn->prepare("SELECT is_typing FROM typing_status 
                        WHERE user_id = ? AND friend_id = ? 
                        AND updated_at > DATE_SUB(NOW(), INTERVAL 3 SECOND)");
$stmt->bind_param("ii", $friend_id, $current_user);
$stmt->execute();
$result = $stmt->get_result();
$is_typing = $result->fetch_assoc()['is_typing'] ?? 0;

echo json_encode(['is_typing' => (int)$is_typing]);
?>