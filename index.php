<?php
session_start();

if (!isset($_SESSION['visited'])) {
    $_SESSION['visited'] = true;
    header("Location: hello.html");
    exit();
}
$userImage = "";

if (isset($_SESSION['user_id'])) {

    $conn = new mysqli(
        "sql213.infinityfree.com",
        "if0_41900150",
        "Rany9NH3lawi",
        "if0_41900150_my_first_project"
    );
    $conn->set_charset("utf8mb4");
    if ($conn->connect_error) {
        die("Database connection failed");
    }

    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT image FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $userData = $result->fetch_assoc();
        $userImage = $userData['image'];
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A gaming store offering the best games at competitive prices">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="Icon.png">
    <link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#930505">
    <title>Gaming World</title>
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
  overflow:hidden;

  transition:
    transform .45s cubic-bezier(.68,-.55,.265,1.55),
    background .35s ease,
    box-shadow .35s ease,
    color .35s ease;
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

  transition:
    opacity .35s ease,
    transform .35s ease;

  text-shadow:0 0 8px rgba(147,5,5,.65);
}

/* Hover Animation فقط عند الماوس */

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
    overflow:hidden;
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

/* Search Container */
.chat-tools-bar{
    position:relative;
    display:flex;
    align-items:center;
    gap:10px;
    padding:8px 10px;
    justify-content:center;
    margin:20px auto;
    width:fit-content;
}

#searchInput{
    width:0;
    opacity:0;
    pointer-events:none;
    background:#1a1a1a;
    border:1px solid rgba(255,255,255,.08);
    height:42px;
    border-radius:14px;
    padding:0 14px;
    color:#fff;
    outline:none;
    transition:.25s ease;
    font-family:'Orbitron',sans-serif;
}

#searchInput.active{
    width:280px;
    opacity:1;
    pointer-events:auto;
}

#searchToggle{
    width:42px;
    height:42px;
    min-width:42px;
    border:none;
    border-radius:14px;
    background:#930505;
    color:#fff;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:17px;
    transition:.2s;
}

#searchToggle:hover{
    background:#b30a0a;
    transform:scale(1.05);
}

#searchResults{
    position:absolute;
    top:55px;
    width:280px;
    background:#111;
    border:1px solid rgba(147,5,5,.4);
    border-radius:14px;
    overflow:hidden;
    display:none;
    z-index:999;
    direction:ltr;
}

#searchResults div{
    padding:12px;
    cursor:pointer;
    color:#fff;
    transition:.2s;
    font-family:Arial,sans-serif;
}

#searchResults div:hover{
    background:#1d1d1d;
    color:#930505;
}

/* Filter Containers */
.filters-wrapper{
    display:flex;
    justify-content:center;
    gap:20px;
    flex-wrap:wrap;
    margin:20px 0;
}

.sort-container select,
.company-container select{
    background:#1a1a1a;
    color:#930505;
    border:1px solid #930505;
    padding:10px 20px;
    border-radius:40px;
    font-family:'Orbitron',sans-serif;
    cursor:pointer;
    transition:.3s;
}

.sort-container select:hover,
.company-container select:hover{
    background:#930505;
    color:#000;
}

/* Products Grid */
.products-container{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
    gap:25px;
    padding:20px;
    max-width:1400px;
    margin:0 auto;
}

.product-card{
    background:#0a0a0a;
    border:1px solid #930505;
    border-radius:15px;
    padding:15px;
    text-align:center;
    transition:.3s;
}

.product-card:hover{
    transform:translateY(-5px);
    box-shadow:0 0 20px rgba(147,5,5,.3);
}

.product-card img{
    width:100%;
    height:350px;
    object-fit:cover;
    border-radius:10px;
    margin-bottom:10px;
}

.product-card h3{
    color:#930505;
    font-size:16px;
    margin:10px 0;
}

.price{
    display:inline-block;
    background:#930505;
    color:#fff;
    padding:5px 15px;
    border-radius:20px;
    font-weight:bold;
}

.product-link{
    text-decoration:none;
    color:inherit;
}

/* Pagination */
.pagination{
    display:flex;
    justify-content:center;
    gap:10px;
    margin:40px 0;
    flex-wrap:wrap;
}

.pagination button{
    background:#1a1a1a;
    border:1px solid #930505;
    color:#930505;
    padding:10px 18px;
    border-radius:30px;
    cursor:pointer;
    font-family:'Orbitron',sans-serif;
    font-size:14px;
    font-weight:bold;
    transition:.3s;
}

.pagination button:hover{
    background:#930505;
    color:#000;
    transform:scale(1.05);
}

.pagination button.active{
    background:#930505;
    color:#000;
    box-shadow:0 0 10px rgba(147,5,5,.5);
}

/* Admin Button */
.admin-panel-link{
    position:fixed;
    bottom:30px;
    right:30px;
    width:55px;
    height:55px;
    background:#930505;
    color:#fff;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    font-weight:bold;
    font-size:22px;
    transition:.3s;
    z-index:1000;
    box-shadow:0 4px 15px rgba(147,5,5,.3);
}

.admin-panel-link:hover{
    background:#000;
    transform:scale(1.1);
    box-shadow:0 6px 20px rgba(0,0,0,.5);
}

/* Footer */
footer{
    text-align:center;
    padding:30px;
    background:#000;
    border-top:1px solid #930505;
    margin-top:40px;
}

footer p{
    color:#930505;
    font-family:'Orbitron',sans-serif;
    font-size:12px;
    margin:5px 0;
}

footer a{
    color:#930505;
    text-decoration:none;
    transition:.3s;
}

footer a:hover{
    color:#b30a0a;
}

/* Responsive */
@media(max-width:768px){

    header{
        flex-direction:column;
    }

    .products-container{
        grid-template-columns:repeat(auto-fill,minmax(200px,1fr));
        gap:15px;
        padding:15px;
    }

    .auth-links{
        width:100%;
        max-width:430px;
        overflow-x:auto;
        justify-content:flex-start;
        padding:0 10px;
        scrollbar-width:none;
    }

    .auth-links::-webkit-scrollbar{
        display:none;
    }

    .magic-nav-item{
        min-width:64px;
        width:64px;
    }

    .magic-nav-item .magic-text{
        font-size:9px;
    }

    .pagination button{
        padding:6px 12px;
        font-size:12px;
    }
}

@media(max-width:600px){

    .footer-content{
        text-align:center;
    }

}

/* No products message */
.no-products{
    text-align:center;
    padding:50px;
    color:#930505;
    font-size:18px;
    grid-column:1 / -1;
}
   /* ===== GAMING FOOTER ANIMATION ===== */

