<!DOCTYPE html>
<html lang="en">
<?php
include("connection/connect.php");
error_reporting(0);
session_start();

if(!empty($_SESSION['restaurant_id'])) {
  header("Location: restaurant_dashboard.php");
  exit();
}

$error = '';

if(isset($_POST['submit'])) {
  $email    = trim($_POST['email']);
  $password = trim($_POST['password']);

  if(empty($email) || empty($password)) {
    $error = "Please fill in all fields.";
  } else {
    $hashed    = md5($password);
    $safe_email = mysqli_real_escape_string($db, $email);
    $q  = "SELECT * FROM restaurant WHERE email='$safe_email' AND password='$hashed' LIMIT 1";
    $res = mysqli_query($db, $q);
    if(mysqli_num_rows($res) == 1) {
      $row = mysqli_fetch_array($res);
      $_SESSION['restaurant_id']   = $row['rs_id'];
      $_SESSION['restaurant_name'] = $row['title'];
      $_SESSION['restaurant_img']  = $row['image'];
      header("Location: restaurant_dashboard.php");
      exit();
    } else {
      $error = "Invalid email or password. Please try again.";
    }
  }
}
?>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Restaurant Login — O.F.O.S</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/font-awesome.min.css" rel="stylesheet">
  <style>
    * { box-sizing:border-box; margin:0; padding:0; }
    body {
      font-family: 'Segoe UI', sans-serif;
      min-height: 100vh;
      display: flex;
    }

    /* Left panel — image side */
    .left-panel {
      flex: 1;
      background: url('https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=1200&q=80') no-repeat center center/cover;
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      padding: 40px;
    }
    .left-panel::before {
      content: "";
      position: absolute; inset: 0;
      background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.75) 100%);
    }
    .left-panel .panel-content { position: relative; z-index: 2; color: #fff; }
    .left-panel .panel-content h2 { font-size: 2rem; font-weight: 700; margin-bottom: 10px; }
    .left-panel .panel-content p  { font-size: 1rem; opacity: 0.8; line-height: 1.6; }
    .left-panel .features { margin-top: 24px; display: flex; flex-direction: column; gap: 10px; }
    .left-panel .feat-item { display: flex; align-items: center; gap: 12px; font-size: 0.9rem; opacity: 0.85; }
    .left-panel .feat-item i { background: rgba(255,87,34,0.8); border-radius: 50%; width:30px; height:30px; display:flex; align-items:center; justify-content:center; font-size:0.85rem; flex-shrink:0; }

    /* Right panel — form side */
    .right-panel {
      width: 420px; flex-shrink: 0;
      background: #fff;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 50px 44px;
    }
    .right-panel .logo-area { text-align: center; margin-bottom: 28px; }
    .right-panel .logo-area .icon-circle {
      width: 70px; height: 70px;
      background: linear-gradient(135deg, #ff5722, #ff9800);
      border-radius: 50%; display: inline-flex;
      align-items: center; justify-content: center;
      font-size: 1.9rem; color: #fff;
      box-shadow: 0 4px 15px rgba(255,87,34,0.35);
      margin-bottom: 14px;
    }
    .right-panel h2 { text-align:center; font-size: 1.5rem; font-weight: 700; color: #222; margin-bottom: 4px; }
    .right-panel .sub { text-align:center; color: #888; font-size: 0.88rem; margin-bottom: 28px; }
    .field-group { margin-bottom: 18px; }
    .field-group label { display: block; font-size: 0.82rem; font-weight: 600; color: #555; margin-bottom: 6px; }
    .field-input {
      width: 100%; padding: 12px 14px;
      border: 1.5px solid #e0e0e0; border-radius: 10px;
      font-size: 0.93rem; color: #333; outline: none;
      transition: border 0.2s, box-shadow 0.2s;
    }
    .field-input:focus { border-color: #ff5722; box-shadow: 0 0 0 3px rgba(255,87,34,0.1); }
    .pw-wrap { position: relative; }
    .pw-wrap .field-input { padding-right: 42px; }
    .pw-eye { position:absolute; right:13px; top:50%; transform:translateY(-50%); color:#bbb; cursor:pointer; font-size:1rem; }
    .pw-eye:hover { color:#ff5722; }
    .btn-login {
      width: 100%; padding: 13px;
      background: linear-gradient(90deg, #ff5722, #ff9800);
      border: none; border-radius: 10px;
      color: #fff; font-size: 1rem; font-weight: 700;
      cursor: pointer; transition: opacity 0.2s; margin-top: 4px;
    }
    .btn-login:hover { opacity: 0.9; }
    .err-box {
      background: #fff3f3; border: 1px solid #ffcccc; border-radius: 8px;
      padding: 11px 14px; color: #c62828; font-size: 0.87rem; margin-bottom: 18px;
    }
    .back-link { text-align: center; margin-top: 22px; font-size: 0.85rem; color: #aaa; }
    .back-link a { color: #ff5722; text-decoration: none; font-weight: 600; }
    .divider { border: none; border-top: 1px solid #f0f0f0; margin: 18px 0; }

    @media(max-width: 768px) {
      .left-panel { display: none; }
      .right-panel { width: 100%; padding: 40px 28px; }
    }
  </style>
</head>
<body>
  <!-- Left side image panel -->
  <div class="left-panel">
    <div class="panel-content">
      <h2>Manage Your Restaurant</h2>
      <p>Login to your dashboard and take full control of your orders, menu, and dishes — all in one place.</p>
      <div class="features">
        <div class="feat-item"><i class="fa fa-check"></i> Accept or reject orders in real time</div>
        <div class="feat-item"><i class="fa fa-cutlery"></i> Add, view and manage your menu dishes</div>
        <div class="feat-item"><i class="fa fa-bar-chart"></i> View order stats and revenue</div>
        <div class="feat-item"><i class="fa fa-bell"></i> Get notified of new pending orders</div>
      </div>
    </div>
  </div>

  <!-- Right side form panel -->
  <div class="right-panel">
    <div class="logo-area">
      <div class="icon-circle"><i class="fa fa-building"></i></div>
      <h2>Restaurant Login</h2>
      <p class="sub">Sign in with your business credentials</p>
    </div>

    <?php if($error): ?>
      <div class="err-box"><i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="field-group">
        <label><i class="fa fa-envelope-o" style="color:#ff5722;margin-right:5px;"></i> Business Email</label>
        <input type="email" name="email" class="field-input"
               placeholder="restaurant@business.com"
               value="<?php echo isset($_POST['email'])?htmlspecialchars($_POST['email']):''; ?>" required>
      </div>
      <div class="field-group">
        <label><i class="fa fa-lock" style="color:#ff5722;margin-right:5px;"></i> Password</label>
        <div class="pw-wrap">
          <input type="password" id="rpw" name="password" class="field-input" placeholder="Enter your password" required>
          <i class="fa fa-eye pw-eye" onclick="togglePw()"></i>
        </div>
      </div>
      <button type="submit" name="submit" class="btn-login">
        <i class="fa fa-sign-in"></i> &nbsp;Login to Dashboard
      </button>
    </form>

    <hr class="divider">
    <div class="back-link">
      <a href="index.php"><i class="fa fa-home"></i> Back to Home</a>
      &nbsp;&middot;&nbsp;
      <a href="login.php"><i class="fa fa-user"></i> Customer Login</a>
    </div>
    <p style="text-align:center;margin-top:14px;font-size:0.78rem;color:#ccc;">Your login credentials are set by the admin.<br>Contact admin if you forgot your password.</p>
  </div>

  <script>
    function togglePw() {
      var el = document.getElementById('rpw');
      var ic = document.querySelector('.pw-eye');
      if(el.type==='password'){ el.type='text'; ic.classList.replace('fa-eye','fa-eye-slash'); }
      else { el.type='password'; ic.classList.replace('fa-eye-slash','fa-eye'); }
    }
  </script>
</body>
</html>
