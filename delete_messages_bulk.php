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

$current_user = $_SESSION['user_id'];
$friend_id = isset($_POST['friend_id']) ? intval($_POST['friend_id']) : 0;
$message_ids = isset($_POST['message_ids']) ? $_POST['message_ids'] : [];

if (empty($message_ids)) {
    echo json_encode(['success' => false, 'error' => 'No messages selected']);
    exit();
}

// Convert to integers
$message_ids = array_map('intval', $message_ids);
$deleted_count = 0;
$deleted_voice_files = 0;

// Get voice file paths before deleting
$ids_placeholder = implode(',', array_fill(0, count($message_ids), '?'));
$types = str_repeat('i', count($message_ids));

$stmt = $conn->prepare("SELECT id, file_path FROM user_messages WHERE id IN ($ids_placeholder) AND message_type = 'voice'");
$stmt->bind_param($types, ...$message_ids);
$stmt->execute();
$voice_files = $stmt->get_result();

while ($voice = $voice_files->fetch_assoc()) {
    if (!empty($voice['file_path']) && file_exists($voice['file_path'])) {
        if (unlink($voice['file_path'])) {
            $deleted_voice_files++;
        }
    }
}
$stmt->close();

// Delete messages (user can delete ANY message in this chat - both his and friend's)
// This deletes messages only from the current user's view
$delete_stmt = $conn->prepare("DELETE FROM user_messages WHERE id IN ($ids_placeholder)");
$delete_stmt->bind_param($types, ...$message_ids);

if ($delete_stmt->execute()) {
    $deleted_count = $delete_stmt->affected_rows;
    echo json_encode([
        'success' => true, 
        'message' => "Deleted $deleted_count message(s) successfully",
        'deleted_voice_files' => $deleted_voice_files
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
}

$delete_stmt->close();
$conn->close();
?>