<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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

if (isset($_POST['delete_multiple_messages'])) {
    if (!empty($_POST['message_ids'])) {
        $message_ids = array_map('intval', $_POST['message_ids']);
        $ids_string = implode(',', $message_ids);

        $stmt = $conn->prepare("DELETE FROM message_replies WHERE message_id IN ($ids_string)");
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM messages WHERE id IN ($ids_string) AND user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: my_messages.php");
    exit();
}

if (isset($_POST['delete_multiple_admin_messages'])) {
    if (!empty($_POST['admin_message_ids'])) {
        $admin_ids = array_map('intval', $_POST['admin_message_ids']);
        $admin_ids_string = implode(',', $admin_ids);

        $stmt = $conn->prepare("DELETE FROM admin_messages WHERE id IN ($admin_ids_string) AND user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: my_messages.php");
    exit();
}

$userImage = "";
$imgStmt = $conn->prepare("SELECT image FROM users WHERE id = ?");
$imgStmt->bind_param("i", $user_id);
$imgStmt->execute();
$imgResult = $imgStmt->get_result();

if ($imgResult && $imgResult->num_rows > 0) {
    $imgRow = $imgResult->fetch_assoc();
    $userImage = $imgRow['image'];
}
$imgStmt->close();

$admin_messages = $conn->query("
    SELECT *
    FROM admin_messages
    WHERE user_id = '$user_id'
    ORDER BY created_at DESC
");

$stmt = $conn->prepare("
    SELECT 
        messages.id,
        messages.subject,
        messages.message,
        messages.created_at,
        message_replies.reply,
        message_replies.created_at AS reply_date
    FROM messages
    LEFT JOIN message_replies 
    ON messages.id = message_replies.message_id
    WHERE messages.user_id = ?
    ORDER BY messages.created_at DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>My Messages - Gaming World</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="Icon.png">
<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#000;
    color:#fff;
    font-family:'Orbitron',sans-serif;
    min-height:100vh;
}

/* Header */
header{
    background:#000;
    border-bottom:2px solid #930505;
    padding:15px 30px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:18px;
}

.site-logo{
    color:#930505;
    font-family:'Orbitron',sans-serif;
    font-size:28px;
    font-weight:900;
    text-shadow:0 0 12px rgba(147,5,5,.65);
}
.logo-img{
    width:110px;
    max-width:100%;
    object-fit:contain;
    filter:
        drop-shadow(0 0 10px rgba(147,5,5,.7))
        drop-shadow(0 0 20px rgba(147,5,5,.35));
}
.auth-links{
    position:relative;
    min-height:76px;
    background:#070707;
    border:1px solid rgba(147,5,5,.75);
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:0;
    padding:0 14px;
    box-shadow:0 0 18px rgba(147,5,5,.25), inset 0 0 18px rgba(147,5,5,.08);
}

.auth-links a{
    color:#fff;
    text-decoration:none;
    transition:.45s cubic-bezier(.68,-.55,.265,1.55);
    font-size:18px;
}

.magic-nav-item{
    position:relative;
    width:74px;
    height:74px;
    display:flex;
    align-items:center;
    justify-content:center;
    z-index:2;
}

.magic-nav-item .magic-icon{
    position:relative;
    display:flex;
    align-items:center;
    justify-content:center;
    width:42px;
    height:42px;
    border-radius:50%;
    color:#fff;
    transition:.45s cubic-bezier(.68,-.55,.265,1.55);
    overflow:hidden;
}

.magic-nav-item .magic-icon i{
    font-size:20px;
    position:relative;
    z-index:2;
    transition:.35s ease;
}

.magic-nav-item .magic-text{
    position:absolute;
    bottom:8px;
    left:50%;
    transform:translate(-50%,18px);
    color:#930505;
    font-size:10px;
    font-weight:700;
    letter-spacing:.03em;
    opacity:0;
    pointer-events:none;
    white-space:nowrap;
    transition:.35s ease;
    text-shadow:0 0 8px rgba(147,5,5,.65);
}

.magic-nav-item:hover .magic-icon{
    transform:translateY(-34px) scale(1.08);
    background:#930505;
    color:#000;
    box-shadow:
        0 0 20px rgba(147,5,5,.95),
        0 0 40px rgba(147,5,5,.45);
}

.magic-nav-item:hover .magic-icon i{
    transform:scale(1.08);
}

.magic-nav-item:hover .magic-text{
    opacity:1;
    transform:translate(-50%,0);
}

.welcome-user-box{
    padding:0;
    border:none;
    background:transparent;
}

.welcome-user-box .magic-icon{
    border:1px solid rgba(147,5,5,.8);
}

.nav-profile-img{
    width:100%;
    height:100%;
    border-radius:50%;
    object-fit:cover;
}

.default-nav-icon{
    background:#930505;
    display:flex;
    align-items:center;
    justify-content:center;
}

.default-nav-icon i{
    color:#000;
    font-size:16px;
}

.nav-username{
    display:none;
}

.bell-link{
    position:relative;
}

.bell-count{
    position:absolute;
    top:7px;
    right:13px;
    background:#ff0000;
    color:#fff;
    font-size:10px;
    padding:2px 5px;
    border-radius:50%;
    z-index:4;
    box-shadow:0 0 10px rgba(255,0,0,.8);
    transition:.35s ease;
}

.magic-nav-item:hover .bell-count{
    top:-25px;
    transform:scale(1.08);
}

nav{
    display:flex;
    gap:20px;
}

nav a{
    color:#fff;
    text-decoration:none;
    font-family:'Orbitron',sans-serif;
    transition:.3s;
}

nav a:hover{
    color:#930505;
}

/* Messages Wrapper */
.messages-wrapper{
    max-width:1400px;
    margin:40px auto;
    padding:20px;
    display:flex;
    gap:30px;
    align-items:flex-start;
    flex-wrap:wrap;
}

.messages-section{
    flex:1;
    min-width:300px;
}

.section-title{
    color:#930505;
    text-align:center;
    margin:20px 0 25px;
    font-size:24px;
    border-bottom:2px solid #930505;
    display:inline-block;
    width:100%;
    padding-bottom:10px;
}

/* Bulk Actions */
.bulk-actions{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:#0a0a0a;
    border:1px solid #930505;
    padding:15px 20px;
    margin-bottom:20px;
    border-radius:15px;
}

.select-all-label{
    color:#fff;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:10px;
    font-size:14px;
}

.select-all-label input{
    width:18px;
    height:18px;
    cursor:pointer;
    accent-color:#930505;
}

/* Message Card */
.message-card{
    background:#0a0a0a;
    border:1px solid #930505;
    border-radius:20px;
    padding:25px;
    margin-bottom:20px;
    color:#fff;
    position:relative;
    transition:.3s;
}

.message-card:hover{
    transform:translateY(-3px);
    box-shadow:0 0 20px rgba(147,5,5,.2);
}

.message-card h3{
    color:#930505;
    margin-bottom:12px;
    font-size:18px;
}

.message-card p{
    line-height:1.6;
    margin:10px 0;
}

.message-card strong{
    color:#930505;
}

.message-checkbox{
    position:absolute;
    top:20px;
    left:20px;
    width:20px;
    height:20px;
    cursor:pointer;
    accent-color:#930505;
}

/* Delete Button */
.delete-btn{
    background:#ff0000;
    color:#fff;
    border:none;
    padding:10px 25px;
    border-radius:40px;
    cursor:pointer;
    font-weight:bold;
    font-family:'Orbitron',sans-serif;
    transition:.3s;
    display:flex;
    align-items:center;
    gap:8px;
}

.delete-btn:hover:not(:disabled){
    background:#cc0000;
    transform:scale(1.05);
}

.delete-btn:disabled{
    background:#555;
    cursor:not-allowed;
    opacity:.6;
}

.no-message{
    text-align:center;
    color:#888;
    font-weight:bold;
    padding:40px;
    background:#0a0a0a;
    border-radius:20px;
    border:1px solid #930505;
}

/* Admin Reply */
.admin-reply{
    background:rgba(0,255,102,.1);
    border-right:3px solid #00ff66;
    padding:12px;
    border-radius:10px;
    margin-top:10px;
}

.admin-reply p{
    color:#00ff66;
}

/* Responsive */
@media(max-width:768px){

    body{
        overflow-x:hidden;
    }

    header{
        width:100%;
        padding:22px 18px 28px;
        flex-direction:column;
        justify-content:center;
        align-items:center;
        text-align:center;
        gap:16px;
    }

    .logo-img{
        width:105px;
    }

    .site-logo{
        font-size:26px;
        line-height:1.2;
    }

    .auth-links{
        width:100%;
        max-width:330px;
        min-height:68px;
        overflow:hidden;
        justify-content:space-around;
        padding:0 8px;
        border-radius:14px;
    }

    .magic-nav-item{
        min-width:48px;
        width:48px;
        height:62px;
    }

    .magic-nav-item .magic-icon{
        width:38px;
        height:38px;
    }

    .magic-nav-item .magic-icon i{
        font-size:17px;
    }

    .magic-nav-item .magic-text{
        display:none;
    }

    .magic-nav-item:hover .magic-icon{
        transform:none;
        background:transparent;
        color:#fff;
        box-shadow:none;
    }

    nav{
        width:100%;
        justify-content:center;
        gap:20px;
        flex-wrap:wrap;
    }

    nav a{
        font-size:14px;
    }

    .messages-wrapper{
        width:100%;
        max-width:100%;
        margin:35px auto;
        padding:0 18px 35px;
        flex-direction:column;
        gap:34px;
    }

    .messages-section{
        width:100%;
        min-width:0;
    }

    .section-title{
        font-size:22px;
        margin:12px 0 22px;
        padding-bottom:12px;
        width:100%;
    }

    .bulk-actions{
        width:100%;
        flex-direction:column;
        justify-content:center;
        gap:12px;
        padding:16px;
        border-radius:14px;
    }

    .select-all-label{
        justify-content:center;
        font-size:12px;
    }

    .delete-btn{
        width:auto;
        max-width:220px;
        justify-content:center;
        font-size:11px;
        padding:10px 22px;
    }

    .message-card{
        width:100%;
        padding:22px 18px 22px;
        border-radius:16px;
        overflow:hidden;
    }

    .message-card h3{
        font-size:15px;
        padding-left:28px;
        word-break:break-word;
    }

    .message-card p{
        font-size:13px;
        line-height:1.7;
        word-break:break-word;
        overflow-wrap:anywhere;
    }

    .message-card strong{
        font-size:13px;
    }

    .message-checkbox{
        top:16px;
        left:14px;
        width:18px;
        height:18px;
    }

    .no-message{
        width:100%;
        padding:38px 18px;
        border-radius:16px;
        font-size:13px;
        line-height:1.6;
    }

    .admin-reply{
        padding:10px;
        border-radius:8px;
    }
}
</style>
</head>
<body>

<header>
        <h1 class="site-logo">
    <img src="Icon.png" alt="Icon" class="logo-img">
</h1>
    <h1 class="site-logo"> My Messages</h1>
    <div class="auth-links">
        <?php if(isset($_SESSION['user_name'])): ?>

            <a href="profile.php" class="magic-nav-item welcome-user-box" title="Profile">
                <span class="magic-icon">
                    <?php if(!empty($userImage)): ?>
                        <img src="<?php echo $userImage; ?>" class="nav-profile-img">
                    <?php else: ?>
                        <span class="nav-profile-img default-nav-icon">
                            <i class="fa-solid fa-user"></i>
                        </span>
                    <?php endif; ?>
                </span>
                <span class="magic-text">Profile</span>
            </a>

            <a href="logout.php" class="magic-nav-item" title="Logout">
                <span class="magic-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
                <span class="magic-text">Logout</span>
            </a>

            <a href="my_messages.php" class="magic-nav-item" title="Messages">
                <span class="magic-icon"><i class="fa-solid fa-envelope"></i></span>
                <span class="magic-text">Messages</span>
            </a>

            <a href="friends.php" class="magic-nav-item" title="Friends">
                <span class="magic-icon"><i class="fa-solid fa-user-group"></i></span>
                <span class="magic-text">Friends</span>
            </a>

            <a href="my_orders.php" class="magic-nav-item" title="My Orders">
                <span class="magic-icon"><i class="fa-solid fa-box-open"></i></span>
                <span class="magic-text">Orders</span>
            </a>

            <a href="notifications.php" class="magic-nav-item bell-link" title="Notifications">
                <span class="magic-icon"><i class="fa-solid fa-bell"></i></span>
                <span class="magic-text">Notifications</span>
                <?php
                if(isset($_SESSION['user_id'])) {
                    $current_user = $_SESSION['user_id'];
                    $notif_query = $conn->query("SELECT COUNT(*) AS total FROM notifications WHERE user_id = '$current_user' AND is_read = 0");
                    if($notif_query){
                        $notif_data = $notif_query->fetch_assoc();
                        if($notif_data['total'] > 0):
                ?>
                        <span class="bell-count"><?php echo $notif_data['total']; ?></span>
                <?php
                        endif;
                    }
                }
                ?>
            </a>

        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="signup.php">Create Account</a>
        <?php endif; ?>
    </div>
<nav>
    <a href="index.php">
        <i class="fa-solid fa-house"></i> Home
    </a>

    <a href="cart.php">
        <i class="fa-solid fa-cart-shopping"></i> Cart
    </a>

    <a href="P2.php">
        <i class="fa-solid fa-envelope"></i> Contact Us
    </a>
</nav>
</header>

<div class="messages-wrapper">

    <!-- Admin Messages Section -->
    <div class="messages-section">
        <h2 class="section-title"><i class="fa-solid fa-user-tie"></i> Admin Messages</h2>
        <?php if($admin_messages && $admin_messages->num_rows > 0): ?>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete selected messages?');">
                <div class="bulk-actions">
                    <label class="select-all-label">
                        <input type="checkbox" id="selectAllAdmin" onclick="toggleSelectAll(this, 'admin_msg_cb')">
                        <i class="fa-solid fa-check-square"></i> Select All
                    </label>
                    <button type="submit" name="delete_multiple_admin_messages" class="delete-btn" id="deleteAdminBtn" disabled>
                        <i class="fa-solid fa-trash"></i> Delete Selected
                    </button>
                </div>
                <?php while($admin_msg = $admin_messages->fetch_assoc()): ?>
                    <div class="message-card">
                        <input type="checkbox" name="admin_message_ids[]" value="<?php echo $admin_msg['id']; ?>" class="message-checkbox admin_msg_cb" onclick="checkButtons('admin_msg_cb', 'deleteAdminBtn')">
                        <h3><i class="fa-solid fa-envelope"></i> From: Admin</h3>
                        <p><?php echo nl2br(htmlspecialchars($admin_msg['message'])); ?></p>
                        <small style="color:#aaa;"><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($admin_msg['created_at']); ?></small>
                    </div>
                <?php endwhile; ?>
            </form>
        <?php else: ?>
            <p class="no-message"><i class="fa-regular fa-envelope"></i> No messages from the admin</p>
        <?php endif; ?>
    </div>

    <!-- User Messages Section -->
    <div class="messages-section">
        <h2 class="section-title"><i class="fa-solid fa-headset"></i> Support Messages</h2>
        <?php if($result && $result->num_rows > 0): ?>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete selected messages?');">
                <div class="bulk-actions">
                    <label class="select-all-label">
                        <input type="checkbox" id="selectAllUser" onclick="toggleSelectAll(this, 'user_msg_cb')">
                        <i class="fa-solid fa-check-square"></i> Select All
                    </label>
                    <button type="submit" name="delete_multiple_messages" class="delete-btn" id="deleteUserBtn" disabled>
                        <i class="fa-solid fa-trash"></i> Delete Selected
                    </button>
                </div>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="message-card">
                        <input type="checkbox" name="message_ids[]" value="<?php echo $row['id']; ?>" class="message-checkbox user_msg_cb" onclick="checkButtons('user_msg_cb', 'deleteUserBtn')">
                        <p><strong><i class="fa-solid fa-tag"></i> Subject:</strong> <?php echo htmlspecialchars($row["subject"]); ?></p>
                        <p><strong><i class="fa-regular fa-message"></i> My Message:</strong> <?php echo nl2br(htmlspecialchars($row["message"])); ?></p>
                        <p style="color:#aaa;"><strong><i class="fa-regular fa-calendar"></i> Sent:</strong> <?php echo htmlspecialchars($row["created_at"]); ?></p>
                        <?php if(!empty($row["reply"])): ?>
                            <div class="admin-reply">
                                <p><strong><i class="fa-solid fa-reply"></i> Admin Reply:</strong> <?php echo nl2br(htmlspecialchars($row["reply"])); ?></p>
                            </div>
                        <?php else: ?>
                            <p style="color:#ff6666;"><i class="fa-regular fa-hourglass"></i> No reply yet</p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </form>
        <?php else: ?>
            <p class="no-message"><i class="fa-regular fa-envelope"></i> No support messages</p>
        <?php endif; ?>
    </div>

</div>

<script>
function toggleSelectAll(master, className) {
    const checkboxes = document.getElementsByClassName(className);
    const btnId = className === 'admin_msg_cb' ? 'deleteAdminBtn' : 'deleteUserBtn';
    let anyChecked = false;
    
    for (let i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = master.checked;
        if (master.checked) anyChecked = true;
    }
    
    document.getElementById(btnId).disabled = !anyChecked;
}

function checkButtons(className, btnId) {
    const checkboxes = document.getElementsByClassName(className);
    const btn = document.getElementById(btnId);
    let atLeastOneChecked = false;
    
    for (let i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].checked) {
            atLeastOneChecked = true;
            break;
        }
    }
    
    btn.disabled = !atLeastOneChecked;
}
</script>

</body>
</html>