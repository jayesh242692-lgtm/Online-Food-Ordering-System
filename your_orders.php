<?php
// ================================================================
//  your_orders.php  — FIXED: one order_id per checkout
//  Place at: project/your_orders.php  (replace the old file)
// ================================================================
include("connection/connect.php");
session_start();
error_reporting(0);

if (empty($_SESSION['user_id'])) {
    header('location:login.php');
    exit();
}

// ── AJAX: star rating ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rate_order'])) {
    $order_id = intval($_POST['order_id']);
    $rating   = intval($_POST['rating']);
    $u_id     = intval($_SESSION['user_id']);
    if ($rating >= 1 && $rating <= 5) {
        mysqli_query($db,
            "UPDATE orders SET rating='$rating'
             WHERE order_id='$order_id' AND u_id='$u_id' AND status='closed'"
        );
        echo json_encode(['success' => mysqli_affected_rows($db) > 0]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

// ================================================================
//  POST from checkout.php  —  THE CORE FIX
//
//  OLD (broken): foreach cart item → INSERT INTO users_orders
//                Result: 3 items = 3 separate o_id rows
//
//  NEW (fixed):  1. INSERT one row into `orders`  → get shared order_id
//                2. foreach cart item → INSERT INTO `order_items`
//                   using that SAME order_id every time
//                Result: 3 items = 1 order_id in orders,
//                        3 rows in order_items all with same order_id
// ================================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_SESSION['cart'])) {

    $u_id            = intval($_SESSION['user_id']);
    $delivery_name   = mysqli_real_escape_string($db, trim($_POST['name']           ?? ''));
    $delivery_email  = mysqli_real_escape_string($db, trim($_POST['email']          ?? ''));
    $delivery_phone  = mysqli_real_escape_string($db, trim($_POST['phone']          ?? ''));
    $delivery_addr   = mysqli_real_escape_string($db, trim($_POST['address']        ?? ''));
    $payment_method  = mysqli_real_escape_string($db, trim($_POST['payment_method'] ?? 'cod'));
    $total_price     = floatval($_POST['total_price']     ?? 0);
    $coupon_code     = mysqli_real_escape_string($db, trim($_POST['coupon_code']    ?? ''));
    $discount_amount = floatval($_POST['discount_amount'] ?? 0);

    // STEP 1: Insert ONE master row — generates the shared order_id
    mysqli_query($db,
        "INSERT INTO orders
           (u_id, delivery_name, delivery_email, delivery_phone,
            delivery_address, payment_method, total_price,
            coupon_code, discount_amount, status, order_date)
         VALUES
           ('$u_id','$delivery_name','$delivery_email','$delivery_phone',
            '$delivery_addr','$payment_method','$total_price',
            '$coupon_code','$discount_amount','pending',NOW())"
    );
    $shared_order_id = mysqli_insert_id($db); // same ID used for ALL items below

    // STEP 2: Insert one row per cart item, ALL using $shared_order_id
    foreach ($_SESSION['cart'] as $dish_id => $qty) {
        $dish_id = intval($dish_id);
        $qty     = intval($qty);
        if ($qty < 1) continue;

        $dq = mysqli_query($db,
            "SELECT d.*, r.discount_pct AS res_disc
               FROM dishes d
               LEFT JOIN restaurant r ON d.rs_id = r.rs_id
              WHERE d.d_id = '$dish_id'"
        );
        $dish = mysqli_fetch_assoc($dq);
        if (!$dish) continue;

        $dish_disc  = floatval($dish['discount_pct'] ?? 0);
        $res_disc   = floatval($dish['res_disc']     ?? 0);
        $disc       = ($dish_disc > 0) ? $dish_disc : $res_disc;
        $unit_price = ($disc > 0) ? round($dish['price'] * (1 - $disc / 100), 2) : floatval($dish['price']);
        $subtotal   = round($unit_price * $qty, 2);

        $title  = mysqli_real_escape_string($db, $dish['title']);
        $img    = mysqli_real_escape_string($db, $dish['img']);
        $rs_id  = intval($dish['rs_id']);

        mysqli_query($db,
            "INSERT INTO order_items
               (order_id, u_id, rs_id, dish_id, title, dish_img, quantity, unit_price, subtotal)
             VALUES
               ('$shared_order_id','$u_id','$rs_id','$dish_id',
                '$title','$img','$qty','$unit_price','$subtotal')"
        );
    }

    unset($_SESSION['cart']);
    header("Location: your_orders.php?success=1");
    exit();
}

// ── Fetch user info ──────────────────────────────────────────────
$uid      = intval($_SESSION['user_id']);
$urow     = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM users WHERE u_id=$uid"));
$username = htmlspecialchars($urow['f_name'] ?? $_SESSION['username'] ?? 'User');

// ── Fetch all grouped orders for this user, newest first ─────────
$oq     = mysqli_query($db, "SELECT * FROM orders WHERE u_id='$uid' ORDER BY order_date DESC");
$orders = [];
while ($o = mysqli_fetch_assoc($oq)) {
    // Fetch all items belonging to this order (JOIN query)
    $iq    = mysqli_query($db,
        "SELECT oi.*, r.title AS restaurant_name
           FROM order_items oi
           LEFT JOIN restaurant r ON oi.rs_id = r.rs_id
          WHERE oi.order_id = '{$o['order_id']}'
          ORDER BY oi.item_id ASC"
    );
    $items = [];
    while ($item = mysqli_fetch_assoc($iq)) $items[] = $item;
    $o['items'] = $items;
    $orders[]   = $o;
}
$total_orders = count($orders);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Orders — O.F.O.S</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', system-ui, sans-serif; margin: 0; color: #1a1a2e; }

        .orders-hero { background: linear-gradient(135deg,#ff5722 0%,#ff9800 100%); padding: 90px 0 60px; position: relative; overflow: hidden; }
        .orders-hero::before { content:''; position:absolute; inset:0; opacity:.06;
            background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill='%23fff' d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/svg%3E"); }
        .orders-hero .container { position:relative; z-index:2; }
        .hero-greeting { color:rgba(255,255,255,.85); font-size:.95rem; margin-bottom:4px; }
        .hero-title    { color:#fff; font-size:2rem; font-weight:800; margin-bottom:6px; }
        .hero-sub      { color:rgba(255,255,255,.75); font-size:.92rem; }
        .stats-row     { display:flex; gap:12px; margin-top:24px; flex-wrap:wrap; }
        .stat-pill     { background:rgba(255,255,255,.2); border:1px solid rgba(255,255,255,.3); border-radius:30px; padding:6px 16px; color:#fff; font-size:.82rem; display:flex; align-items:center; gap:7px; }

        .orders-wrap { max-width:870px; margin:-30px auto 60px; padding:0 16px; }

        .notif-success { background:#fff; border-radius:14px; padding:16px 20px; margin-bottom:20px;
            display:flex; align-items:center; gap:14px;
            box-shadow:0 4px 20px rgba(76,175,80,.18); border-left:4px solid #4caf50;
            animation:slideDown .4s ease; }
        .notif-success .nicon  { width:42px; height:42px; border-radius:50%; background:#e8f5e9; display:flex; align-items:center; justify-content:center; color:#4caf50; font-size:1.2rem; flex-shrink:0; }
        .notif-success .ntitle { font-weight:700; color:#2e7d32; font-size:.95rem; }
        .notif-success .nsub   { font-size:.82rem; color:#888; }
        @keyframes slideDown { from{opacity:0;transform:translateY(-14px)} to{opacity:1;transform:none} }

        .order-card { background:#fff; border-radius:16px; box-shadow:0 2px 18px rgba(0,0,0,.07);
            margin-bottom:26px; overflow:hidden; border:1px solid #f0f0f0; transition:box-shadow .2s,transform .2s; }
        .order-card:hover { box-shadow:0 6px 30px rgba(0,0,0,.11); transform:translateY(-2px); }

        .card-strip      { height:5px; }
        .strip-pending   { background:linear-gradient(90deg,#ff9800,#ffb74d); }
        .strip-preparing { background:linear-gradient(90deg,#2196f3,#64b5f6); }
        .strip-onway     { background:linear-gradient(90deg,#ff5722,#ff8a65); }
        .strip-delivered { background:linear-gradient(90deg,#4caf50,#81c784); }
        .strip-rejected  { background:linear-gradient(90deg,#f44336,#e57373); }

        .card-head { padding:16px 22px 14px; display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px; border-bottom:1px solid #f5f5f5; }
        .order-num  { font-weight:800; color:#1a1a2e; font-size:1.05rem; }
        .order-ts   { font-size:.78rem; color:#bbb; margin-top:3px; display:flex; align-items:center; gap:5px; }
        .order-meta { margin-top:6px; display:flex; gap:16px; flex-wrap:wrap; font-size:.78rem; color:#999; }
        .order-meta span { display:flex; align-items:center; gap:4px; }

        .status-pill { display:inline-flex; align-items:center; gap:6px; border-radius:20px; padding:5px 14px; font-size:.8rem; font-weight:700; white-space:nowrap; }
        .pill-dot    { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
        .sp-pending  { background:#fff8e1; color:#f57f17; } .sp-pending  .pill-dot { background:#f57f17; animation:pulse 1.2s infinite; }
        .sp-preparing{ background:#e3f2fd; color:#1565c0; } .sp-preparing .pill-dot { background:#1565c0; animation:pulse 1.2s infinite; }
        .sp-onway    { background:#fff3e0; color:#e65100; } .sp-onway    .pill-dot { background:#e65100; animation:pulse 1.2s infinite; }
        .sp-delivered{ background:#e8f5e9; color:#2e7d32; } .sp-delivered .pill-dot { background:#4caf50; }
        .sp-rejected { background:#ffebee; color:#c62828; } .sp-rejected  .pill-dot { background:#f44336; }
        @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(.8)} }

        .items-section { padding:4px 22px 8px; }
        .section-label { font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:1px; color:#bbb; margin:14px 0 10px; display:flex; align-items:center; gap:8px; }
        .section-label::after { content:''; flex:1; height:1px; background:#f0f0f0; }

        .items-table    { width:100%; border-collapse:collapse; font-size:.88rem; }
        .items-table th { color:#ff5722; font-weight:700; font-size:.72rem; text-transform:uppercase; letter-spacing:.5px; padding:7px 10px; border-bottom:2px solid #f5f5f5; text-align:left; }
        .items-table td { padding:11px 10px; border-bottom:1px solid #fafafa; vertical-align:middle; }
        .items-table tbody tr:last-child td { border-bottom:none; }
        .items-table tbody tr:hover td { background:#fdf9f7; }
        .dish-thumb  { width:42px; height:42px; border-radius:8px; object-fit:cover; display:block; }
        .dish-emoji  { width:42px; height:42px; border-radius:8px; background:#fff3e0; display:flex; align-items:center; justify-content:center; font-size:1.3rem; }
        .dish-name   { font-weight:700; color:#222; }
        .dish-rest   { font-size:.72rem; color:#ff9800; margin-top:2px; }
        .qty-badge   { background:#f5f5f5; border-radius:6px; padding:3px 9px; font-weight:700; font-size:.82rem; color:#555; display:inline-block; }
        .sub-cell    { font-weight:800; color:#ff5722; text-align:right; }

        .total-bar    { padding:13px 22px; background:linear-gradient(90deg,#fff9f6,#fff4ed); border-top:1px solid #f0e0d8; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; }
        .total-left   { font-size:.85rem; color:#888; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
        .total-amount { font-size:1.15rem; font-weight:800; color:#ff5722; }
        .coupon-saved { font-size:.75rem; font-weight:700; color:#4caf50; background:#e8f5e9; border-radius:12px; padding:3px 10px; }
        .pay-badge    { background:#e8f5e9; color:#2e7d32; border-radius:20px; padding:5px 14px; font-size:.75rem; font-weight:800; display:flex; align-items:center; gap:5px; }

        .tracking-section { padding:20px 22px 22px; }
        .timeline-h { display:flex; align-items:flex-start; justify-content:space-between; position:relative; }
        .tl-bg-line { position:absolute; top:22px; left:22px; right:22px; height:3px; background:#f0f0f0; border-radius:3px; z-index:0; }
        .tl-fill    { position:absolute; top:22px; left:22px; height:3px; border-radius:3px; z-index:1; width:0%; transition:width 1s ease; }
        .fill-orange{ background:linear-gradient(90deg,#ff5722,#ff9800); }
        .fill-green { background:linear-gradient(90deg,#4caf50,#81c784); }
        .fill-red   { background:linear-gradient(90deg,#f44336,#e57373); }
        .tl-step { display:flex; flex-direction:column; align-items:center; position:relative; z-index:2; flex:1; }
        .tl-node { width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1rem; border:3px solid #e8e8e8; background:#fff; color:#d0d0d0; transition:all .3s; }
        .tl-node.s-done   { background:#4caf50; border-color:#4caf50; color:#fff; box-shadow:0 3px 10px rgba(76,175,80,.35); }
        .tl-node.s-active { background:#fff; border-color:#ff5722; color:#ff5722; box-shadow:0 0 0 6px rgba(255,87,34,.1); }
        .tl-node.s-active::after { content:''; position:absolute; inset:-7px; border-radius:50%; border:2px solid rgba(255,87,34,.25); animation:ripple 1.5s infinite; }
        .tl-node.s-reject { background:#f44336; border-color:#f44336; color:#fff; }
        @keyframes ripple { 0%{transform:scale(.85);opacity:1} 100%{transform:scale(1.3);opacity:0} }
        .tl-text  { margin-top:10px; text-align:center; }
        .tl-name  { font-size:.7rem; font-weight:700; color:#bbb; white-space:nowrap; }
        .tl-name.t-done   { color:#4caf50; }
        .tl-name.t-active { color:#ff5722; }
        .tl-name.t-reject { color:#f44336; }
        .tl-time  { font-size:.63rem; color:#ccc; margin-top:2px; }
        .eta-bar     { margin-top:16px; background:linear-gradient(90deg,#fff8f5,#fff3e0); border:1px solid #ffe0cc; border-radius:10px; padding:11px 16px; display:flex; align-items:center; gap:12px; font-size:.83rem; }
        .reject-note { margin-top:16px; background:#fff5f5; border:1px solid #ffcdd2; border-radius:10px; padding:12px 16px; font-size:.83rem; color:#c62828; display:flex; align-items:flex-start; gap:10px; }

        .rating-section { padding:16px 22px 20px; border-top:1px solid #f5f5f5; background:linear-gradient(135deg,#fffdf5,#fff8f0); }
        .star-row { display:flex; gap:6px; align-items:center; }
        .star-btn { font-size:1.7rem; background:none; border:none; cursor:pointer; color:#e0e0e0; transition:transform .15s,color .15s; padding:2px; line-height:1; }
        .star-btn:hover,.star-btn.hovered { color:#ff9800; transform:scale(1.25); }
        .star-btn.active { color:#ff9800; }

        .empty-wrap { background:#fff; border-radius:16px; padding:60px 30px; text-align:center; box-shadow:0 2px 18px rgba(0,0,0,.07); }
        .empty-icon { width:90px; height:90px; border-radius:50%; background:linear-gradient(135deg,#fff3e0,#ffe0cc); display:inline-flex; align-items:center; justify-content:center; font-size:2.5rem; margin-bottom:20px; }
        .btn-browse { display:inline-flex; align-items:center; gap:8px; background:linear-gradient(90deg,#ff5722,#ff9800); color:#fff; text-decoration:none; border-radius:25px; padding:12px 28px; font-weight:700; font-size:.9rem; box-shadow:0 4px 15px rgba(255,87,34,.3); }
        .btn-browse:hover { opacity:.9; color:#fff; text-decoration:none; }

        @media(max-width:600px){
            .orders-hero { padding:80px 0 50px; }
            .hero-title  { font-size:1.5rem; }
            .tl-node     { width:36px; height:36px; font-size:.85rem; }
            .tl-bg-line,.tl-fill { top:18px; left:18px; right:18px; }
            .tl-name     { font-size:.62rem; }
            .items-table th,.items-table td { padding:8px 4px; font-size:.8rem; }
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="orders-hero">
    <div class="container">
        <p class="hero-greeting">Hello, <?php echo $username; ?> 👋</p>
        <h1 class="hero-title">My Orders</h1>
        <p class="hero-sub">All items from one checkout share a single Order ID — just like Amazon or Flipkart.</p>
        <div class="stats-row">
            <?php
            $cnt_all  = count($orders);
            $cnt_live = count(array_filter($orders, fn($o) => in_array($o['status'], ['pending','confirmed','in process','on the way'])));
            $cnt_done = count(array_filter($orders, fn($o) => $o['status'] === 'closed'));
            $cnt_rej  = count(array_filter($orders, fn($o) => $o['status'] === 'rejected'));
            ?>
            <div class="stat-pill"><i class="fa fa-shopping-bag"></i> <?php echo $cnt_all; ?> Total</div>
            <?php if ($cnt_live > 0): ?>
            <div class="stat-pill"><i class="fa fa-circle" style="color:#ffeb3b;font-size:.6rem;"></i> <?php echo $cnt_live; ?> Active</div>
            <?php endif; ?>
            <div class="stat-pill"><i class="fa fa-check-circle"></i> <?php echo $cnt_done; ?> Delivered</div>
            <?php if ($cnt_rej > 0): ?>
            <div class="stat-pill"><i class="fa fa-times-circle"></i> <?php echo $cnt_rej; ?> Cancelled</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="orders-wrap">

    <?php if (isset($_GET['success'])): ?>
    <div class="notif-success">
        <div class="nicon"><i class="fa fa-check"></i></div>
        <div>
            <div class="ntitle">Order Placed! 🎉 All items grouped under one Order ID.</div>
            <div class="nsub">The restaurant has been notified and will start preparing shortly.</div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($total_orders === 0): ?>
    <div class="empty-wrap">
        <div class="empty-icon">🛍️</div>
        <h3 style="color:#333;font-weight:700;margin-bottom:8px;">No Orders Yet</h3>
        <p style="color:#aaa;margin-bottom:24px;">Explore restaurants and find something delicious!</p>
        <a href="restaurants.php" class="btn-browse"><i class="fa fa-search"></i> Browse Restaurants</a>
    </div>

    <?php else:
        foreach ($orders as $order):
            $status   = $order['status'] ?? 'pending';
            $order_id = $order['order_id'];
            $items    = $order['items'];

            if ($status === 'rejected') {
                $steps=[['fa-check','Placed','s-done','t-done','Done'],['fa-times','Cancelled','s-reject','t-reject',''],['fa-fire','Preparing','','',''],['fa-motorcycle','On the Way','','',''],['fa-home','Delivered','','','']];
                $fill_pct='22%';$fill_cls='fill-red';$strip='strip-rejected';
                $sp_cls='sp-rejected';$sp_txt='Cancelled';$sp_icon='fa-times-circle';
            } elseif ($status === 'closed') {
                $steps=[['fa-check','Placed','s-done','t-done','Done'],['fa-thumbs-up','Confirmed','s-done','t-done','Done'],['fa-fire','Preparing','s-done','t-done','Done'],['fa-motorcycle','On the Way','s-done','t-done','Done'],['fa-home','Delivered','s-done','t-done','Done']];
                $fill_pct='100%';$fill_cls='fill-green';$strip='strip-delivered';
                $sp_cls='sp-delivered';$sp_txt='Delivered';$sp_icon='fa-check-circle';
            } elseif ($status === 'confirmed') {
                $steps=[['fa-check','Placed','s-done','t-done','Done'],['fa-thumbs-up','Confirmed','s-active','t-active','Now ✓'],['fa-fire','Preparing','','',''],['fa-motorcycle','On the Way','','',''],['fa-home','Delivered','','','']];
                $fill_pct='33%';$fill_cls='fill-orange';$strip='strip-preparing';
                $sp_cls='sp-preparing';$sp_txt='Confirmed ✅';$sp_icon='fa-thumbs-up';
            } elseif ($status === 'on the way') {
                $steps=[['fa-check','Placed','s-done','t-done','Done'],['fa-thumbs-up','Confirmed','s-done','t-done','Done'],['fa-fire','Preparing','s-done','t-done','Done'],['fa-motorcycle','On the Way','s-active','t-active','Now'],['fa-home','Delivered','','','']];
                $fill_pct='77%';$fill_cls='fill-orange';$strip='strip-onway';
                $sp_cls='sp-onway';$sp_txt='On the Way 🛵';$sp_icon='fa-motorcycle';
            } elseif ($status === 'in process') {
                $steps=[['fa-check','Placed','s-done','t-done','Done'],['fa-thumbs-up','Confirmed','s-done','t-done','Done'],['fa-fire','Preparing','s-active','t-active','Now 🔥'],['fa-motorcycle','On the Way','','',''],['fa-home','Delivered','','','']];
                $fill_pct='55%';$fill_cls='fill-orange';$strip='strip-preparing';
                $sp_cls='sp-preparing';$sp_txt='Preparing 🔥';$sp_icon='fa-fire';
            } else {
                $steps=[['fa-check','Placed','s-active','t-active','Just now'],['fa-thumbs-up','Confirmed','','','Waiting...'],['fa-fire','Preparing','','',''],['fa-motorcycle','On the Way','','',''],['fa-home','Delivered','','','']];
                $fill_pct='0%';$fill_cls='fill-orange';$strip='strip-pending';
                $sp_cls='sp-pending';$sp_txt='Awaiting Confirmation';$sp_icon='fa-clock-o';
            }
    ?>

    <div class="order-card">
        <div class="card-strip <?php echo $strip; ?>"></div>

        <div class="card-head">
            <div>
                <div class="order-num">
                    <i class="fa fa-shopping-bag" style="color:#ff5722;margin-right:6px;"></i>
                    Order #<?php echo $order_id; ?>
                    <span style="font-size:.75rem;color:#bbb;font-weight:400;margin-left:6px;">
                        (<?php echo count($items); ?> item<?php echo count($items)!==1?'s':''; ?>)
                    </span>
                </div>
                <div class="order-ts">
                    <i class="fa fa-calendar-o"></i>
                    <?php echo date('d M Y', strtotime($order['order_date'])); ?>
                    &nbsp;·&nbsp;
                    <i class="fa fa-clock-o"></i>
                    <?php echo date('h:i A', strtotime($order['order_date'])); ?>
                </div>
                <div class="order-meta">
                    <span><i class="fa fa-user-o"></i> <?php echo htmlspecialchars($order['delivery_name']); ?></span>
                    <span><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($order['delivery_address']); ?></span>
                </div>
            </div>
            <span class="status-pill <?php echo $sp_cls; ?>">
                <span class="pill-dot"></span>
                <i class="fa <?php echo $sp_icon; ?>"></i>
                <?php echo $sp_txt; ?>
            </span>
        </div>

        <!-- ALL items under this ONE order_id -->
        <div class="items-section">
            <div class="section-label">
                <i class="fa fa-cutlery" style="color:#ff5722;"></i>
                Items in Order #<?php echo $order_id; ?>
            </div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th colspan="2">Dish</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th style="text-align:right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item):
                    $tl = strtolower($item['title']);
                    if      (str_contains($tl,'pizza'))                              $emoji='🍕';
                    elseif  (str_contains($tl,'burger'))                             $emoji='🍔';
                    elseif  (str_contains($tl,'noodle')||str_contains($tl,'pasta'))  $emoji='🍝';
                    elseif  (str_contains($tl,'rice')||str_contains($tl,'biryani'))  $emoji='🍛';
                    elseif  (str_contains($tl,'coffee')||str_contains($tl,'tea'))    $emoji='☕';
                    elseif  (str_contains($tl,'lassi')||str_contains($tl,'mojito'))  $emoji='🥤';
                    elseif  (str_contains($tl,'paneer')||str_contains($tl,'paratha')) $emoji='🫓';
                    else    $emoji='🍽️';
                ?>
                <tr>
                    <td style="width:54px;padding-right:0;">
                        <?php if (!empty($item['dish_img'])): ?>
                        <img src="admin/Res_img/dishes/<?php echo htmlspecialchars($item['dish_img']); ?>"
                             class="dish-thumb" alt=""
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <div class="dish-emoji" style="display:none;"><?php echo $emoji; ?></div>
                        <?php else: ?>
                        <div class="dish-emoji"><?php echo $emoji; ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="dish-name"><?php echo htmlspecialchars($item['title']); ?></div>
                        <?php if (!empty($item['restaurant_name'])): ?>
                        <div class="dish-rest"><i class="fa fa-cutlery"></i> <?php echo htmlspecialchars($item['restaurant_name']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td><span class="qty-badge">× <?php echo intval($item['quantity']); ?></span></td>
                    <td style="color:#888;font-size:.85rem;">₹<?php echo number_format($item['unit_price'], 2); ?></td>
                    <td class="sub-cell">₹<?php echo number_format($item['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="total-bar">
            <div class="total-left">
                <?php echo count($items); ?> item(s) · Grand Total:
                <span class="total-amount">₹<?php echo number_format($order['total_price'], 2); ?></span>
                <?php if (floatval($order['discount_amount']) > 0): ?>
                <span class="coupon-saved"><i class="fa fa-tag"></i> Saved ₹<?php echo number_format($order['discount_amount'], 2); ?></span>
                <?php endif; ?>
            </div>
            <div class="pay-badge">
                <i class="fa fa-<?php echo $order['payment_method']==='cod'?'money':'credit-card'; ?>"></i>
                <?php echo strtoupper($order['payment_method']); ?>
            </div>
        </div>

        <div class="tracking-section">
            <div class="section-label">
                <i class="fa fa-map-marker" style="color:#ff5722;"></i> Live Tracking
            </div>
            <div class="timeline-h">
                <div class="tl-bg-line"></div>
                <div class="tl-fill <?php echo $fill_cls; ?>" data-target="<?php echo $fill_pct; ?>"></div>
                <?php foreach ($steps as [$icon, $name, $nodeCls, $textCls, $time]): ?>
                <div class="tl-step">
                    <div class="tl-node <?php echo $nodeCls; ?>"><i class="fa <?php echo $icon; ?>"></i></div>
                    <div class="tl-text">
                        <div class="tl-name <?php echo $textCls; ?>"><?php echo $name; ?></div>
                        <?php if ($time): ?><div class="tl-time"><?php echo $time; ?></div><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (in_array($status, ['confirmed','in process','on the way'])): ?>
            <div class="eta-bar">
                <span style="font-size:1.3rem;">⏱️</span>
                <div>
                    <?php
                    if ($status==='confirmed')     echo 'Order <strong>confirmed</strong>! Restaurant starts preparing soon.';
                    elseif ($status==='in process') echo 'Being <strong>freshly prepared</strong> — almost on its way!';
                    else                            echo 'Your order is <strong>on the way</strong>! Keep an eye out.';
                    ?>
                </div>
            </div>
            <?php elseif ($status === 'rejected'): ?>
            <div class="reject-note">
                <i class="fa fa-info-circle" style="margin-top:2px;"></i>
                <div>This order was <strong>cancelled by the restaurant.</strong> Please try a different restaurant.</div>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($status === 'closed'): ?>
        <div class="rating-section">
            <div class="section-label">
                <i class="fa fa-star" style="color:#ff9800;"></i>
                <?php echo empty($order['rating']) ? 'Rate Your Experience' : 'Your Rating'; ?>
            </div>
            <div class="star-row" id="starRow_<?php echo $order_id; ?>">
                <?php
                $saved  = intval($order['rating'] ?? 0);
                $labels = ['','Terrible 😞','Bad 😕','Okay 😐','Good 😊','Excellent 🤩'];
                for ($s = 1; $s <= 5; $s++): ?>
                <button type="button"
                    class="star-btn <?php echo ($saved >= $s) ? 'active' : ''; ?>"
                    data-val="<?php echo $s; ?>"
                    title="<?php echo $labels[$s]; ?>"
                    <?php echo $saved > 0 ? 'disabled' : ''; ?>
                    onclick="submitRating(<?php echo $order_id; ?>,<?php echo $s; ?>)"
                    onmouseenter="hoverStars(<?php echo $order_id; ?>,<?php echo $s; ?>)"
                    onmouseleave="unhoverStars(<?php echo $order_id; ?>)">★</button>
                <?php endfor; ?>
                <span id="rThanks_<?php echo $order_id; ?>"
                      style="font-size:.83rem;color:#4caf50;font-weight:700;margin-left:8px;<?php echo $saved>0?'':'display:none;'; ?>">
                    <?php echo $saved > 0 ? '✅ '.$labels[$saved] : ''; ?>
                </span>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /order-card -->
    <?php endforeach; endif; ?>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.tl-fill').forEach(function (el) {
        var t = el.getAttribute('data-target');
        setTimeout(function () { el.style.width = t; }, 250);
    });
});
setTimeout(function () {
    var n = document.querySelector('.notif-success');
    if (n) { n.style.transition='all .5s'; n.style.opacity='0'; n.style.transform='translateY(-10px)'; setTimeout(function(){n.remove();},500); }
}, 6000);
function hoverStars(oid,val){ document.querySelectorAll('#starRow_'+oid+' .star-btn').forEach(function(s){ s.classList.toggle('hovered',parseInt(s.dataset.val)<=val); }); }
function unhoverStars(oid){ document.querySelectorAll('#starRow_'+oid+' .star-btn').forEach(function(s){ s.classList.remove('hovered'); }); }
function submitRating(oid,rating){
    var labels=['','Terrible 😞','Bad 😕','Okay 😐','Good 😊','Excellent 🤩'];
    var fd=new FormData(); fd.append('rate_order','1'); fd.append('order_id',oid); fd.append('rating',rating);
    fetch(window.location.href,{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(d){
        if(d.success){
            document.querySelectorAll('#starRow_'+oid+' .star-btn').forEach(function(s){ s.classList.toggle('active',parseInt(s.dataset.val)<=rating); s.disabled=true; });
            var t=document.getElementById('rThanks_'+oid);
            if(t){t.style.display='';t.textContent='✅ '+labels[rating];}
        }
    });
}
</script>
</body>
</html>
