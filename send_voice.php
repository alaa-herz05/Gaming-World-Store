<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$conn = new mysqli("sql213.infinityfree.com", "if0_41900150", "Rany9NH3lawi", "if0_41900150_my_first_project");
$conn->set_charset("utf8mb4");

$sender_id = $_SESSION['user_id'];
$sender_name = $_SESSION['user_name'];
$receiver_id = intval($_POST['receiver_id']);

// التحقق من وجود الملف
if (!isset($_FILES['voice']) || $_FILES['voice']['error'] !== UPLOAD_ERR_OK) {
    $error_message = '';
    if (isset($_FILES['voice'])) {
        switch ($_FILES['voice']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $error_message = 'File too large (server limit)';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $error_message = 'File too large (form limit)';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message = 'File was partially uploaded';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_message = 'No file uploaded';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error_message = 'Missing temporary folder';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error_message = 'Failed to write file';
                break;
            default:
                $error_message = 'Unknown upload error';
        }
    } else {
        $error_message = 'No voice file received';
    }
    echo json_encode(['success' => false, 'error' => $error_message]);
    exit();
}

// التحقق من حجم الملف
if ($_FILES['voice']['size'] > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'File too large (max 10MB)']);
    exit();
}

// إنشاء مجلد الصوتيات
$audio_dir = 'uploads/audio/';
if (!file_exists($audio_dir)) {
    mkdir($audio_dir, 0777, true);
}

$filename = time() . '_voice_' . rand(10000, 99999) . '.webm';
$file_path = $audio_dir . $filename;

// نقل الملف المرفوع
if (move_uploaded_file($_FILES['voice']['tmp_name'], $file_path)) {
    // التحقق من أن الملف تم حفظه بشكل صحيح
    if (file_exists($file_path) && filesize($file_path) > 0) {
        $getUser = $conn->query("SELECT name FROM users WHERE id = '$receiver_id'");
        $userData = $getUser->fetch_assoc();
        $receiver_name = $userData['name'];
        
        $stmt = $conn->prepare("INSERT INTO user_messages (sender_id, sender_name, receiver_id, receiver_name, message, message_type, file_path, created_at) VALUES (?, ?, ?, ?, '🎤 Voice message', 'voice', ?, NOW())");
        $stmt->bind_param("isiss", $sender_id, $sender_name, $receiver_id, $receiver_name, $file_path);
        
        if ($stmt->execute()) {
            $is_active = $conn->query("
    SELECT id
    FROM user_activity
    WHERE user_id = '$receiver_id'
    AND friend_id = '$sender_id'
    AND updated_at > NOW() - INTERVAL 10 SECOND
    LIMIT 1
");

if (!$is_active || $is_active->num_rows == 0) {

    $notification_text =
        $sender_name . " sent you a voice message";

    $notif = $conn->prepare("
        INSERT INTO notifications
        (user_id, sender_id, text)
        VALUES (?, ?, ?)
    ");

    if ($notif) {

        $notif->bind_param(
            "iis",
            $receiver_id,
            $sender_id,
            $notification_text
        );

        $notif->execute();
        $notif->close();
    }
}
            echo json_encode(['success' => true, 'file_size' => filesize($file_path)]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'File was not saved correctly']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file']);
}

$conn->close();
?>