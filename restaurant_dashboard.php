<?php
// ================================================================
//  restaurant_dashboard.php
//  Reads orders from NEW tables: orders + order_items
//  Old users_orders table is NOT touched / NOT read here
// ================================================================
include("connection/connect.php");
session_start();
error_reporting(0);

// Support whichever session key your login sets
if (!empty($_SESSION['rs_id'])) {
    $rs_id = intval($_SESSION['rs_id']);
} elseif (!empty($_SESSION['restaurant_id'])) {
    $rs_id = intval($_SESSION['restaurant_id']);
} elseif (!empty($_SESSION['id']) && !empty($_SESSION['role']) && $_SESSION['role'] === 'restaurant') {
    $rs_id = intval($_SESSION['id']);
} else {
    // ── TEMPORARY DEBUG: remove this block once login works ──────
    // Uncomment the two lines below to see what SESSION contains:
    // echo '<pre>'; print_r($_SESSION); echo '</pre>'; exit();
    // ─────────────────────────────────────────────────────────────
    header('location:restaurant_login.php');
    exit();
}

$rs_id = intval($rs_id);

// ── Fetch restaurant info ────────────────────────────────────────
$rsq  = mysqli_query($db, "SELECT * FROM restaurant WHERE rs_id='$rs_id'");
$rest = mysqli_fetch_assoc($rsq);
$rs_name = htmlspecialchars($rest['title'] ?? 'Restaurant');

// ── Active tab ───────────────────────────────────────────────────
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

// ================================================================
//  POST: update order status
// ================================================================
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status   = mysqli_real_escape_string($db, $_POST['status']);

    // Security: only update if this order has items from this restaurant
    $chk = mysqli_query($db,
        "SELECT o.order_id FROM orders o
           INNER JOIN order_items oi ON oi.order_id = o.order_id
          WHERE o.order_id='$order_id' AND oi.rs_id='$rs_id'
          LIMIT 1"
    );
    if (mysqli_num_rows($chk) > 0) {
        mysqli_query($db, "UPDATE orders SET status='$status' WHERE order_id='$order_id'");
        mysqli_query($db, "INSERT INTO remark(frm_id,status,remark)
                           VALUES('$order_id','$status','Updated by restaurant')");
    }
    header("Location: restaurant_dashboard.php?tab=orders&updated=1");
    exit();
}

