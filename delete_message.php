<?php
session_start();
if(!isset($_SESSION['user_id'])) exit();

$conn=new mysqli("sql213.infinityfree.com","if0_41900150","Rany9NH3lawi","if0_41900150_my_first_project");
$conn->set_charset("utf8mb4");

$id=intval($_POST['message_id']??0);
$uid=intval($_SESSION['user_id']);

$conn->query("UPDATE user_messages SET message='This message was deleted', file_path='', media_path='' WHERE id='$id' AND sender_id='$uid'");
echo "OK";
?>