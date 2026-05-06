<?php
include("connection/connect.php");
error_reporting(0);
session_start();

if(!empty($_SESSION['delivery_id'])) { header("Location: delivery_dashboard.php"); exit(); }

// Ensure table exists
mysqli_query($db,"CREATE TABLE IF NOT EXISTS `delivery_partners` (
  `dp_id`      int(11) NOT NULL AUTO_INCREMENT,
  `name`       varchar(222) NOT NULL,
  `email`      varchar(222) NOT NULL,
  `phone`      varchar(20) NOT NULL,
  `password`   varchar(222) NOT NULL,
  `vehicle`    varchar(100) NOT NULL DEFAULT '',
  `vehicle_no` varchar(50)  NOT NULL DEFAULT '',
  `address`    text NOT NULL,
  `status`     tinyint(1) NOT NULL DEFAULT 1,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `date`       timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`dp_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1");

$error = '';
if(isset($_POST['submit'])) {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');
    if(empty($email) || empty($pass)) {
        $error = "Please fill in all fields.";
    } else {
        $se = mysqli_real_escape_string($db, $email);
        $hp = md5($pass);
        $r  = mysqli_fetch_assoc(mysqli_query($db,"SELECT * FROM delivery_partners WHERE email='$se' AND password='$hp' LIMIT 1"));
        if($r) {
            if(!$r['status']) { $error = "Your account is currently inactive. Contact support."; }
            else {
                $_SESSION['delivery_id']   = $r['dp_id'];
                $_SESSION['delivery_name'] = $r['name'];
                header("Location: delivery_dashboard.php"); exit();
            }
        } else {
            $error = "Invalid email or password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Delivery Login — O.F.O.S</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/font-awesome.min.css" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',system-ui,sans-serif;min-height:100vh;background:#0d1117;display:flex;flex-direction:column;}
.blob{position:fixed;border-radius:50%;filter:blur(90px);opacity:.15;pointer-events:none;}
.blob1{width:450px;height:450px;background:#1565c0;top:-100px;left:-80px;}
.blob2{width:320px;height:320px;background:#00bcd4;bottom:-60px;right:-60px;}
nav{background:rgba(0,0,0,.5);padding:13px 0;z-index:10;backdrop-filter:blur(10px);}
.nav-in{max-width:1100px;margin:0 auto;padding:0 20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;}
.brand{color:#4fc3f7;font-size:1.2rem;font-weight:800;text-decoration:none;}
.brand i{color:#ff9800;}
.nav-links a{color:rgba(255,255,255,.7);text-decoration:none;margin-left:16px;font-size:.87rem;}
.nav-links a:hover{color:#4fc3f7;}
main{flex:1;display:flex;align-items:center;justify-content:center;padding:40px 16px;position:relative;z-index:5;}
.card{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);border-radius:22px;padding:40px 36px 34px;width:100%;max-width:420px;color:#fff;box-shadow:0 25px 60px rgba(0,0,0,.5);}
.icon-ring{width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#1565c0,#0097a7);display:flex;align-items:center;justify-content:center;font-size:1.8rem;box-shadow:0 6px 20px rgba(21,101,192,.4);margin:0 auto 16px;}
h2{text-align:center;font-size:1.5rem;font-weight:800;color:#fff;margin-bottom:4px;}
.sub{text-align:center;color:rgba(255,255,255,.4);font-size:.83rem;margin-bottom:28px;}
.fgroup{margin-bottom:18px;}
label{display:block;color:rgba(255,255,255,.55);font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:7px;}
.inp{width:100%;background:rgba(255,255,255,.07);border:1.5px solid rgba(255,255,255,.1);border-radius:10px;padding:12px 14px;color:#fff;font-size:.95rem;outline:none;transition:border .2s;}
.inp:focus{border-color:#4fc3f7;background:rgba(79,195,247,.06);}
.err-box{background:rgba(244,67,54,.12);border:1px solid rgba(244,67,54,.3);border-radius:10px;padding:12px 16px;margin-bottom:18px;font-size:.85rem;color:#ef9a9a;display:flex;align-items:center;gap:8px;}
.btn-submit{width:100%;background:linear-gradient(135deg,#1565c0,#0097a7);color:#fff;border:none;border-radius:12px;padding:14px;font-size:1rem;font-weight:700;cursor:pointer;transition:opacity .2s,transform .2s;}
.btn-submit:hover{opacity:.9;transform:translateY(-1px);}
.divider{display:flex;align-items:center;gap:12px;margin:20px 0;color:rgba(255,255,255,.25);font-size:.8rem;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.1);}
.link-row{text-align:center;color:rgba(255,255,255,.4);font-size:.85rem;}
.link-row a{color:#4fc3f7;text-decoration:none;font-weight:600;}
</style>
</head>
<body>
<div class="blob blob1"></div>
<div class="blob blob2"></div>
<nav>
  <div class="nav-in">
    <a href="index.php" class="brand"><i class="fa fa-motorcycle"></i> O.F.O.S</a>
    <div class="nav-links">
      <a href="login.php"><i class="fa fa-user"></i> User Login</a>
      <a href="restaurant_login.php"><i class="fa fa-cutlery"></i> Restaurant</a>
      <a href="delivery_register.php"><i class="fa fa-user-plus"></i> Join as Partner</a>
    </div>
  </div>
</nav>
<main>
  <div class="card">
    <div class="icon-ring">🛵</div>
    <h2>Delivery Partner Login</h2>
    <p class="sub">Access your delivery dashboard</p>

    <?php if($error): ?>
    <div class="err-box"><i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="fgroup">
        <label>Email Address</label>
        <input type="email" name="email" class="inp" placeholder="you@example.com" value="<?php echo htmlspecialchars($_POST['email']??''); ?>" required>
      </div>
      <div class="fgroup">
        <label>Password</label>
        <input type="password" name="password" class="inp" placeholder="Your password" required>
      </div>
      <button type="submit" name="submit" class="btn-submit"><i class="fa fa-sign-in"></i> &nbsp;Login</button>
    </form>

    <div class="divider">or</div>
    <div class="link-row">New partner? <a href="delivery_register.php">Register here</a></div>
  </div>
</main>
</body>
</html>
