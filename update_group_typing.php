<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$conn = new mysqli("sql213.infinityfree.com", "if0_41900150", "Rany9NH3lawi", "if0_41900150_my_first_project");
$conn->set_charset("utf8mb4");

$user_id = intval($_SESSION['user_id']);
$group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
$is_typing = isset($_POST['is_typing']) ? intval($_POST['is_typing']) : 0;

if ($group_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid group']);
    exit();
}

$member = $conn->query("SELECT id FROM group_members WHERE group_id='$group_id' AND user_id='$user_id' LIMIT 1");
if (!$member || $member->num_rows == 0) {
    echo json_encode(['success' => false, 'error' => 'Not a member']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO group_typing_status (group_id, user_id, is_typing, updated_at)
                        VALUES (?, ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE is_typing = ?, updated_at = NOW()");
$stmt->bind_param("iiii", $group_id, $user_id, $is_typing, $is_typing);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);
?>
