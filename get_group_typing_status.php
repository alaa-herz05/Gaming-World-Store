<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['is_typing' => 0, 'names' => []]);
    exit();
}

$conn = new mysqli("sql213.infinityfree.com", "if0_41900150", "Rany9NH3lawi", "if0_41900150_my_first_project");
$conn->set_charset("utf8mb4");

$current_user = intval($_SESSION['user_id']);
$group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;

$member = $conn->query("SELECT id FROM group_members WHERE group_id='$group_id' AND user_id='$current_user' LIMIT 1");
if (!$member || $member->num_rows == 0) {
    echo json_encode(['is_typing' => 0, 'names' => []]);
    exit();
}

$stmt = $conn->prepare("SELECT u.name
                        FROM group_typing_status gts
                        INNER JOIN users u ON u.id = gts.user_id
                        WHERE gts.group_id = ?
                        AND gts.user_id != ?
                        AND gts.is_typing = 1
                        AND gts.updated_at > DATE_SUB(NOW(), INTERVAL 3 SECOND)
                        LIMIT 3");
$stmt->bind_param("ii", $group_id, $current_user);
$stmt->execute();
$result = $stmt->get_result();
$names = [];
while ($row = $result->fetch_assoc()) {
    $names[] = $row['name'];
}
$stmt->close();

echo json_encode(['is_typing' => count($names) > 0 ? 1 : 0, 'names' => $names]);
?>