// ================================================================
//  DATA: stats for overview
// ================================================================
$r = mysqli_fetch_assoc(mysqli_query($db,
    "SELECT COUNT(DISTINCT o.order_id) AS cnt
       FROM orders o INNER JOIN order_items oi ON oi.order_id=o.order_id
      WHERE oi.rs_id='$rs_id'"));
$total_orders = intval($r['cnt']);

$r = mysqli_fetch_assoc(mysqli_query($db,
    "SELECT COUNT(DISTINCT o.order_id) AS cnt
       FROM orders o INNER JOIN order_items oi ON oi.order_id=o.order_id
      WHERE oi.rs_id='$rs_id' AND o.status='pending'"));
$pending_count = intval($r['cnt']);

$r = mysqli_fetch_assoc(mysqli_query($db,
    "SELECT COUNT(DISTINCT o.order_id) AS cnt
       FROM orders o INNER JOIN order_items oi ON oi.order_id=o.order_id
      WHERE oi.rs_id='$rs_id' AND o.status='closed'"));
$delivered_count = intval($r['cnt']);

$r = mysqli_fetch_assoc(mysqli_query($db,
    "SELECT COALESCE(SUM(oi.subtotal),0) AS rev
       FROM order_items oi INNER JOIN orders o ON o.order_id=oi.order_id
      WHERE oi.rs_id='$rs_id' AND o.status='closed'"));
$total_revenue = floatval($r['rev']);

$r = mysqli_fetch_assoc(mysqli_query($db,
    "SELECT COUNT(*) AS cnt FROM dishes WHERE rs_id='$rs_id'"));
$total_dishes = intval($r['cnt']);

// ================================================================
//  DATA: Orders tab — one row per order_item (matches old UI layout)
// ================================================================
$orders_q = mysqli_query($db,
    "SELECT oi.item_id, oi.order_id, oi.title AS dish_title,
            oi.dish_img, oi.quantity, oi.unit_price, oi.subtotal,
            o.status, o.order_date, o.delivery_phone,
            o.payment_method, o.delivery_address,
            u.f_name, u.l_name, u.email AS user_email
       FROM order_items oi
       INNER JOIN orders o ON o.order_id = oi.order_id
       INNER JOIN users  u ON u.u_id     = o.u_id
      WHERE oi.rs_id = '$rs_id'
      ORDER BY o.order_date DESC, oi.item_id ASC"
);

// ================================================================
//  DATA: My Dishes tab
// ================================================================
$dishes_q = mysqli_query($db,
    "SELECT * FROM dishes WHERE rs_id='$rs_id' ORDER BY d_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $rs_name; ?> — Restaurant Dashboard</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { background:#f4f6f9; font-family:'Segoe UI',system-ui,sans-serif; margin:0; color:#222; }

        /* ── Header ── */
        .rs-header { background:linear-gradient(135deg,#ff5722,#ff9800); padding:10px 24px;
                     display:flex; justify-content:space-between; align-items:center; }
        .rs-brand  { color:#fff; font-size:1.25rem; font-weight:800; line-height:1.1; }
        .rs-brand small { display:block; font-size:.72rem; font-weight:400; opacity:.85; }
        .header-links a { color:#fff; text-decoration:none; font-size:.85rem; margin-left:18px; opacity:.9; }
        .header-links a:hover { opacity:1; }

        /* ── Nav tabs ── */
        .rs-nav { background:#fff; border-bottom:2px solid #f0f0f0; padding:0 24px;
                  display:flex; overflow-x:auto; }
        .rs-nav a { display:inline-flex; align-items:center; gap:7px; padding:14px 16px;
                    font-size:.85rem; font-weight:600; color:#666; text-decoration:none;
                    border-bottom:3px solid transparent; white-space:nowrap; transition:color .2s; }
        .rs-nav a:hover  { color:#ff5722; }
        .rs-nav a.active { color:#ff5722; border-bottom-color:#ff5722; }

        /* ── Page wrapper ── */
        .rs-page { padding:28px 24px; max-width:1200px; margin:0 auto; }
        .page-title { font-size:1.3rem; font-weight:800; margin-bottom:22px;
                      display:flex; align-items:center; gap:10px; }

        /* ── Stats grid ── */
        .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr));
                      gap:16px; margin-bottom:28px; }
        .stat-card  { background:#fff; border-radius:12px; padding:20px;
                      box-shadow:0 2px 10px rgba(0,0,0,.06);
                      display:flex; align-items:center; gap:15px; }
        .stat-icon  { width:50px; height:50px; border-radius:11px; display:flex;
                      align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0; }
        .si-orange { background:#fff3e0; color:#ff5722; }
        .si-blue   { background:#e3f2fd; color:#1565c0; }
        .si-green  { background:#e8f5e9; color:#2e7d32; }
        .si-purple { background:#f3e5f5; color:#7b1fa2; }
        .si-yellow { background:#fff8e1; color:#f57f17; }
        .stat-num  { font-size:1.55rem; font-weight:800; line-height:1; }
        .stat-lbl  { font-size:.72rem; color:#aaa; margin-top:3px; font-weight:600;
                     text-transform:uppercase; letter-spacing:.4px; }

        /* ── Table card ── */
        .table-card   { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.06); overflow:hidden; }
        .table-header { background:linear-gradient(90deg,#ff5722,#ff9800); color:#fff;
                        padding:14px 22px; font-weight:700; font-size:1rem; }
        .table-responsive { overflow-x:auto; }
        table.orders-tbl { width:100%; border-collapse:collapse; font-size:.88rem; }
        table.orders-tbl thead th { padding:11px 14px; font-weight:700; font-size:.75rem;
                                    text-transform:uppercase; letter-spacing:.4px; color:#888;
                                    border-bottom:2px solid #f5f5f5; text-align:left; white-space:nowrap; }
        table.orders-tbl tbody td { padding:13px 14px; border-bottom:1px solid #fafafa; vertical-align:middle; }
        table.orders-tbl tbody tr:last-child td { border-bottom:none; }
        table.orders-tbl tbody tr:hover td { background:#fdf9f7; }

        .order-num-badge { font-weight:800; color:#ff5722; font-size:.95rem; }
        .dish-cell       { font-weight:600; color:#222; }
        .customer-name   { font-weight:700; color:#333; font-size:.87rem; }
        .customer-email  { font-size:.75rem; color:#aaa; }

        /* Status badges */
        .badge-st     { border-radius:20px; padding:4px 13px; font-size:.76rem; font-weight:700; display:inline-block; }
        .st-pending   { background:#fff8e1; color:#f57f17; }
        .st-confirmed { background:#e8f5e9; color:#2e7d32; }
        .st-inprocess { background:#e3f2fd; color:#1565c0; }
        .st-onway     { background:#f3e5f5; color:#7b1fa2; }
        .st-closed    { background:#e8f5e9; color:#2e7d32; }
        .st-rejected  { background:#ffebee; color:#c62828; }

        /* Action */
        .action-form select { font-size:.78rem; padding:4px 8px; border:1px solid #ddd;
                              border-radius:6px; outline:none; }
        .btn-upd { background:#ff5722; color:#fff; border:none; border-radius:6px;
                   padding:5px 12px; font-size:.75rem; font-weight:700; cursor:pointer;
                   margin-left:5px; transition:background .2s; }
        .btn-upd:hover { background:#e64a19; }
        .delivered-lbl { color:#4caf50; font-weight:700; font-size:.82rem;
                         display:inline-flex; align-items:center; gap:5px; }

        /* Alert */
        .alert-success { background:#e8f5e9; color:#2e7d32; border:1px solid #c8e6c9;
                         border-radius:10px; padding:12px 18px; margin-bottom:18px;
                         font-size:.87rem; font-weight:600; }

        /* Dish thumb */
        .dish-img-thumb { width:44px; height:44px; border-radius:8px; object-fit:cover; }
        .no-img         { width:44px; height:44px; border-radius:8px; background:#fff3e0;
                          display:inline-flex; align-items:center; justify-content:center; font-size:1.1rem; }

        /* Empty state */
        .empty-state { text-align:center; padding:50px 20px; color:#bbb; }
        .empty-state .ei { font-size:3rem; margin-bottom:12px; }

        @media(max-width:640px){
            .rs-page { padding:16px 12px; }
            .rs-nav a { padding:12px 10px; font-size:.78rem; }
        }
    </style>
</head>
<body>

<!-- ── Header ── -->
<div class="rs-header">
    <div class="rs-brand">
        <?php echo $rs_name; ?>
        <small>Restaurant Dashboard</small>
    </div>
    <div class="header-links">
        <a href="index.php"><i class="fa fa-home"></i> Home</a>
        <a href="restaurant_logout.php"><i class="fa fa-sign-out"></i> Logout</a>
    </div>
</div>

<!-- ── Nav Tabs ── -->
<div class="rs-nav">
    <a href="?tab=overview" class="<?php echo $tab==='overview'?'active':''; ?>">
        <i class="fa fa-tachometer"></i> Overview</a>
    <a href="?tab=orders" class="<?php echo $tab==='orders'?'active':''; ?>">
        <i class="fa fa-shopping-cart"></i> Orders
        <?php if($pending_count > 0): ?>
        <span style="background:#ff5722;color:#fff;border-radius:10px;padding:1px 7px;font-size:.7rem;font-weight:800;"><?php echo $pending_count; ?></span>
        <?php endif; ?>
    </a>
    <a href="?tab=tracking" class="<?php echo $tab==='tracking'?'active':''; ?>">
        <i class="fa fa-map-marker"></i> Order Tracking</a>
    <a href="?tab=dishes" class="<?php echo $tab==='dishes'?'active':''; ?>">
        <i class="fa fa-cutlery"></i> My Dishes</a>
    <a href="add_dish.php">
        <i class="fa fa-plus-circle"></i> Add Dish</a>
    <a href="?tab=info" class="<?php echo $tab==='info'?'active':''; ?>">
        <i class="fa fa-info-circle"></i> Restaurant Info</a>
    <a href="change_password.php">
        <i class="fa fa-key"></i> Change Password</a>
    <a href="coupons.php" style="color:#ff9800;">
        <i class="fa fa-tag"></i> Coupons</a>
    <a href="reports.php" style="color:#1565c0;">
        <i class="fa fa-bar-chart"></i> Reports</a>
    <a href="restaurant_logout.php">
        <i class="fa fa-sign-out"></i> Logout</a>
</div>

<!-- ── Content ── -->
<div class="rs-page">

<?php if(isset($_GET['updated'])): ?>
<div class="alert-success"><i class="fa fa-check-circle"></i> Order status updated successfully.</div>
<?php endif; ?>

<!-- ===========================================================
     OVERVIEW TAB
=========================================================== -->
<?php if($tab === 'overview'): ?>
<div class="page-title"><i class="fa fa-tachometer" style="color:#ff5722;"></i> Overview</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon si-orange"><i class="fa fa-shopping-cart"></i></div>
        <div><div class="stat-num"><?php echo $total_orders; ?></div><div class="stat-lbl">Total Orders</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-yellow"><i class="fa fa-clock-o"></i></div>
        <div><div class="stat-num"><?php echo $pending_count; ?></div><div class="stat-lbl">Pending</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-green"><i class="fa fa-check-circle"></i></div>
        <div><div class="stat-num"><?php echo $delivered_count; ?></div><div class="stat-lbl">Delivered</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-purple"><i class="fa fa-cutlery"></i></div>
        <div><div class="stat-num"><?php echo $total_dishes; ?></div><div class="stat-lbl">Menu Items</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-blue"><i class="fa fa-inr"></i></div>
        <div><div class="stat-num">₹<?php echo number_format($total_revenue,0); ?></div><div class="stat-lbl">Revenue</div></div>
    </div>
</div>

<?php
// Recent 5 rows preview
$recent_q = mysqli_query($db,
    "SELECT oi.title AS dish_title, oi.quantity, oi.subtotal,
            o.order_id, o.status, o.order_date, u.f_name, u.l_name
       FROM order_items oi
       INNER JOIN orders o ON o.order_id=oi.order_id
       INNER JOIN users  u ON u.u_id=o.u_id
      WHERE oi.rs_id='$rs_id'
      ORDER BY o.order_date DESC LIMIT 5");
if ($recent_q && mysqli_num_rows($recent_q) > 0): ?>
<div class="table-card">
    <div class="table-header">🕐 Recent Orders</div>
    <div class="table-responsive">
    <table class="orders-tbl">
        <thead><tr>
            <th>#Order</th><th>Dish</th><th>Customer</th><th>Qty</th><th>Amount</th><th>Status</th><th>Date</th>
        </tr></thead>
        <tbody>
        <?php while($r = mysqli_fetch_assoc($recent_q)):
            switch($r['status']){
                case 'pending':    $bs='st-pending';   $bl='Pending';    break;
                case 'confirmed':  $bs='st-confirmed'; $bl='Confirmed';  break;
                case 'in process': $bs='st-inprocess'; $bl='Preparing';  break;
                case 'on the way': $bs='st-onway';     $bl='On the Way'; break;
                case 'closed':     $bs='st-closed';    $bl='Closed';     break;
                case 'rejected':   $bs='st-rejected';  $bl='Rejected';   break;
                default:           $bs='st-pending';   $bl='Pending';    break;
            }
        ?>
        <tr>
            <td><span class="order-num-badge">#<?php echo $r['order_id']; ?></span></td>
            <td class="dish-cell"><?php echo htmlspecialchars($r['dish_title']); ?></td>
            <td><?php echo htmlspecialchars($r['f_name'].' '.$r['l_name']); ?></td>
            <td><?php echo $r['quantity']; ?></td>
            <td><strong style="color:#ff5722;">₹<?php echo number_format($r['subtotal'],2); ?></strong></td>
            <td><span class="badge-st <?php echo $bs; ?>"><?php echo $bl; ?></span></td>
            <td style="font-size:.8rem;color:#aaa;"><?php echo date('d M y, H:i', strtotime($r['order_date'])); ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

<!-- ===========================================================
     ORDERS TAB  — mirrors old UI: one row per dish
=========================================================== -->
<?php elseif($tab === 'orders'): ?>
<div class="page-title"><i class="fa fa-shopping-cart" style="color:#ff5722;"></i> All Orders</div>

<div class="table-card">
    <div class="table-header">Orders for <?php echo $rs_name; ?></div>
    <div class="table-responsive">
    <table class="orders-tbl">
        <thead><tr>
            <th>#Order</th>
            <th>Dish</th>
            <th>Customer</th>
            <th>Phone</th>
            <th>Qty</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
        </tr></thead>
        <tbody>
        <?php if (!$orders_q || mysqli_num_rows($orders_q) == 0): ?>
        <tr><td colspan="9">
            <div class="empty-state">
                <div class="ei">🛒</div>
                <div>No orders yet. New orders placed by customers will appear here.</div>
            </div>
        </td></tr>
        <?php else: while($row = mysqli_fetch_assoc($orders_q)):
            $status = $row['status'];
            switch($status){
                case 'pending':    $bs='st-pending';   $bl='Pending';    break;
                case 'confirmed':  $bs='st-confirmed'; $bl='Confirmed';  break;
                case 'in process': $bs='st-inprocess'; $bl='Preparing';  break;
                case 'on the way': $bs='st-onway';     $bl='On the Way'; break;
                case 'closed':     $bs='st-closed';    $bl='Closed';     break;
                case 'rejected':   $bs='st-rejected';  $bl='Rejected';   break;
                default:           $bs='st-pending';   $bl='Pending';    break;
            }
        ?>
        <tr>
            <td><span class="order-num-badge">#<?php echo $row['order_id']; ?></span></td>
            <td class="dish-cell"><?php echo htmlspecialchars($row['dish_title']); ?></td>
            <td>
                <div class="customer-name"><?php echo htmlspecialchars($row['f_name'].' '.$row['l_name']); ?></div>
                <div class="customer-email"><?php echo htmlspecialchars($row['user_email']); ?></div>
            </td>
            <td><?php echo htmlspecialchars($row['delivery_phone']); ?></td>
            <td><?php echo intval($row['quantity']); ?></td>
            <td><strong style="color:#ff5722;">₹<?php echo number_format($row['subtotal'],2); ?></strong></td>
            <td><span class="badge-st <?php echo $bs; ?>"><?php echo $bl; ?></span></td>
            <td style="font-size:.8rem;color:#aaa;white-space:nowrap;">
                <?php echo date('d M y, H:i', strtotime($row['order_date'])); ?>
            </td>
            <td>
                <?php if(in_array($status,['closed','rejected'])): ?>
                <span class="delivered-lbl">
                    <i class="fa fa-<?php echo $status==='closed'?'truck':'times-circle'; ?>"></i>
                    <?php echo $status==='closed'?'Delivered':'Cancelled'; ?>
                </span>
                <?php else: ?>
                <form method="POST" class="action-form" style="display:flex;align-items:center;">
                    <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                    <select name="status">
                        <option value="pending"    <?php echo $status==='pending'   ?'selected':''; ?>>⏳ Pending</option>
                        <option value="confirmed"  <?php echo $status==='confirmed' ?'selected':''; ?>>👍 Confirmed</option>
                        <option value="in process" <?php echo $status==='in process'?'selected':''; ?>>🔥 Preparing</option>
                        <option value="on the way" <?php echo $status==='on the way'?'selected':''; ?>>🛵 On the Way</option>
                        <option value="closed">✅ Delivered</option>
                        <option value="rejected">❌ Cancel</option>
                    </select>
                    <button type="submit" name="update_status" class="btn-upd">Update</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- ===========================================================
     ORDER TRACKING TAB  — active orders only, grouped by order
=========================================================== -->
<?php elseif($tab === 'tracking'): ?>
<div class="page-title"><i class="fa fa-map-marker" style="color:#ff5722;"></i> Order Tracking</div>

<?php
$track_q = mysqli_query($db,
    "SELECT o.order_id, o.status, o.order_date, o.delivery_address, o.delivery_phone,
            u.f_name, u.l_name,
            GROUP_CONCAT(oi.title ORDER BY oi.item_id SEPARATOR ', ') AS dish_list,
            SUM(oi.subtotal) AS rs_total
       FROM orders o
       INNER JOIN order_items oi ON oi.order_id=o.order_id
       INNER JOIN users u ON u.u_id=o.u_id
      WHERE oi.rs_id='$rs_id' AND o.status NOT IN('closed','rejected')
      GROUP BY o.order_id
      ORDER BY o.order_date DESC"
);
if (!$track_q || mysqli_num_rows($track_q) == 0): ?>
<div class="table-card" style="padding:50px;text-align:center;color:#bbb;">
    <div style="font-size:2.5rem;margin-bottom:10px;">✅</div>
    <div style="font-weight:600;">No active orders right now.</div>
</div>
<?php else: ?>
<div class="table-card">
    <div class="table-header">🔴 Live / Active Orders</div>
    <div class="table-responsive">
    <table class="orders-tbl">
        <thead><tr>
            <th>#Order</th><th>Dishes</th><th>Customer</th><th>Address</th>
            <th>Amount</th><th>Status</th><th>Date</th><th>Update</th>
        </tr></thead>
        <tbody>
        <?php while($tr = mysqli_fetch_assoc($track_q)):
            $status = $tr['status'];
            switch($status){
                case 'pending':    $bs='st-pending';   $bl='⏳ Pending';      break;
                case 'confirmed':  $bs='st-confirmed'; $bl='👍 Confirmed';    break;
                case 'in process': $bs='st-inprocess'; $bl='🔥 Preparing';    break;
                case 'on the way': $bs='st-onway';     $bl='🛵 On the Way';   break;
                default:           $bs='st-pending';   $bl='⏳ Pending';      break;
            }
        ?>
        <tr>
            <td><span class="order-num-badge">#<?php echo $tr['order_id']; ?></span></td>
            <td class="dish-cell" style="font-size:.82rem;"><?php echo htmlspecialchars($tr['dish_list']); ?></td>
            <td>
                <?php echo htmlspecialchars($tr['f_name'].' '.$tr['l_name']); ?><br>
                <small style="color:#aaa;"><?php echo htmlspecialchars($tr['delivery_phone']); ?></small>
            </td>
            <td style="font-size:.8rem;color:#777;max-width:160px;"><?php echo htmlspecialchars($tr['delivery_address']); ?></td>
            <td><strong style="color:#ff5722;">₹<?php echo number_format($tr['rs_total'],2); ?></strong></td>
            <td><span class="badge-st <?php echo $bs; ?>"><?php echo $bl; ?></span></td>
            <td style="font-size:.8rem;color:#aaa;"><?php echo date('d M y, H:i', strtotime($tr['order_date'])); ?></td>
            <td>
                <form method="POST" class="action-form" style="display:flex;align-items:center;">
                    <input type="hidden" name="order_id" value="<?php echo $tr['order_id']; ?>">
                    <select name="status">
                        <option value="pending"    <?php echo $status==='pending'   ?'selected':''; ?>>⏳ Pending</option>
                        <option value="confirmed"  <?php echo $status==='confirmed' ?'selected':''; ?>>👍 Confirmed</option>
                        <option value="in process" <?php echo $status==='in process'?'selected':''; ?>>🔥 Preparing</option>
                        <option value="on the way" <?php echo $status==='on the way'?'selected':''; ?>>🛵 On the Way</option>
                        <option value="closed">✅ Delivered</option>
                        <option value="rejected">❌ Cancel</option>
                    </select>
                    <button type="submit" name="update_status" class="btn-upd">Go</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

<!-- ===========================================================
     MY DISHES TAB
=========================================================== -->
<?php elseif($tab === 'dishes'): ?>
<div class="page-title"><i class="fa fa-cutlery" style="color:#ff5722;"></i> My Dishes</div>

<div class="table-card">
    <div class="table-header">Menu — <?php echo $rs_name; ?></div>
    <div class="table-responsive">
    <table class="orders-tbl">
        <thead><tr>
            <th>Image</th><th>Title</th><th>Price</th><th>Discount</th><th>Category</th><th>Action</th>
        </tr></thead>
        <tbody>
        <?php if(!$dishes_q || mysqli_num_rows($dishes_q)==0): ?>
        <tr><td colspan="6"><div class="empty-state"><div class="ei">🍽️</div><div>No dishes added yet.</div></div></td></tr>
        <?php else: while($d = mysqli_fetch_assoc($dishes_q)): ?>
        <tr>
            <td>
                <?php if(!empty($d['img'])): ?>
                <img src="admin/Res_img/dishes/<?php echo htmlspecialchars($d['img']); ?>"
                     class="dish-img-thumb" alt="" onerror="this.style.display='none'">
                <?php else: ?>
                <span class="no-img">🍽️</span>
                <?php endif; ?>
            </td>
            <td class="dish-cell"><?php echo htmlspecialchars($d['title']); ?></td>
            <td><strong style="color:#ff5722;">₹<?php echo number_format($d['price'],2); ?></strong></td>
            <td><?php echo intval($d['discount_pct']??0); ?>%</td>
            <td style="font-size:.82rem;color:#888;"><?php echo htmlspecialchars($d['category']??'—'); ?></td>
            <td>
                <a href="edit_dish.php?d_id=<?php echo $d['d_id']; ?>"
                   style="color:#1565c0;font-weight:700;font-size:.8rem;margin-right:10px;">
                   <i class="fa fa-edit"></i> Edit</a>
                <a href="delete_dish.php?d_id=<?php echo $d['d_id']; ?>"
                   onclick="return confirm('Delete this dish?')"
                   style="color:#c62828;font-weight:700;font-size:.8rem;">
                   <i class="fa fa-trash-o"></i> Delete</a>
            </td>
        </tr>
        <?php endwhile; endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- ===========================================================
     RESTAURANT INFO TAB
=========================================================== -->
<?php elseif($tab === 'info'): ?>
<div class="page-title"><i class="fa fa-info-circle" style="color:#ff5722;"></i> Restaurant Info</div>
<div class="table-card" style="padding:28px;">
    <table style="width:100%;font-size:.9rem;border-collapse:collapse;">
        <?php
        $info_fields = ['title'=>'Restaurant Name','email'=>'Email','phone'=>'Phone',
                        'address'=>'Address','category'=>'Category','description'=>'Description'];
        foreach($info_fields as $key => $label):
            if(!isset($rest[$key])) continue;
        ?>
        <tr style="border-bottom:1px solid #f5f5f5;">
            <td style="padding:12px 16px;font-weight:700;color:#888;width:170px;"><?php echo $label; ?></td>
            <td style="padding:12px 16px;color:#333;"><?php echo htmlspecialchars($rest[$key]); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <div style="padding:16px 16px 0;">
        <a href="edit_restaurant.php"
           style="background:#ff5722;color:#fff;padding:9px 22px;border-radius:8px;text-decoration:none;font-weight:700;font-size:.87rem;">
            <i class="fa fa-edit"></i> Edit Info
        </a>
    </div>
</div>

<?php endif; ?>
</div><!-- /rs-page -->

<script>
setTimeout(function(){
    var a = document.querySelector('.alert-success');
    if(a){ a.style.transition='all .4s'; a.style.opacity='0'; setTimeout(function(){a.remove();},400); }
}, 4000);
</script>
</body>
</html>
