<?php
session_start();
if (!isset($_SESSION['user_id'])) exit();

$conn = new mysqli(
    "sql213.infinityfree.com",
    "if0_41900150",
    "Rany9NH3lawi",
    "if0_41900150_my_first_project"
);

$conn->set_charset("utf8mb4");

$mid = intval($_SESSION['user_id']);
$fid = intval($_GET['friend_id'] ?? 0);

$u_q = $conn->query("SELECT image FROM users WHERE id='$mid'");
$u_img = ($r = $u_q->fetch_assoc()) && !empty($r['image'])
    ? $r['image']
    : "default.png";

$f_q = $conn->query("SELECT image FROM users WHERE id='$fid'");
$f_img = ($r = $f_q->fetch_assoc()) && !empty($r['image'])
    ? $r['image']
    : "default.png";

$msgs = $conn->query("
    SELECT *
    FROM user_messages
    WHERE (sender_id='$mid' AND receiver_id='$fid')
       OR (sender_id='$fid' AND receiver_id='$mid')
    ORDER BY id ASC
");

while($m = $msgs->fetch_assoc()):

    $is_me = ($m['sender_id'] == $mid);

    $message_type = !empty($m['message_type'])
        ? $m['message_type']
        : 'text';

    $file_path = !empty($m['file_path'])
        ? $m['file_path']
        : '';

    $media_type = !empty($m['media_type'])
        ? $m['media_type']
        : $message_type;

    $media_path = !empty($m['media_path'])
        ? $m['media_path']
        : $file_path;

    $message_text = htmlspecialchars(
        $m['message'] ?? '',
        ENT_QUOTES,
        'UTF-8'
    );

    $created = new DateTime($m['created_at'], new DateTimeZone('UTC'));
    $created->modify('+10 hours');
    $time = $created->format('h:i A');

    $reply_preview = "";
    if (!empty($m['reply_message_id'])) {
        $rid = intval($m['reply_message_id']);
        $rq = $conn->query("SELECT message, message_type, media_type FROM user_messages WHERE id='$rid' LIMIT 1");
        if ($rq && $rq->num_rows > 0) {
            $rd = $rq->fetch_assoc();
            $reply_preview = !empty($rd['message']) ? $rd['message'] : "Media message";
        }
    }

    $reaction_html = "";
    $react_q = $conn->query("SELECT reaction, COUNT(*) AS total FROM message_reactions WHERE message_id='{$m['id']}' GROUP BY reaction");
    if ($react_q && $react_q->num_rows > 0) {
        while($react = $react_q->fetch_assoc()) {
            $reaction_html .= "<span class='reaction-chip'>" . htmlspecialchars($react['reaction'], ENT_QUOTES, 'UTF-8') . " " . intval($react['total']) . "</span>";
        }
    }
?>

<div class="message-row <?php echo $is_me ? 'me' : 'friend'; ?>"
     id="msg_<?php echo $m['id']; ?>"
     data-message-text="<?php echo htmlspecialchars(strip_tags($m['message'] ?? 'Media message'), ENT_QUOTES, 'UTF-8'); ?>">

    <input
        type="checkbox"
        class="msg-checkbox hidden-check"
        id="check_<?php echo $m['id']; ?>"
        value="<?php echo $m['id']; ?>"
    >

    <?php if($is_me): ?>
        <img
            src="<?php echo htmlspecialchars($u_img, ENT_QUOTES, 'UTF-8'); ?>"
            class="msg-avatar"
        >
    <?php else: ?>
        <img
            src="<?php echo htmlspecialchars($f_img, ENT_QUOTES, 'UTF-8'); ?>"
            class="msg-avatar"
        >
    <?php endif; ?>

    <div class="message-bubble" data-id="<?php echo $m['id']; ?>">

        
<div class="message-menu">
<button type="button"
onclick="editMessage(
'<?php echo $m['id']; ?>',
`<?php echo htmlspecialchars($m['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>`,
'private'
)">
<i class="fa-solid fa-pen"></i>
</button>

