<?php
include("connection/connect.php");
error_reporting(0);
session_start();

if(empty($_SESSION['delivery_id'])) { header("Location: delivery_login.php"); exit(); }

$dp_id   = intval($_SESSION['delivery_id']);
$dp_name = htmlspecialchars($_SESSION['delivery_name']);

// Ensure delivery_partner_id column in users_orders
mysqli_query($db,"ALTER TABLE users_orders ADD COLUMN IF NOT EXISTS `dp_id` int(11) DEFAULT NULL");
mysqli_query($db,"ALTER TABLE users_orders ADD COLUMN IF NOT EXISTS `delivery_name` varchar(222) NOT NULL DEFAULT ''");

// Toggle availability
if(isset($_POST['toggle_availability'])) {
    $cur = mysqli_fetch_assoc(mysqli_query($db,"SELECT is_available FROM delivery_partners WHERE dp_id=$dp_id"));
    $nv  = $cur['is_available'] ? 0 : 1;
    mysqli_query($db,"UPDATE delivery_partners SET is_available=$nv WHERE dp_id=$dp_id");
    header("Location: delivery_dashboard.php"); exit();
}

// Mark order picked up (on the way)
if(isset($_POST['pickup_order'])) {
    $oid = intval($_POST['o_id']);
    mysqli_query($db,"UPDATE users_orders SET status='on the way' WHERE o_id=$oid AND dp_id=$dp_id");
    mysqli_query($db,"INSERT INTO remark(frm_id,status,remark) VALUES($oid,'on the way','Order picked up by delivery partner')");
    header("Location: delivery_dashboard.php?msg=picked"); exit();
}

// Mark order delivered
if(isset($_POST['deliver_order'])) {
    $oid = intval($_POST['o_id']);
    mysqli_query($db,"UPDATE users_orders SET status='closed' WHERE o_id=$oid AND dp_id=$dp_id");
    mysqli_query($db,"INSERT INTO remark(frm_id,status,remark) VALUES($oid,'closed','Order delivered successfully')");
    header("Location: delivery_dashboard.php?msg=delivered"); exit();
}

// Fetch partner info
$dp = mysqli_fetch_assoc(mysqli_query($db,"SELECT * FROM delivery_partners WHERE dp_id=$dp_id"));