.gaming-footer{
    position:relative;
    margin-top:100px;
    padding:70px 20px 35px;
    background:
        radial-gradient(circle at top, rgba(147,5,5,.45), transparent 45%),
        linear-gradient(180deg, #000 0%, #120000 45%, #930505 100%);
    border-top:2px solid #930505;
    overflow:hidden;
    text-align:center;
    box-shadow:0 -20px 60px rgba(147,5,5,.25);
}

.gaming-footer::before{
    content:'';
    position:absolute;
    top:0;
    left:-100%;
    width:100%;
    height:100%;
    background:linear-gradient(
        120deg,
        transparent,
        rgba(255,255,255,.12),
        transparent
    );
    animation:footerShine 4s linear infinite;
}

@keyframes footerShine{
    0%{ left:-100%; }
    100%{ left:100%; }
}

.footer-content{
    position:relative;
    z-index:5;
    animation:footerFloat 3s ease-in-out infinite;
}

@keyframes footerFloat{
    0%,100%{
        transform:translateY(0);
    }
    50%{
        transform:translateY(-6px);
    }
}

.footer-content p{
    color:#fff;
    font-size:13px;
    margin:8px 0;
    text-shadow:0 0 12px rgba(147,5,5,.9);
}

.footer-content a{
    color:#fff;
    text-decoration:none;
    font-weight:900;
    transition:.3s;
}

.footer-content a:hover{
    color:#000;
    text-shadow:
        0 0 10px #fff,
        0 0 25px #930505;
}

/* FIRE ANIMATION */

.footer-fire{
    position:absolute;
    inset:0;
    z-index:2;
    pointer-events:none;
    overflow:hidden;
}

.footer-fire span{
    position:absolute;
    bottom:-80px;
    width:8px;
    height:80px;
    background:linear-gradient(
        to top,
        transparent,
        #930505,
        #ff1b1b,
        transparent
    );
    border-radius:50%;
    filter:blur(1px);
    box-shadow:
        0 0 12px #930505,
        0 0 25px #ff0000;
    animation:footerFireUp linear infinite;
}

.footer-fire span:nth-child(1){left:5%; animation-duration:3s; animation-delay:0s;}
.footer-fire span:nth-child(2){left:15%; animation-duration:4s; animation-delay:.4s;}
.footer-fire span:nth-child(3){left:25%; animation-duration:3.5s; animation-delay:.8s;}
.footer-fire span:nth-child(4){left:35%; animation-duration:4.5s; animation-delay:.2s;}
.footer-fire span:nth-child(5){left:45%; animation-duration:3.2s; animation-delay:1s;}
.footer-fire span:nth-child(6){left:55%; animation-duration:4.2s; animation-delay:.6s;}
.footer-fire span:nth-child(7){left:65%; animation-duration:3.7s; animation-delay:.1s;}
.footer-fire span:nth-child(8){left:75%; animation-duration:4.4s; animation-delay:.9s;}
.footer-fire span:nth-child(9){left:85%; animation-duration:3.3s; animation-delay:.3s;}
.footer-fire span:nth-child(10){left:95%; animation-duration:4.1s; animation-delay:.7s;}

@keyframes footerFireUp{
    0%{
        transform:translateY(0) scale(.6);
        opacity:0;
    }

    20%{
        opacity:1;
    }

    100%{
        transform:translateY(-260px) scale(1.8);
        opacity:0;
    }
}
    /* ===== INTRO PAGE ANIMATION ===== */

.intro-burn{
    position:fixed;
    inset:0;

    z-index:99999;

    background:
    radial-gradient(
        circle at center,
        rgba(255,60,60,.95) 0%,
        rgba(147,5,5,.78) 20%,
        rgba(0,0,0,1) 72%
    );

    animation:introBurn 1.6s ease forwards;

    pointer-events:none;
}

@keyframes introBurn{

    0%{
        opacity:1;
        transform:scale(3);
        filter:blur(0);
    }

    40%{
        opacity:1;
        transform:scale(1.6);
        filter:blur(3px);
    }

    100%{
        opacity:0;
        transform:scale(.7);
        filter:blur(12px);
        visibility:hidden;
    }
}

/* FIRE SPARKS */

.intro-fire{
    position:fixed;
    inset:0;

    z-index:99998;

    pointer-events:none;

    overflow:hidden;
}

.intro-fire span{
    position:absolute;

    bottom:-150px;

    width:10px;
    height:180px;

    border-radius:50%;

    background:
    linear-gradient(
        to top,
        rgba(255,0,0,0),
        rgba(255,60,60,1),
        rgba(147,5,5,0)
    );

    box-shadow:
        0 0 20px #930505,
        0 0 45px #ff0000;

    animation:introSparks linear forwards;
}

.intro-fire span:nth-child(1){left:8%; animation-duration:1.2s;}
.intro-fire span:nth-child(2){left:18%; animation-duration:1.4s;}
.intro-fire span:nth-child(3){left:28%; animation-duration:1.1s;}
.intro-fire span:nth-child(4){left:40%; animation-duration:1.5s;}
.intro-fire span:nth-child(5){left:52%; animation-duration:1.2s;}
.intro-fire span:nth-child(6){left:64%; animation-duration:1.6s;}
.intro-fire span:nth-child(7){left:75%; animation-duration:1.1s;}
.intro-fire span:nth-child(8){left:85%; animation-duration:1.4s;}
.intro-fire span:nth-child(9){left:95%; animation-duration:1.3s;}

@keyframes introSparks{

    0%{
        transform:translateY(0) scale(.5);
        opacity:0;
    }

    20%{
        opacity:1;
    }

    100%{
        transform:translateY(-120vh) scale(1.8);
        opacity:0;
    }
}

/* PAGE CONTENT ENTRANCE */

header,
.chat-tools-bar,
.filters-wrapper,
.products-container,
.pagination,
footer{
    animation:pageFade 1.1s ease forwards;
}

@keyframes pageFade{

    from{
        opacity:0;
        transform:translateY(25px);
    }

    to{
        opacity:1;
        transform:translateY(0);
    }
}


/* ================= LOGOUT RED EXIT ANIMATION ================= */

.logout-idea{
    isolation:isolate;
}

.logout-idea .logout-door{
    position:absolute;
    top:14px;
    left:50%;
    width:46px;
    height:46px;
    transform:translateX(-50%);
    border-radius:15px;
    border:1px solid rgba(147,5,5,.75);
    background:linear-gradient(145deg,#050505,#150000 60%,#300000);
    box-shadow:
        inset 0 0 18px rgba(147,5,5,.22),
        0 0 16px rgba(147,5,5,.35);
    opacity:.95;
    transition:.45s cubic-bezier(.68,-.55,.265,1.55);
    z-index:0;
    overflow:hidden;
}

.logout-idea .logout-door-panel{
    position:absolute;
    inset:6px;
    border-radius:11px;
    background:linear-gradient(135deg,#0a0a0a,#930505 120%);
    transform-origin:left center;
    transition:.45s cubic-bezier(.68,-.55,.265,1.55);
    box-shadow:
        inset -8px 0 14px rgba(0,0,0,.55),
        0 0 14px rgba(147,5,5,.28);
}

.logout-idea .logout-door-panel::after{
    content:'';
    position:absolute;
    right:7px;
    top:50%;
    width:5px;
    height:5px;
    transform:translateY(-50%);
    border-radius:50%;
    background:#930505;
    box-shadow:0 0 10px #930505;
}

.logout-idea .logout-door-glow{
    position:absolute;
    inset:-20px;
    background:radial-gradient(circle,rgba(147,5,5,.75),transparent 55%);
    opacity:0;
    transform:scale(.5);
    transition:.45s ease;
}

.logout-idea .logout-icon{
    background:rgba(147,5,5,.12);
    border:1px solid rgba(147,5,5,.55);
    box-shadow:0 0 12px rgba(147,5,5,.22);
}

.logout-idea .logout-smoke{
    position:absolute;
    top:22px;
    left:50%;
    width:60px;
    height:40px;
    transform:translateX(-50%);
    pointer-events:none;
    z-index:1;
}

.logout-idea .logout-smoke span{
    position:absolute;
    bottom:0;
    left:50%;
    width:8px;
    height:8px;
    border-radius:50%;
    background:rgba(147,5,5,.65);
    box-shadow:0 0 12px rgba(147,5,5,.8);
    opacity:0;
}

.logout-idea:hover .logout-door{
    transform:translateX(-50%) translateY(-34px) scale(1.08);
    border-color:#930505;
    box-shadow:
        inset 0 0 20px rgba(147,5,5,.35),
        0 0 25px rgba(147,5,5,.85),
        0 0 45px rgba(147,5,5,.35);
}

.logout-idea:hover .logout-door-panel{
    transform:perspective(80px) rotateY(-58deg);
}

.logout-idea:hover .logout-door-glow{
    opacity:1;
    transform:scale(1);
}

.logout-idea:hover .logout-icon{
    transform:translateY(-34px) translateX(19px) scale(1.05);
    background:#930505;
    color:#000;
    box-shadow:
        0 0 22px rgba(147,5,5,.95),
        0 0 42px rgba(147,5,5,.45);
}

.logout-idea:hover .logout-icon i{
    animation:logoutRun .55s ease infinite alternate;
}

.logout-idea:hover .logout-smoke span:nth-child(1){
    animation:logoutSmoke .8s ease infinite;
}

.logout-idea:hover .logout-smoke span:nth-child(2){
    animation:logoutSmoke .8s ease .15s infinite;
}

.logout-idea:hover .logout-smoke span:nth-child(3){
    animation:logoutSmoke .8s ease .3s infinite;
}

@keyframes logoutRun{
    from{
        transform:translateX(0) scale(1);
    }

    to{
        transform:translateX(5px) scale(1.08);
    }
}

@keyframes logoutSmoke{
    0%{
        transform:translate(-50%,0) scale(.4);
        opacity:0;
    }

    25%{
        opacity:.9;
    }

    100%{
        transform:translate(calc(-50% - 22px),-28px) scale(1.4);
        opacity:0;
    }
}



/* ================= LOGOUT VIDEO STYLE CLICK ANIMATION ================= */
.logout-idea.logout-clicked{
    pointer-events:none;
}

.logout-idea.logout-clicked .logout-door{
    transform:translateX(-50%) translateY(-34px) scale(1.12);
    border-color:#930505;
    box-shadow:
        inset 0 0 24px rgba(147,5,5,.42),
        0 0 28px rgba(147,5,5,1),
        0 0 55px rgba(147,5,5,.55);
}

.logout-idea.logout-clicked .logout-door-panel{
    animation:logoutDoorOpen .85s cubic-bezier(.2,.1,.2,1) forwards;
}

.logout-idea.logout-clicked .logout-icon{
    animation:logoutFigureRun .85s cubic-bezier(.2,.1,.2,1) forwards;
    background:#930505;
    color:#000;
    box-shadow:
        0 0 24px rgba(147,5,5,1),
        0 0 48px rgba(147,5,5,.5);
}

.logout-idea.logout-clicked .logout-icon i{
    animation:logoutLegs .18s ease-in-out infinite alternate;
}

.logout-idea.logout-clicked .magic-text{
    opacity:1;
    transform:translate(-50%,0);
    color:#fff;
}

.logout-idea.logout-clicked .logout-smoke span:nth-child(1){
    animation:logoutSmoke .55s ease infinite;
}

.logout-idea.logout-clicked .logout-smoke span:nth-child(2){
    animation:logoutSmoke .55s ease .12s infinite;
}

.logout-idea.logout-clicked .logout-smoke span:nth-child(3){
    animation:logoutSmoke .55s ease .24s infinite;
}

@keyframes logoutDoorOpen{
    0%{
        transform:perspective(80px) rotateY(0deg);
    }
    45%{
        transform:perspective(80px) rotateY(-65deg);
    }
    100%{
        transform:perspective(80px) rotateY(-75deg);
    }
}

@keyframes logoutFigureRun{
    0%{
        transform:translateY(-34px) translateX(0) scale(1.06);
        opacity:1;
    }
    45%{
        transform:translateY(-34px) translateX(23px) scale(1.05);
        opacity:1;
    }
    100%{
        transform:translateY(-34px) translateX(48px) scale(.82);
        opacity:0;
    }
}

@keyframes logoutLegs{
    from{
        transform:translateX(0) rotate(-8deg);
    }
    to{
        transform:translateX(5px) rotate(8deg);
    }
}

.logout-page-fade{
    position:fixed;
    inset:0;
    z-index:99999;
    background:#000;
    opacity:0;
    pointer-events:none;
    transition:opacity .55s ease;
}

.logout-page-fade.active{
    opacity:1;
}


/* ================= FINAL LOGOUT CHARACTER DOOR ANIMATION ================= */
/* This override makes the logout look like: door opens -> character runs out -> page goes to hello.html */

.logout-idea{
    position:relative !important;
    overflow:visible !important;
    isolation:isolate;
}

.logout-idea .logout-door{
    position:absolute;
    top:14px;
    left:50%;
    width:48px;
    height:48px;
    transform:translateX(-50%);
    border-radius:14px;
    border:1px solid rgba(147,5,5,.85);
    background:
        linear-gradient(145deg,#030303 0%,#160000 55%,#930505 135%);
    box-shadow:
        inset 0 0 18px rgba(147,5,5,.25),
        0 0 18px rgba(147,5,5,.35);
    z-index:1;
    overflow:hidden;
    transition:.45s cubic-bezier(.68,-.55,.265,1.55);
}

.logout-idea .logout-door-panel{
    position:absolute;
    inset:5px;
    border-radius:10px;
    background:
        linear-gradient(135deg,#090909 0%,#260000 55%,#930505 130%);
    transform-origin:left center;
    box-shadow:
        inset -10px 0 18px rgba(0,0,0,.65),
        0 0 14px rgba(147,5,5,.28);
}

.logout-idea .logout-door-panel::after{
    content:'';
    position:absolute;
    right:7px;
    top:50%;
    width:5px;
    height:5px;
    transform:translateY(-50%);
    border-radius:50%;
    background:#930505;
    box-shadow:0 0 10px #930505;
}

.logout-idea .logout-door-glow{
    position:absolute;
    inset:-25px;
    background:radial-gradient(circle,rgba(147,5,5,.85),transparent 58%);
    opacity:0;
}

.logout-idea .logout-icon{
    position:relative;
    z-index:3;
    background:rgba(147,5,5,.12);
    border:1px solid rgba(147,5,5,.55);
    box-shadow:0 0 14px rgba(147,5,5,.28);
}

.logout-idea .logout-icon i{
    color:#fff;
}

/* Hover preview */
.logout-idea:hover .logout-door{
    transform:translateX(-50%) translateY(-34px) scale(1.08);
}

.logout-idea:hover .logout-door-panel{
    transform:perspective(90px) rotateY(-62deg);
}

.logout-idea:hover .logout-door-glow{
    opacity:1;
}

.logout-idea:hover .logout-icon{
    transform:translateY(-34px) translateX(18px) scale(1.06);
    background:#930505;
    color:#000;
    box-shadow:
        0 0 22px rgba(147,5,5,.95),
        0 0 44px rgba(147,5,5,.45);
}

.logout-idea:hover .logout-icon i{
    color:#000;
    animation:logoutCharacterSteps .22s ease-in-out infinite alternate;
}

/* Click animation: character comes out of the door clearly */
.logout-idea.logout-clicked{
    pointer-events:none;
}

.logout-idea.logout-clicked .logout-door{
    animation:logoutDoorLift .95s cubic-bezier(.2,.1,.2,1) forwards;
}

.logout-idea.logout-clicked .logout-door-panel{
    animation:logoutDoorOpenFinal .95s cubic-bezier(.2,.1,.2,1) forwards;
}

.logout-idea.logout-clicked .logout-door-glow{
    animation:logoutGlowFinal .95s ease forwards;
}

.logout-idea.logout-clicked .logout-icon{
    animation:logoutCharacterOutFinal 1.05s cubic-bezier(.2,.1,.2,1) forwards;
    background:#930505;
    color:#000;
    box-shadow:
        0 0 26px rgba(147,5,5,1),
        0 0 55px rgba(147,5,5,.55);
}

.logout-idea.logout-clicked .logout-icon i{
    color:#000;
    animation:logoutCharacterSteps .16s ease-in-out infinite alternate;
}

.logout-idea.logout-clicked .magic-text{
    opacity:1;
    transform:translate(-50%,0);
    color:#fff;
}

.logout-idea.logout-clicked .logout-smoke span:nth-child(1){
    animation:logoutSmoke .55s ease infinite;
}
.logout-idea.logout-clicked .logout-smoke span:nth-child(2){
    animation:logoutSmoke .55s ease .12s infinite;
}
.logout-idea.logout-clicked .logout-smoke span:nth-child(3){
    animation:logoutSmoke .55s ease .24s infinite;
}

@keyframes logoutDoorLift{
    0%{
        transform:translateX(-50%) translateY(0) scale(1);
    }
    35%{
        transform:translateX(-50%) translateY(-34px) scale(1.1);
    }
    100%{
        transform:translateX(-50%) translateY(-34px) scale(1.12);
        box-shadow:
            inset 0 0 24px rgba(147,5,5,.42),
            0 0 30px rgba(147,5,5,1),
            0 0 58px rgba(147,5,5,.55);
    }
}

@keyframes logoutDoorOpenFinal{
    0%{
        transform:perspective(90px) rotateY(0deg);
    }
    35%{
        transform:perspective(90px) rotateY(-78deg);
    }
    100%{
        transform:perspective(90px) rotateY(-82deg);
    }
}

@keyframes logoutGlowFinal{
    0%{
        opacity:0;
        transform:scale(.4);
    }
    100%{
        opacity:1;
        transform:scale(1);
    }
}

@keyframes logoutCharacterOutFinal{
    0%{
        transform:translateY(-34px) translateX(-10px) scale(.65);
        opacity:0;
    }
    18%{
        transform:translateY(-34px) translateX(-2px) scale(.9);
        opacity:1;
    }
    48%{
        transform:translateY(-34px) translateX(22px) scale(1.12);
        opacity:1;
    }
    78%{
        transform:translateY(-34px) translateX(52px) scale(.98);
        opacity:1;
    }
    100%{
        transform:translateY(-34px) translateX(82px) scale(.75);
        opacity:0;
    }
}

@keyframes logoutCharacterSteps{
    from{
        transform:translateX(0) rotate(-10deg);
    }
    to{
        transform:translateX(4px) rotate(10deg);
    }
}

.logout-page-fade{
    position:fixed;
    inset:0;
    z-index:99999;
    background:#000;
    opacity:0;
    pointer-events:none;
    transition:opacity .55s ease;
}

.logout-page-fade.active{
    opacity:1;
}


/* ================= SAFE 3D FLOATING PRODUCTS - FIXED ================= */
/* تأثير آمن على المنتجات فقط: لا يحذف ولا يغير HTML ولا يكسر الفلترة */
.products-container{
    overflow:visible;
    perspective:1100px;
}

.product-link{
    display:block;
    text-decoration:none;
    color:inherit;
    height:100%;
}

.product-card{
    position:relative;
    overflow:hidden;
    cursor:pointer;
    transform:translateY(0) scale(1) rotateX(0) rotateY(0);
    transform-style:preserve-3d;
    will-change:transform, box-shadow, filter;
    transition:
        transform .28s ease,
        box-shadow .28s ease,
        border-color .28s ease,
        filter .28s ease;
}

.product-card::before{
    content:'';
    position:absolute;
    inset:0;
    background:radial-gradient(circle at var(--mx,50%) var(--my,50%), rgba(255,255,255,.22), rgba(147,5,5,.18) 22%, transparent 55%);
    opacity:0;
    pointer-events:none;
    z-index:2;
    transition:opacity .25s ease;
}

.product-card::after{
    content:'';
    position:absolute;
    inset:0;
    background:linear-gradient(120deg, transparent 0%, rgba(255,255,255,.16) 45%, transparent 70%);
    transform:translateX(-130%) skewX(-18deg);
    opacity:0;
    pointer-events:none;
    z-index:3;
}

.product-card:hover,
.product-card.is-3d-active{
    z-index:10;
    border-color:#ff1b1b;
    filter:brightness(1.08);
    box-shadow:
        0 18px 35px rgba(0,0,0,.75),
        0 0 22px rgba(147,5,5,.55),
        0 0 45px rgba(147,5,5,.25);
}

.product-card:hover::before,
.product-card.is-3d-active::before{
    opacity:1;
}

.product-card:hover::after,
.product-card.is-3d-active::after{
    opacity:1;
    animation:productSafeShine .8s ease forwards;
}

@keyframes productSafeShine{
    from{ transform:translateX(-130%) skewX(-18deg); }
    to{ transform:translateX(130%) skewX(-18deg); }
}

.product-card img{
    position:relative;
    z-index:1;
    transition:transform .28s ease, filter .28s ease;
    transform:translateZ(0);
}

.product-card:hover img,
.product-card.is-3d-active img{
    transform:scale(1.045) translateZ(28px);
    filter:saturate(1.12) contrast(1.08);
}

.product-card h3,
.product-card .price{
    position:relative;
    z-index:4;
    transition:transform .28s ease, text-shadow .28s ease, box-shadow .28s ease;
}

.product-card:hover h3,
.product-card.is-3d-active h3{
    transform:translateY(-3px) translateZ(34px);
    text-shadow:0 0 12px rgba(147,5,5,.9);
}

.product-card:hover .price,
.product-card.is-3d-active .price{
    transform:translateY(-2px) scale(1.04) translateZ(40px);
    box-shadow:0 0 16px rgba(147,5,5,.75);
}

@media(max-width:768px){
    .product-card:hover,
    .product-card.is-3d-active{
        transform:translateY(-8px) scale(1.03) !important;
    }

    .product-card:hover img,
    .product-card.is-3d-active img{
        transform:scale(1.035) !important;
    }
}


/* ================= FILTERS RIGHT SIDE FIX ================= */
/* Moves Price + Genre filters to the far right, stacked vertically.
   Search bar stays centered and won't overlap with filters. */
.filters-wrapper{
    width:100% !important;
    max-width:1400px !important;
    margin:18px auto 25px !important;
    padding:0 30px !important;
    display:flex !important;
    flex-direction:column !important;
    align-items:flex-end !important;
    justify-content:flex-start !important;
    gap:12px !important;
    position:relative !important;
    z-index:50 !important;
}

.sort-container,
.company-container{
    width:260px !important;
    max-width:100% !important;
    display:flex !important;
    justify-content:flex-end !important;
}

.sort-container select,
.company-container select{
    width:260px !important;
    max-width:100% !important;
    height:48px !important;
    text-align:left !important;
    direction:ltr !important;
}

/* Keep search alone in the center */
.chat-tools-bar{
    margin:28px auto 8px !important;
    z-index:80 !important;
}

/* Phone/tablet fix */
@media(max-width:768px){
    .filters-wrapper{
        align-items:flex-end !important;
        padding:0 15px !important;
        margin:15px auto 20px !important;
        gap:10px !important;
    }

    .sort-container,
    .company-container{
        width:210px !important;
    }

    .sort-container select,
    .company-container select{
        width:210px !important;
        height:44px !important;
        padding:8px 14px !important;
        font-size:12px !important;
    }

    .chat-tools-bar{
        margin:22px auto 10px !important;
    }

    #searchInput.active{
        width:230px !important;
    }

    #searchResults{
        width:230px !important;
    }
}

@media(max-width:480px){
    .filters-wrapper{
        padding:0 12px !important;
    }

    .sort-container,
    .company-container,
    .sort-container select,
    .company-container select{
        width:190px !important;
    }
}

</style>
</head>
<body>
<div class="intro-burn"></div>

<div class="intro-fire">
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
</div>
<header>
    <h1 class="site-logo">
    <img src="Icon.png" alt="Icon" class="logo-img">
</h1>
    <div class="auth-links">
        <?php if(isset($_SESSION['user_name'])): ?>
            <a href="profile.php" class="magic-nav-item welcome-user-box active" title="Profile">
                <span class="magic-icon">
                    <?php if(!empty($userImage)): ?>
                        <img src="<?php echo $userImage; ?>" class="nav-profile-img" alt="Profile">
                    <?php else: ?>
                        <span class="nav-profile-img default-nav-icon">
                            <i class="fa-solid fa-user"></i>
                        </span>
                    <?php endif; ?>
                </span>
                <span class="magic-text"><?php echo $_SESSION['user_name']; ?></span>
            </a>
            <a href="#" id="logoutBtn" class="magic-nav-item logout-idea" title="Logout">
                <span class="logout-door">
                    <span class="logout-door-panel"></span>
                    <span class="logout-door-glow"></span>
                </span>

                <span class="magic-icon logout-icon">
                    <i class="fa-solid fa-person-running"></i>
                </span>

                <span class="logout-smoke">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>

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
        <a href="login.php" style="margin-right:20px;">Login</a>
        <a href="signup.php">Create Account</a>
        <?php endif; ?>
    </div>
<nav>
    <a href="index.php" style="color:#930505;">
        <i class="fa-solid fa-house"></i> Home
    </a>

    <a href="cart.php">
        <i class="fa-solid fa-cart-shopping"></i> Cart
    </a>

    <a href="P2.php">
        <i class="fa-solid fa-headset"></i> Contact Us
    </a>
</nav>
</header>
   
<div class="chat-tools-bar">

    <button type="button" id="searchToggle">
        <i class="fa-solid fa-magnifying-glass"></i>
    </button>

    <input
        type="text"
        id="searchInput"
        placeholder="Search games"
        autocomplete="off"
    >

    <div id="searchResults"></div>

</div>

<div class="filters-wrapper">
    <div class="sort-container">
        <select id="sortPrice">
            <option value="Price">📊 By Price</option>
            <option value="low">⬆️ Lowest to Highest</option>
            <option value="high">⬇️ Highest to Lowest</option>
        </select>
    </div>

    <div class="company-container">
        <select id="companyFilter">
            <option value="all">🎮 By Genre</option>
            <option value="Adventure">🗺️ Adventure</option>
            <option value="RPG">⚔️ RPG</option>
            <option value="Horror">👻 Horror</option>
            <option value="Meroidvania">🐞 Meroidvania</option>
            <option value="Sports">⚽ Sports</option>
            <option value="Souls">💀 Souls</option>
            <option value="Shooting">🔫 Shooting</option>
            <option value="Simulation">🏗️ Simulation</option>
            <option value="Hack">⚡ Hack & Slash</option>
            <option value="Fighting">🥊 Fighting</option>
            <option value="Platforms">🎯 Platforms</option>
            <option value="others">🎲 Others</option>
        </select>
    </div>
</div>

<main class="products-container" id="productsContainer"></main>

<div class="pagination" id="pagination"></div>

<footer class="gaming-footer">
    <div class="footer-fire">
        <span></span><span></span><span></span><span></span><span></span>
        <span></span><span></span><span></span><span></span><span></span>
    </div>

    <div class="footer-content">
        <p>&copy; Gaming World rights reserved</p>

        <p>My Social</p>

        <p>
            <a href="https://3laa.66ghz.com/" target="_blank">
                <i class="fa-solid fa-user"></i>
                Alaa Herzallah
            </a>
        </p>
    </div>
</footer>
    <svg style="position:absolute;width:0;height:0;overflow:hidden;" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <filter id="liquid-effect">
            <feGaussianBlur in="SourceGraphic" stdDeviation="12" result="blur"/>
            <feColorMatrix
                in="blur"
                mode="matrix"
                values="
                1 0 0 0 0
                0 1 0 0 0
                0 0 1 0 0
                0 0 0 19 -9"
                result="liquid"
            />
        </filter>
    </defs>
</svg>
<div class="logout-page-fade" id="logoutPageFade"></div>
    <!-- FIRE BACKGROUND FROM FOOTER STYLE -->
<div class="site-fire-bg" aria-hidden="true">
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
</div>

<style>
/* ================= SITE BACKGROUND FIRE - SAME FOOTER IDEA ================= */
/* نفس حركة نار الفوتر لكن ممتدة على خلفية الموقع بدل الدوائر */
.site-fire-bg{
    position:fixed;
    inset:0;
    z-index:0;
    pointer-events:none;
    overflow:hidden;
    background:
        radial-gradient(circle at 50% 100%, rgba(147,5,5,.30), transparent 34%),
        linear-gradient(to top, rgba(147,5,5,.10), transparent 48%);
}

.site-fire-bg::before{
    content:'';
    position:absolute;
    left:0;
    right:0;
    bottom:0;
    height:42vh;
    background:
        radial-gradient(circle at 18% 100%, rgba(255,0,0,.20), transparent 34%),
        radial-gradient(circle at 50% 100%, rgba(147,5,5,.32), transparent 38%),
        radial-gradient(circle at 82% 100%, rgba(255,0,0,.18), transparent 34%),
        linear-gradient(to top, rgba(147,5,5,.34), rgba(147,5,5,.10), transparent);
    filter:blur(10px);
    opacity:.9;
    animation:siteFireGlow 3.2s ease-in-out infinite alternate;
}

.site-fire-bg::after{
    content:'';
    position:absolute;
    inset:0;
    background:
        linear-gradient(to top, rgba(0,0,0,0) 0%, rgba(0,0,0,.12) 48%, rgba(0,0,0,.02) 100%);
    opacity:.75;
}

.site-fire-bg span{
    position:absolute;
    bottom:-120px;
    width:8px;
    height:120px;
    background:linear-gradient(
        to top,
        rgba(147,5,5,0),
        rgba(147,5,5,.85),
        rgba(255,27,27,.95),
        rgba(255,255,255,.22),
        rgba(147,5,5,0)
    );
    border-radius:50%;
    filter:blur(1px);
    box-shadow:
        0 0 12px #930505,
        0 0 25px #ff0000,
        0 0 42px rgba(147,5,5,.45);
    opacity:0;
    animation:siteFireUp linear infinite;
}

.site-fire-bg span:nth-child(1){left:3%; animation-duration:5.2s; animation-delay:-1.1s; height:110px; width:7px;}
.site-fire-bg span:nth-child(2){left:7%; animation-duration:4.4s; animation-delay:-2.4s; height:95px;}
.site-fire-bg span:nth-child(3){left:12%; animation-duration:6s; animation-delay:-.6s; height:135px; width:9px;}
.site-fire-bg span:nth-child(4){left:16%; animation-duration:4.8s; animation-delay:-3s; height:115px;}
.site-fire-bg span:nth-child(5){left:21%; animation-duration:5.7s; animation-delay:-1.7s; height:150px; width:10px;}
.site-fire-bg span:nth-child(6){left:26%; animation-duration:4.9s; animation-delay:-2.2s; height:100px;}
.site-fire-bg span:nth-child(7){left:31%; animation-duration:6.3s; animation-delay:-.9s; height:130px; width:7px;}
.site-fire-bg span:nth-child(8){left:36%; animation-duration:4.6s; animation-delay:-3.3s; height:105px;}
.site-fire-bg span:nth-child(9){left:41%; animation-duration:5.5s; animation-delay:-1.2s; height:145px; width:9px;}
.site-fire-bg span:nth-child(10){left:46%; animation-duration:4.7s; animation-delay:-2.8s; height:115px;}
.site-fire-bg span:nth-child(11){left:51%; animation-duration:6.1s; animation-delay:-.3s; height:155px; width:10px;}
.site-fire-bg span:nth-child(12){left:56%; animation-duration:4.5s; animation-delay:-3.6s; height:95px;}
.site-fire-bg span:nth-child(13){left:61%; animation-duration:5.8s; animation-delay:-1.9s; height:140px; width:9px;}
.site-fire-bg span:nth-child(14){left:66%; animation-duration:4.9s; animation-delay:-2.6s; height:105px;}
.site-fire-bg span:nth-child(15){left:71%; animation-duration:6.4s; animation-delay:-.8s; height:160px; width:10px;}
.site-fire-bg span:nth-child(16){left:76%; animation-duration:4.6s; animation-delay:-3.1s; height:110px;}
.site-fire-bg span:nth-child(17){left:81%; animation-duration:5.4s; animation-delay:-1.5s; height:135px; width:8px;}
.site-fire-bg span:nth-child(18){left:86%; animation-duration:4.8s; animation-delay:-2.9s; height:100px;}
.site-fire-bg span:nth-child(19){left:91%; animation-duration:6.2s; animation-delay:-.5s; height:150px; width:9px;}
.site-fire-bg span:nth-child(20){left:96%; animation-duration:5s; animation-delay:-2.1s; height:120px;}
.site-fire-bg span:nth-child(21){left:10%; animation-duration:7.2s; animation-delay:-4.5s; height:210px; width:5px; opacity:.55;}
.site-fire-bg span:nth-child(22){left:24%; animation-duration:7.8s; animation-delay:-5.2s; height:230px; width:5px; opacity:.50;}
.site-fire-bg span:nth-child(23){left:38%; animation-duration:7.4s; animation-delay:-4.8s; height:220px; width:5px; opacity:.55;}
.site-fire-bg span:nth-child(24){left:52%; animation-duration:8s; animation-delay:-5.6s; height:245px; width:5px; opacity:.50;}
.site-fire-bg span:nth-child(25){left:67%; animation-duration:7.6s; animation-delay:-4.2s; height:225px; width:5px; opacity:.55;}
.site-fire-bg span:nth-child(26){left:83%; animation-duration:8.2s; animation-delay:-5.8s; height:250px; width:5px; opacity:.50;}
.site-fire-bg span:nth-child(27){left:44%; animation-duration:9s; animation-delay:-6s; height:280px; width:4px; opacity:.42;}
.site-fire-bg span:nth-child(28){left:73%; animation-duration:8.8s; animation-delay:-6.4s; height:270px; width:4px; opacity:.42;}

@keyframes siteFireUp{
    0%{
        transform:translateY(0) scale(.55) rotate(0deg);
        opacity:0;
    }
    14%{
        opacity:.95;
    }
    65%{
        opacity:.72;
    }
    100%{
        transform:translateY(-118vh) scale(1.7) rotate(18deg);
        opacity:0;
    }
}

@keyframes siteFireGlow{
    from{
        opacity:.55;
        transform:scaleY(.95);
    }
    to{
        opacity:1;
        transform:scaleY(1.05);
    }
}

/* يخلي النار خلف كل المحتوى وما تغطي المنتجات */
header,
.chat-tools-bar,
.filters-wrapper,
.products-container,
.pagination,
footer,
.logout-page-fade{
    position:relative;
    z-index:2;
}

@media(max-width:768px){
    .site-fire-bg span{
        height:90px;
        width:6px;
    }

    .site-fire-bg::before{
        height:34vh;
        filter:blur(8px);
    }
}

@media (prefers-reduced-motion:reduce){
    .site-fire-bg span,
    .site-fire-bg::before{
        animation:none!important;
    }
}
</style>
<script>
// Magic profile navigation animation
const magicNavItems = document.querySelectorAll('.magic-nav-item');

magicNavItems.forEach((item) => {
    item.addEventListener('mouseenter', () => {
        magicNavItems.forEach((link) => link.classList.remove('active'));
        item.classList.add('active');
    });
});

// Games data
const games = [
  { name: "Grand Theft Auto V", page: "gta.html", price: 20, genre: "RPG", image: "https://upload.wikimedia.org/wikipedia/en/a/a5/Grand_Theft_Auto_V.png" },
  { name: "Red Dead Redemption 2", page: "rdr.html", price: 25, genre: "RPG", image: "https://upload.wikimedia.org/wikipedia/en/4/44/Red_Dead_Redemption_II.jpg" },
  { name: "Elden Ring", page: "elden.html", price: 35, genre: "Souls", image: "https://upload.wikimedia.org/wikipedia/en/b/b9/Elden_Ring_Box_art.jpg" },
  { name: "Resident Evil 4 Remake", page: "re4.html", price: 30, genre: "Horror", image: "https://upload.wikimedia.org/wikipedia/en/d/df/Resident_Evil_4_remake_cover_art.jpg" },
  { name: "Cyberpunk 2077", page: "cyberpunk.html", price: 30, genre: "RPG", image: "https://upload.wikimedia.org/wikipedia/en/9/9f/Cyberpunk_2077_box_art.jpg" },
  { name: "Bloodborne", page: "bloodborne.html", price: 15, genre: "Souls", image: "https://upload.wikimedia.org/wikipedia/en/6/68/Bloodborne_Cover_Wallpaper.jpg" },
  { name: "Sekiro Shadows Die Twice", page: "sekiro.html", price: 15, genre: "Souls", image: "https://upload.wikimedia.org/wikipedia/en/6/6e/Sekiro_art.jpg" },
  { name: "Ghost of Tsushima", page: "Ghost.html", price: 25, genre: "Adventure", image: "https://upload.wikimedia.org/wikipedia/en/b/b6/Ghost_of_Tsushima.jpg" },
  { name: "FC 26", page: "fc26.html", price: 50, genre: "Sports", image: "https://image.api.playstation.com/vulcan/ap/rnd/202507/1617/f0fe830f8f01600d13cce060680e0287374c58613a63c716.png" },
  { name: "Uncharted 4", page: "Uncharted.html", price: 10, genre: "Adventure", image: "https://upload.wikimedia.org/wikipedia/en/1/1a/Uncharted_4_box_artwork.jpg" },
  { name: "The Last of Us", page: "tlou.html", price: 15, genre: "Horror", image: "https://upload.wikimedia.org/wikipedia/en/4/46/Video_Game_Cover_-_The_Last_of_Us.jpg" },
  { name: "God of War Ragnarök", page: "gow.html", price: 45, genre: "Adventure", image: "https://upload.wikimedia.org/wikipedia/en/e/ee/God_of_War_Ragnar%C3%B6k_cover.jpg" },
  { name: "Marvel Spider-Man 2", page: "sm2.html", price: 50, genre: "Adventure", image: "https://upload.wikimedia.org/wikipedia/en/0/0f/SpiderMan2PS5BoxArt.jpeg" },
  { name: "The Witcher 3 Wild Hunt", page: "witcher3.html", price: 15, genre: "RPG", image: "https://upload.wikimedia.org/wikipedia/commons/0/0b/The_Witcher_3_-_Standard_Edition_Unboxing_%28Official_Trailer%29_cover.jpg" },
  { name: "Assassin's Creed IV Black Flag", page: "acbf.html", price: 20, genre: "Adventure", image: "https://upload.wikimedia.org/wikipedia/en/2/28/Assassin%27s_Creed_IV_-_Black_Flag_cover.jpg" },
  { name: "Minecraft", page: "minecraft.html", price: 20, genre: "RPG", image: "https://www.metacritic.com/a/img/resize/2632ef55e90ff9375b4ca536ad814726741a573b/catalog/provider/6/12/6-1-702279-52.jpg?auto=webp&fit=cover&height=264&width=176" },
  { name: "Mortal Kombat 1", page: "mk1.html", price: 40, genre: "Fighting", image: "https://upload.wikimedia.org/wikipedia/en/5/5b/Mortal_Kombat_1_key_art.jpeg" },
  { name: "Call of Duty Modern Warfare Trilogy", page: "MWT.html", price: 15, genre: "Shooting", image: "https://cdn2.steamgriddb.com/thumb/07d253164fa54f7f2c4e801a06696134.jpg" },
  { name: "Call of Duty Black Ops II", page: "codbo2.html", price: 10, genre: "Shooting", image: "https://upload.wikimedia.org/wikipedia/en/0/05/Call_of_Duty_Black_Ops_II_box_artwork.png" },
  { name: "Batman Arkham City", page: "bmac.html", price: 25, genre: "Adventure", image: "https://upload.wikimedia.org/wikipedia/en/0/00/Batman_Arkham_City_Game_Cover.jpg" },
  { name: "WWE 2K26", page: "wwe.html", price: 50, genre: "Sports", image: "https://upload.wikimedia.org/wikipedia/en/4/4f/WWE_2K26_standard_cover.jpeg" },
  { name: "Outlast", page: "outlast.html", price: 15, genre: "Horror", image: "https://upload.wikimedia.org/wikipedia/en/a/aa/Outlast_cover.jpg" },
  { name: "Mafia Trilogy", page: "mafia.html", price: 10, genre: "Adventure", image: "https://cdn2.unrealengine.com/Diesel%2Fbundles%2Fmafia-trilogy%2FEGS_MafiaTrilogy_Hangar13_S2-1200x1600-cc4971fd3a4fd7cab997c42804d981c08e83b13b.jpg" },
  { name: "Hollow Knight", page: "HollowKnight.html", price: 15, genre: "Meroidvania", image: "https://upload.wikimedia.org/wikipedia/en/d/de/Hollow_Knight_2026_cover_art.jpg" },
  { name: "Devil May Cry 5", page: "DMC5.html", price: 25, genre: "Hack", image: "https://upload.wikimedia.org/wikipedia/en/c/cb/Devil_May_Cry_5.jpg" },
  { name: "Gran Turismo 7", page: "GT7.html", price: 35, genre: "Sports", image: "https://upload.wikimedia.org/wikipedia/en/1/14/Gran_Turismo_7_cover_art.jpg" },
  { name: "ARC Raiders", page: "Arc.html", price: 25, genre: "Shooting", image: "https://upload.wikimedia.org/wikipedia/en/7/73/Arc_Raiders_cover_art.jpg" },
  { name: "Stardew Valley", page: "Stardew.html", price: 5, genre: "Simulation", image: "https://upload.wikimedia.org/wikipedia/en/f/fd/Logo_of_Stardew_Valley.png" },
  { name: "Cuphead", page: "cup.html", price: 20, genre: "Platforms", image: "https://upload.wikimedia.org/wikipedia/en/e/eb/Cuphead_%28artwork%29.png" },
  { name: "Psychonauts 2", page: "Psychonauts2.html", price: 20, genre: "Platforms", image: "https://upload.wikimedia.org/wikipedia/en/2/23/Psychonauts_2_cover.png" },
  { name: "Invincible VS", page: "invincible.html", price: 25, genre: "Fighting", image: "https://upload.wikimedia.org/wikipedia/en/8/84/Invincible_VS_cover_art.jpeg" },
  { name: "The Evil Within", page: "EW.html", price: 5, genre: "Horror", image: "https://upload.wikimedia.org/wikipedia/en/5/56/The_Evil_Within_boxart.jpg" }
];

// Global variables
let currentPage = 1;
const productsPerPage = 8;
let currentFilteredGames = [...games];

// DOM elements
const productsContainer = document.getElementById('productsContainer');
const paginationContainer = document.getElementById('pagination');
const sortSelect = document.getElementById('sortPrice');
const genreSelect = document.getElementById('companyFilter');
const searchInput = document.getElementById('searchInput');

// Function to filter and sort games
function filterGames() {
    let filtered = [...games];
    
    // Filter by genre
    const selectedGenre = genreSelect.value;
    if (selectedGenre !== 'all') {
        filtered = filtered.filter(game => game.genre === selectedGenre);
    }
    
    // Filter by search
    const searchText = searchInput.value.toLowerCase().trim();
    if (searchText) {
        filtered = filtered.filter(game => game.name.toLowerCase().includes(searchText));
    }
    
    // Sort by price
    const sortValue = sortSelect.value;
    if (sortValue === 'low') {
        filtered.sort((a, b) => a.price - b.price);
    } else if (sortValue === 'high') {
        filtered.sort((a, b) => b.price - a.price);
    }
    
    currentFilteredGames = filtered;
    currentPage = 1;
    displayProducts();
}

// Function to display products for current page
function displayProducts() {
    const startIndex = (currentPage - 1) * productsPerPage;
    const endIndex = startIndex + productsPerPage;
    const productsToShow = currentFilteredGames.slice(startIndex, endIndex);
    
    if (productsToShow.length === 0) {
        productsContainer.innerHTML = '<div class="no-products">🎮 No games found matching your criteria</div>';
        renderPagination();
        return;
    }
    
    productsContainer.innerHTML = productsToShow.map(game => `
        <div class="product-card" data-company="${game.genre}" tabindex="0">
            <a href="${game.page}" class="product-link">
                <img src="${game.image}" width="284" height="351" alt="${game.name}" loading="lazy">
                <h3>${game.name}</h3>
                <span class="price">${game.price}JD</span>
            </a>
        </div>
    `).join('');
    
    renderPagination();
    enableProductTouchAnimation();
}


/* SAFE 3D PRODUCT ANIMATION - MOUSE + PHONE */
function enableProductTouchAnimation(){
    const cards = document.querySelectorAll('.product-card');

    cards.forEach(card => {
        card.onmousemove = function(e){
            if (window.matchMedia('(max-width: 768px)').matches) return;

            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const midX = rect.width / 2;
            const midY = rect.height / 2;
            const rotateY = ((x - midX) / midX) * 8;
            const rotateX = ((midY - y) / midY) * 8;

            card.style.setProperty('--mx', `${x}px`);
            card.style.setProperty('--my', `${y}px`);
            card.style.transform = `translateY(-14px) scale(1.055) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
        };

        card.onmouseleave = function(){
            card.style.transform = '';
            card.classList.remove('is-3d-active');
        };

        card.ontouchstart = function(){
            cards.forEach(item => {
                if (item !== card) {
                    item.classList.remove('is-3d-active');
                    item.style.transform = '';
                }
            });
            card.classList.add('is-3d-active');
        };
    });
}

document.addEventListener('touchstart', function(e){
    if (!e.target.closest('.product-card')) {
        document.querySelectorAll('.product-card.is-3d-active').forEach(card => {
            card.classList.remove('is-3d-active');
            card.style.transform = '';
        });
    }
}, { passive:true });

// Function to render pagination buttons
function renderPagination() {
    const totalPages = Math.ceil(currentFilteredGames.length / productsPerPage);
    
    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }
    
    let paginationHTML = '';
    
    // Previous button
    paginationHTML += `<button onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''}>« Prev</button>`;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            paginationHTML += `<button onclick="goToPage(${i})" class="${i === currentPage ? 'active' : ''}">${i}</button>`;
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            paginationHTML += `<button disabled style="opacity:0.5; cursor:default;">...</button>`;
        }
    }
    
    // Next button
    paginationHTML += `<button onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''}>Next »</button>`;
    
    paginationContainer.innerHTML = paginationHTML;
}

// Function to change page
function goToPage(page) {
    const totalPages = Math.ceil(currentFilteredGames.length / productsPerPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    displayProducts();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Event listeners
sortSelect.addEventListener('change', filterGames);
genreSelect.addEventListener('change', filterGames);
searchInput.addEventListener('input', filterGames);

// Search toggle like chat search
const searchBtn = document.getElementById('searchToggle');
const searchResults = document.getElementById('searchResults');

searchBtn.addEventListener('click', () => {

    searchInput.classList.toggle('active');

    if (searchInput.classList.contains('active')) {

        searchInput.focus();

    } else {

        searchInput.value = '';
        searchResults.style.display = 'none';
        filterGames();
    }
});

// Search results for dropdown
searchInput.addEventListener("input", function () {

    const value = this.value.toLowerCase().trim();

    searchResults.innerHTML = "";

    filterGames();

    if (value === "") {
        searchResults.style.display = "none";
        return;
    }

    const filteredGames = games.filter(game =>
        game.name.toLowerCase().includes(value)
    ).slice(0, 8);

    if (filteredGames.length === 0) {
        searchResults.style.display = "none";
        return;
    }

    filteredGames.forEach(game => {

        const item = document.createElement("div");

        item.innerHTML = `
            <div style="
                display:flex;
                align-items:center;
                gap:10px;
            ">
                <img
                    src="${game.image}"
                    style="
                        width:45px;
                        height:45px;
                        border-radius:10px;
                        object-fit:cover;
                        flex-shrink:0;
                    "
                >

                <span>${game.name}</span>
            </div>
        `;

        item.addEventListener("click", () => {
            window.location.href = game.page;
        });

        searchResults.appendChild(item);
    });

    searchResults.style.display = "block";
});

document.addEventListener("click", function(e) {

    if (!document.querySelector(".chat-tools-bar").contains(e.target)) {
        searchResults.style.display = "none";
    }
});

// Initialize
filterGames();
    if ('serviceWorker' in navigator) {

    window.addEventListener('load', () => {

        navigator.serviceWorker.register('sw.js')
        .then(reg => console.log('SW Registered'))
        .catch(err => console.log(err));

    });
}
    window.addEventListener("load", () => {

    setTimeout(() => {

        document.querySelector(".intro-burn")?.remove();
        document.querySelector(".intro-fire")?.remove();

    }, 1600);

});
    /* FOOTER PARTICLES */

document.addEventListener("DOMContentLoaded", () => {

    const container = document.getElementById("particle-container");

    if (!container) return;

    const particleCount = 90;

    const fragment = document.createDocumentFragment();

    for (let i = 0; i < particleCount; i++) {

        const span = document.createElement("span");

        span.classList.add("particle");

        const size = 2 + Math.random() * 5;

        const distance = 10 + Math.random() * 18;

        const position = Math.random() * 100;

        const time = 3 + Math.random() * 3;

        const delay = -1 * (Math.random() * 10);

        span.style.setProperty("--dim", `${size}rem`);
        span.style.setProperty("--uplift", `${distance}rem`);
        span.style.setProperty("--pos-x", `${position}%`);
        span.style.setProperty("--dur", `${time}s`);
        span.style.setProperty("--delay", `${delay}s`);

        fragment.appendChild(span);
    }

    container.appendChild(fragment);

});




/* LOGOUT CHARACTER DOOR ANIMATION */
const logoutBtn = document.getElementById("logoutBtn");
const logoutPageFade = document.getElementById("logoutPageFade");

if (logoutBtn) {
    logoutBtn.addEventListener("click", function(e){
        e.preventDefault();

        logoutBtn.classList.add("logout-clicked");

        setTimeout(() => {
            if (logoutPageFade) {
                logoutPageFade.classList.add("active");
            }
        }, 850);

        setTimeout(() => {
            window.location.href = "hello.html";
        }, 1350);
    });
}

</script>
</body>
</html>