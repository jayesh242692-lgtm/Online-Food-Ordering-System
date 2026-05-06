<?php
include("connection/connect.php");
error_reporting(0);
session_start();

if(!empty($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$errors = [];
$success = '';

if(isset($_POST['submit'])) {
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $password  = $_POST['password'] ?? '';
    $cpassword = $_POST['cpassword'] ?? '';

    // --- Validation ---
    if(empty($username))                          $errors[] = "Username is required.";
    elseif(strlen($username) < 3)                 $errors[] = "Username must be at least 3 characters.";
    elseif(!preg_match('/^[a-zA-Z0-9_]+$/', $username)) $errors[] = "Username can only contain letters, numbers, underscores.";

    if(empty($email))                             $errors[] = "Email address is required.";
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email address.";

    if(empty($phone))                             $errors[] = "Phone number is required.";
    elseif(!preg_match('/^[0-9]{10}$/', $phone))    $errors[] = "Phone must be 10digits only.";

    if(empty($password))                          $errors[] = "Password is required.";
    elseif(strlen($password) < 6)                 $errors[] = "Password must be at least 6 characters.";
    elseif(!preg_match('/[A-Za-z]/', $password))  $errors[] = "Password must contain at least one letter.";
    elseif(!preg_match('/[0-9]/', $password))     $errors[] = "Password must contain at least one number.";

    if($password !== $cpassword)                  $errors[] = "Passwords do not match.";

    if(empty($errors)) {
        $safe_u = mysqli_real_escape_string($db, $username);
        $safe_e = mysqli_real_escape_string($db, $email);
        if(mysqli_num_rows(mysqli_query($db,"SELECT u_id FROM users WHERE username='$safe_u'")) > 0)
            $errors[] = "Username already taken. Please choose another.";
        elseif(mysqli_num_rows(mysqli_query($db,"SELECT u_id FROM users WHERE email='$safe_e'")) > 0)
            $errors[] = "This email is already registered.";
    }

    if(empty($errors)) {
        $safe_ph = mysqli_real_escape_string($db, $phone);
        $safe_ad = mysqli_real_escape_string($db, $address);
        $hpwd    = md5($password);
        mysqli_query($db,"INSERT INTO users(username,f_name,l_name,email,phone,password,address,status)
                          VALUES('$safe_u','$safe_u','','$safe_e','$safe_ph','$hpwd','$safe_ad',1)");
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register — O.F.O.S</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Segoe UI',system-ui,sans-serif;min-height:100vh;background:#0f0f1a;overflow-x:hidden;display:flex;flex-direction:column;}
    .blob{position:fixed;border-radius:50%;filter:blur(80px);opacity:.18;pointer-events:none;}
    .blob1{width:400px;height:400px;background:#ff5722;top:-80px;left:-100px;}
    .blob2{width:320px;height:320px;background:#ff9800;bottom:-60px;right:-80px;}
    nav{background:rgba(0,0,0,.45);padding:12px 0;position:relative;z-index:10;}
    .nav-in{max-width:1100px;margin:0 auto;padding:0 20px;display:flex;justify-content:space-between;align-items:center;}
    .brand{color:#ffcb05;font-size:1.2rem;font-weight:800;text-decoration:none;}
    .nav-links a{color:rgba(255,255,255,.7);text-decoration:none;margin-left:18px;font-size:.88rem;}
    .nav-links a:hover{color:#ff9800;}
    main{flex:1;display:flex;align-items:center;justify-content:center;padding:30px 16px;position:relative;z-index:5;}
    .card{background:rgba(255,255,255,.06);backdrop-filter:blur(18px);border:1px solid rgba(255,255,255,.11);border-radius:22px;padding:36px 36px 30px;width:100%;max-width:460px;color:#fff;box-shadow:0 20px 60px rgba(0,0,0,.45);}
    .icon-ring{width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,#ff5722,#ff9800);display:flex;align-items:center;justify-content:center;font-size:1.6rem;box-shadow:0 5px 20px rgba(255,87,34,.4);margin:0 auto 14px;}
    h2{text-align:center;font-size:1.4rem;font-weight:800;color:#ffcb05;margin-bottom:4px;}
    .sub{text-align:center;color:rgba(255,255,255,.35);font-size:.82rem;margin-bottom:22px;}

    /* Error list */
    .err-box{background:rgba(244,67,54,.12);border:1px solid rgba(244,67,54,.35);border-radius:10px;padding:12px 16px;margin-bottom:16px;}
    .err-box ul{margin:0;padding:0 0 0 16px;color:#ff8a80;font-size:.82rem;line-height:1.8;}
    .ok-box{background:rgba(76,175,80,.13);border:1px solid rgba(76,175,80,.35);border-radius:10px;padding:14px 18px;margin-bottom:16px;text-align:center;}
    .ok-box .ok-icon{font-size:2rem;display:block;margin-bottom:8px;}
    .ok-box p{color:#b9f6ca;font-size:.9rem;margin:0;}
    .ok-box a{color:#ffcb05;font-weight:800;text-decoration:none;}

    /* Fields */
    .lbl{display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:rgba(255,255,255,.45);margin-bottom:5px;}
    .lbl span{color:#ff5722;}
    .fwrap{position:relative;margin-bottom:13px;}
    .inp{width:100%;padding:11px 14px;background:rgba(255,255,255,.07);border:1.5px solid rgba(255,255,255,.11);border-radius:10px;color:#fff;font-size:.9rem;outline:none;transition:border .2s,background .2s;}
    .inp::placeholder{color:rgba(255,255,255,.2);}
    .inp:focus{border-color:#ff5722;background:rgba(255,87,34,.08);}
    .inp.err-field{border-color:#f44336 !important;}
    .inp.ok-field{border-color:#4caf50 !important;}
    textarea.inp{resize:vertical;min-height:70px;}
    .pw-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
    .eye{position:absolute;right:12px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.3);cursor:pointer;font-size:.95rem;}
    .eye:hover{color:#ff9800;}
    /* Password strength */
    .pw-strength{height:4px;border-radius:2px;margin-top:5px;transition:width .3s,background .3s;width:0;}
    .pw-hint{font-size:.7rem;color:rgba(255,255,255,.3);margin-top:3px;}

    .btn-reg{width:100%;padding:13px;background:linear-gradient(90deg,#ff5722,#ff9800);border:none;border-radius:11px;color:#fff;font-size:.97rem;font-weight:800;cursor:pointer;box-shadow:0 4px 18px rgba(255,87,34,.35);transition:opacity .2s,transform .15s;margin-top:6px;}
    .btn-reg:hover{opacity:.9;transform:translateY(-1px);}
    .login-link{text-align:center;margin-top:16px;font-size:.85rem;color:rgba(255,255,255,.35);}
    .login-link a{color:#ffcb05;font-weight:800;text-decoration:none;}
    footer{text-align:center;padding:12px;font-size:.73rem;color:rgba(255,255,255,.18);position:relative;z-index:5;}
    </style>
</head>
<body>
<div class="blob blob1"></div>
<div class="blob blob2"></div>
<nav>
    <div class="nav-in">
        <a class="brand" href="index.php">🍔 O.F.O.S</a>
        <div class="nav-links">
            <a href="index.php"><i class="fa fa-home"></i> Home</a>
            <a href="login.php"><i class="fa fa-sign-in"></i> Login</a>
        </div>
    </div>
</nav>
<main>
<div class="card">
    <div class="icon-ring"><i class="fa fa-user-plus"></i></div>
    <h2>Create Account</h2>
    <p class="sub">Join O.F.O.S and order from the best restaurants</p>

    <?php if($success): ?>
    <div class="ok-box">
        <span class="ok-icon">🎉</span>
        <p><strong style="color:#fff;">Account created successfully!</strong></p>
        <p style="margin-top:8px;">Welcome to O.F.O.S! <a href="login.php">Login now →</a></p>
    </div>
    <?php else: ?>

    <?php if(!empty($errors)): ?>
    <div class="err-box">
        <ul>
            <?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="POST" id="regForm" novalidate>
        <label class="lbl">Username <span>*</span></label>
        <div class="fwrap">
            <input type="text" name="username" id="username" class="inp <?php echo isset($_POST['submit']) && empty($_POST['username']) ? 'err-field' : ''; ?>"
                   placeholder="e.g. ravi_shah" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" autocomplete="off" required>
        </div>
        <small id="un-hint" style="font-size:.7rem;color:rgba(255,255,255,.3);display:block;margin-top:-10px;margin-bottom:10px;">3+ chars, letters/numbers/underscore only</small>

        <label class="lbl">Email Address <span>*</span></label>
        <div class="fwrap">
            <input type="email" name="email" id="email" class="inp"
                   placeholder="you@email.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        </div>

        <label class="lbl">Phone Number <span>*</span></label>
        <div class="fwrap">
            <input type="tel" name="phone" id="phone" class="inp"
                   placeholder="9876543210 (10digits)" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
        </div>

        <label class="lbl">Delivery Address</label>
        <div class="fwrap">
            <textarea name="address" class="inp" placeholder="Street, City, State..."><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
        </div>

        <div class="pw-row">
            <div>
                <label class="lbl">Password <span>*</span></label>
                <div class="fwrap">
                    <input type="password" id="pw1" name="password" class="inp" placeholder="Min 6 chars" required oninput="checkStrength(this.value)">
                    <i class="fa fa-eye eye" onclick="togglePw('pw1',this)"></i>
                </div>
                <div style="background:rgba(255,255,255,.1);border-radius:2px;height:4px;margin-top:-10px;margin-bottom:4px;">
                    <div id="pwBar" class="pw-strength"></div>
                </div>
                <div id="pwHint" class="pw-hint">Must have letter + number</div>
            </div>
            <div>
                <label class="lbl">Confirm <span>*</span></label>
                <div class="fwrap">
                    <input type="password" id="pw2" name="cpassword" class="inp" placeholder="Re-enter" required oninput="checkMatch()">
                    <i class="fa fa-eye eye" onclick="togglePw('pw2',this)"></i>
                </div>
                <div id="matchHint" class="pw-hint"></div>
            </div>
        </div>

        <button type="submit" name="submit" class="btn-reg" id="submitBtn">
            <i class="fa fa-user-plus"></i> &nbsp;Create Account
        </button>
    </form>
    <?php endif; ?>

    <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
    <p style="text-align:center;margin-top:10px;font-size:0.85rem;">
        Want to deliver food? <a href="delivery_register.php" style="color:#4fc3f7;font-weight:700;text-decoration:none;"><i class="fa fa-motorcycle"></i> Join as Delivery Partner</a>
    </p>
</div>
</main>
<footer>O.F.O.S &copy; <?php echo date('Y'); ?> — Fresh food at your doorstep 🍕</footer>
<script>
function togglePw(id,ic){
    var el=document.getElementById(id);
    el.type=el.type==='password'?'text':'password';
    ic.classList.toggle('fa-eye'); ic.classList.toggle('fa-eye-slash');
}
function checkStrength(v){
    var bar=document.getElementById('pwBar'), hint=document.getElementById('pwHint');
    var score=0;
    if(v.length>=6) score++;
    if(v.length>=10) score++;
    if(/[A-Za-z]/.test(v)&&/[0-9]/.test(v)) score++;
    if(/[^A-Za-z0-9]/.test(v)) score++;
    var colors=['#f44336','#ff9800','#ffeb3b','#4caf50'];
    var labels=['Too short','Weak','Good','Strong'];
    var w=['25%','50%','75%','100%'];
    if(v.length===0){bar.style.width='0';hint.textContent='Must have letter + number';return;}
    var i=Math.min(score-1,3);
    bar.style.width=w[i]; bar.style.background=colors[i];
    hint.style.color=colors[i]; hint.textContent=labels[i];
}
function checkMatch(){
    var p1=document.getElementById('pw1').value;
    var p2=document.getElementById('pw2').value;
    var hint=document.getElementById('matchHint');
    if(p2.length===0){hint.textContent='';return;}
    if(p1===p2){hint.textContent='✓ Passwords match';hint.style.color='#4caf50';}
    else{hint.textContent='✗ Does not match';hint.style.color='#f44336';}
}
// Live field validation
document.getElementById('username').addEventListener('blur',function(){
    var v=this.value.trim();
    if(v.length<3||!/^[a-zA-Z0-9_]+$/.test(v))
        this.classList.add('err-field'), this.classList.remove('ok-field');
    else this.classList.add('ok-field'), this.classList.remove('err-field');
});
document.getElementById('email').addEventListener('blur',function(){
    var re=/^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if(!re.test(this.value)) this.classList.add('err-field'), this.classList.remove('ok-field');
    else this.classList.add('ok-field'), this.classList.remove('err-field');
});
document.getElementById('phone').addEventListener('blur',function(){
    if(!/^[0-9]{10,15}$/.test(this.value)) this.classList.add('err-field'), this.classList.remove('ok-field');
    else this.classList.add('ok-field'), this.classList.remove('err-field');
});
</script>
</body>
</html>