<button type="button"
onclick="deleteForEveryone(
'<?php echo $m['id']; ?>',
'private'
)">
<i class="fa-solid fa-trash"></i>
</button>



<a href="<?php echo htmlspecialchars($media_path ?? $file_path ?? '', ENT_QUOTES, 'UTF-8'); ?>"
download
class="reply-action">
<i class="fa-solid fa-download"></i>
</a>
</div>

<?php if(!empty($reply_preview)): ?>
            <div class="reply-preview"><i class="fa-solid fa-reply"></i> <?php echo htmlspecialchars(mb_substr($reply_preview, 0, 80), ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if($message_type == 'voice' && !empty($file_path)): ?>

            <div class="voice-message">
                <i class="fa-solid fa-microphone-lines"></i>

                <audio controls preload="metadata">
                    <source
                        src="<?php echo htmlspecialchars($file_path, ENT_QUOTES, 'UTF-8'); ?>"
                        type="audio/webm"
                    >
                    Your browser does not support audio.
                </audio>
            </div>

        <?php elseif($media_type == 'image' && !empty($media_path)): ?>

            <img
                src="<?php echo htmlspecialchars($media_path, ENT_QUOTES, 'UTF-8'); ?>"
                class="chat-media-img"
                alt="Image"
            >

        <?php elseif($media_type == 'video' && !empty($media_path)): ?>

            <video controls class="chat-media-video">
                <source src="<?php echo htmlspecialchars($media_path, ENT_QUOTES, 'UTF-8'); ?>">
                Your browser does not support video.
            </video>

        <?php elseif($media_type == 'sticker' && !empty($media_path)): ?>

            <?php
            $sticker_ext = strtolower(pathinfo($media_path, PATHINFO_EXTENSION));
            $is_video_sticker = in_array($sticker_ext, ['mp4', 'webm']);
            ?>

            <?php if($is_video_sticker): ?>
                <video
                    autoplay
                    loop
                    muted
                    playsinline
                    class="chat-sticker-video saveable-sticker"
                    data-sticker="<?php echo htmlspecialchars($media_path, ENT_QUOTES, 'UTF-8'); ?>"
                >
                    <source src="<?php echo htmlspecialchars($media_path, ENT_QUOTES, 'UTF-8'); ?>">
                    Your browser does not support video.
                </video>
            <?php else: ?>
                <img
                    src="<?php echo htmlspecialchars($media_path, ENT_QUOTES, 'UTF-8'); ?>"
                    class="chat-sticker-img saveable-sticker"
                    data-sticker="<?php echo htmlspecialchars($media_path, ENT_QUOTES, 'UTF-8'); ?>"
                    alt="Sticker"
                >
            <?php endif; ?>

        <?php else: ?>

            <p class="message-text">
                <?php echo nl2br($message_text); ?>
            </p>

        <?php endif; ?>

        <div class="quick-actions">
            <button type="button" class="reply-action" data-id="<?php echo $m['id']; ?>" data-text="<?php echo htmlspecialchars(strip_tags($m['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"><i class="fa-solid fa-reply"></i></button>
            <button type="button" class="react-action" data-id="<?php echo $m['id']; ?>" data-reaction="❤️">❤️</button>
            <button type="button" class="react-action" data-id="<?php echo $m['id']; ?>" data-reaction="😂">😂</button>
            <button type="button" class="react-action" data-id="<?php echo $m['id']; ?>" data-reaction="🔥">🔥</button>
            <button type="button" class="react-action" data-id="<?php echo $m['id']; ?>" data-reaction="👍">👍</button>
        </div>
        <?php if(!empty($reaction_html)): ?><div class="reaction-bar"><?php echo $reaction_html; ?></div><?php endif; ?>

        <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-top:5px;">
            <small class="message-time">
                <?php echo $time; ?>
            </small>

            <?php if($is_me && isset($m['is_seen']) && $m['is_seen'] == 1): ?>
                <small style="color:#00ff00;">
                    <i class="fa-solid fa-check-double"></i>
                </small>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php endwhile; ?>