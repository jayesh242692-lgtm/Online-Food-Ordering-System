<?php
session_start();
include("connection/connect.php");
error_reporting(0);

// Already logged in — redirect to correct place
if(!empty($_SESSION['adm_id']))        { header("Location: admin/dashboard.php"); exit(); }
if(!empty($_SESSION['restaurant_id'])) { header("Location: restaurant_dashboard.php"); exit(); }
if(!empty($_SESSION['user_id']))       { header("Location: index.php"); exit(); }

$error = '';

if(isset($_POST['submit'])) {
    $identifier = trim($_POST['identifier']);
    $password   = trim($_POST['password']);

    if(empty($identifier) || empty($password)) {
        $error = "Please enter your username/email and password.";
    } else {
        $hpwd    = md5($password);
        $safe_id = mysqli_real_escape_string($db, $identifier);

        // 1. Check ADMIN (username match)
        $adm = mysqli_query($db, "SELECT * FROM admin WHERE username='$safe_id' AND password='$hpwd' LIMIT 1");
        if(mysqli_num_rows($adm) == 1) {
            $r = mysqli_fetch_array($adm);
            $_SESSION['adm_id']       = $r['adm_id'];
            $_SESSION['adm_username'] = $r['username'];
            header("Location: admin/dashboard.php");
            exit();
        }

        // 2. Check RESTAURANT (email match)
        $res = mysqli_query($db, "SELECT * FROM restaurant WHERE email='$safe_id' AND password='$hpwd' LIMIT 1");
        if(mysqli_num_rows($res) == 1) {
            $r = mysqli_fetch_array($res);
            $_SESSION['restaurant_id']   = $r['rs_id'];
            $_SESSION['restaurant_name'] = $r['title'];
            $_SESSION['restaurant_img']  = $r['image'];
            header("Location: restaurant_dashboard.php");
            exit();
        }

        // 3. Check USER (username or email match)
        $usr = mysqli_query($db, "SELECT * FROM users WHERE (username='$safe_id' OR email='$safe_id') AND password='$hpwd' AND status=1 LIMIT 1");
        if(mysqli_num_rows($usr) == 1) {
            $r = mysqli_fetch_array($usr);
            $_SESSION['user_id']   = $r['u_id'];
            $_SESSION['username']  = $r['username'];
            $_SESSION['user_name'] = $r['f_name'].' '.$r['l_name'];
            header("Location: index.php");
            exit();
        }

        $error = "Invalid credentials. Please check and try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — O.F.O.S</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #0f0f1a;
            overflow-x: hidden;
        }

                /* Animated background blobs */
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.22;
            pointer-events: none;
            animation: bfloat 9s ease-in-out infinite;
        }
        .b1 { width:420px; height:420px; background:#ff5722; top:-100px; left:-120px; animation-delay:0s; }
        .b2 { width:350px; height:350px; background:#ff9800; bottom:-90px; right:-100px; animation-delay:3s; }
        .b3 { width:240px; height:240px; background:#7b1fa2; top:40%; left:60%; animation-delay:6s; }
        @keyframes bfloat {
            0%,100% { transform:translateY(0) scale(1); }
            50%      { transform:translateY(-30px) scale(1.04); }
        }

        .wrap {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .card {
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 24px;
            padding: 48px 40px 38px;
            box-shadow: 0 25px 70px rgba(0,0,0,0.5);
        }

        /* Logo */
        .logo-area { text-align:center; margin-bottom:32px; }
        .logo-circle {
            width: 74px; height: 74px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff5722, #ff9800);
            display: inline-flex; align-items:center; justify-content:center;
            font-size: 2.1rem;
            box-shadow: 0 6px 25px rgba(255,87,34,0.4);
            margin-bottom: 14px;
        }
        .logo-title { color:#fff; font-size:1.75rem; font-weight:800; letter-spacing:-0.3px; }
        .logo-sub   { color:rgba(255,255,255,0.4); font-size:0.87rem; margin-top:5px; }

        /* Fields */
        .field { margin-bottom:18px; }
        .field label {
            display:block;
            font-size:0.76rem; font-weight:700;
            text-transform:uppercase; letter-spacing:0.6px;
            color:rgba(255,255,255,0.5);
            margin-bottom:7px;
        }
        .field-wrap { position:relative; }
        .inp {
            width:100%;
            padding: 13px 44px 13px 16px;
            background: rgba(255,255,255,0.07);
            border: 1.5px solid rgba(255,255,255,0.13);
            border-radius: 12px;
            color: #fff;
            font-size: 0.93rem;
            outline: none;
            transition: border 0.2s, background 0.2s;
        }
        .inp::placeholder { color:rgba(255,255,255,0.25); }
        .inp:focus {
            border-color: #ff5722;
            background: rgba(255,87,34,0.09);
        }
        .inp-icon {
            position:absolute; right:14px; top:50%;
            transform:translateY(-50%);
            color:rgba(255,255,255,0.3);
            font-size:1rem;
        }
        .eye-toggle { cursor:pointer; pointer-events:all !important; }
        .eye-toggle:hover { color:#ff9800; }

        /* Error */
        .err {
            background: rgba(244,67,54,0.12);
            border: 1px solid rgba(244,67,54,0.35);
            border-radius: 10px;
            padding: 11px 15px;
            color: #ff8a80;
            font-size: 0.86rem;
            margin-bottom: 20px;
            display: flex; align-items:center; gap:9px;
            animation: shake 0.35s ease;
        }
        @keyframes shake {
            0%,100% { transform:translateX(0); }
            25%,75%  { transform:translateX(-6px); }
            50%      { transform:translateX(6px); }
        }

        /* Button */
        .btn-go {
            width:100%; padding:14px;
            background: linear-gradient(90deg, #ff5722, #ff9800);
            border:none; border-radius:12px;
            color:#fff; font-size:1rem; font-weight:800;
            cursor:pointer; letter-spacing:0.3px;
            box-shadow: 0 4px 20px rgba(255,87,34,0.35);
            transition: opacity 0.2s, transform 0.15s;
            margin-top:6px;
        }
        .btn-go:hover { opacity:0.91; transform:translateY(-1px); }
        .btn-go:active { transform:translateY(0); }

    </style>
</head>
<body>
    <?php include('navbar.php'); ?>
    <div class="blob b1"></div>
    <div class="blob b2"></div>
    <div class="blob b3"></div>

    <div class="wrap">
        <div class="card">
            <div class="logo-area">
                <div class="logo-circle">🍔</div>
                <div class="logo-title">O.F.O.S</div>
                <div class="logo-sub">Online Food Ordering System</div>
            </div>

            <?php if($error): ?>
            <div class="err">
                <i class="fa fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="field">
                    <label>Username or Email</label>
                    <div class="field-wrap">
                        <input type="text" name="identifier" class="inp"
                               placeholder="Enter your username or email"
                               value="<?php echo isset($_POST['identifier']) ? htmlspecialchars($_POST['identifier']) : ''; ?>"
                               autocomplete="username" required>
                        <i class="fa fa-user inp-icon"></i>
                    </div>
                </div>

                <div class="field">
                    <label>Password</label>
                    <div class="field-wrap">
                        <input type="password" id="pw" name="password" class="inp"
                               placeholder="Your password"
                               autocomplete="current-password" required>
                        <i class="fa fa-eye inp-icon eye-toggle" onclick="togglePw()"></i>
                    </div>
                </div>

                <button type="submit" name="submit" class="btn-go">
                    <i class="fa fa-sign-in"></i> &nbsp;Login
                </button>
            </form>

            <div style="text-align:center;margin-top:22px;padding-top:18px;border-top:1px solid rgba(255,255,255,0.08);">
                <span style="color:rgba(255,255,255,0.4);font-size:0.88rem;">Don't have an account?</span>
                <a href="registration.php" style="color:#ff9800;font-weight:800;text-decoration:none;font-size:0.88rem;margin-left:6px;">Create Account</a>
            </div>

            <!-- Other login portals 
            <div style="margin-top:24px;padding-top:18px;border-top:1px solid rgba(255,255,255,0.08);">
                <p style="text-align:center;color:rgba(255,255,255,0.3);font-size:0.75rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;">Delivery Partner?</p>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <a href="delivery_register.php" style="flex:1;min-width:130px;display:flex;align-items:center;gap:10px;background:linear-gradient(135deg,#1565c0,#0097a7);color:#fff;text-decoration:none;border-radius:10px;padding:11px 14px;font-size:0.84rem;font-weight:700;">
                        <span style="font-size:1.3rem;">🛵</span>
                        <span>Become a<br><span style="font-size:0.75rem;opacity:0.85;">Delivery Partner</span></span>
                    </a>
                    <a href="delivery_login.php" style="flex:1;min-width:130px;display:flex;align-items:center;gap:10px;background:rgba(79,195,247,0.12);border:1px solid rgba(79,195,247,0.3);color:#4fc3f7;text-decoration:none;border-radius:10px;padding:11px 14px;font-size:0.84rem;font-weight:700;">
                        <span style="font-size:1.3rem;">🏍️</span>
                        <span>Already a partner?<br><span style="font-size:0.75rem;opacity:0.85;">Login here</span></span>
                    </a>
                </div>
            </div>
            -->
        </div>
    </div>

    <script>
    function togglePw() {
        var el = document.getElementById('pw');
        var ic = document.querySelector('.eye-toggle');
        if(el.type === 'password') {
            el.type = 'text';
            ic.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            el.type = 'password';
            ic.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
    </script>
</body>
</html>
