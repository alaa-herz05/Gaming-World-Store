<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

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

$current_user = intval($_SESSION['user_id']);
$group_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($group_id <= 0) {
    die("Invalid group");
}


$group_q = $conn->query("
    SELECT *
    FROM groups_list
    WHERE id = '$group_id'
    LIMIT 1
");

if (!$group_q || $group_q->num_rows == 0) {
    die("Group not found");
}

$group = $group_q->fetch_assoc();

/* أي عضو داخل القروب يقدر يدخل صفحة التفاصيل */
$member_check = $conn->query("
    SELECT id, role
    FROM group_members
    WHERE group_id = '$group_id'
    AND user_id = '$current_user'
    LIMIT 1
");

if (!$member_check || $member_check->num_rows == 0) {
    die("You are not a member of this group.");
}

$current_member = $member_check->fetch_assoc();

/* الأدمن فقط له صلاحيات إدارة الأعضاء */
$is_admin =
    intval($group['created_by']) === $current_user
    || ($current_member['role'] === 'admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['leave_group'])) {

    $is_creator = intval($group['created_by']) === $current_user;

    if ($is_creator) {

        $admins_q = $conn->query("
            SELECT user_id
            FROM group_members
            WHERE group_id = '$group_id'
            AND role = 'admin'
            AND user_id != '$current_user'
            LIMIT 1
        ");

        if (!$admins_q || $admins_q->num_rows == 0) {

            die("
            <script>
                alert('You must assign another admin before leaving the group.');
                window.location='edit_group.php?id=$group_id';
            </script>
            ");
        }
    }

    $stmt = $conn->prepare("
        DELETE FROM group_members
        WHERE group_id = ?
        AND user_id = ?
    ");

    $stmt->bind_param("ii", $group_id, $current_user);
    $stmt->execute();

    if ($is_creator) {

        $new_admin_q = $conn->query("
            SELECT user_id
            FROM group_members
            WHERE group_id = '$group_id'
            AND role = 'admin'
            LIMIT 1
        ");

        if ($new_admin_q && $new_admin_q->num_rows > 0) {

            $new_admin = $new_admin_q->fetch_assoc();

            $new_admin_id = intval($new_admin['user_id']);

            $conn->query("
                UPDATE groups_list
                SET created_by = '$new_admin_id'
                WHERE id = '$group_id'
            ");
        }
    }

    header("Location: friends.php");
    exit();
}
    if (isset($_POST['update_group'])) {

        $group_name = trim($_POST['group_name']);
        $group_image = $group['group_image'];

        if (
            isset($_FILES['group_image']) &&
            $_FILES['group_image']['error'] === 0
        ) {
            $upload_dir = "uploads/groups/";

            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $ext = strtolower(pathinfo($_FILES['group_image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($ext, $allowed)) {
                $filename = time() . "_" . rand(1000, 9999) . "." . $ext;
                $target = $upload_dir . $filename;

                if (move_uploaded_file($_FILES['group_image']['tmp_name'], $target)) {
                    $group_image = $target;
                }
            }
        }

        $stmt = $conn->prepare("
            UPDATE groups_list
            SET group_name = ?, group_image = ?
            WHERE id = ?
        ");

        $stmt->bind_param("ssi", $group_name, $group_image, $group_id);
        $stmt->execute();
        $stmt->close();

        header("Location: edit_group.php?id=" . $group_id);
        exit();
    }

    if (isset($_POST['add_member'])) {

        if (!$is_admin) {
            die("Only admins can add members.");
        }

        $new_member = intval($_POST['new_member']);

        if ($new_member > 0) {
            $stmt = $conn->prepare("
                INSERT IGNORE INTO group_members
                (group_id, user_id, role)
                VALUES (?, ?, 'member')
            ");

            $stmt->bind_param("ii", $group_id, $new_member);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: edit_group.php?id=" . $group_id);
        exit();
    }

    if (isset($_POST['remove_member'])) {

        if (!$is_admin) {
            die("Only admins can remove members.");
        }

        $remove_user = intval($_POST['remove_user']);

        if ($remove_user != $current_user) {
            $stmt = $conn->prepare("
                DELETE FROM group_members
                WHERE group_id = ?
                AND user_id = ?
                AND role != 'admin'
            ");

            $stmt->bind_param("ii", $group_id, $remove_user);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: edit_group.php?id=" . $group_id);
        exit();
    }

    if (isset($_POST['make_admin'])) {

        if (!$is_admin) {
            die("Only admins can make another admin.");
        }

        $admin_user = intval($_POST['admin_user']);

        if ($admin_user > 0) {
            $stmt = $conn->prepare("
                UPDATE group_members
                SET role = 'admin'
                WHERE group_id = ?
                AND user_id = ?
            ");

            $stmt->bind_param("ii", $group_id, $admin_user);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: edit_group.php?id=" . $group_id);
        exit();
    }

    if (isset($_POST['remove_admin'])) {

        if (!$is_admin) {
            die("Only admins can remove admin role.");
        }

        $admin_user = intval($_POST['admin_user']);

        /* لا تسمح بإزالة أدمن صاحب القروب */
        if ($admin_user > 0 && $admin_user != intval($group['created_by'])) {
            $stmt = $conn->prepare("
                UPDATE group_members
                SET role = 'member'
                WHERE group_id = ?
                AND user_id = ?
            ");

            $stmt->bind_param("ii", $group_id, $admin_user);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: edit_group.php?id=" . $group_id);
        exit();
    }
}

/* تحديث بيانات القروب بعد أي تعديل */
$group_q = $conn->query("
    SELECT *
    FROM groups_list
    WHERE id = '$group_id'
    LIMIT 1
");
$group = $group_q->fetch_assoc();

/* أعضاء القروب */
$members = $conn->query("
    SELECT gm.role, u.id, u.name, u.image
    FROM group_members gm
    INNER JOIN users u ON gm.user_id = u.id
    WHERE gm.group_id = '$group_id'
    ORDER BY 
        CASE WHEN gm.role = 'admin' THEN 0 ELSE 1 END,
        u.name ASC
");

/* المستخدمين غير الموجودين بالقروب */
$available_users = $conn->query("
    SELECT u.id, u.name, u.image
    FROM users u
    WHERE u.id != '$current_user'

    AND u.id NOT IN (
        SELECT user_id
        FROM group_members
        WHERE group_id = '$group_id'
    )

    AND EXISTS (
        SELECT 1
        FROM follows f1
        WHERE f1.follower_id = '$current_user'
        AND f1.following_id = u.id
    )

    AND EXISTS (
        SELECT 1
        FROM follows f2
        WHERE f2.follower_id = u.id
        AND f2.following_id = '$current_user'
    )

    ORDER BY u.name ASC
");
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Group</title>

<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#c94d06">
<link rel="apple-touch-icon" href="icons/icon-192.png">

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
    padding:20px;
}

.container{
    width:100%;
    max-width:780px;
    margin:0 auto;
}

.card{
    background:#0a0a0a;
    border:1px solid #930505;
    border-radius:20px;
    padding:25px;
    margin-bottom:20px;
    box-shadow:0 0 18px rgba(147,5,5,.12);
}

h1,h2{
    text-align:center;
    color:#930505;
    margin-bottom:22px;
}

label{
    display:block;
    margin-bottom:8px;
    color:#930505;
    font-size:14px;
}

input,
select{
    width:100%;
    padding:12px;
    background:#111;
    border:1px solid #333;
    border-radius:12px;
    color:#fff;
    margin-bottom:18px;
    outline:none;
    transition:.3s;
}

input:focus,
select:focus{
    border-color:#930505;
    box-shadow:0 0 12px rgba(147,5,5,.18);
}

button,
.back-btn{
    border:none;
    border-radius:30px;
    background:#930505;
    color:#fff;
    font-weight:bold;
    cursor:pointer;
    transition:.3s;
    padding:11px 18px;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    font-family:'Orbitron',sans-serif;
    font-size:13px;
}

button:hover,
.back-btn:hover{
    background:#000;
    color:#930505;
    box-shadow:0 0 0 1px #930505;
    transform:scale(1.03);
}

.group-preview{
    text-align:center;
    margin-bottom:20px;
}

.group-preview img,
.default-img{
    width:95px;
    height:95px;
    border-radius:50%;
    border:3px solid #930505;
    object-fit:cover;
    background:#111;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    color:#930505;
    font-size:38px;
}

.member-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    background:#111;
    border:1px solid #333;
    border-radius:15px;
    padding:12px;
    margin-bottom:12px;
    transition:.3s;
}

.member-row:hover{
    border-color:#930505;
    box-shadow:0 0 15px rgba(147,5,5,.12);
}

.member-info{
    display:flex;
    align-items:center;
    gap:12px;
}

.member-img{
    width:45px;
    height:45px;
    border-radius:50%;
    border:2px solid #930505;
    object-fit:cover;
}

.member-img.default{
    display:flex;
    align-items:center;
    justify-content:center;
    background:#111;
    color:#930505;
}

.role{
    color:#930505;
    font-size:11px;
    margin-top:4px;
}

.remove-btn{
    background:#ff0000;
}

.remove-btn:hover{
    background:#cc0000;
    color:#fff;
    box-shadow:none;
}

.admin-btn{
    background:#008000;
}

.admin-btn:hover{
    background:#00a000;
    color:#fff;
    box-shadow:none;
}

.demote-btn{
    background:#444;
}

.demote-btn:hover{
    background:#666;
    color:#fff;
    box-shadow:none;
}

.admin-badge{
    color:#00ff66;
    font-size:11px;
    margin-top:4px;
}

.actions{
    display:flex;
    justify-content:center;
    margin-bottom:20px;
}

.member-actions{
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    justify-content:flex-end;
}

.member-actions form{
    margin:0;
}

.empty{
    text-align:center;
    color:#888;
    padding:15px;
}

/* Friends Search */
.friends-search-list{
    display:flex;
    flex-direction:column;
    gap:12px;
    max-height:420px;
    overflow-y:auto;
    padding-right:4px;
}

.friend-search-item{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    background:#111;
    border:1px solid #333;
    border-radius:15px;
    padding:12px;
    transition:.3s;
}

.friend-search-item:hover{
    border-color:#930505;
    box-shadow:0 0 15px rgba(147,5,5,.15);
}

.friend-search-name{
    color:#fff;
    font-size:14px;
}

/* Scrollbar */
.friends-search-list::-webkit-scrollbar{
    width:6px;
}

.friends-search-list::-webkit-scrollbar-thumb{
    background:#930505;
    border-radius:20px;
}

/* Responsive */
@media(max-width:650px){

    .member-row{
        flex-direction:column;
        text-align:center;
    }

    .member-info{
        flex-direction:column;
    }

    .member-actions{
        justify-content:center;
    }

    button{
        width:100%;
    }

    .member-actions form{
        width:100%;
    }

    .friend-search-item{
        flex-direction:column;
        text-align:center;
    }
}
</style>
</head>

<body>

<div class="container">

    <div class="actions">
        <a href="group_room.php?id=<?php echo $group_id; ?>" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i>
            Back To Group
        </a>
    </div>

    <div class="card">
        <h1>Group Details</h1>

        <div class="group-preview">
            <?php if(!empty($group['group_image'])): ?>
                <img src="<?php echo htmlspecialchars($group['group_image'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php else: ?>
                <div class="default-img">
                    <i class="fa-solid fa-users"></i>
                </div>
            <?php endif; ?>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <label>Group Name</label>

            <input
                type="text"
                name="group_name"
                required
                value="<?php echo htmlspecialchars($group['group_name'], ENT_QUOTES, 'UTF-8'); ?>"
            >

            <label>Group Image From Gallery</label>

            <input
                type="file"
                name="group_image"
                accept="image/*"
            >

            <button type="submit" name="update_group">
                <i class="fa-solid fa-save"></i>
                Save Changes
            </button>
        </form>
    </div>

    <?php if($is_admin): ?>
    <div class="card">
        <h2>Add Member</h2>

        <input
            type="text"
            id="memberSearch"
            placeholder="Search friends..."
            onkeyup="filterMembers()"
            autocomplete="off"
        >

        <div class="friends-search-list" id="friendsSearchList">

            <?php if($available_users && $available_users->num_rows > 0): ?>
                <?php while($u = $available_users->fetch_assoc()): ?>

                    <form method="POST" class="friend-search-item" style="display:none;">
                        <div class="member-info">
                            <?php if(!empty($u['image'])): ?>
                                <img src="<?php echo htmlspecialchars($u['image']); ?>" class="member-img">
                            <?php else: ?>
                                <div class="member-img default">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                            <?php endif; ?>

                            <div class="friend-search-name">
                                <?php echo htmlspecialchars($u['name']); ?>
                            </div>
                        </div>

                        <input type="hidden" name="new_member" value="<?php echo $u['id']; ?>">

                        <button type="submit" name="add_member">
                            <i class="fa-solid fa-user-plus"></i>
                            Add
                        </button>
                    </form>

                <?php endwhile; ?>

                <p class="empty" id="searchHint">
                    Type a friend's name to search
                </p>

                <p class="empty" id="noMemberResult" style="display:none;">
                    No matching friends found
                </p>

            <?php else: ?>
                <p class="empty">No friends available</p>
            <?php endif; ?>

        </div>
    </div>
    <?php else: ?>
    <div class="card">
        <h2>Group Details</h2>
        <p class="empty">
            You can edit the group name and image. Only admins can manage members.
        </p>
    </div>
    <?php endif; ?>

    <div class="card">
        <h2>Group Members</h2>

        <?php if($members && $members->num_rows > 0): ?>
            <?php while($m = $members->fetch_assoc()): ?>

                <div class="member-row">

                    <div class="member-info">
                        <?php if(!empty($m['image'])): ?>
                            <img src="<?php echo htmlspecialchars($m['image']); ?>" class="member-img">
                        <?php else: ?>
                            <div class="member-img default">
                                <i class="fa-solid fa-user"></i>
                            </div>
                        <?php endif; ?>

                        <div>
                            <div><?php echo htmlspecialchars($m['name']); ?></div>

                            <?php if($m['role'] === 'admin'): ?>
                                <div class="admin-badge">
                                    <i class="fa-solid fa-crown"></i>
                                    Admin
                                </div>
                            <?php else: ?>
                                <div class="role">Member</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if($is_admin): ?>
                    <div class="member-actions">

                        <?php if($m['role'] !== 'admin'): ?>

                            <form method="POST" onsubmit="return confirm('Make this member admin?');">
                                <input type="hidden" name="admin_user" value="<?php echo $m['id']; ?>">
                                <button type="submit" name="make_admin" class="admin-btn">
                                    <i class="fa-solid fa-crown"></i>
                                    Make Admin
                                </button>
                            </form>

                            <form method="POST" onsubmit="return confirm('Remove this member?');">
                                <input type="hidden" name="remove_user" value="<?php echo $m['id']; ?>">
                                <button type="submit" name="remove_member" class="remove-btn">
                                    <i class="fa-solid fa-user-minus"></i>
                                    Remove
                                </button>
                            </form>

                        <?php else: ?>

                            <?php if($m['id'] != intval($group['created_by'])): ?>
                                <form method="POST" onsubmit="return confirm('Remove admin role from this member?');">
                                    <input type="hidden" name="admin_user" value="<?php echo $m['id']; ?>">
                                    <button type="submit" name="remove_admin" class="demote-btn">
                                        <i class="fa-solid fa-user-shield"></i>
                                        Remove Admin
                                    </button>
                                </form>
                            <?php endif; ?>

                        <?php endif; ?>

                    </div>
                    <?php endif; ?>

                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty">No members found</p>
        <?php endif; ?>

    </div>
<form method="POST" onsubmit="return confirm('Are you sure you want to leave the group?');">
    <button type="submit" name="leave_group" class="remove-btn">
        <i class="fa-solid fa-right-from-bracket"></i>
        Leave Group
    </button>
</form>
</div>
<script>
function filterMembers() {
    const searchInput = document.getElementById('memberSearch');
    if (!searchInput) return;

    const input = searchInput.value.toLowerCase().trim();
    const items = document.querySelectorAll('.friend-search-item');
    const hint = document.getElementById('searchHint');
    const noResult = document.getElementById('noMemberResult');

    let visibleCount = 0;

    items.forEach(item => {
        const name = item.querySelector('.friend-search-name').innerText.toLowerCase();

        if (input !== "" && name.includes(input)) {
            item.style.display = "flex";
            visibleCount++;
        } else {
            item.style.display = "none";
        }
    });

    if (hint) {
        hint.style.display = input === "" ? "block" : "none";
    }

    if (noResult) {
        noResult.style.display = input !== "" && visibleCount === 0 ? "block" : "none";
    }
}
    if ('serviceWorker' in navigator) {

    window.addEventListener('load', () => {

        navigator.serviceWorker.register('sw.js')
        .then(reg => console.log('SW Registered'))
        .catch(err => console.log(err));

    });
}
</script>
</body>
</html>
