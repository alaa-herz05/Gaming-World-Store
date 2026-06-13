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
$gid = intval($_GET['group_id'] ?? 0);

$member_check = $conn->query("
    SELECT id
    FROM group_members
    WHERE group_id='$gid'
    AND user_id='$mid'
    LIMIT 1
");

if (!$member_check || $member_check->num_rows == 0) exit();

$msgs = $conn->query("
    SELECT gm.*, u.image
    FROM group_messages gm
    LEFT JOIN users u ON gm.sender_id = u.id
    WHERE gm.group_id='$gid'
    ORDER BY gm.id ASC
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

    $avatar = !empty($m['image'])
        ? $m['image']
        : "default.png";

    $created = new DateTime($m['created_at'], new DateTimeZone('UTC'));
    $created->modify('+10 hours');
    $time = $created->format('h:i A');

    $reply_preview = "";
    if (!empty($m['reply_message_id'])) {
        $rid = intval($m['reply_message_id']);
        $rq = $conn->query("SELECT message, message_type, media_type FROM group_messages WHERE id='$rid' LIMIT 1");
        if ($rq && $rq->num_rows > 0) {
            $rd = $rq->fetch_assoc();
            $reply_preview = !empty($rd['message']) ? $rd['message'] : "Media message";
        }
    }

    if (!empty($m['message'])) {
        $message_for_reply = $m['message'];
    } elseif ($message_type === 'voice') {
        $message_for_reply = '🎤 Voice message';
    } elseif ($media_type === 'image') {
        $message_for_reply = '📷 Image';
    } elseif ($media_type === 'video') {
        $message_for_reply = '🎬 Video';
    } elseif ($media_type === 'sticker') {
        $message_for_reply = '💟 Sticker';
    } else {
        $message_for_reply = 'Media message';
    }

    $reaction_html = "";
    $react_q = $conn->query("SELECT reaction, COUNT(*) AS total FROM group_message_reactions WHERE message_id='{$m['id']}' GROUP BY reaction");
    if ($react_q && $react_q->num_rows > 0) {
        while($react = $react_q->fetch_assoc()) {
            $reaction_html .= "<span class='reaction-chip'>" . htmlspecialchars($react['reaction'], ENT_QUOTES, 'UTF-8') . " " . intval($react['total']) . "</span>";
        }
    }
?>

<div class="message-row <?php echo $is_me ? 'me' : 'friend'; ?>"
     id="gmsg_<?php echo $m['id']; ?>"
     data-message-text="<?php echo htmlspecialchars(strip_tags($message_for_reply), ENT_QUOTES, 'UTF-8'); ?>">

    <input
        type="checkbox"
        class="msg-checkbox hidden-check"
        id="gcheck_<?php echo $m['id']; ?>"
        value="<?php echo $m['id']; ?>"
    >

    <img
        src="<?php echo htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8'); ?>"
        class="msg-avatar"
        data-user-id="<?php echo intval($m['sender_id']); ?>"
    >

    <div class="message-bubble" data-id="<?php echo $m['id']; ?>">

<?php if(!empty($m['reply_message_id'])): ?>

<?php
$reply_id = intval($m['reply_message_id']);

$reply_q = $conn->query("
    SELECT sender_name, message, message_type, media_type
    FROM group_messages
    WHERE id='$reply_id'
    LIMIT 1
");

$reply_data = $reply_q && $reply_q->num_rows > 0
    ? $reply_q->fetch_assoc()
    : null;

$reply_sender = $reply_data['sender_name'] ?? 'User';

if (!empty($reply_data['message'])) {

    $reply_text = $reply_data['message'];

} elseif (($reply_data['message_type'] ?? '') === 'voice') {

    $reply_text = '🎤 Voice message';

} elseif (($reply_data['media_type'] ?? '') === 'image') {

    $reply_text = '📷 Image';

} elseif (($reply_data['media_type'] ?? '') === 'video') {

    $reply_text = '🎬 Video';

} elseif (($reply_data['media_type'] ?? '') === 'sticker') {

    $reply_text = '💟 Sticker';

} else {

    $reply_text = 'Message';
}
?>

<div class="reply-preview">

    <div class="reply-user">
        <?php echo htmlspecialchars($reply_sender, ENT_QUOTES, 'UTF-8'); ?>
    </div>

    <div class="reply-text">
        <?php
        echo htmlspecialchars(
            mb_substr($reply_text, 0, 80),
            ENT_QUOTES,
            'UTF-8'
        );
        ?>
    </div>

</div>

<?php endif; ?>

        <?php if(!$is_me): ?>
            <div class="sender-name">
                <?php echo htmlspecialchars($m['sender_name'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
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
            <button type="button" class="reply-action" data-id="<?php echo $m['id']; ?>" data-text="<?php echo htmlspecialchars(strip_tags($message_for_reply), ENT_QUOTES, 'UTF-8'); ?>"><i class="fa-solid fa-reply"></i></button>
            <button type="button" class="react-action" data-id="<?php echo $m['id']; ?>" data-reaction="❤️">❤️</button>
            <button type="button" class="react-action" data-id="<?php echo $m['id']; ?>" data-reaction="😂">😂</button>
            <button type="button" class="react-action" data-id="<?php echo $m['id']; ?>" data-reaction="🔥">🔥</button>
            <button type="button" class="react-action" data-id="<?php echo $m['id']; ?>" data-reaction="👍">👍</button>
        </div>
        <?php if(!empty($reaction_html)): ?><div class="reaction-bar"><?php echo $reaction_html; ?></div><?php endif; ?>

        <small class="message-time">
            <?php echo $time; ?>
        </small>

    </div>
</div>

<?php endwhile; ?>