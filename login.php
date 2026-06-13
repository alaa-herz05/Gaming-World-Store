<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
$conn = new mysqli(
    "sql213.infinityfree.com",
    "if0_41900150",
    "Rany9NH3lawi",
    "if0_41900150_my_first_project"
);
$conn->set_charset("utf8mb4");
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_image"] = $user["image"];
            header("Location: index.php");
            exit();
        } else {
            $error = "✗ Incorrect password";
        }
    } else {
        $error = "✗ Email not found";
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gaming World</title>
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
    display:flex;
    justify-content:center;
    align-items:center;
    padding:40px 20px;
}

/* Login Container */
.login-container{
    max-width:450px;
    width:100%;
    margin:0 auto;
}

/* Login Card */
.login-card{
    background:#0a0a0a;
    border:2px solid #930505;
    border-radius:30px;
    padding:45px 40px;
    text-align:center;
    transition:.3s;
}

.login-card:hover{
    transform:translateY(-5px);
    box-shadow:0 0 30px rgba(147,5,5,.2);
}

/* Logo */
.site-logo{
    color:#930505;
    font-size:32px;
    font-weight:900;
    margin-bottom:30px;
}

.site-logo i{
    margin-left:10px;
}
.logo-img{
    width:110px;
    max-width:100%;
    object-fit:contain;
    filter:
        drop-shadow(0 0 10px rgba(147,5,5,.7))
        drop-shadow(0 0 20px rgba(147,5,5,.35));
}
/* Error Message */
.error-message{
    text-align:center;
    padding:12px;
    border-radius:12px;
    margin-bottom:25px;
    font-weight:bold;
    font-size:14px;
    background:rgba(255,0,0,.1);
    border:1px solid #ff0000;
    color:#ff6666;
}

/* Input Fields */
input[type="email"],
input[type="password"]{
    width:100%;
    background:#1a1a1a;
    color:#fff;
    border:1px solid #930505;
    padding:14px 18px;
    border-radius:15px;
    font-size:14px;
    margin-bottom:20px;
    transition:.3s;
    font-family:Arial,sans-serif;
}

input[type="email"]:focus,
input[type="password"]:focus{
    outline:none;
    border-color:#b30a0a;
    box-shadow:0 0 15px rgba(147,5,5,.2);
    background:#000;
}

input::placeholder{
    color:#666;
}

/* Password Box */
.password-box{
    position:relative;
    width:100%;
    margin-bottom:20px;
}

.password-box input{
    margin-bottom:0;
    padding-left:50px;
}

.toggle-password{
    position:absolute;
    left:15px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    font-size:18px;
    color:#930505;
    transition:.3s;
}

.toggle-password:hover{
    color:#b30a0a;
}

/* Login Button */
.login-btn{
    width:100%;
    background:#930505;
    color:#fff;
    border:none;
    padding:14px 28px;
    border-radius:40px;
    font-size:16px;
    font-weight:bold;
    font-family:'Orbitron',sans-serif;
    cursor:pointer;
    transition:.3s;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:12px;
    margin-top:10px;
}

.login-btn:hover{
    background:#000;
    transform:scale(1.02);
    box-shadow:0 5px 25px rgba(147,5,5,.4);
}

/* Forgot Password */
.forgot-link{
    display:block;
    margin-top:20px;
    color:#930505;
    text-align:center;
    text-decoration:none;
    font-weight:bold;
    font-size:13px;
    transition:.3s;
}

.forgot-link:hover{
    color:#b30a0a;
}

/* Divider */
.divider{
    display:flex;
    align-items:center;
    text-align:center;
    margin:25px 0;
    color:#666;
    font-size:12px;
}

.divider::before,
.divider::after{
    content:'';
    flex:1;
    border-bottom:1px solid #333;
}

.divider::before{
    margin-left:10px;
}

.divider::after{
    margin-right:10px;
}

/* Create Account Button */
.create-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    background:#1a1a1a;
    border:1px solid #930505;
    padding:12px 28px;
    border-radius:40px;
    text-decoration:none;
    font-family:'Orbitron',sans-serif;
    font-weight:bold;
    font-size:14px;
    color:#fff;
    transition:.3s;
    width:100%;
}

.create-btn:hover{
    background:#930505;
    transform:scale(1.02);
}

.create-btn:hover i,
.create-btn:hover span{
    color:#000;
}

/* Home Button */
.home-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    background:#1a1a1a;
    border:1px solid #930505;
    padding:12px 28px;
    border-radius:40px;
    text-decoration:none;
    font-family:'Orbitron',sans-serif;
    font-weight:bold;
    font-size:14px;
    color:#fff;
    transition:.3s;
    margin-top:15px;
    width:100%;
}

