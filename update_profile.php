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
$conn->set_charset("utf8mb4");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات");
}

$user_id = $_SESSION['user_id'];
$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];
$deleteImage = isset($_POST['delete_image']);

$imagePath = "";

/* رفع صورة جديدة */
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {

    $imageName = time() . "_" . basename($_FILES['profile_image']['name']);
    $target = "uploads/" . $imageName;

    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target)) {
        $imagePath = $target;
    } else {
        die("فشل رفع الصورة");
    }
}

/* تجهيز الباسورد */
$passwordSql = "";

if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $passwordSql = ", password='$hashed_password'";
}

/* تجهيز الصورة */
$imageSql = "";

if ($deleteImage) {
    $imageSql = ", image=''";
    $_SESSION['user_image'] = "";
} elseif (!empty($imagePath)) {
    $imageSql = ", image='$imagePath'";
    $_SESSION['user_image'] = $imagePath;
}

/* تحديث البيانات */
$sql = "UPDATE users 
        SET name='$name', email='$email' $passwordSql $imageSql
        WHERE id='$user_id'";

if ($conn->query($sql) === TRUE) {
    $_SESSION['user_name'] = $name;

    header("Location: profile.php");
    exit();
} else {
    echo "حدث خطأ: " . $conn->error;
}

$conn->close();
?>