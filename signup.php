<?php
$conn = new mysqli(
    "sql213.infinityfree.com",
    "if0_41900150",
    "Rany9NH3lawi",
    "if0_41900150_my_first_project"
);
$conn->set_charset("utf8mb4");
if ($conn->connect_error) {
    die("Connection failed");
}
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($name) || empty($email) || empty($password)) {
        $message = "✗ Please fill in all fields";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashedPassword);
        if ($stmt->execute()) {
            $message = "✓ Account created successfully";
        } else {
            $message = "✗ An error occurred, the email may already be in use";
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="Icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Register - Gaming World</title>
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

/* Register Container */
.register-container{
    max-width:450px;
    width:100%;
    margin:0 auto;
}

/* Register Card */
.register-card{
    background:#0a0a0a;
    border:2px solid #930505;
    border-radius:30px;
    padding:45px 40px;
    text-align:center;
    transition:.3s;
}

.register-card:hover{
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
/* Message */
.message{
    text-align:center;
    padding:12px;
    border-radius:12px;
    margin-bottom:25px;
    font-weight:bold;
    font-size:14px;
}

.message-success{
    background:rgba(0,255,102,.1);
    border:1px solid #00ff66;
    color:#00ff66;
}

.message-error{
    background:rgba(255,0,0,.1);
    border:1px solid #ff0000;
    color:#ff6666;
}

/* Input Fields */
input[type="text"],
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

input[type="text"]:focus,
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

/* Register Button */
.register-btn{
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

.register-btn:hover{
    background:#000;
    transform:scale(1.02);
    box-shadow:0 5px 25px rgba(147,5,5,.4);
}

/* Login Link */
.login-link{
    display:block;
    margin-top:20px;
    color:#930505;
    text-align:center;
    text-decoration:none;
    font-weight:bold;
    font-size:14px;
    transition:.3s;
}

.login-link:hover{
    color:#b30a0a;
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
    margin-top:20px;
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

    .register-card{
        padding:30px 25px;
    }

    .site-logo{
        font-size:28px;
    }
}

/* ================= LAMP REGISTER INTRO ================= */

body{
    position:relative;
    overflow:hidden;
}

.lamp-screen{
    position:fixed;
    inset:0;
    background:#000;
    display:flex;
    justify-content:center;
    align-items:center;
    z-index:9999;
    transition:opacity 1s ease, visibility 1s ease;
}

.lamp-wrapper{
    position:relative;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    animation:lampEntry .8s ease both;
}

@keyframes lampEntry{
    from{
        opacity:0;
        transform:translateY(-25px) scale(.95);
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
    margin-bottom:-5px;
    border-radius:20px;
    box-shadow:0 0 12px rgba(147,5,5,.12);
}

.lamp-light{
    position:absolute;
    top:105px;
    width:560px;
    height:560px;
    background:radial-gradient(
        circle,
        rgba(147,5,5,.58) 0%,
        rgba(147,5,5,.24) 28%,
        rgba(147,5,5,.08) 48%,
        transparent 72%
    );
    filter:blur(45px);
    opacity:0;
    transform:scale(.65);
    transition:1s ease;
    pointer-events:none;
}

.lamp-body{
    width:120px;
    height:120px;
    border-radius:50%;
    background:linear-gradient(145deg,#050505,#151515);
    border:2px solid #333;
    display:flex;
    justify-content:center;
    align-items:center;
    cursor:pointer;
    position:relative;
    transition:.55s ease;
    box-shadow:
        inset 0 0 22px rgba(255,255,255,.04),
        0 0 18px rgba(0,0,0,.9);
}

.lamp-body::before{
    content:'';
    position:absolute;
    inset:-8px;
    border-radius:50%;
    border:1px solid rgba(147,5,5,.18);
    opacity:.6;
}

.lamp-inner{
    width:76px;
    height:76px;
    border-radius:50%;
    background:#0c0c0c;
    display:flex;
    justify-content:center;
    align-items:center;
    transition:.55s ease;
}

.lamp-inner i{
    color:#444;
    font-size:30px;
    transition:.55s ease;
}

.lamp-text{
    margin-top:35px;
    color:#444;
    font-size:28px;
    letter-spacing:7px;
    transition:.55s ease;
    text-shadow:none;
}

.lamp-screen.active .lamp-light{
    opacity:1;
    transform:scale(1);
    animation:lampPulse 2s ease-in-out infinite;
}

.lamp-screen.active .lamp-body{
    border-color:#930505;
    background:linear-gradient(145deg,#140000,#280000,#930505);
    box-shadow:
        0 0 25px rgba(147,5,5,.75),
        0 0 55px rgba(147,5,5,.42),
        inset 0 0 22px rgba(255,255,255,.08);
}

.lamp-screen.active .lamp-inner{
    background:#930505;
    box-shadow:
        0 0 18px rgba(147,5,5,1),
        inset 0 0 18px rgba(255,255,255,.12);
}

.lamp-screen.active .lamp-inner i{
    color:#fff;
    text-shadow:
        0 0 10px #fff,
        0 0 22px #930505;
}

.lamp-screen.active .lamp-text{
    color:#fff;
    text-shadow:
        0 0 14px rgba(147,5,5,.9),
        0 0 35px rgba(147,5,5,.55);
}

@keyframes lampPulse{
    0%,100%{
        transform:scale(1);
        opacity:1;
    }
    50%{
        transform:scale(1.08);
        opacity:.86;
    }
}

.register-container{
    opacity:0;
    transform:translateY(40px);
    transition:1s ease;
    position:relative;
    z-index:2;
}

.register-container.show,
body.form-visible .register-container{
    opacity:1;
    transform:translateY(0);
}

body.form-visible .lamp-screen{
    display:none;
}

.register-card{
    position:relative;
    overflow:hidden;
}

.register-card::before{
    content:'';
    position:absolute;
    inset:-2px;
    background:linear-gradient(
        135deg,
        transparent,
        rgba(147,5,5,.25),
        transparent
    );
    transform:translateX(-100%);
    animation:borderGlow 5s linear infinite;
    z-index:0;
    pointer-events:none;
}

@keyframes borderGlow{
    0%{ transform:translateX(-100%); }
    100%{ transform:translateX(100%); }
}

.register-card > *{
    position:relative;
    z-index:2;
}

@media(max-width:768px){
    .lamp-wire{
        height:130px;
    }

    .lamp-light{
        width:420px;
        height:420px;
        top:95px;
    }

    .lamp-body{
        width:105px;
        height:105px;
    }

    .lamp-inner{
        width:66px;
        height:66px;
    }

    .lamp-text{
        font-size:22px;
        letter-spacing:5px;
    }
}

</style>
</head>
<body class="<?php echo !empty($message) ? 'form-visible' : ''; ?>">
<div class="lamp-screen" id="lampScreen">

    <div class="lamp-wrapper">

        <div class="lamp-wire"></div>

        <div class="lamp-light"></div>

        <div class="lamp-body" id="lampBtn">
            <div class="lamp-inner">
                <i class="fa-solid fa-power-off"></i>
            </div>
        </div>

        <h2 class="lamp-text">REGISTER</h2>

    </div>

</div>

<div class="register-container" id="registerContainer">
    <div class="register-card">
            <h2 class="site-logo">
    <img src="Icon.png" alt="Icon" class="logo-img">
</h2>
        <h2 class="site-logo">
            <i class="fa-solid fa-user-plus"></i> Register
        </h2>

        <?php if (!empty($message)) : ?>
            <div class="message <?php echo (strpos($message, '✓') !== false) ? 'message-success' : 'message-error'; ?>">
                <i class="fas <?php echo (strpos($message, '✓') !== false) ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <input 
                type="text" 
                name="name" 
                placeholder=" Full Name" 
                required
            >
            <input 
                type="email" 
                name="email" 
                placeholder="Email Address" 
                required
            >
            <div class="password-box">
                <input 
                    type="password" 
                    name="password" 
                    id="password"
                    placeholder=" Password" 
                    required
                >
                <span class="toggle-password" onclick="togglePassword()">
                    <i class="fa-regular fa-eye"></i>
                </span>
            </div>
            <button type="submit" class="register-btn">
                <i class="fa-solid fa-user-plus"></i> Create Account
            </button>
        </form>

        <a href="login.php" class="login-link">
            Already have an account? <strong>Login</strong>
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

/* LAMP REGISTER */

const lampScreen = document.getElementById("lampScreen");
const lampBtn = document.getElementById("lampBtn");
const registerContainer = document.getElementById("registerContainer");

if (lampBtn && lampScreen && registerContainer) {
    lampBtn.addEventListener("click", () => {

        lampScreen.classList.add("active");

        setTimeout(() => {
            lampScreen.style.opacity = "0";
            registerContainer.classList.add("show");
        }, 1400);

        setTimeout(() => {
            lampScreen.style.display = "none";
        }, 2200);

    });
}

</script>

</body>
</html>