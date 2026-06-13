<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli(
    "sql213.infinityfree.com",
    "if0_41900150",
    "Rany9NH3lawi",
    "if0_41900150_my_first_project"
);

if ($conn->connect_error) {
    die("Database Error");
}

$current_user = $_SESSION['user_id'];
$friend_id = $_POST['friend_id'];

$conn->query("
DELETE FROM follows
WHERE follower_id='$current_user'
AND following_id='$friend_id'
");

header("Location: friends.php");
exit();

?>