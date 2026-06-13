<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("sql213.infinityfree.com", "if0_41900150", "Rany9NH3lawi", "if0_41900150_my_first_project");
$conn->set_charset("utf8mb4");
date_default_timezone_set('Asia/Amman');

$conn->query("CREATE TABLE IF NOT EXISTS typing_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    friend_id INT NOT NULL,
    is_typing TINYINT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_typing (user_id, friend_id),
    INDEX idx_friend_status (friend_id, is_typing)
)");

$conn->query("CREATE TABLE IF NOT EXISTS user_activity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    friend_id INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_activity (user_id, friend_id),
    INDEX idx_user_activity_lookup (user_id, friend_id, updated_at)
)");

$current_user = $_SESSION['user_id'];
$friend_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT name, image FROM users WHERE id = ?");
$stmt->bind_param("i", $friend_id);
$stmt->execute();
$friend = $stmt->get_result()->fetch_assoc();

$userImage = "";
$imgResult = $conn->query("SELECT image FROM users WHERE id='$current_user'");
if ($imgResult && $imgResult->num_rows > 0) {
    $imgRow = $imgResult->fetch_assoc();
    $userImage = $imgRow['image'];
}

$notif_query = $conn->query("SELECT COUNT(*) AS total FROM notifications WHERE user_id = '$current_user' AND is_read = 0");
$notif_count = ($notif_query && $notif_query->num_rows > 0) ? $notif_query->fetch_assoc()['total'] : 0;
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Chat - <?php echo htmlspecialchars($friend['name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="main-icon.png">
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: #000;
            color: #fff;
            font-family: 'Orbitron', sans-serif;
            height: 100dvh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: fixed;
            width: 100%;
        }

        .chat-header {
            background: #000;
            border-bottom: 2px solid #930505;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            flex-shrink: 0;
            min-height: 60px;
        }

        .chat-header-user {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .chat-friend-img {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 2px solid #930505;
            object-fit: cover;
            flex-shrink: 0;
        }

        .chat-friend-name {
            font-size: 15px;
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 160px;
        }

        .back-btn {
            color: #930505;
            text-decoration: none;
            font-weight: bold;
            padding: 6px 12px;
            border: 1px solid #930505;
            border-radius: 20px;
            font-size: 12px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .bulk-delete-bar {
            display: none;
            position: sticky;
            top: 0;
            z-index: 1500;
            background: rgba(147,5,5, 0.95);
            padding: 8px 15px;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px dashed #fff;
            backdrop-filter: blur(5px);
            flex-shrink: 0;
        }

        .selected-count {
            background: #fff;
            color: #930505;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }

        .btn-delete-selected {
            background: #ff0000;
            color: #fff;
            border: none;
            padding: 5px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-family: 'Orbitron';
            font-size: 11px;
            font-weight: bold;
        }

        .messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 12px;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
        }

        .messages-container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
        }

        .message-row {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            margin-bottom: 10px;
            width: 100%;
            animation: fadeIn 0.3s ease;
            padding: 2px;
            border-radius: 10px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message-row.selected { background: rgba(147,5,5, 0.15); }
        .message-row.me { flex-direction: row-reverse; }
        .message-row.friend { flex-direction: row; }

        .msg-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid #930505;
            flex-shrink: 0;
            object-fit: cover;
            cursor: pointer;
        }

        .message-bubble {
            padding: 8px 12px;
            border-radius: 16px;
            max-width: min(72%, 520px);
            line-height: 1.4;
            font-family: Arial, sans-serif;
            word-break: break-word;
            overflow: hidden;
        }

        .message-row.me .message-bubble {
            background: linear-gradient(135deg, #930505, #b30a0a);
            color: #fff;
            border-bottom-right-radius: 3px;
        }

        .message-row.friend .message-bubble {
            background: #1a1a1a;
            color: #fff;
            border: 1px solid #333;
            border-bottom-left-radius: 3px;
        }

        .message-text { margin: 0; font-size: 13px; }

        .message-time {
            display: inline-block;
            font-size: 8px;
            margin-top: 4px;
            opacity: 0.6;
        }

        .voice-message { display: flex; align-items: center; gap: 8px; }
        .voice-message i { font-size: 16px; color: #930505; }
        audio { height: 28px; border-radius: 20px; max-width: 160px; }

        .typing-indicator-wrapper {
            padding: 4px 15px;
            background: linear-gradient(to top, #000, transparent);
            pointer-events: none;
            z-index: 100;
            min-height: 24px;
        }

        #typing-indicator {
            color: #930505;
            font-size: 10px;
            font-style: italic;
            padding: 2px 8px;
            background: rgba(0,0,0,0.8);
            border-radius: 12px;
            display: inline-block;
            border: 1px solid #930505;
        }

        .input-area {
            background: #000;
            border-top: 1px solid #930505;
            padding: 8px 10px;
            flex-shrink: 0;
            padding-bottom: max(8px, env(safe-area-inset-bottom));
        }

        .chat-form {
            display: flex;
            gap: 6px;
            align-items: center;
            width: 100%;
        }

        .chat-form textarea {
            flex: 1;
            background: #1a1a1a;
            border: 1px solid #930505;
            color: #fff;
            padding: 9px 12px;
            border-radius: 22px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            resize: none;
            height: 40px;
            max-height: 90px;
            outline: none;
            -webkit-appearance: none;
        }

        .chat-form textarea:focus { border-color: #b30a0a; }

        .chat-form button, .voice-btn {
            background: #930505;
            color: #fff;
            border: none;
            padding: 0 14px;
            border-radius: 22px;
            cursor: pointer;
            font-weight: bold;
            font-family: 'Orbitron';
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            height: 40px;
            font-size: 12px;
            white-space: nowrap;
            flex-shrink: 0;
            -webkit-tap-highlight-color: transparent;
        }

        .voice-btn { background: #1a1a1a; border: 1px solid #930505; }

        .voice-btn.recording {
            background: #ff0000 !important;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); }
        }

        .loading-messages { text-align: center; padding: 20px; color: #930505; font-size: 12px; }

        .toast-message {
            position: fixed;
            bottom: 80px;
            left: 10px;
            right: 10px;
            background: #930505;
            color: #fff;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 11px;
            z-index: 2000;
            text-align: center;
        }
		.hidden-check {
    opacity: 0;
    pointer-events: none;
    width: 14px;
    height: 14px;
    transition: 0.2s;
}

.message-row.selected .hidden-check {
    opacity: 1;
    pointer-events: auto;
}

        .media-btn,
        .sticker-btn,
        .make-sticker-btn {
            background:#1a1a1a;
            color:#930505;
            border:1px solid #930505;
            padding:0 12px;
            border-radius:22px;
            cursor:pointer;
            font-weight:bold;
            height:40px;
            font-size:14px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            flex-shrink:0;
            -webkit-tap-highlight-color:transparent;
        }

        .media-btn:hover,
        .sticker-btn:hover,
        .make-sticker-btn:hover {
            background:#930505;
            color:#fff;
        }

        .sticker-panel {
            display:none;
            position:fixed;
            left:10px;
            right:10px;
            bottom:70px;
            max-height:260px;
            overflow-y:auto;
            background:#0a0a0a;
            border:1px solid #930505;
            border-radius:18px;
            padding:12px;
            z-index:3000;
            box-shadow:0 0 25px rgba(147,5,5,.25);
        }

        .sticker-grid {
            display:grid;
            grid-template-columns:repeat(auto-fill, minmax(70px, 1fr));
            gap:10px;
        }

        .sticker-item {
            background:#111;
            border:1px solid #333;
            border-radius:14px;
            padding:8px;
            cursor:pointer;
        }

        .sticker-item img {
            width:100%;
            height:65px;
            object-fit:contain;
        }

        .no-stickers {
            text-align:center;
            color:#888;
            padding:20px;
        }

        .chat-media-img {
            width: auto;
            max-width: min(260px, 62vw);
            max-height: 320px;
            border-radius:14px;
            border:1px solid rgba(255,255,255,.15);
            display:block;
            object-fit:cover;
        }

        .chat-media-video {
            width: min(280px, 64vw);
            max-height: 320px;
            border-radius:14px;
            border:1px solid rgba(255,255,255,.15);
            display:block;
            object-fit:cover;
            background:#000;
        }

        .chat-sticker-img {
            width: min(130px, 38vw);
            height: min(130px, 38vw);
            object-fit:contain;
            display:block;
        }
.sticker-wrapper{
    position:relative;
    width:100%;
}

.sticker-wrapper .sticker-item{
    width:100%;
}

.delete-sticker-btn{
    position:absolute;
    top:-6px;
    right:-6px;
    width:24px !important;
    min-width:24px !important;
    height:24px !important;
    padding:0 !important;
    border:none;
    border-radius:50%;
    background:#ff0000;
    color:#fff;
    cursor:pointer;
    font-size:11px;
    display:flex;
    align-items:center;
    justify-content:center;
    z-index:5;
}

.delete-sticker-btn:hover{
    transform:scale(1.08);
}

        @media (max-width: 600px) {
            .messages-area {
                padding: 10px 8px;
            }

            .message-row {
                gap: 6px;
                margin-bottom: 9px;
            }

            .msg-avatar {
                width: 28px;
                height: 28px;
            }

            .message-bubble {
                max-width: 78%;
                padding: 7px 10px;
                border-radius: 15px;
            }

            .message-row.me .message-bubble {
                border-bottom-right-radius: 4px;
            }

            .message-row.friend .message-bubble {
                border-bottom-left-radius: 4px;
            }

            .chat-media-img {
                max-width: 68vw;
                max-height: 280px;
                border-radius: 13px;
            }

            .chat-media-video {
                width: 68vw;
                max-height: 280px;
                border-radius: 13px;
            }

            .chat-sticker-img {
                width: 115px;
                height: 115px;
            }

            audio {
                max-width: 170px;
            }

            .input-area {
                padding: 7px 8px;
                padding-bottom: max(7px, env(safe-area-inset-bottom));
            }

            .chat-form {
                gap: 5px;
            }

            .chat-form button,
            .voice-btn,
            .media-btn,
            .sticker-btn,
            .make-sticker-btn {
                width: 38px;
                min-width: 38px;
                height: 38px;
                padding: 0;
                border-radius: 50%;
            }

            .chat-form textarea {
                height: 38px;
                font-size: 13px;
                padding: 9px 11px;
            }

            #sendBtn {
                width: 46px;
                min-width: 46px;
                border-radius: 22px;
            }

            #sendBtn span {
                display:none;
            }
        }

        @media (max-width: 420px) {
            .message-bubble {
                max-width: 80%;
            }

            .chat-media-img {
                max-width: 66vw;
                max-height: 245px;
            }

            .chat-media-video {
                width: 66vw;
                max-height: 245px;
            }

            .chat-sticker-img {
                width: 105px;
                height: 105px;
            }

            audio {
                max-width: 145px;
            }
        }

        @media (max-width: 380px) {
            .chat-form button span { display: none; }
            .chat-form button, .voice-btn { padding: 0 12px; }
            .chat-friend-name { max-width: 120px; font-size: 13px; }
        }
        
    
.chat-sticker-video{
    width:min(130px,38vw);
    height:min(130px,38vw);
    object-fit:cover;
    display:block;
    border-radius:16px;
    background:#000;
}
.sticker-item video{
    width:100%;
    height:65px;
    object-fit:cover;
    border-radius:10px;
    display:block;
    background:#000;
}
@media(max-width:600px){
    .chat-sticker-video{width:115px;height:115px}
}
@media(max-width:420px){
    .chat-sticker-video{width:105px;height:105px}
}


.reply-compose-box{display:none;background:#111;border-left:3px solid #930505;border-radius:12px;padding:8px 10px;margin-bottom:8px;align-items:center;justify-content:space-between;gap:10px}
.reply-compose-text{color:#fff;font-size:12px;font-family:Arial,sans-serif;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.reply-compose-box button{background:transparent!important;border:none!important;color:#930505!important;width:auto!important;min-width:auto!important;height:auto!important;padding:3px!important;cursor:pointer}
.reply-preview{background:rgba(255,255,255,.12);border-left:3px solid #fff;padding:6px 8px;margin-bottom:6px;border-radius:8px;font-size:11px;opacity:.9}
.quick-actions{display:none;gap:5px;margin-top:6px;flex-wrap:wrap}
.message-bubble:hover .quick-actions,.message-row.selected .quick-actions{display:flex}
.reply-action,.react-action{background:rgba(0,0,0,.25)!important;border:1px solid rgba(255,255,255,.25)!important;color:#fff!important;width:auto!important;min-width:auto!important;height:24px!important;padding:2px 7px!important;border-radius:20px!important;font-size:12px!important;cursor:pointer}
.reaction-bar{display:flex;gap:5px;margin-top:6px;flex-wrap:wrap}
.reaction-chip{background:rgba(255,255,255,.18);padding:3px 7px;border-radius:20px;font-size:11px;line-height:1}

.chat-tools-bar{
    position:relative;
    display:flex;
    align-items:center;
    gap:10px;
    padding:8px 10px;
    background:transparent;
    flex-shrink:0;
}

#chatSearchInput{
    width:0;
    opacity:0;
    pointer-events:none;
    background:#1a1a1a;
    border:1px solid rgba(255,255,255,.08);
    height:40px;
    border-radius:12px;
    padding:0 12px;
    color:#fff;
    outline:none;
    transition:.25s ease;
    font-family:Arial,sans-serif;
}

#chatSearchInput.active{
    width:220px;
    opacity:1;
    pointer-events:auto;
}

#toggleSearchBtn{
    width:40px;
    height:40px;
    min-width:40px;
    border:none;
    border-radius:12px;
    background:#930505;
    color:#fff;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:16px;
    transition:.2s;
}

#toggleSearchBtn:hover{
    background:#b30a0a;
    transform:scale(1.05);
}
.pinned-message{
background:#1b1b1b;
border-left:3px solid #930505;
padding:8px 10px;
margin:6px 10px;
border-radius:10px;
font-size:12px;
color:#fff;
}
.message-menu{
display:none;
position:absolute;
top:-45px;
right:0;
background:#111;
padding:6px;
border-radius:12px;
gap:5px;
z-index:99;
}
.message-bubble{
position:relative;
overflow:visible!important;
}
.message-bubble.show-menu .message-menu{
display:flex;
}
.message-menu button{
background:#1e1e1e!important;
border:none!important;
color:#fff!important;
padding:5px 8px!important;
border-radius:8px!important;
font-size:11px!important;
width:auto!important;
height:auto!important;
}
.online-dot{
width:8px;
height:8px;
background:#00ff66;
border-radius:50%;
display:inline-block;
margin-left:5px;
}
.fullscreen-media{
position:fixed;
inset:0;
background:#000;
display:flex;
align-items:center;
justify-content:center;
z-index:99999;
}
.fullscreen-media img,
.fullscreen-media video{
max-width:100%;
max-height:100%;
}


@media (max-width: 650px){
    .chat-header{
        gap:8px;
    }

    #chatSearchInput.active{
        width:150px;
    }

    .chat-friend-name{
        max-width:110px;
    }

    .back-btn{
        padding:6px 9px;
        font-size:11px;
    }
}


.voice-record-bar{display:none;align-items:center;gap:10px;background:#111;border:1px solid #930505;border-radius:16px;padding:8px 12px;margin-bottom:8px;box-shadow:0 0 18px rgba(147,5,5,.25)}
.voice-record-bar.active{display:flex}
.voice-record-dot{width:11px;height:11px;border-radius:50%;background:#ff2222;box-shadow:0 0 12px rgba(255,0,0,.8);animation:voicePulse 1s infinite;flex-shrink:0}
@keyframes voicePulse{0%{transform:scale(1);opacity:1}50%{transform:scale(1.35);opacity:.55}100%{transform:scale(1);opacity:1}}
.voice-wave{display:flex;align-items:center;gap:3px;height:22px;flex:1}
.voice-wave span{width:3px;border-radius:10px;background:#930505;animation:voiceWave .7s infinite ease-in-out}
.voice-wave span:nth-child(1){height:8px;animation-delay:.05s}
.voice-wave span:nth-child(2){height:15px;animation-delay:.12s}
.voice-wave span:nth-child(3){height:20px;animation-delay:.20s}
.voice-wave span:nth-child(4){height:13px;animation-delay:.28s}
.voice-wave span:nth-child(5){height:18px;animation-delay:.36s}
.voice-wave span:nth-child(6){height:10px;animation-delay:.44s}
@keyframes voiceWave{0%,100%{transform:scaleY(.55);opacity:.65}50%{transform:scaleY(1.25);opacity:1}}
.voice-record-text{font-family:Arial,sans-serif;font-size:12px;color:#fff;white-space:nowrap}
.voice-record-time{color:#930505;font-size:12px;min-width:45px;text-align:center}
.voice-cancel-btn{background:transparent!important;border:none!important;color:#ff4444!important;width:28px!important;min-width:28px!important;height:28px!important;padding:0!important;cursor:pointer;font-size:16px!important}



/* ================= FULL PAGE FIRE THEME ================= */
body{
    background:
        radial-gradient(circle at 50% 100%, rgba(147,5,5,.28), transparent 38%),
        linear-gradient(180deg, #000 0%, #070000 45%, #120000 100%);
}

.fire-theme-bg{
    position:fixed;
    inset:0;
    z-index:-3;
    pointer-events:none;
    overflow:hidden;
    background:
        radial-gradient(circle at 15% 20%, rgba(147,5,5,.18), transparent 28%),
        radial-gradient(circle at 85% 75%, rgba(147,5,5,.22), transparent 32%),
        linear-gradient(180deg, #000 0%, #050000 45%, #130000 100%);
}

.fire-theme-bg::before{
    content:'';
    position:absolute;
    inset:-25%;
    background:
        radial-gradient(circle, rgba(147,5,5,.22) 0%, rgba(147,5,5,.08) 24%, transparent 55%);
    filter:blur(55px);
    animation:chatThemeGlow 8s ease-in-out infinite alternate;
}

.fire-theme-bg::after{
    content:'';
    position:absolute;
    inset:0;
    background:linear-gradient(120deg, transparent 0%, rgba(255,255,255,.045) 20%, transparent 38%);
    transform:translateX(-120%);
    animation:chatThemeShine 6s linear infinite;
}

@keyframes chatThemeGlow{
    0%{transform:translate(-8%,4%) scale(1);opacity:.75;}
    50%{transform:translate(8%,-5%) scale(1.12);opacity:1;}
    100%{transform:translate(-2%,7%) scale(.98);opacity:.85;}
}

@keyframes chatThemeShine{
    0%{transform:translateX(-120%)}
    100%{transform:translateX(120%)}
}

.fire-theme-particles{
    position:fixed;
    inset:0;
    z-index:-2;
    pointer-events:none;
    overflow:hidden;
}

.fire-theme-particles span{
    position:absolute;
    bottom:-110px;
    width:7px;
    height:90px;
    border-radius:50%;
    background:linear-gradient(to top, transparent, #930505, #ff2424, transparent);
    filter:blur(1px);
    box-shadow:0 0 12px #930505, 0 0 28px rgba(255,0,0,.85);
    animation:chatFireUp linear infinite;
    opacity:0;
}

.fire-theme-particles span:nth-child(1){left:4%;animation-duration:4s;animation-delay:0s;}
.fire-theme-particles span:nth-child(2){left:12%;animation-duration:5.4s;animation-delay:.4s;}
.fire-theme-particles span:nth-child(3){left:20%;animation-duration:4.6s;animation-delay:1.1s;}
.fire-theme-particles span:nth-child(4){left:29%;animation-duration:6s;animation-delay:.8s;}
.fire-theme-particles span:nth-child(5){left:38%;animation-duration:4.2s;animation-delay:1.5s;}
.fire-theme-particles span:nth-child(6){left:47%;animation-duration:5.7s;animation-delay:.2s;}
.fire-theme-particles span:nth-child(7){left:56%;animation-duration:4.8s;animation-delay:1s;}
.fire-theme-particles span:nth-child(8){left:65%;animation-duration:6.3s;animation-delay:.6s;}
.fire-theme-particles span:nth-child(9){left:74%;animation-duration:4.5s;animation-delay:1.3s;}
.fire-theme-particles span:nth-child(10){left:83%;animation-duration:5.5s;animation-delay:.3s;}
.fire-theme-particles span:nth-child(11){left:92%;animation-duration:4.9s;animation-delay:.9s;}
.fire-theme-particles span:nth-child(12){left:98%;animation-duration:6.2s;animation-delay:1.7s;}

@keyframes chatFireUp{
    0%{transform:translateY(0) scale(.55) rotate(0deg);opacity:0;}
    18%{opacity:.95;}
    100%{transform:translateY(-115vh) scale(1.75) rotate(18deg);opacity:0;}
}

.chat-header,
.bulk-delete-bar,
.messages-area,
.typing-indicator-wrapper,
.input-area,
.sticker-panel{
    position:relative;
    z-index:2;
}

.chat-header{
    background:rgba(0,0,0,.82)!important;
    backdrop-filter:blur(14px);
    box-shadow:0 10px 35px rgba(147,5,5,.18);
}

.messages-area{
    background:
        radial-gradient(circle at 50% 100%, rgba(147,5,5,.12), transparent 45%),
        rgba(0,0,0,.18);
}

.messages-container{
    position:relative;
    z-index:2;
}

.message-row.me .message-bubble{
    box-shadow:0 0 18px rgba(147,5,5,.35);
}

.message-row.friend .message-bubble{
    background:rgba(16,16,16,.88)!important;
    border:1px solid rgba(147,5,5,.28)!important;
    box-shadow:0 0 16px rgba(147,5,5,.12);
    backdrop-filter:blur(6px);
}

.input-area{
    background:rgba(0,0,0,.86)!important;
    backdrop-filter:blur(14px);
    box-shadow:0 -14px 45px rgba(147,5,5,.18);
}

.chat-form textarea,
#chatSearchInput{
    background:rgba(16,16,16,.9)!important;
    box-shadow:inset 0 0 18px rgba(147,5,5,.08);
}

.voice-record-bar,
.reply-compose-box,
.sticker-panel{
    background:rgba(10,0,0,.92)!important;
    box-shadow:0 0 25px rgba(147,5,5,.25);
}

.back-btn,
#toggleSearchBtn,
.chat-form button,
.voice-btn,
.media-btn,
.sticker-btn,
.make-sticker-btn{
    box-shadow:0 0 14px rgba(147,5,5,.22);
}

</style>
</head>
<body>
<div class="fire-theme-bg"></div>
<div class="fire-theme-particles">
    <span></span><span></span><span></span><span></span>
    <span></span><span></span><span></span><span></span>
    <span></span><span></span><span></span><span></span>
</div>


<div class="chat-header">

    <div class="chat-tools-bar">

        <button type="button" id="toggleSearchBtn">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>

        <input
            type="text"
            id="chatSearchInput"
            placeholder="Search messages..."
            autocomplete="off"
        >

    </div>

    <div class="chat-header-user" onclick="window.location.href='user_profile.php?id=<?php echo $friend_id; ?>'">
        <img src="<?php echo !empty($friend['image']) ? htmlspecialchars($friend['image']) : 'default.png'; ?>" class="chat-friend-img">
        <div>
            <div class="chat-friend-name"><?php echo htmlspecialchars($friend['name']); ?></div>
            <div style="font-size:9px; color:#4caf50;">● Online</div>
        </div>
    </div>
    <a href="friends.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="bulk-delete-bar" id="bulkDeleteBar">
    <span class="selected-count" id="selectedCount">0 selected</span>
    <button class="btn-delete-selected" id="deleteSelectedBtn">Delete</button>
</div>

<div class="messages-area" id="messages-area">
    <div class="messages-container" id="messages-container">
        <div class="loading-messages">
            <i class="fas fa-spinner fa-spin"></i> Loading...
        </div>
    </div>
</div>

<div class="typing-indicator-wrapper">
    <div id="typing-indicator"></div>
</div>

<div class="input-area">

    <div class="voice-record-bar" id="voiceRecordBar">
        <span class="voice-record-dot"></span>
        <span class="voice-record-text">Recording voice...</span>
        <div class="voice-wave"><span></span><span></span><span></span><span></span><span></span><span></span></div>
        <span class="voice-record-time" id="voiceRecordTime">00:00</span>
        <button type="button" class="voice-cancel-btn" id="cancelVoiceBtn" title="Cancel voice"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="reply-compose-box" id="replyBox"><div class="reply-compose-text" id="replyText"></div><button type="button" onclick="cancelReply()"><i class="fa-solid fa-xmark"></i></button></div>
    <div class="chat-form">
        <input type="hidden" id="receiver_id" value="<?php echo $friend_id; ?>">
        <button type="button" class="voice-btn" id="voiceBtn">
            <i class="fa-solid fa-microphone"></i>
            
        </button>
        <button type="button" class="media-btn" id="mediaBtn" title="Photo / Video">
            <i class="fa-solid fa-image"></i>
        </button>
        <input type="file" id="mediaInput" accept="image/*,video/*" hidden>

        <button type="button" class="sticker-btn" id="stickerBtn" title="Stickers">
            <i class="fa-regular fa-face-smile"></i>
        </button>

        <button type="button" class="make-sticker-btn" id="makeStickerBtn" title="Create Sticker">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
        </button>
        <input type="file" id="stickerMakerInput" accept="image/*,video/mp4,video/webm" hidden>

        <textarea id="message-text" placeholder="Type a message..." rows="1"></textarea>
        <button type="button" id="sendBtn">
            <i class="fa-regular fa-paper-plane"></i>
            <span>Send</span>
        </button>
    </div>
</div>


<div class="sticker-panel" id="stickerPanel">
    <div class="sticker-grid" id="stickerGrid"></div>
</div>

<script>
const messagesArea = document.getElementById('messages-area');
const messagesContainer = document.getElementById('messages-container');
const msgInput = document.getElementById('message-text');
const sendBtn = document.getElementById('sendBtn');
const voiceBtn = document.getElementById('voiceBtn');
const receiverId = document.getElementById('receiver_id').value;
const currentFriendId = <?php echo $friend_id; ?>;

const mediaBtn = document.getElementById('mediaBtn');
const mediaInput = document.getElementById('mediaInput');
const stickerBtn = document.getElementById('stickerBtn');
const stickerPanel = document.getElementById('stickerPanel');
const stickerGrid = document.getElementById('stickerGrid');
const makeStickerBtn = document.getElementById('makeStickerBtn');
const stickerMakerInput = document.getElementById('stickerMakerInput');

const voiceRecordBar = document.getElementById('voiceRecordBar');
const voiceRecordTime = document.getElementById('voiceRecordTime');
const cancelVoiceBtn = document.getElementById('cancelVoiceBtn');

mediaBtn?.addEventListener('click', () => {
    mediaInput.click();
});

mediaInput?.addEventListener('change', async () => {
    const file = mediaInput.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('receiver_id', currentFriendId);
    formData.append('media', file);

    try {
        const res = await fetch('send_media.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();

        if (data.success) {
            mediaInput.value = '';
            await loadMessages(true);
            scrollToBottom();
        } else {
            showToast(data.error || 'Failed to send media', true);
        }
    } catch (e) {
        showToast('Media upload failed', true);
    }
});

async function loadStickersPanel() {
    const res = await fetch('load_stickers.php?t=' + Date.now());
    stickerGrid.innerHTML = await res.text();

    document.querySelectorAll('.sticker-item').forEach(btn => {
        btn.addEventListener('click', async () => {
            const path = btn.dataset.path;

            const formData = new FormData();
            formData.append('receiver_id', currentFriendId);
            formData.append('sticker_path', path);

            const res = await fetch('send_sticker.php', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (data.success) {
                stickerPanel.style.display = 'none';
                await loadMessages(true);
                scrollToBottom();
            } else {
                showToast(data.error || 'Failed to send sticker', true);
            }
        });
    });

    document.querySelectorAll('.delete-sticker-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            if(!confirm('Delete this sticker?')) return;

            const path = btn.dataset.path;

            const formData = new FormData();
            formData.append('sticker_path', path);

            try {
                const res = await fetch('delete_sticker.php', {
                    method:'POST',
                    body:formData
                });

                const data = await res.json();

                if(data.success){
                    const wrapper = btn.closest('.sticker-wrapper');
                    if(wrapper) wrapper.remove();
                    showToast('Sticker deleted');
                }else{
                    showToast('Failed to delete sticker', true);
                }
            } catch(err) {
                showToast('Failed to delete sticker', true);
            }
        });
    });
}
stickerBtn?.addEventListener('click', async () => {
    if (stickerPanel.style.display === 'block') {
        stickerPanel.style.display = 'none';
    } else {
        stickerPanel.style.display = 'block';
        await loadStickersPanel();
    }
});

makeStickerBtn?.addEventListener('click', () => {
    stickerMakerInput.click();
});

stickerMakerInput?.addEventListener('change', async () => {
    const file = stickerMakerInput.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('sticker', file);

    try {
        const res = await fetch('make_sticker.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();

        if (data.success) {
            stickerMakerInput.value = '';
            stickerPanel.style.display = 'block';
            await loadStickersPanel();
            showToast('Sticker created');
        } else {
            showToast(data.error || 'Failed to create sticker', true);
        }
    } catch (e) {
        showToast('Failed to create sticker', true);
    }
});

document.addEventListener('click', (e) => {
    if (
        stickerPanel &&
        stickerPanel.style.display === 'block' &&
        !stickerPanel.contains(e.target) &&
        !e.target.closest('#stickerBtn')
    ) {
        stickerPanel.style.display = 'none';
    }
});


function setUserActivity(status) {
    fetch("update_user_activity.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `friend_id=${currentFriendId}&status=${status}`
    }).catch(e => console.log("Activity error:", e));
}

const friendName = "<?php echo htmlspecialchars($friend['name']); ?>";


let replyingTo = null;

function setReply(messageId, text){
    replyingTo = messageId;
    const replyBox = document.getElementById('replyBox');
    const replyText = document.getElementById('replyText');
    if(replyBox && replyText){
        replyBox.style.display = 'flex';
        replyText.innerText = (text && text.trim() !== '' ? text : 'Media message').substring(0, 90);
    }
    msgInput.focus();
}

function cancelReply(){
    replyingTo = null;
    const replyBox = document.getElementById('replyBox');
    if(replyBox) replyBox.style.display = 'none';
}

async function reactToMessage(messageId, reaction){
    const formData = new FormData();
    formData.append('message_id', messageId);
    formData.append('reaction', reaction);
    try{
        const res = await fetch('react_message.php', { method:'POST', body:formData });
        const data = await res.json();
        if(data.success){ await loadMessages(true); }
        else{ showToast(data.error || 'Failed to react', true); }
    }catch(e){ showToast('Failed to react', true); }
}

function bindMessageActions(){
    document.querySelectorAll('.reply-action').forEach(btn=>{
        btn.onclick = (e)=>{
            e.preventDefault();
            e.stopPropagation();
            setReply(btn.dataset.id, btn.dataset.text || 'Media message');
        };
    });

    document.querySelectorAll('.react-action').forEach(btn=>{
        btn.onclick = (e)=>{
            e.preventDefault();
            e.stopPropagation();
            reactToMessage(btn.dataset.id, btn.dataset.reaction);
        };
    });

    document.querySelectorAll('.message-bubble').forEach(bubble=>{
        bubble.ondblclick = (e)=>{
            if(e.target.closest('audio, video, button, input')) return;
            reactToMessage(bubble.dataset.id, '❤️');
        };
    });
}

let selectedMessages = new Set();
let typingTimeout;
let isRecording = false;
let mediaRecorder;
let audioChunks = [];
let recordingStream = null;
let pendingVoiceBlob = null;
let voiceTimer = null;
let voiceSeconds = 0;
let sendAfterStop = false;
let lastMessageHtml = '';

function updateSelectedCount() {
    const countSpan = document.getElementById('selectedCount');
    const deleteBar = document.getElementById('bulkDeleteBar');
    if (countSpan) countSpan.textContent = selectedMessages.size + ' selected';
    deleteBar.style.display = selectedMessages.size > 0 ? 'flex' : 'none';
}

function showToast(message, isError = false) {
    const toast = document.createElement('div');
    toast.className = 'toast-message';
    toast.style.background = isError ? '#ff0001' : '#930505';
    toast.innerHTML = `<i class="fas ${isError ? 'fa-exclamation-triangle' : 'fa-check-circle'}"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3001);
}

msgInput.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 91) + 'px';
});

function scrollToBottom() {
    messagesArea.scrollTop = messagesArea.scrollHeight;
}

function toggleMessageSelection(msgId) {

    const row = document.getElementById('msg_' + msgId);

    const checkbox =
    document.getElementById('check_' + msgId);

    if (row.classList.contains('selected')) {

        row.classList.remove('selected');

        selectedMessages.delete(msgId.toString());

        if (checkbox) {
            checkbox.checked = false;
        }

    } else {

        row.classList.add('selected');

        selectedMessages.add(msgId.toString());

        if (checkbox) {
            checkbox.checked = true;
        }
    }

    updateSelectedCount();
}

async function deleteSelectedMessages() {
    const messageIds = Array.from(selectedMessages);
    if (messageIds.length === 0) { showToast('Select messages first', true); return; }
    if (!confirm(`Delete ${messageIds.length} message(s)?`)) return;

    const formData = new FormData();
    formData.append('friend_id', currentFriendId);
    messageIds.forEach(id => formData.append('message_ids[]', id));

    try {
        const response = await fetch('delete_messages_bulk.php', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) {
            selectedMessages.clear();
            updateSelectedCount();
            showToast('Messages deleted');
            loadMessages(true);
        } else {
            showToast(result.error || 'Failed to delete', true);
        }
    } catch (error) {
        showToast('Error deleting messages', true);
    }
}

document.getElementById('deleteSelectedBtn')?.addEventListener('click', deleteSelectedMessages);

function updateTyping(status) {
    fetch("update_typing.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `friend_id=${currentFriendId}&is_typing=${status}`
    }).catch(e => console.log('Typing error:', e));
}

function checkTyping() {
    fetch(`get_typing_status.php?friend_id=${currentFriendId}&t=${Date.now()}`)
    .then(res => res.json())
    .then(data => {
        const indicator = document.getElementById("typing-indicator");
        if (data.is_typing == 1) {
            indicator.innerHTML = `<i class="fas fa-pencil-alt"></i> ${friendName} is typing...`;
            indicator.style.opacity = "1";
        } else {
            indicator.innerHTML = "";
            indicator.style.opacity = "0";
        }
    })
    .catch(e => console.log('Check typing error:', e));
}

msgInput.addEventListener("input", () => {
    updateTyping(1);
    clearTimeout(typingTimeout);
    typingTimeout = setTimeout(() => updateTyping(0), 1500);
});

msgInput.addEventListener("blur", () => updateTyping(0));

function markAsSeen() {
    fetch("seen.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `friend_id=${currentFriendId}`
    }).catch(e => console.log('Seen error:', e));
}

async function loadMessages(forceReload = false) {
    try {
        const response = await fetch(`load_messages.php?friend_id=${currentFriendId}&t=${Date.now()}`);
        const html = await response.text();

        if (forceReload || html !== lastMessageHtml) {
            const wasAtBottom = messagesArea.scrollHeight - messagesArea.scrollTop <= messagesArea.clientHeight + 101;

            messagesContainer.innerHTML = html;
            lastMessageHtml = html;
            applyMessageSearch();

            document.querySelectorAll('.message-row').forEach(row => {
                const msgId = row.id.replace('msg_', '');
                row.addEventListener('click', (e) => {
                    if (!e.target.closest('audio, video, button, input, .quick-actions, .reply-action, .react-action, .chat-media-img, .chat-sticker-img')) toggleMessageSelection(msgId);
                });
            });

            document.querySelectorAll('.message-row.friend .msg-avatar').forEach(avatar => {
                avatar.addEventListener('click', (e) => {
                    e.stopPropagation();
                    window.location.href = `user_profile.php?id=${currentFriendId}`;
                });
            });

            document.querySelectorAll('.message-row').forEach(row => {
                const msgId = row.id.replace('msg_', '');
                if (selectedMessages.has(msgId)) row.classList.add('selected');
            });

            bindMessageActions();
            if (wasAtBottom) scrollToBottom();
            markAsSeen();
        }
    } catch (error) {
        messagesContainer.innerHTML = error;
console.error(error);
    }
}

async function sendMessage() {
    if (isRecording || pendingVoiceBlob) {
        await finishAndSendVoice();
        return;
    }

    const text = msgInput.value.trim();
    if (text === "") return;

    try {
        await fetch("send_message.php", {
            method: "POST",
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `receiver_id=${currentFriendId}&message=${encodeURIComponent(text)}&reply_message_id=${replyingTo || ''}`
        });

        msgInput.value = "";
        msgInput.style.height = '41px';
        cancelReply();
        updateTyping(0);
        await loadMessages(true);
        scrollToBottom();
        msgInput.focus();
    } catch (error) {
        showToast('Failed to send message', true);
    }
}


function formatVoiceTime(seconds){
    const m = String(Math.floor(seconds / 60)).padStart(2, '0');
    const s = String(seconds % 60).padStart(2, '0');
    return `${m}:${s}`;
}

function startVoiceTimer(){
    voiceSeconds = 0;
    if(voiceRecordTime) voiceRecordTime.textContent = '00:00';
    clearInterval(voiceTimer);
    voiceTimer = setInterval(()=>{
        voiceSeconds++;
        if(voiceRecordTime) voiceRecordTime.textContent = formatVoiceTime(voiceSeconds);
    }, 1000);
}

function stopVoiceTimer(){
    clearInterval(voiceTimer);
    voiceTimer = null;
}

function showVoiceBar(){
    if(voiceRecordBar) voiceRecordBar.classList.add('active');
}

function hideVoiceBar(){
    if(voiceRecordBar) voiceRecordBar.classList.remove('active');
}

function resetVoiceUI(){
    isRecording = false;
    sendAfterStop = false;
    voiceBtn.classList.remove('recording');
    voiceBtn.innerHTML = '<i class="fa-solid fa-microphone"></i>';
    stopVoiceTimer();
    hideVoiceBar();
}

function toggleRecording() {
    if (isRecording) {
        showToast('Press Send to send the voice, or X to cancel');
        return;
    }

    if (pendingVoiceBlob) {
        showVoiceBar();
        showToast('Voice is ready. Press Send to send it.');
        return;
    }

    startRecording();
}

function startRecording() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        showToast('Voice recording not supported!', true);
        return;
    }

    navigator.mediaDevices.getUserMedia({ audio: true })
    .then(stream => {
        recordingStream = stream;
        mediaRecorder = new MediaRecorder(stream);
        audioChunks = [];
        pendingVoiceBlob = null;
        sendAfterStop = false;

        mediaRecorder.ondataavailable = event => {
            if (event.data && event.data.size > 0) audioChunks.push(event.data);
        };

        mediaRecorder.onstop = async () => {
            stopVoiceTimer();

            if (recordingStream) {
                recordingStream.getTracks().forEach(track => track.stop());
                recordingStream = null;
            }

            if (audioChunks.length > 0) {
                pendingVoiceBlob = new Blob(audioChunks, { type: 'audio/webm' });
            }

            isRecording = false;
            voiceBtn.classList.remove('recording');
            voiceBtn.innerHTML = '<i class="fa-solid fa-microphone"></i>';

            if (sendAfterStop && pendingVoiceBlob) {
                const blobToSend = pendingVoiceBlob;
                pendingVoiceBlob = null;
                hideVoiceBar();
                await sendVoiceMessage(blobToSend);
            }
        };

        mediaRecorder.start();
        isRecording = true;
        showVoiceBar();
        startVoiceTimer();
        voiceBtn.classList.add('recording');
        voiceBtn.innerHTML = '<i class="fa-solid fa-wave-square"></i>';
        showToast('Recording... press Send to send');

        setTimeout(() => {
            if (isRecording && mediaRecorder && mediaRecorder.state === 'recording') {
                showToast('Max voice time reached. Press Send to send.');
                mediaRecorder.stop();
            }
        }, 60000);
    })
    .catch(() => showToast('Cannot access microphone!', true));
}

function cancelVoiceRecording(){
    sendAfterStop = false;
    pendingVoiceBlob = null;
    audioChunks = [];

    if (mediaRecorder && isRecording && mediaRecorder.state === 'recording') {
        mediaRecorder.stop();
    }

    if (recordingStream) {
        recordingStream.getTracks().forEach(track => track.stop());
        recordingStream = null;
    }

    resetVoiceUI();
    showToast('Voice canceled');
}

async function finishAndSendVoice(){
    if (isRecording && mediaRecorder && mediaRecorder.state === 'recording') {
        sendAfterStop = true;
        mediaRecorder.stop();
        return true;
    }

    if (pendingVoiceBlob) {
        const blobToSend = pendingVoiceBlob;
        pendingVoiceBlob = null;
        resetVoiceUI();
        await sendVoiceMessage(blobToSend);
        return true;
    }

    return false;
}

async function sendVoiceMessage(audioBlob) {
    if (!audioBlob || audioBlob.size <= 0) {
        showToast('Voice is empty', true);
        resetVoiceUI();
        return;
    }

    const formData = new FormData();
    formData.append('receiver_id', receiverId);
    formData.append('voice', audioBlob, 'voice.webm');

    try {
        const response = await fetch('send_voice.php', { method: 'POST', body: formData });
        const data = await response.json();

        if (data.success) {
            showToast('Voice sent');
            await loadMessages(true);
            scrollToBottom();
        } else {
            showToast(data.error || 'Failed to send voice message', true);
        }
    } catch (error) {
        showToast('Failed to send voice message!', true);
    } finally {
        resetVoiceUI();
    }
}


sendBtn.addEventListener('click', sendMessage);
voiceBtn.addEventListener('click', toggleRecording);
cancelVoiceBtn?.addEventListener('click', cancelVoiceRecording);

msgInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

setInterval(async () => {

    try {

        const response = await fetch(
            `load_messages.php?friend_id=${currentFriendId}&t=${Date.now()}`
        );

        const html = await response.text();

        if (html !== lastMessageHtml) {

            const wasAtBottom =
                messagesArea.scrollHeight - messagesArea.scrollTop
                <= messagesArea.clientHeight + 101;

            messagesContainer.innerHTML = html;

            lastMessageHtml = html;
            applyMessageSearch();

            document.querySelectorAll('.message-row').forEach(row => {

                const msgId = row.id.replace('msg_', '');

                row.addEventListener('click', (e) => {

                    if (
                        !e.target.closest(
                            'audio, video, button, input, .quick-actions, .reply-action, .react-action, .chat-media-img, .chat-sticker-img'
                        )
                    ) {
                        toggleMessageSelection(msgId);
                    }
                });
            });

            bindMessageActions();
            if (wasAtBottom) {
                scrollToBottom();
            }

            markAsSeen();
        }

    } catch (e) {
        console.log(e);
    }

}, 3001);
setInterval(checkTyping, 2001);
setInterval(() => setUserActivity(1), 5000);

setUserActivity(1);
loadMessages(true);
setTimeout(scrollToBottom, 501);
msgInput.focus();

window.addEventListener('beforeunload', () => {
    if (isRecording) cancelVoiceRecording();
    updateTyping(0);
    setUserActivity(0);
});
    document.addEventListener('contextmenu', async function(e) {
    const sticker = e.target.closest('.saveable-sticker');

    if (!sticker) return;

    e.preventDefault();

    const path = sticker.dataset.sticker;

    const formData = new FormData();
    formData.append('sticker_path', path);

    const res = await fetch('save_sticker.php', {
        method: 'POST',
        body: formData
    });

    const data = await res.json();

    if (data.success) {
        showToast(data.message || 'Sticker saved');
    } else {
        showToast('Failed to save sticker', true);
    }
});
    let swipeStartX = 0;
let swipeStartY = 0;
let swipingRow = null;

document.addEventListener('touchstart', function(e) {
    const row = e.target.closest('.message-row');

    if (!row || e.target.closest('audio, video, button, input')) return;

    swipeStartX = e.touches[0].clientX;
    swipeStartY = e.touches[0].clientY;
    swipingRow = row;
}, { passive: true });

document.addEventListener('touchmove', function(e) {
    if (!swipingRow) return;

    const moveX = e.touches[0].clientX - swipeStartX;
    const moveY = e.touches[0].clientY - swipeStartY;

    if (Math.abs(moveY) > 26) return;

    if (moveX > 0 && moveX < 85) {
        swipingRow.style.transform = `translateX(${moveX}px)`;
    }
}, { passive: true });

document.addEventListener('touchend', function() {
    if (!swipingRow) return;

    const moved = parseInt(
        swipingRow.style.transform.replace('translateX(', '')
    ) || 0;

    if (moved > 55) {
        const msgId = swipingRow.id.replace('msg_', '').replace('gmsg_', '');
        const text = swipingRow.dataset.messageText || 'Media message';

        setReply(msgId, text);
    }

    swipingRow.style.transform = '';
    swipingRow = null;
});


function applyMessageSearch(){
    const input = document.getElementById('chatSearchInput');
    if(!input) return;

    const value = input.value.toLowerCase().trim();

    document.querySelectorAll('.message-row').forEach(row=>{
        const text = (row.innerText || '').toLowerCase();
        row.style.display = text.includes(value) ? '' : 'none';
    });
}

function resetMessageSearch(){
    document.querySelectorAll('.message-row').forEach(row=>{
        row.style.display = '';
    });
}

const chatSearchBtn = document.getElementById('toggleSearchBtn');
const chatSearchInput = document.getElementById('chatSearchInput');

if(chatSearchBtn && chatSearchInput){

    chatSearchBtn.addEventListener('click', ()=>{

        chatSearchInput.classList.toggle('active');

        if(chatSearchInput.classList.contains('active')){
            chatSearchInput.focus();
        }else{
            chatSearchInput.value = '';
            resetMessageSearch();
        }
    });

    chatSearchInput.addEventListener('input', applyMessageSearch);
}

</script>
</body>
</html>