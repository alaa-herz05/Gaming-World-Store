<?php
session_start();
header("Content-Type: application/json");
if (!isset($_SESSION['user_id'])) { echo json_encode(["success"=>false,"error"=>"Not logged in"]); exit(); }

$conn = new mysqli("sql213.infinityfree.com","if0_41900150","Rany9NH3lawi","if0_41900150_my_first_project");
if ($conn->connect_error) { echo json_encode(["success"=>false,"error"=>"Database Error"]); exit(); }
$conn->set_charset("utf8mb4");

$user_id = intval($_SESSION['user_id']);
$message_id = intval($_POST['message_id'] ?? 0);
$reaction = trim($_POST['reaction'] ?? "");
$allowed = ["❤️","😂","🔥","👍"];

if ($message_id <= 0 || !in_array($reaction, $allowed)) {
    echo json_encode(["success"=>false,"error"=>"Invalid reaction"]);
    exit();
}

$check = $conn->prepare("
    SELECT gm.id
    FROM group_messages gm
    INNER JOIN group_members gmem ON gm.group_id = gmem.group_id
    WHERE gm.id=? AND gmem.user_id=?
    LIMIT 1
");
$check->bind_param("ii", $message_id, $user_id);
$check->execute();
$res = $check->get_result();

if (!$res || $res->num_rows == 0) {
    echo json_encode(["success"=>false,"error"=>"Message not found"]);
    exit();
}

$stmt = $conn->prepare("
    INSERT INTO group_message_reactions (message_id, user_id, reaction)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE reaction=VALUES(reaction), created_at=CURRENT_TIMESTAMP
");
$stmt->bind_param("iis", $message_id, $user_id, $reaction);

echo json_encode(["success"=>$stmt->execute()]);
?>