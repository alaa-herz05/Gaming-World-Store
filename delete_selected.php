<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli("sql213.infinityfree.com", "if0_41900150", "Rany9NH3lawi", "if0_41900150_my_first_project");
    $conn->set_charset("utf8mb4");

    $friend_id = intval($_POST['friend_id']);
    $message_ids = isset($_POST['message_ids']) ? $_POST['message_ids'] : [];
    $current_user = $_SESSION['user_id'];

    if (!empty($message_ids)) {
        $ids = array_map('intval', $message_ids);
        
        // Get voice file paths
        $ids_list = implode(',', $ids);
        $result = $conn->query("SELECT file_path FROM user_messages WHERE id IN ($ids_list) AND message_type = 'voice'");
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['file_path']) && file_exists($row['file_path'])) {
                unlink($row['file_path']);
            }
        }
        
        // Delete messages (user can delete any message in their chat)
        $stmt = $conn->prepare("DELETE FROM user_messages WHERE id IN ($ids_list)");
        $stmt->execute();
        $stmt->close();
    }

    header("Location: messages_room.php?id=" . $friend_id);
    exit();
} else {
    header("Location: friends.php");
    exit();
}
?>