<?php
session_start();
if (!isset($_SESSION['user_id'])) exit();

$conn = new mysqli("sql213.infinityfree.com", "if0_41900150", "Rany9NH3lawi", "if0_41900150_my_first_project");
$conn->set_charset("utf8mb4");

$user_id = intval($_SESSION['user_id']);
$group_id = intval($_POST['group_id'] ?? 0);
$status = intval($_POST['status'] ?? 1);

if ($group_id <= 0) exit();

if ($status == 1) {
    $stmt = $conn->prepare("
        INSERT INTO group_activity (group_id, user_id, updated_at)
        VALUES (?, ?, NOW())
        ON DUPLICATE KEY UPDATE updated_at = NOW()
    ");
    $stmt->bind_param("ii", $group_id, $user_id);
    $stmt->execute();
} else {
    $stmt = $conn->prepare("
        DELETE FROM group_activity
        WHERE group_id = ?
        AND user_id = ?
    ");
    $stmt->bind_param("ii", $group_id, $user_id);
    $stmt->execute();
}

echo "OK";
?>