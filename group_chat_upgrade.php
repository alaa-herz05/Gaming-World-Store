<?php
session_start();
$conn = new mysqli("sql213.infinityfree.com", "if0_41900150", "Rany9NH3lawi", "if0_41900150_my_first_project");
if ($conn->connect_error) { die("Database Error"); }
$conn->set_charset("utf8mb4");

function add_column_if_missing($conn, $table, $column, $definition) {
    $table_safe = $conn->real_escape_string($table);
    $column_safe = $conn->real_escape_string($column);
    $check = $conn->query("SHOW COLUMNS FROM `$table_safe` LIKE '$column_safe'");
    if ($check && $check->num_rows == 0) {
        $conn->query("ALTER TABLE `$table_safe` ADD COLUMN $definition");
    }
}

$conn->query("CREATE TABLE IF NOT EXISTS group_typing_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    is_typing TINYINT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_group_typing (group_id, user_id),
    INDEX idx_group_typing (group_id, is_typing, updated_at)
) DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS group_activity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_group_activity (group_id, user_id),
    INDEX idx_group_activity (group_id, user_id, updated_at)
) DEFAULT CHARSET=utf8mb4");

add_column_if_missing($conn, "group_messages", "message_type", "message_type VARCHAR(20) DEFAULT 'text'");
add_column_if_missing($conn, "group_messages", "file_path", "file_path TEXT");
add_column_if_missing($conn, "notifications", "group_id", "group_id INT NULL");

echo "Group chat upgrade completed successfully.";
?>
