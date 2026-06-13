<?php
session_start();

if (!isset($_SESSION['admin_logged'])) {
    exit();
}

$conn = new mysqli(
    "sql213.infinityfree.com",
    "if0_41900150",
    "Rany9NH3lawi",
    "if0_41900150_my_first_project"
);

$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    exit();
}

$result = $conn->query("
    SELECT *
    FROM admin_messages
    ORDER BY created_at DESC
");

if($result && $result->num_rows > 0):

    while($msg = $result->fetch_assoc()):
?>

<div class="sent-card">
    <p>
        <strong style="color:#c94d06;">Username:</strong>
        <?php echo htmlspecialchars($msg['username']); ?>
    </p>

    <p>
        <strong style="color:#c94d06;">Message:</strong>
        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
    </p>

    <small style="color:#aaa;">
        <?php echo htmlspecialchars($msg['created_at']); ?>
    </small>

    <form method="POST">
        <input type="hidden" name="sent_id" value="<?php echo $msg['id']; ?>">

        <button
            type="submit"
            name="delete_sent_message"
            onclick="return confirm('Delete this sent message?');"
            class="delete-btn">
            Delete Message
        </button>
    </form>
</div>

<?php
    endwhile;

else:
?>

<p style="text-align:center; color:red; font-weight:bold;">
    No sent messages yet
</p>

<?php endif; ?>
