<?php
session_start();
if (!isset($_SESSION['user_id'])) exit();
$conn = new mysqli("sql213.infinityfree.com", "if0_41900150", "Rany9NH3lawi", "if0_41900150_my_first_project");
$mid = $_SESSION['user_id'];
$fid = intval($_POST['friend_id']);

$conn->query("UPDATE user_messages SET is_seen = 1 WHERE sender_id = $fid AND receiver_id = $mid AND is_seen = 0");
?>