.home-btn:hover{
    background:#930505;
    transform:scale(1.02);
}

.home-btn:hover i,
.home-btn:hover span{
    color:#000;
}

/* Responsive */
@media(max-width:768px){

    .login-card{
        padding:30px 25px;
    }

    .site-logo{
        font-size:28px;
    }
}


/* ================= LAMP LOGIN INTRO ================= */

/* disable old background light from previous version */
body::before,
body::after{
    display:none !important;
}

.lamp-screen{
    position:fixed;
    inset:0;
    background:
        radial-gradient(circle at center, rgba(20,0,0,.35), #000 65%);
    display:flex;
    justify-content:center;
    align-items:center;
    z-index:9999;
    transition:opacity 1s ease, visibility 1s ease;
}

.lamp-screen.hide{
    opacity:0;
    visibility:hidden;
    pointer-events:none;
}

.lamp-wrapper{
    position:relative;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    animation:lampIntro .9s ease both;
}

@keyframes lampIntro{
    from{
        opacity:0;
        transform:translateY(-30px) scale(.95);
    }
    to{
        opacity:1;
        transform:translateY(0) scale(1);
    }
}

.lamp-wire{
    width:4px;
    height:170px;
    background:linear-gradient(to bottom,#111,#333,#111);
    border-radius:10px;
    box-shadow:0 0 10px rgba(255,255,255,.08);
}

.lamp-light{
    position:absolute;
    top:125px;
    width:520px;
    height:520px;
    background:
        radial-gradient(
            circle,
            rgba(147,5,5,.58) 0%,
            rgba(147,5,5,.24) 28%,
            rgba(147,5,5,.08) 48%,
            transparent 72%
        );
    filter:blur(42px);
    opacity:0;
    transform:scale(.7);
    transition:opacity .8s ease, transform .8s ease;
    pointer-events:none;
}

.lamp-cone{
    position:absolute;
    top:175px;
    width:440px;
    height:360px;
    background:
        radial-gradient(
            ellipse at top,
            rgba(147,5,5,.35) 0%,
            rgba(147,5,5,.12) 38%,
            transparent 72%
        );
    clip-path:polygon(42% 0,58% 0,100% 100%,0 100%);
    opacity:0;
    filter:blur(8px);
    transition:opacity .8s ease;
    pointer-events:none;
}

.lamp-body{
    width:120px;
    height:82px;
    background:
        linear-gradient(
            145deg,
            #050505,
            #111 45%,
            #1b0000
        );
    border:2px solid #333;
    border-radius:60px 60px 18px 18px;
    display:flex;
    justify-content:center;
    align-items:center;
    cursor:pointer;
    position:relative;
    overflow:hidden;
    transition:.45s ease;
    box-shadow:
        inset 0 0 22px rgba(255,255,255,.04),
        0 15px 35px rgba(0,0,0,.75);
}

.lamp-body::before{
    content:'';
    position:absolute;
    inset:8px 16px auto 16px;
    height:18px;
    border-radius:50%;
    background:rgba(255,255,255,.04);
}

.lamp-inner{
    width:62px;
    height:62px;
    border-radius:50%;
    background:#090909;
    border:1px solid #222;
    display:flex;
    justify-content:center;
    align-items:center;
    position:relative;
    z-index:2;
    transition:.45s ease;
}

.lamp-inner i{
    color:#444;
    font-size:27px;
    transition:.45s ease;
}

.lamp-text{
    margin-top:32px;
    color:#444;
    font-size:30px;
    letter-spacing:9px;
    font-weight:900;
    transition:.45s ease;
    user-select:none;
}

.lamp-hint{
    margin-top:14px;
    color:#333;
    font-size:11px;
    letter-spacing:2px;
    transition:.45s ease;
}

.lamp-screen.active .lamp-light{
    opacity:1;
    transform:scale(1);
    animation:lampPulse 2s ease-in-out infinite;
}

.lamp-screen.active .lamp-cone{
    opacity:1;
}

.lamp-screen.active .lamp-body{
    border-color:#930505;
    transform:translateY(3px);
    box-shadow:
        0 0 28px rgba(147,5,5,.9),
        0 0 70px rgba(147,5,5,.45),
        inset 0 0 28px rgba(147,5,5,.25);
}

.lamp-screen.active .lamp-inner{
    background:#930505;
    border-color:#b30a0a;
    box-shadow:
        0 0 20px rgba(147,5,5,1),
        inset 0 0 14px rgba(255,255,255,.16);
}

.lamp-screen.active .lamp-inner i{
    color:#fff;
    text-shadow:
        0 0 10px #fff,
        0 0 28px #930505;
}

.lamp-screen.active .lamp-text{
    color:#fff;
    text-shadow:
        0 0 14px rgba(147,5,5,1),
        0 0 35px rgba(147,5,5,.75);
}

.lamp-screen.active .lamp-hint{
    color:#930505;
}

@keyframes lampPulse{
    0%,100%{
        opacity:1;
        transform:scale(1);
    }
    50%{
        opacity:.82;
        transform:scale(1.08);
    }
}

/* login form appears only after lamp click */
.login-container{
    opacity:0;
    transform:translateY(45px) scale(.96);
    pointer-events:none;
    transition:
        opacity .9s ease,
        transform .9s ease;
    position:relative;
    z-index:2;
}

.login-container.show{
    opacity:1;
    transform:translateY(0) scale(1);
    pointer-events:auto;
}

.login-card{
    box-shadow:
        0 0 35px rgba(147,5,5,.22),
        inset 0 0 25px rgba(147,5,5,.05);
}

.login-card.show-glow{
    animation:cardWake .9s ease both;
}

@keyframes cardWake{
    from{
        box-shadow:0 0 0 rgba(147,5,5,0);
    }
    to{
        box-shadow:
            0 0 35px rgba(147,5,5,.32),
            0 0 70px rgba(147,5,5,.12),
            inset 0 0 25px rgba(147,5,5,.06);
    }
}

@media(max-width:768px){
    .lamp-wire{
        height:130px;
    }

    .lamp-light{
        width:360px;
        height:360px;
        top:105px;
    }

    .lamp-cone{
        width:310px;
        height:300px;
        top:150px;
    }

    .lamp-body{
        width:105px;
        height:74px;
    }

    .lamp-text{
        font-size:24px;
        letter-spacing:7px;
    }
}

</style>
</head>
<body>
<?php $showLogin = !empty($error); ?>

<div class="lamp-screen <?php echo $showLogin ? 'hide' : ''; ?>" id="lampScreen">
    <div class="lamp-wrapper">
        <div class="lamp-wire"></div>
        <div class="lamp-light"></div>
        <div class="lamp-cone"></div>

        <button type="button" class="lamp-body" id="lampBtn" aria-label="Turn on login lamp">
            <span class="lamp-inner">
                <i class="fa-solid fa-power-off"></i>
            </span>
        </button>

        <h2 class="lamp-text">LOGIN</h2>
        <p class="lamp-hint">CLICK THE LAMP</p>
    </div>
</div>

<div class="login-container <?php echo $showLogin ? 'show' : ''; ?>" id="loginContainer">
    <div class="login-card">
            <h2 class="site-logo">
    <img src="Icon.png" alt="Icon" class="logo-img">
</h2>
        <h2 class="site-logo">
            <i class="fa-solid fa-key"></i> Login
        </h2>

        <?php if(!empty($error)): ?>
            <div class="error-message">
                <i class="fa-solid fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <input 
                type="email" 
                name="email" 
                placeholder=" Email Address" 
                required
            >
            <div class="password-box">
                <input 
                    type="password" 
                    name="password" 
                    id="password"
                    placeholder="Password" 
                    required
                >
                <span class="toggle-password" onclick="togglePassword()">
                    <i class="fa-regular fa-eye"></i>
                </span>
            </div>
            <button type="submit" class="login-btn">
                <i class="fa-solid fa-right-to-bracket"></i> Login
            </button>
        </form>

        <a href="forgot_password.php" class="forgot-link">
             Forgot Password ?
        </a>

        <div class="divider">or</div>

        <a href="signup.php" class="create-btn">
            <i class="fa-solid fa-user-plus"></i> Create New Account
        </a>

        <a href="index.php" class="home-btn">
            <i class="fa-solid fa-home"></i> Home Page
        </a>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById("password");
    const toggleIcon = document.querySelector(".toggle-password i");
    
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleIcon.classList.remove("fa-eye");
        toggleIcon.classList.add("fa-eye-slash");
    } else {
        passwordInput.type = "password";
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");
    }
}

/* LAMP LOGIN INTRO */
const lampScreen = document.getElementById("lampScreen");
const lampBtn = document.getElementById("lampBtn");
const loginContainer = document.getElementById("loginContainer");
const loginCard = document.querySelector(".login-card");

if (lampBtn && lampScreen && loginContainer) {
    lampBtn.addEventListener("click", () => {
        lampScreen.classList.add("active");

        setTimeout(() => {
            loginContainer.classList.add("show");
            loginCard?.classList.add("show-glow");
            lampScreen.classList.add("hide");
        }, 1150);
    });
}

</script>

</body>
</html>