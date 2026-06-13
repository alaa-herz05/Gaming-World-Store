<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$conn = new mysqli("sql213.infinityfree.com", "if0_41900150", "Rany9NH3lawi", "if0_41900150_my_first_project");
$conn->set_charset("utf8mb4");

$current_user = intval($_SESSION['user_id']);
$group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
$message_ids = isset($_POST['message_ids']) ? $_POST['message_ids'] : [];

if ($group_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid group']);
    exit();
}

$member = $conn->query("SELECT id FROM group_members WHERE group_id='$group_id' AND user_id='$current_user' LIMIT 1");
if (!$member || $member->num_rows == 0) {
    echo json_encode(['success' => false, 'error' => 'Not a member']);
    exit();
}

if (empty($message_ids)) {
    echo json_encode(['success' => false, 'error' => 'No messages selected']);
    exit();
}

$message_ids = array_map('intval', $message_ids);
$ids_placeholder = implode(',', array_fill(0, count($message_ids), '?'));
$types = str_repeat('i', count($message_ids));
$deleted_voice_files = 0;

$stmt = $conn->prepare("SELECT file_path FROM group_messages WHERE group_id = ? AND id IN ($ids_placeholder) AND message_type = 'voice'");
$bind_types = 'i' . $types;
$stmt->bind_param($bind_types, $group_id, ...$message_ids);
$stmt->execute();
$voice_files = $stmt->get_result();
while ($voice = $voice_files->fetch_assoc()) {
    if (!empty($voice['file_path']) && file_exists($voice['file_path'])) {
        if (unlink($voice['file_path'])) $deleted_voice_files++;
    }
}
$stmt->close();

$delete_stmt = $conn->prepare("DELETE FROM group_messages WHERE group_id = ? AND id IN ($ids_placeholder)");
$delete_stmt->bind_param($bind_types, $group_id, ...$message_ids);

if ($delete_stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Deleted ' . $delete_stmt->affected_rows . ' message(s)',
        'deleted_voice_files' => $deleted_voice_files
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
$delete_stmt->close();
?>
