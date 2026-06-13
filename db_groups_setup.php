<?php
session_start();

$conn = new mysqli(
    "sql213.infinityfree.com",
    "if0_41900150",
    "Rany9NH3lawi",
    "if0_41900150_my_first_project"
);

if ($conn->connect_error) {
    die("Database Error");
}

$conn->set_charset("utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS groups_list (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL,
    group_image TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS group_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_group_member (group_id, user_id),
    INDEX idx_group_id (group_id),
    INDEX idx_user_id (user_id)
) DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS group_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    sender_id INT NOT NULL,
    sender_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL,
    message TEXT CHARACTER SET utf8mb4,
    message_type VARCHAR(20) DEFAULT 'text',
    file_path TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_group_messages (group_id, id)
) DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS group_activity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_group_activity (group_id, user_id),
    INDEX idx_group_activity (group_id, user_id, updated_at)
) DEFAULT CHARSET=utf8mb4");

echo "Groups tables created successfully.";
?>
