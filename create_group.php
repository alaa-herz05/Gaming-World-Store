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

$user_id = intval($_SESSION['user_id']);
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_name = trim($_POST['group_name'] ?? '');
    $group_image = trim($_POST['group_image'] ?? '');
    $members = $_POST['members'] ?? [];

    if ($group_name === '') {
        $error = "Group name is required";
    } else {
        $stmt = $conn->prepare("INSERT INTO groups_list (group_name, group_image, created_by) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $group_name, $group_image, $user_id);
        $stmt->execute();
        $group_id = $stmt->insert_id;
        $stmt->close();

        $add = $conn->prepare("INSERT IGNORE INTO group_members (group_id, user_id) VALUES (?, ?)");

        $add->bind_param("ii", $group_id, $user_id);
        $add->execute();

        foreach ($members as $member_id) {
            $member_id = intval($member_id);
            if ($member_id > 0) {
                $add->bind_param("ii", $group_id, $member_id);
                $add->execute();
            }
        }

        $add->close();
        header("Location: group_room.php?id=" . $group_id);
        exit();
    }
}

$friends = $conn->query("
    SELECT u.id, u.name, u.image
    FROM users u
    WHERE u.id != '$user_id'
    AND EXISTS (
        SELECT 1 FROM follows f1
        WHERE f1.follower_id = '$user_id'
        AND f1.following_id = u.id
    )
    AND EXISTS (
        SELECT 1 FROM follows f2
        WHERE f2.follower_id = u.id
        AND f2.following_id = '$user_id'
    )
    ORDER BY u.name ASC
");
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>Create Group - Gaming World</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
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
}

header{
    background:#000;
    border-bottom:2px solid #930505;
    padding:15px 30px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    flex-wrap:wrap;
}

.site-logo{
    color:#930505;
    font-size:26px;
    font-weight:900;
}

nav a{
    color:#fff;
    text-decoration:none;
    margin:0 8px;
    transition:.3s;
}

nav a:hover{
    color:#930505;
}

.container{
    max-width:850px;
    margin:35px auto;
    padding:0 18px;
}

.card{
    background:#0a0a0a;
    border:1px solid #930505;
    border-radius:20px;
    padding:25px;
    box-shadow:0 0 20px rgba(147,5,5,.12);
}

label{
    display:block;
    color:#930505;
    margin:15px 0 8px;
    font-weight:bold;
}

input[type=text]{
    width:100%;
    background:#000;
    border:1px solid #930505;
    color:#fff;
    padding:12px;
    border-radius:12px;
    font-size:15px;
    transition:.3s;
}

input[type=text]:focus{
    outline:none;
    border-color:#b30a0a;
    box-shadow:0 0 15px rgba(147,5,5,.2);
}

.member{
    display:flex;
    align-items:center;
    gap:12px;
    background:#111;
    border:1px solid #333;
    border-radius:14px;
    padding:12px;
    margin-bottom:10px;
    transition:.3s;
}

.member:hover{
    border-color:#930505;
    transform:translateX(-3px);
}

.member img,
.member-icon{
    width:42px;
    height:42px;
    border-radius:50%;
    border:1px solid #930505;
    object-fit:cover;
    background:#000;
    color:#930505;
    display:flex;
    align-items:center;
    justify-content:center;
}

.member input{
    width:18px;
    height:18px;
    accent-color:#930505;
}

.btn{
    width:100%;
    background:#930505;
    color:#fff;
    border:none;
    padding:14px;
    border-radius:40px;
    font-weight:bold;
    margin-top:18px;
    cursor:pointer;
    font-size:15px;
    font-family:'Orbitron',sans-serif;
    transition:.3s;
}

.btn:hover{
    background:#000;
    color:#930505;
    box-shadow:0 0 0 1px #930505;
    transform:scale(1.02);
}

.error{
    background:#220000;
    border:1px solid #ff3333;
    color:#ff8080;
    padding:12px;
    border-radius:10px;
    margin-bottom:15px;
    text-align:center;
}
</style>
</head>
<body>
<header>
    <h1 class="site-logo"><i class="fa-solid fa-users"></i> Create Group</h1>
    <nav>
        <a href="groups.php">Groups</a>
        <a href="index.php">Home</a>
    </nav>
</header>

<div class="container">
    <form class="card" method="POST">
        <?php if($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <label>Group Name </label>
        <input type="text" name="group_name" required>

        <label>Group Image URL </label>
        <input type="text" name="group_image" placeholder="Optional">

        <label>Choose Members </label>

        <?php if($friends && $friends->num_rows > 0): ?>
            <?php while($f = $friends->fetch_assoc()): ?>
                <label class="member">
                    <input type="checkbox" name="members[]" value="<?php echo $f['id']; ?>">
                    <?php if(!empty($f['image'])): ?>
                        <img src="<?php echo htmlspecialchars($f['image']); ?>">
                    <?php else: ?>
                        <div class="member-icon"><i class="fa-solid fa-user"></i></div>
                    <?php endif; ?>
                    <span><?php echo htmlspecialchars($f['name']); ?></span>
                </label>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color:#888;text-align:center;padding:20px;">No mutual friends found</p>
        <?php endif; ?>

        <button class="btn" type="submit"><i class="fa-solid fa-plus"></i> Create Group</button>
    </form>
</div>
    <script>
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
