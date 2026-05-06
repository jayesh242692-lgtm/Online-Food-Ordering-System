<?php
include("connection/connect.php");
error_reporting(0);
session_start();

if(!empty($_SESSION['delivery_id'])) { header("Location: delivery_dashboard.php"); exit(); }

// Auto-create delivery_partners table
mysqli_query($db,"CREATE TABLE IF NOT EXISTS `delivery_partners` (
  `dp_id`     int(11) NOT NULL AUTO_INCREMENT,
  `name`      varchar(222) NOT NULL,
  `email`     varchar(222) NOT NULL,
  `phone`     varchar(20) NOT NULL,
  `password`  varchar(222) NOT NULL,
  `vehicle`   varchar(100) NOT NULL DEFAULT '',
  `vehicle_no` varchar(50) NOT NULL DEFAULT '',
  `address`   text NOT NULL,
  `status`    tinyint(1) NOT NULL DEFAULT 1,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `date`      timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`dp_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1");

$errors = [];
$success = false;

if(isset($_POST['submit'])) {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $vehicle  = trim($_POST['vehicle'] ?? '');
    $veh_no   = strtoupper(trim($_POST['vehicle_no'] ?? ''));
    $address  = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $cpass    = $_POST['cpassword'] ?? '';

    if(empty($name))   $errors[] = "Full name is required.";
    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if(empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) $errors[] = "Valid 10-digit phone is required.";
    if(empty($vehicle)) $errors[] = "Vehicle type is required.";
    if(empty($veh_no))  $errors[] = "Vehicle number is required.";
    if(strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if($password !== $cpass)  $errors[] = "Passwords do not match.";

    if(empty($errors)) {
        $se = mysqli_real_escape_string($db, $email);
        if(mysqli_num_rows(mysqli_query($db,"SELECT dp_id FROM delivery_partners WHERE email='$se'")) > 0) {
            $errors[] = "This email is already registered.";
        } else {
            $sn  = mysqli_real_escape_string($db, $name);
            $sp  = mysqli_real_escape_string($db, $phone);
            $sv  = mysqli_real_escape_string($db, $vehicle);
            $svn = mysqli_real_escape_string($db, $veh_no);
            $sa  = mysqli_real_escape_string($db, $address);
            $hp  = md5($password);
            mysqli_query($db,"INSERT INTO delivery_partners(name,email,phone,password,vehicle,vehicle_no,address)
                               VALUES('$sn','$se','$sp','$hp','$sv','$svn','$sa')");
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Delivery Partner Registration — O.F.O.S</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/font-awesome.min.css" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',system-ui,sans-serif;min-height:100vh;background:#0d1117;overflow-x:hidden;display:flex;flex-direction:column;}
.blob{position:fixed;border-radius:50%;filter:blur(90px);opacity:.15;pointer-events:none;}
.blob1{width:500px;height:500px;background:#1565c0;top:-100px;left:-100px;}
.blob2{width:350px;height:350px;background:#00bcd4;bottom:-80px;right:-80px;}
.blob3{width:280px;height:280px;background:#4caf50;top:40%;left:40%;transform:translate(-50%,-50%);}

nav{background:rgba(0,0,0,.5);padding:13px 0;position:relative;z-index:10;backdrop-filter:blur(10px);}
.nav-in{max-width:1100px;margin:0 auto;padding:0 20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;}
.brand{color:#4fc3f7;font-size:1.2rem;font-weight:800;text-decoration:none;display:flex;align-items:center;gap:8px;}
.brand i{color:#ff9800;}
.nav-links a{color:rgba(255,255,255,.7);text-decoration:none;margin-left:16px;font-size:.87rem;}
.nav-links a:hover{color:#4fc3f7;}

main{flex:1;display:flex;align-items:flex-start;justify-content:center;padding:40px 16px 60px;position:relative;z-index:5;}

.hero-col{display:none;}
@media(min-width:900px){
  main{align-items:center;gap:60px;}
  .hero-col{display:flex;flex-direction:column;gap:24px;max-width:420px;}
}

/* Hero content */
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(79,195,247,.15);border:1px solid rgba(79,195,247,.3);border-radius:20px;padding:6px 14px;color:#4fc3f7;font-size:.82rem;font-weight:600;margin-bottom:10px;}
.hero-title{color:#fff;font-size:2.4rem;font-weight:800;line-height:1.2;margin-bottom:14px;}
.hero-title span{color:#4fc3f7;}
.hero-desc{color:rgba(255,255,255,.55);font-size:.95rem;line-height:1.7;}
.perks{display:flex;flex-direction:column;gap:14px;margin-top:24px;}
.perk{display:flex;align-items:flex-start;gap:14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:14px 16px;}
.perk-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;}
.pi-blue{background:rgba(79,195,247,.15);}
.pi-green{background:rgba(76,175,80,.15);}
.pi-orange{background:rgba(255,152,0,.15);}
.perk-title{color:#fff;font-weight:700;font-size:.9rem;}
.perk-desc{color:rgba(255,255,255,.45);font-size:.8rem;margin-top:2px;}

/* Card */
.card{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);border-radius:22px;padding:36px 32px 30px;width:100%;max-width:500px;color:#fff;box-shadow:0 25px 60px rgba(0,0,0,.5);}
.card-head{text-align:center;margin-bottom:28px;}
.icon-ring{width:62px;height:62px;border-radius:50%;background:linear-gradient(135deg,#1565c0,#0097a7);display:flex;align-items:center;justify-content:center;font-size:1.7rem;box-shadow:0 6px 20px rgba(21,101,192,.4);margin:0 auto 14px;}
h2{font-size:1.5rem;font-weight:800;color:#fff;margin-bottom:4px;}
.sub{color:rgba(255,255,255,.4);font-size:.83rem;}

/* Form */
.row2{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
@media(max-width:480px){.row2{grid-template-columns:1fr;}}
.fgroup{margin-bottom:18px;}
label{display:block;color:rgba(255,255,255,.6);font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:7px;}
.inp{width:100%;background:rgba(255,255,255,.07);border:1.5px solid rgba(255,255,255,.1);border-radius:10px;padding:11px 14px;color:#fff;font-size:.92rem;outline:none;transition:border .2s;}
.inp:focus{border-color:#4fc3f7;background:rgba(79,195,247,.06);}
.inp option{background:#1a2332;color:#fff;}
select.inp{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%234fc3f7' d='M6 8L1 3h10z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;}

.btn-submit{width:100%;background:linear-gradient(135deg,#1565c0,#0097a7);color:#fff;border:none;border-radius:12px;padding:14px;font-size:1rem;font-weight:700;cursor:pointer;transition:opacity .2s,transform .2s;margin-top:6px;}
.btn-submit:hover{opacity:.9;transform:translateY(-1px);}

.err-box{background:rgba(244,67,54,.12);border:1px solid rgba(244,67,54,.3);border-radius:10px;padding:12px 16px;margin-bottom:18px;font-size:.85rem;color:#ef9a9a;}
.err-box li{margin-left:16px;}

.success-box{background:rgba(76,175,80,.12);border:1px solid rgba(76,175,80,.3);border-radius:14px;padding:28px;text-align:center;}
.success-icon{font-size:3rem;margin-bottom:12px;}
.success-title{font-size:1.3rem;font-weight:800;color:#81c784;margin-bottom:6px;}
.success-sub{color:rgba(255,255,255,.5);font-size:.88rem;margin-bottom:20px;}
.btn-login{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#1565c0,#0097a7);color:#fff;text-decoration:none;border-radius:10px;padding:11px 24px;font-weight:700;font-size:.9rem;}

.divider{display:flex;align-items:center;gap:12px;margin:20px 0;color:rgba(255,255,255,.25);font-size:.8rem;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.1);}
.link-row{text-align:center;color:rgba(255,255,255,.4);font-size:.85rem;}
.link-row a{color:#4fc3f7;text-decoration:none;font-weight:600;}
</style>
</head>
<body>
<div class="blob blob1"></div>
<div class="blob blob2"></div>
<div class="blob blob3"></div>

<nav>
  <div class="nav-in">
    <a href="index.php" class="brand"><i class="fa fa-motorcycle"></i> O.F.O.S</a>
    <div class="nav-links">
      <a href="login.php"><i class="fa fa-user"></i> User Login</a>
      <a href="restaurant_login.php"><i class="fa fa-cutlery"></i> Restaurant</a>
      <a href="delivery_login.php"><i class="fa fa-motorcycle"></i> Delivery Login</a>
    </div>
  </div>
</nav>

<main>
  <!-- Hero col -->
  <div class="hero-col">
    <div>
      <div class="hero-badge"><i class="fa fa-motorcycle"></i> Delivery Partner Program</div>
      <h1 class="hero-title">Earn on your<br><span>own schedule</span></h1>
      <p class="hero-desc">Join our growing fleet of delivery partners. Set your own hours, pick your own rides, and earn competitive pay with every delivery.</p>
    </div>
    <div class="perks">
      <div class="perk">
        <div class="perk-icon pi-blue"><i class="fa fa-money" style="color:#4fc3f7;"></i></div>
        <div>
          <div class="perk-title">Earn up to ₹1000/day</div>
          <div class="perk-desc">Get paid per delivery + weekly bonuses for high performers.</div>
        </div>
      </div>
      <div class="perk">
        <div class="perk-icon pi-green"><i class="fa fa-clock-o" style="color:#81c784;"></i></div>
        <div>
          <div class="perk-title">Flexible Hours</div>
          <div class="perk-desc">Work whenever you want. Go online and offline any time.</div>
        </div>
      </div>
      <div class="perk">
        <div class="perk-icon pi-orange"><i class="fa fa-map-marker" style="color:#ffb74d;"></i></div>
        <div>
          <div class="perk-title">Choose your zone</div>
          <div class="perk-desc">Deliver within your preferred area and skip long-distance trips.</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Registration Card -->
  <div class="card">
    <?php if($success): ?>
    <div class="success-box">
      <div class="success-icon">🎉</div>
      <div class="success-title">You're registered!</div>
      <p class="success-sub">Welcome to the O.F.O.S delivery team. You can now log in and start accepting orders.</p>
      <a href="delivery_login.php" class="btn-login"><i class="fa fa-sign-in"></i> Go to Login</a>
    </div>
    <?php else: ?>
    <div class="card-head">
      <div class="icon-ring">🛵</div>
      <h2>Become a Delivery Partner</h2>
      <p class="sub">Join thousands of partners delivering smiles every day</p>
    </div>

    <?php if(!empty($errors)): ?>
    <div class="err-box">
      <ul><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <form method="POST">
      <div class="fgroup">
        <label>Full Name</label>
        <input type="text" name="name" class="inp" placeholder="Your full name" value="<?php echo htmlspecialchars($_POST['name']??''); ?>" required>
      </div>

      <div class="row2">
        <div class="fgroup">
          <label>Email</label>
          <input type="email" name="email" class="inp" placeholder="email@example.com" value="<?php echo htmlspecialchars($_POST['email']??''); ?>" required>
        </div>
        <div class="fgroup">
          <label>Phone</label>
          <input type="tel" name="phone" class="inp" placeholder="10-digit number" value="<?php echo htmlspecialchars($_POST['phone']??''); ?>" required>
        </div>
      </div>

      <div class="row2">
        <div class="fgroup">
          <label>Vehicle Type</label>
          <select name="vehicle" class="inp" required>
            <option value="">-- Select --</option>
            <option value="Bicycle" <?php echo (($_POST['vehicle']??'')==='Bicycle')?'selected':''; ?>>🚲 Bicycle</option>
            <option value="Motorcycle" <?php echo (($_POST['vehicle']??'')==='Motorcycle')?'selected':''; ?>>🏍️ Motorcycle</option>
            <option value="Scooter" <?php echo (($_POST['vehicle']??'')==='Scooter')?'selected':''; ?>>🛵 Scooter</option>
            <option value="Car" <?php echo (($_POST['vehicle']??'')==='Car')?'selected':''; ?>>🚗 Car</option>
            <option value="E-Bike" <?php echo (($_POST['vehicle']??'')==='E-Bike')?'selected':''; ?>>⚡ E-Bike</option>
          </select>
        </div>
        <div class="fgroup">
          <label>Vehicle Number</label>
          <input type="text" name="vehicle_no" class="inp" placeholder="GJ01AB1234" value="<?php echo htmlspecialchars($_POST['vehicle_no']??''); ?>" required>
        </div>
      </div>

      <div class="fgroup">
        <label>Address / Area</label>
        <input type="text" name="address" class="inp" placeholder="Your area / city" value="<?php echo htmlspecialchars($_POST['address']??''); ?>">
      </div>

      <div class="row2">
        <div class="fgroup">
          <label>Password</label>
          <input type="password" name="password" class="inp" placeholder="Min 6 characters" required>
        </div>
        <div class="fgroup">
          <label>Confirm Password</label>
          <input type="password" name="cpassword" class="inp" placeholder="Re-enter password" required>
        </div>
      </div>

      <button type="submit" name="submit" class="btn-submit">
        <i class="fa fa-user-plus"></i> &nbsp;Register as Delivery Partner
      </button>
    </form>

    <div class="divider">or</div>
    <div class="link-row">Already a partner? <a href="delivery_login.php">Log in here</a></div>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
