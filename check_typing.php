<?php

session_start();

$conn = new mysqli(
    "sql213.infinityfree.com",
    "if0_41900150",
    "Rany9NH3lawi",
    "if0_41900150_my_first_project"
);

$friend_id = $_GET['friend_id'];

$result = $conn->query("
SELECT typing FROM typing_status
WHERE user_id = '$friend_id'
");

if($result && $result->num_rows > 0){

    $row = $result->fetch_assoc();

    if($row['typing'] == 1){
        echo "Typing...";
    }

}
?>