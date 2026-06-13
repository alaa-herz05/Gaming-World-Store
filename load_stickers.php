<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    exit();
}

$conn = new mysqli(
    "sql213.infinityfree.com",
    "if0_41900150",
    "Rany9NH3lawi",
    "if0_41900150_my_first_project"
);

if ($conn->connect_error) {
    exit();
}

$conn->set_charset("utf8mb4");

$user_id = intval($_SESSION['user_id']);

$stickers = $conn->query("
    SELECT *
    FROM user_stickers
    WHERE user_id = '$user_id'
    ORDER BY id DESC
");

if ($stickers && $stickers->num_rows > 0) {
    while ($s = $stickers->fetch_assoc()) {
        $path_raw = $s['sticker_path'];
        $path = htmlspecialchars($path_raw, ENT_QUOTES, 'UTF-8');
        $ext = strtolower(pathinfo($path_raw, PATHINFO_EXTENSION));
        $is_video = in_array($ext, ['mp4', 'webm']);

        echo '
        <div class="sticker-wrapper">
            <button type="button" class="sticker-item" data-path="' . $path . '">';

        if ($is_video) {
            echo '<video autoplay loop muted playsinline><source src="' . $path . '"></video>';
        } else {
            echo '<img src="' . $path . '" alt="Sticker">';
        }

        echo '
            </button>
            <button type="button" class="delete-sticker-btn" data-path="' . $path . '" title="Delete sticker">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>';
    }
} else {
    echo '<div class="no-stickers">No stickers yet</div>';
}
?>
