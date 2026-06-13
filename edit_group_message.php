<?php
session_start();
if(!isset($_SESSION['user_id'])) exit();

$conn=new mysqli("sql213.infinityfree.com","if0_41900150","Rany9NH3lawi","if0_41900150_my_first_project");
$conn->set_charset("utf8mb4");

$id=intval($_POST['message_id']??0);
$msg=trim($_POST['message']??'');
$uid=intval($_SESSION['user_id']);

$stmt=$conn->prepare("UPDATE group_messages SET message=? WHERE id=? AND sender_id=?");
$stmt->bind_param("sii",$msg,$id,$uid);
$stmt->execute();

echo "OK";
?>