// Fetch orders assigned to this delivery partner
// Orders are assigned where dp_id = this partner, OR delivery_name matches partner name (backward compat)
$q_assigned = mysqli_query($db,"SELECT uo.*, r.title as restaurant_name, u.username, u.phone as cust_phone, u.address as cust_address
    FROM users_orders uo
    LEFT JOIN restaurant r ON uo.rs_id = r.rs_id
    LEFT JOIN users u ON uo.u_id = u.u_id
    WHERE uo.dp_id=$dp_id
    ORDER BY uo.date DESC");
$assigned_orders = [];
while($r = mysqli_fetch_assoc($q_assigned)) $assigned_orders[] = $r;

// Also fetch orders ready for pickup (accepted by restaurant, no delivery partner yet)
$q_ready = mysqli_query($db,"SELECT uo.*, r.title as restaurant_name, u.username, u.phone as cust_phone, u.address as cust_address
    FROM users_orders uo
    LEFT JOIN restaurant r ON uo.rs_id = r.rs_id
    LEFT JOIN users u ON uo.u_id = u.u_id
    WHERE uo.status='confirmed' AND (uo.dp_id IS NULL OR uo.dp_id=0)
    ORDER BY uo.date ASC");
$ready_orders = [];
while($r = mysqli_fetch_assoc($q_ready)) $ready_orders[] = $r;

// Accept a new delivery (assign self)
if(isset($_POST['accept_delivery'])) {
    $oid  = intval($_POST['o_id']);
    $safe_name = mysqli_real_escape_string($db, $dp['name']);
    // Check not already taken
    $chk = mysqli_fetch_assoc(mysqli_query($db,"SELECT dp_id FROM users_orders WHERE o_id=$oid"));
    if(!$chk['dp_id']) {
        mysqli_query($db,"UPDATE users_orders SET dp_id=$dp_id, delivery_name='$safe_name' WHERE o_id=$oid");
        mysqli_query($db,"INSERT INTO remark(frm_id,status,remark) VALUES($oid,'in process','Delivery partner assigned: $safe_name')");
    }
    header("Location: delivery_dashboard.php?msg=accepted"); exit();
}

// Stats
$total   = count($assigned_orders);
$active  = count(array_filter($assigned_orders, fn($o)=>in_array($o['status'],['in process','on the way'])));
$done    = count(array_filter($assigned_orders, fn($o)=>$o['status']=='closed'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Delivery Dashboard — O.F.O.S</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/font-awesome.min.css" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',system-ui,sans-serif;background:#f0f2f5;min-height:100vh;color:#1a1a2e;}

/* NAV */
.topnav{background:linear-gradient(135deg,#1565c0,#0097a7);padding:0;box-shadow:0 2px 12px rgba(0,0,0,.2);}
.topnav-in{max-width:1200px;margin:0 auto;padding:0 20px;display:flex;justify-content:space-between;align-items:center;height:62px;}
.brand{color:#fff;font-size:1.15rem;font-weight:800;text-decoration:none;display:flex;align-items:center;gap:8px;}
.nav-right{display:flex;align-items:center;gap:16px;}
.nav-right a{color:rgba(255,255,255,.8);text-decoration:none;font-size:.87rem;}
.nav-right a:hover{color:#fff;}
.avail-badge{display:inline-flex;align-items:center;gap:6px;border-radius:20px;padding:5px 14px;font-size:.8rem;font-weight:700;}
.badge-online{background:rgba(76,175,80,.25);color:#a5d6a7;border:1px solid rgba(76,175,80,.4);}
.badge-offline{background:rgba(244,67,54,.2);color:#ef9a9a;border:1px solid rgba(244,67,54,.3);}

/* HERO */
.hero{background:linear-gradient(135deg,#1565c0 0%,#0097a7 100%);padding:50px 0 40px;position:relative;overflow:hidden;}
.hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");}
.hero .container{position:relative;z-index:2;}
.hero-top{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;}
.hero-title{color:#fff;font-size:1.8rem;font-weight:800;margin-bottom:4px;}
.hero-sub{color:rgba(255,255,255,.7);font-size:.9rem;}
.avail-form button{border:none;border-radius:20px;padding:9px 20px;font-size:.85rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:8px;}
.btn-go-online{background:#4caf50;color:#fff;}
.btn-go-offline{background:#f44336;color:#fff;}

.stats-row{display:flex;gap:16px;margin-top:28px;flex-wrap:wrap;}
.stat-card{background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.2);border-radius:14px;padding:18px 22px;min-width:130px;flex:1;}
.stat-label{color:rgba(255,255,255,.7);font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;}
.stat-val{color:#fff;font-size:1.8rem;font-weight:800;}
.stat-icon{font-size:1.2rem;float:right;margin-top:-4px;}

/* MAIN */
.main-wrap{max-width:1100px;margin:-20px auto 60px;padding:0 16px;}

.toast-msg{border-radius:12px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;gap:12px;font-size:.88rem;font-weight:600;}
.toast-success{background:#e8f5e9;border:1px solid #a5d6a7;color:#2e7d32;}
.toast-info{background:#e3f2fd;border:1px solid #90caf9;color:#1565c0;}

.section-title{font-size:1rem;font-weight:800;color:#1a1a2e;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
.section-title .badge-count{background:#ff5722;color:#fff;border-radius:12px;padding:2px 10px;font-size:.75rem;}

.order-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;margin-bottom:36px;}

/* Order card */
.ocard{background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.07);border:1px solid #f0f0f0;overflow:hidden;transition:box-shadow .2s,transform .2s;}
.ocard:hover{box-shadow:0 6px 28px rgba(0,0,0,.12);transform:translateY(-2px);}
.ocard-strip{height:4px;}
.strip-inprocess{background:linear-gradient(90deg,#2196f3,#64b5f6);}
.strip-onway{background:linear-gradient(90deg,#ff5722,#ff9800);}
.strip-done{background:linear-gradient(90deg,#4caf50,#81c784);}
.strip-available{background:linear-gradient(90deg,#9c27b0,#e040fb);}

.ocard-head{padding:14px 18px 12px;border-bottom:1px solid #f5f5f5;display:flex;justify-content:space-between;align-items:center;}
.order-id{font-weight:800;color:#1a1a2e;font-size:.95rem;}
.order-time{font-size:.75rem;color:#bbb;margin-top:2px;}
.status-pill{display:inline-flex;align-items:center;gap:5px;border-radius:16px;padding:4px 12px;font-size:.75rem;font-weight:700;}
.pill-blue{background:#e3f2fd;color:#1565c0;}
.pill-orange{background:#fff3e0;color:#e65100;}
.pill-green{background:#e8f5e9;color:#2e7d32;}
.pill-purple{background:#f3e5f5;color:#7b1fa2;}
.pill-dot{width:6px;height:6px;border-radius:50%;}

.ocard-body{padding:14px 18px;}
.dish-row{display:flex;align-items:center;gap:12px;margin-bottom:12px;}
.dish-img{width:48px;height:48px;border-radius:10px;object-fit:cover;background:#f5f5f5;flex-shrink:0;}
.dish-emoji{width:48px;height:48px;border-radius:10px;background:linear-gradient(135deg,#fff3e0,#ffe0cc);display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;}
.dish-title{font-weight:700;color:#222;font-size:.9rem;}
.dish-qty{font-size:.78rem;color:#999;margin-top:2px;}
.dish-price{margin-left:auto;font-size:1rem;font-weight:800;color:#ff5722;}

.info-row{display:flex;flex-direction:column;gap:6px;font-size:.82rem;color:#666;margin-bottom:14px;}
.info-row span{display:flex;align-items:flex-start;gap:7px;}
.info-row i{color:#1565c0;margin-top:1px;width:14px;}

.action-row{display:flex;gap:8px;flex-wrap:wrap;}
.btn-action{border:none;border-radius:8px;padding:8px 16px;font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;transition:opacity .15s,transform .15s;}
.btn-action:hover{opacity:.88;transform:translateY(-1px);}
.btn-pickup{background:#ff9800;color:#fff;}
.btn-deliver{background:#4caf50;color:#fff;}
.btn-accept{background:linear-gradient(135deg,#1565c0,#0097a7);color:#fff;}

/* Empty state */
.empty-state{background:#fff;border-radius:16px;padding:50px 20px;text-align:center;box-shadow:0 2px 12px rgba(0,0,0,.05);}
.empty-icon{font-size:2.5rem;margin-bottom:12px;}
.empty-title{font-weight:700;color:#333;font-size:1rem;margin-bottom:6px;}
.empty-sub{color:#aaa;font-size:.85rem;}

/* Partner info card */
.partner-card{background:#fff;border-radius:16px;padding:20px 22px;box-shadow:0 2px 12px rgba(0,0,0,.06);margin-bottom:24px;display:flex;align-items:center;gap:20px;flex-wrap:wrap;}
.partner-avatar{width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#1565c0,#0097a7);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.5rem;font-weight:800;flex-shrink:0;}
.partner-name{font-size:1.05rem;font-weight:800;color:#1a1a2e;}
.partner-meta{font-size:.82rem;color:#888;margin-top:2px;}
.partner-tags{display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;}
.ptag{background:#f0f0f0;border-radius:8px;padding:4px 10px;font-size:.78rem;color:#555;display:flex;align-items:center;gap:5px;}

@media(max-width:600px){
  .hero-title{font-size:1.4rem;}
  .stat-val{font-size:1.4rem;}
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="topnav">
  <div class="topnav-in">
    <a href="delivery_dashboard.php" class="brand"><i class="fa fa-motorcycle"></i> &nbsp;Delivery Partner</a>
    <div class="nav-right">
      <span class="avail-badge <?php echo $dp['is_available'] ? 'badge-online' : 'badge-offline'; ?>">
        <i class="fa fa-circle" style="font-size:.5rem;"></i>
        <?php echo $dp['is_available'] ? 'Online' : 'Offline'; ?>
      </span>
      <a href="delivery_logout.php"><i class="fa fa-sign-out"></i> Logout</a>
    </div>
  </div>
</nav>

<!-- HERO -->
<div class="hero">
  <div class="container">
    <div class="hero-top">
      <div>
        <h1 class="hero-title">Welcome, <?php echo $dp_name; ?> 👋</h1>
        <p class="hero-sub">
          <?php if($dp['is_available']): ?>
          You're <strong style="color:#a5d6a7;">online</strong> and visible to restaurants. You can accept new deliveries.
          <?php else: ?>
          You're <strong style="color:#ef9a9a;">offline</strong>. Go online to start accepting delivery requests.
          <?php endif; ?>
        </p>
      </div>
      <form method="POST" class="avail-form">
        <input type="hidden" name="toggle_availability" value="1">
        <?php if($dp['is_available']): ?>
        <button type="submit" class="btn-go-offline"><i class="fa fa-stop-circle"></i> Go Offline</button>
        <?php else: ?>
        <button type="submit" class="btn-go-online"><i class="fa fa-play-circle"></i> Go Online</button>
        <?php endif; ?>
      </form>
    </div>

    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-label">Total Deliveries <span class="stat-icon">📦</span></div>
        <div class="stat-val"><?php echo $total; ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Active Now <span class="stat-icon">🔥</span></div>
        <div class="stat-val"><?php echo $active; ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Delivered <span class="stat-icon">✅</span></div>
        <div class="stat-val"><?php echo $done; ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Available Pickups <span class="stat-icon">🛵</span></div>
        <div class="stat-val"><?php echo count($ready_orders); ?></div>
      </div>
    </div>
  </div>
</div>

<!-- MAIN -->
<div class="main-wrap">

  <?php if(isset($_GET['msg'])): ?>
  <?php
    $msgs = ['accepted'=>'✅ Delivery accepted! Check your active orders below.',
             'picked'  =>'🛵 Order marked as picked up. On the way!',
             'delivered'=>'🎉 Order marked as delivered successfully!'];
    $m = $msgs[$_GET['msg']] ?? '';
  ?>
  <?php if($m): ?>
  <div class="toast-msg toast-success"><i class="fa fa-check-circle"></i> <?php echo $m; ?></div>
  <?php endif; ?>
  <?php endif; ?>

  <!-- Partner Info -->
  <div class="partner-card">
    <div class="partner-avatar"><?php echo strtoupper(substr($dp['name'],0,1)); ?></div>
    <div>
      <div class="partner-name"><?php echo htmlspecialchars($dp['name']); ?></div>
      <div class="partner-meta"><?php echo htmlspecialchars($dp['email']); ?> &nbsp;·&nbsp; <?php echo htmlspecialchars($dp['phone']); ?></div>
      <div class="partner-tags">
        <span class="ptag"><i class="fa fa-motorcycle" style="color:#1565c0;"></i> <?php echo htmlspecialchars($dp['vehicle']); ?></span>
        <span class="ptag"><i class="fa fa-id-card" style="color:#0097a7;"></i> <?php echo htmlspecialchars($dp['vehicle_no']); ?></span>
        <span class="ptag"><i class="fa fa-map-marker" style="color:#ff5722;"></i> <?php echo htmlspecialchars(substr($dp['address'],0,40)); ?></span>
      </div>
    </div>
  </div>

  <!-- ===== ACTIVE / MY ORDERS ===== -->
  <div class="section-title">
    <i class="fa fa-motorcycle" style="color:#1565c0;"></i>
    My Active Deliveries
    <span class="badge-count"><?php echo count(array_filter($assigned_orders, fn($o)=>in_array($o['status'],['in process','on the way']))); ?></span>
  </div>

  <?php
  $active_orders = array_filter($assigned_orders, fn($o)=>in_array($o['status'],['in process','on the way']));
  if(empty($active_orders)):
  ?>
  <div class="empty-state" style="margin-bottom:28px;">
    <div class="empty-icon">📭</div>
    <div class="empty-title">No active deliveries</div>
    <p class="empty-sub">Accept a delivery request below to get started.</p>
  </div>
  <?php else: ?>
  <div class="order-grid">
    <?php foreach($active_orders as $o):
      $strip = $o['status']=='on the way' ? 'strip-onway' : 'strip-inprocess';
      $pill  = $o['status']=='on the way' ? 'pill-orange' : 'pill-blue';
      $ptxt  = $o['status']=='on the way' ? '🛵 On the Way' : '🔥 Preparing';
      $tl = strtolower($o['title']);
      if(strpos($tl,'pizza')!==false) $emoji='🍕';
      elseif(strpos($tl,'burger')!==false) $emoji='🍔';
      elseif(strpos($tl,'rice')!==false||strpos($tl,'biryani')!==false) $emoji='🍛';
      elseif(strpos($tl,'noodle')!==false||strpos($tl,'pasta')!==false) $emoji='🍝';
      elseif(strpos($tl,'coffee')!==false||strpos($tl,'tea')!==false) $emoji='☕';
      else $emoji='🍽️';
    ?>
    <div class="ocard">
      <div class="ocard-strip <?php echo $strip; ?>"></div>
      <div class="ocard-head">
        <div>
          <div class="order-id">Order #<?php echo $o['o_id']; ?></div>
          <div class="order-time"><i class="fa fa-clock-o"></i> <?php echo date('d M, h:i A', strtotime($o['date'])); ?></div>
        </div>
        <span class="status-pill <?php echo $pill; ?>">
          <span class="pill-dot" style="background:currentColor;"></span>
          <?php echo $ptxt; ?>
        </span>
      </div>
      <div class="ocard-body">
        <div class="dish-row">
          <?php if(!empty($o['dish_img'])): ?>
          <img src="admin/Res_img/dishes/<?php echo htmlspecialchars($o['dish_img']); ?>" class="dish-img" onerror="this.style.display='none'">
          <?php else: ?>
          <div class="dish-emoji"><?php echo $emoji; ?></div>
          <?php endif; ?>
          <div>
            <div class="dish-title"><?php echo htmlspecialchars($o['title']); ?></div>
            <div class="dish-qty">Qty: <?php echo $o['quantity']; ?></div>
          </div>
          <div class="dish-price">₹<?php echo number_format($o['price'],2); ?></div>
        </div>

        <div class="info-row">
          <span><i class="fa fa-cutlery"></i> <?php echo htmlspecialchars($o['restaurant_name'] ?? 'Restaurant'); ?></span>
          <span><i class="fa fa-user"></i> <?php echo htmlspecialchars($o['username'] ?? 'Customer'); ?></span>
          <?php if(!empty($o['cust_phone'])): ?>
          <span><i class="fa fa-phone"></i> <?php echo htmlspecialchars($o['cust_phone']); ?></span>
          <?php endif; ?>
          <?php if(!empty($o['cust_address'])): ?>
          <span><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars(substr($o['cust_address'],0,80)); ?></span>
          <?php endif; ?>
        </div>

        <div class="action-row">
          <?php if($o['status']=='in process'): ?>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="o_id" value="<?php echo $o['o_id']; ?>">
            <button type="submit" name="pickup_order" class="btn-action btn-pickup">
              <i class="fa fa-motorcycle"></i> Picked Up — On the Way
            </button>
          </form>
          <?php elseif($o['status']=='on the way'): ?>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="o_id" value="<?php echo $o['o_id']; ?>">
            <button type="submit" name="deliver_order" class="btn-action btn-deliver" onclick="return confirm('Confirm delivery for Order #<?php echo $o['o_id']; ?>?')">
              <i class="fa fa-check-circle"></i> Mark as Delivered
            </button>
          </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- ===== AVAILABLE ORDERS TO PICK UP ===== -->
  <?php if($dp['is_available']): ?>
  <div class="section-title">
    <i class="fa fa-list" style="color:#9c27b0;"></i>
    Available Orders
    <span class="badge-count" style="background:#9c27b0;"><?php echo count($ready_orders); ?></span>
  </div>
  <p style="font-size:.85rem;color:#888;margin-bottom:16px;margin-top:-8px;">Orders accepted by restaurants and ready for pickup — click Accept to assign yourself.</p>

  <?php if(empty($ready_orders)): ?>
  <div class="empty-state" style="margin-bottom:28px;">
    <div class="empty-icon">🔍</div>
    <div class="empty-title">No orders available right now</div>
    <p class="empty-sub">Check back soon — new orders come in frequently.</p>
  </div>
  <?php else: ?>
  <div class="order-grid">
    <?php foreach($ready_orders as $o):
      $tl = strtolower($o['title']);
      if(strpos($tl,'pizza')!==false) $emoji='🍕';
      elseif(strpos($tl,'burger')!==false) $emoji='🍔';
      elseif(strpos($tl,'rice')!==false||strpos($tl,'biryani')!==false) $emoji='🍛';
      elseif(strpos($tl,'noodle')!==false||strpos($tl,'pasta')!==false) $emoji='🍝';
      else $emoji='🍽️';
    ?>
    <div class="ocard">
      <div class="ocard-strip strip-available"></div>
      <div class="ocard-head">
        <div>
          <div class="order-id">Order #<?php echo $o['o_id']; ?></div>
          <div class="order-time"><i class="fa fa-clock-o"></i> <?php echo date('d M, h:i A', strtotime($o['date'])); ?></div>
        </div>
        <span class="status-pill pill-purple"><span class="pill-dot" style="background:#7b1fa2;"></span>Ready for Pickup</span>
      </div>
      <div class="ocard-body">
        <div class="dish-row">
          <?php if(!empty($o['dish_img'])): ?>
          <img src="admin/Res_img/dishes/<?php echo htmlspecialchars($o['dish_img']); ?>" class="dish-img" onerror="this.style.display='none'">
          <?php else: ?>
          <div class="dish-emoji"><?php echo $emoji; ?></div>
          <?php endif; ?>
          <div>
            <div class="dish-title"><?php echo htmlspecialchars($o['title']); ?></div>
            <div class="dish-qty">Qty: <?php echo $o['quantity']; ?></div>
          </div>
          <div class="dish-price">₹<?php echo number_format($o['price'],2); ?></div>
        </div>
        <div class="info-row">
          <span><i class="fa fa-cutlery"></i> <?php echo htmlspecialchars($o['restaurant_name'] ?? 'Restaurant'); ?></span>
          <?php if(!empty($o['cust_address'])): ?>
          <span><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars(substr($o['cust_address'],0,80)); ?></span>
          <?php endif; ?>
        </div>
        <div class="action-row">
          <form method="POST">
            <input type="hidden" name="o_id" value="<?php echo $o['o_id']; ?>">
            <button type="submit" name="accept_delivery" class="btn-action btn-accept">
              <i class="fa fa-check"></i> Accept Delivery
            </button>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>

  <!-- ===== COMPLETED ORDERS ===== -->
  <?php $completed = array_filter($assigned_orders, fn($o)=>$o['status']=='closed'); ?>
  <?php if(!empty($completed)): ?>
  <div class="section-title" style="margin-top:8px;">
    <i class="fa fa-check-circle" style="color:#4caf50;"></i>
    Completed Deliveries
    <span class="badge-count" style="background:#4caf50;"><?php echo count($completed); ?></span>
  </div>
  <div class="order-grid">
    <?php foreach($completed as $o): ?>
    <div class="ocard">
      <div class="ocard-strip strip-done"></div>
      <div class="ocard-head">
        <div>
          <div class="order-id">Order #<?php echo $o['o_id']; ?></div>
          <div class="order-time"><i class="fa fa-clock-o"></i> <?php echo date('d M, h:i A', strtotime($o['date'])); ?></div>
        </div>
        <span class="status-pill pill-green"><span class="pill-dot" style="background:#4caf50;"></span>Delivered ✓</span>
      </div>
      <div class="ocard-body">
        <div class="dish-row">
          <div>
            <div class="dish-title"><?php echo htmlspecialchars($o['title']); ?></div>
            <div class="dish-qty">Qty: <?php echo $o['quantity']; ?> &nbsp;·&nbsp; <?php echo htmlspecialchars($o['restaurant_name']??''); ?></div>
          </div>
          <div class="dish-price">₹<?php echo number_format($o['price'],2); ?></div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div><!-- /main-wrap -->

<script src="js/jquery.min.js"></script>
<script>
// Auto-refresh every 30 seconds for new orders
setTimeout(function(){ location.reload(); }, 30000);
</script>
</body>
</html>
