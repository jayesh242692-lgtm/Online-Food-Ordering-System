<!DOCTYPE html>
<html lang="en">
<?php
include("connection/connect.php"); 
error_reporting(0);
session_start();

include_once 'product-action.php'; 
?>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="#">
    <title>Dishes</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/animsition.min.css" rel="stylesheet">
    <link href="css/animate.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9 !important; }
        .page-wrapper { padding-top: 0 !important; background: #f4f6f9; }
        /* Prevent browser from jumping to anchor before JS takes over */
        html { scroll-behavior: auto !important; }
    </style>
</head>

<body>
    <!-- Header -->
    <?php include('navbar.php'); ?>
    
    <div class="page-wrapper">
        <!-- Steps -->
        <div class="top-links">
            <div class="container">
                <ul class="row links">
                    <li class="col-xs-12 col-sm-4 link-item"><span>1</span><a href="restaurants.php">Choose Restaurant</a></li>
                    <li class="col-xs-12 col-sm-4 link-item active"><span>2</span><a href="#">Choose Dishes</a></li>
                    <li class="col-xs-12 col-sm-4 link-item"><span>3</span><a href="#">Order and Pay</a></li>
                </ul>
            </div>
        </div>

        <!-- Restaurant Info -->
        <?php 
        $ress= mysqli_query($db,"select * from restaurant where rs_id='$_GET[res_id]'");
        $rows=mysqli_fetch_array($ress);
        ?>
        <section class="inner-page-hero" style="background:url('https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=1600&q=80') no-repeat center center/cover; position:relative; height:260px; display:flex; align-items:flex-end;">
            <!-- Dark overlay -->
            <div style="position:absolute;inset:0;background:linear-gradient(to bottom, rgba(0,0,0,0.25) 0%, rgba(0,0,0,0.72) 100%);"></div>
            <div class="container" style="position:relative;z-index:2;padding:30px 20px;">
                <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
                    <!-- Restaurant logo/image -->
                    <div style="flex-shrink:0;">
                        <img src="admin/Res_img/<?php echo htmlspecialchars($rows['image']); ?>"
                             alt="<?php echo htmlspecialchars($rows['title']); ?>"
                             style="width:110px;height:110px;object-fit:cover;border-radius:12px;border:3px solid rgba(255,255,255,0.4);box-shadow:0 4px 20px rgba(0,0,0,0.4);"
                             onerror="this.src='images/icn.png'">
                    </div>
                    <!-- Restaurant info -->
                    <div style="color:#fff;">
                        <h2 style="margin:0 0 6px;font-size:1.8rem;font-weight:800;color:#fff;text-shadow:0 2px 8px rgba(0,0,0,0.5);">
                            <?php echo htmlspecialchars($rows['title']); ?>
                        </h2>
                        <p style="margin:0 0 8px;color:rgba(255,255,255,0.85);font-size:0.9rem;max-width:600px;">
                            <i class="fa fa-map-marker" style="color:#ff9800;margin-right:6px;"></i>
                            <?php echo htmlspecialchars($rows['address']); ?>
                        </p>
                        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:6px;">
                            <span style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);border-radius:20px;padding:4px 14px;font-size:0.8rem;color:#fff;">
                                <i class="fa fa-clock-o" style="margin-right:5px;color:#ff9800;"></i>
                                <?php echo htmlspecialchars($rows['o_hr']); ?> – <?php echo htmlspecialchars($rows['c_hr']); ?>
                            </span>
                            <span style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);border-radius:20px;padding:4px 14px;font-size:0.8rem;color:#fff;">
                                <i class="fa fa-calendar" style="margin-right:5px;color:#ff9800;"></i>
                                <?php echo htmlspecialchars($rows['o_days']); ?>
                            </span>
                            <?php if(!empty($rows['phone'])): ?>
                            <span style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);border-radius:20px;padding:4px 14px;font-size:0.8rem;color:#fff;">
                                <i class="fa fa-phone" style="margin-right:5px;color:#ff9800;"></i>
                                <?php echo htmlspecialchars($rows['phone']); ?>
                            </span>
                            <?php endif; ?>
                            <?php
                            // Show restaurant average rating in hero
                            $hero_rq = mysqli_query($db,"SELECT ROUND(AVG(rating),1) as avg_r, COUNT(rating) as total_r FROM users_orders WHERE rs_id='".intval($_GET['res_id'])."' AND rating IS NOT NULL AND status='closed'");
                            $hero_rr = mysqli_fetch_assoc($hero_rq);
                            if($hero_rr && $hero_rr['total_r'] > 0):
                            ?>
                            <span style="background:rgba(255,152,0,0.25);border:1px solid rgba(255,152,0,0.5);border-radius:20px;padding:4px 14px;font-size:0.8rem;color:#fff;">
                                <i class="fa fa-star" style="color:#ff9800;margin-right:4px;"></i>
                                <?php echo $hero_rr['avg_r']; ?> &nbsp;·&nbsp; <?php echo $hero_rr['total_r']; ?> review<?php echo $hero_rr['total_r']!=1?'s':''; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Menu Section -->
        <?php
        // Show this restaurant's active coupons & offers
        // Auto-create table if needed
        mysqli_query($db, "CREATE TABLE IF NOT EXISTS `coupons` (
            `c_id` int(11) NOT NULL AUTO_INCREMENT, `code` varchar(50) NOT NULL,
            `type` enum('percent','flat') NOT NULL DEFAULT 'percent', `value` decimal(10,2) NOT NULL,
            `min_order` decimal(10,2) NOT NULL DEFAULT 0, `max_discount` decimal(10,2) NOT NULL DEFAULT 0,
            `expiry_date` datetime NOT NULL, `usage_limit` int(11) NOT NULL DEFAULT 0,
            `used_count` int(11) NOT NULL DEFAULT 0, `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `description` varchar(255) DEFAULT '', `rs_id` int(11) NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`c_id`), UNIQUE KEY `code` (`code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1");

        // Also auto-create res_discount column if not exists
        mysqli_query($db, "ALTER TABLE restaurant ADD COLUMN IF NOT EXISTS `discount_pct` decimal(5,2) NOT NULL DEFAULT 0");
        // Auto-create dish-level discount column
        mysqli_query($db, "ALTER TABLE dishes ADD COLUMN IF NOT EXISTS `discount_pct` decimal(5,2) NOT NULL DEFAULT 0");

        $res_id_page = intval($_GET['res_id']);
        $now_cp = date('Y-m-d H:i:s');
        $cp_list = [];
        $cpq = mysqli_query($db, "SELECT * FROM coupons WHERE rs_id=$res_id_page AND is_active=1 AND expiry_date>='$now_cp' ORDER BY value DESC");
        if($cpq) while($cpr = mysqli_fetch_assoc($cpq)) $cp_list[] = $cpr;

        // Check restaurant-level discount
        $disc_row = mysqli_fetch_assoc(mysqli_query($db, "SELECT discount_pct FROM restaurant WHERE rs_id=$res_id_page"));
        $res_disc = $disc_row ? floatval($disc_row['discount_pct']) : 0;

        // Fetch average rating per dish title for this restaurant
        $dish_ratings_map = [];
        $drq = mysqli_query($db, "SELECT title, ROUND(AVG(rating),1) as avg_r, COUNT(rating) as total_r FROM users_orders WHERE rs_id=$res_id_page AND rating IS NOT NULL AND status='closed' GROUP BY title");
        if($drq) while($drr = mysqli_fetch_assoc($drq)) $dish_ratings_map[strtolower($drr['title'])] = $drr;

        if(!empty($cp_list) || $res_disc > 0):
        ?>
        <div style="max-width:1000px;margin:0 auto;padding:0 24px;margin-top:20px;">
            <div style="background:linear-gradient(135deg,#fff8f5,#fff3e0);border:1.5px dashed #ffb74d;border-radius:14px;padding:18px 20px;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                    <span style="font-size:1.6rem;">🏷️</span>
                    <div>
                        <div style="font-weight:800;color:#e65100;font-size:1rem;">Offers & Coupons Available!</div>
                        <div style="font-size:0.8rem;color:#aaa;">Apply these at checkout to save on your order.</div>
                    </div>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:12px;">

                    <?php if($res_disc > 0): ?>
                    <!-- Restaurant-level auto discount (no code needed) -->
                    <div style="background:#fff;border-radius:12px;padding:14px 18px;min-width:220px;flex:1;box-shadow:0 2px 10px rgba(255,87,34,0.1);border-left:4px solid #4caf50;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                            <span style="background:#4caf50;color:#fff;border-radius:6px;padding:2px 10px;font-size:0.78rem;font-weight:800;">AUTO DEAL</span>
                            <span style="font-size:0.75rem;color:#aaa;">No code needed</span>
                        </div>
                        <div style="font-size:1.1rem;font-weight:800;color:#2e7d32;"><?php echo $res_disc; ?>% OFF</div>
                        <div style="font-size:0.78rem;color:#888;margin-top:2px;">Automatically applied on all dishes</div>
                    </div>
                    <?php endif; ?>

                    <?php foreach($cp_list as $cp):
                        $disc_label = $cp['type']=='percent' ? $cp['value'].'% off' : '₹'.number_format($cp['value'],2).' off';
                        $days_left  = max(0, ceil((strtotime($cp['expiry_date']) - time()) / 86400));
                    ?>
                    <div style="background:#fff;border-radius:12px;padding:14px 18px;min-width:220px;flex:1;box-shadow:0 2px 10px rgba(255,87,34,0.1);border-left:4px solid #ff9800;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                            <span style="font-family:monospace;font-size:0.88rem;font-weight:800;background:#fff3e0;color:#e65100;border:1.5px dashed #ff9800;border-radius:6px;padding:2px 10px;letter-spacing:1px;">
                                <?php echo htmlspecialchars($cp['code']); ?>
                            </span>
                            <button onclick="copyCoupon('<?php echo htmlspecialchars($cp['code']); ?>')"
                                    style="background:none;border:1px solid #ddd;border-radius:5px;padding:2px 8px;font-size:0.72rem;cursor:pointer;color:#888;"
                                    title="Copy code">
                                <i class="fa fa-copy"></i> Copy
                            </button>
                        </div>
                        <div style="font-size:1.1rem;font-weight:800;color:#e65100;"><?php echo $disc_label; ?></div>
                        <?php if($cp['description']): ?><div style="font-size:0.78rem;color:#888;margin-top:2px;"><?php echo htmlspecialchars($cp['description']); ?></div><?php endif; ?>
                        <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:6px;">
                            <?php if($cp['min_order']>0): ?>
                            <span style="font-size:0.72rem;background:#f5f5f5;color:#666;border-radius:8px;padding:1px 8px;">Min ₹<?php echo $cp['min_order']; ?></span>
                            <?php endif; ?>
                            <span style="font-size:0.72rem;background:#fff3e0;color:#e65100;border-radius:8px;padding:1px 8px;">Expires in <?php echo $days_left; ?> day(s)</span>
                        </div>
                    </div>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>
        <?php endif; ?>

        <div style="background:#f4f6f9; padding:20px 0 50px;">

            <!-- Toast notification -->
            <!-- Cart notification - bottom center -->
            <div id="cartToastBox" style="display:none;position:fixed;bottom:30px;left:50%;transform:translateX(-50%);z-index:99999;pointer-events:none;">
                <div id="cartToastInner" style="display:flex;align-items:center;gap:14px;background:#1a1a2e;border:1.5px solid rgba(76,175,80,0.4);border-radius:16px;padding:14px 20px;min-width:300px;max-width:420px;box-shadow:0 8px 32px rgba(0,0,0,0.4);pointer-events:all;">
                    <i class="fa fa-check-circle" style="color:#4caf50;font-size:1.6rem;flex-shrink:0;"></i>
                    <div style="flex:1;">
                        <div style="font-weight:800;font-size:0.95rem;color:#fff;">Added to Cart!</div>
                        <div id="cartToastMsg" style="font-size:0.78rem;color:rgba(255,255,255,0.6);margin-top:2px;"></div>
                    </div>
                    <a href="cart.php" style="flex-shrink:0;background:linear-gradient(90deg,#ff5722,#ff9800);color:#fff;text-decoration:none;border-radius:8px;padding:7px 14px;font-size:0.82rem;font-weight:800;white-space:nowrap;">View Cart</a>
                </div>
            </div>

            <!-- Floating cart button (appears after first add) -->
            <a href="cart.php" id="cartFab" style="display:none;position:fixed;bottom:30px;right:24px;z-index:9998;background:linear-gradient(135deg,#ff5722,#ff9800);color:#fff;border-radius:50px;padding:12px 20px;font-size:1rem;text-decoration:none;box-shadow:0 6px 20px rgba(255,87,34,0.45);gap:8px;font-weight:700;">
                <i class="fa fa-shopping-cart" style="margin-right:4px;"></i>
                <span id="cartFabCount" style="background:#fff;color:#ff5722;border-radius:50%;width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:900;">0</span>
            </a>

            <style>
            @keyframes toastPop {
                from { opacity:0; transform:translateY(20px) scale(0.92); }
                to   { opacity:1; transform:translateY(0) scale(1); }
            }
            @keyframes toastFade {
                from { opacity:1; transform:translateY(0) scale(1); }
                to   { opacity:0; transform:translateY(10px) scale(0.96); }
            }
            @keyframes fabPop {
                0%   { transform:scale(1); }
                40%  { transform:scale(1.18); }
                70%  { transform:scale(0.95); }
                100% { transform:scale(1); }
            }

            .dish-card {
                background:#fff; border-radius:14px; overflow:hidden;
                box-shadow:0 2px 12px rgba(0,0,0,0.07); margin-bottom:16px;
                display:flex; align-items:stretch; position:relative;
                transition:box-shadow 0.2s, transform 0.15s;
                border:1px solid #f5f5f5;
            }
            .dish-card:hover { box-shadow:0 6px 24px rgba(255,87,34,0.12); transform:translateY(-1px); }

            .dish-img-wrap {
                width:200px; min-height:180px; flex-shrink:0; position:relative;
                overflow:hidden;
            }
            .dish-img-wrap img {
                width:100%; height:100%; object-fit:cover;
                transition:transform 0.3s;
            }
            .dish-card:hover .dish-img-wrap img { transform:scale(1.04); }

            .disc-badge {
                position:absolute; top:8px; left:8px;
                background:linear-gradient(135deg,#f44336,#e53935);
                color:#fff; font-size:0.72rem; font-weight:800;
                padding:3px 8px; border-radius:6px;
                box-shadow:0 2px 6px rgba(244,67,54,0.4);
            }

            .dish-body {
                flex:1; padding:16px 20px;
                display:flex; flex-direction:column;
                gap:0;
            }
            .dish-name { font-size:1.05rem; font-weight:800; color:#1a1a2e; margin:0 0 6px; }
            .dish-desc { font-size:0.82rem; color:#aaa; line-height:1.55; margin:0 0 14px; flex:1; }
            .dish-footer { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-top:auto; }

            .price-block  { display:flex; flex-direction:column; }
            .price-main   { font-size:1.2rem; font-weight:800; color:#ff5722; }
            .price-orig   { font-size:0.82rem; color:#bbb; text-decoration:line-through; }
            .price-save   { font-size:0.76rem; color:#4caf50; font-weight:700; }

            .add-row { display:flex; align-items:center; gap:8px; }
            .qty-wrap-d {
                display:flex; align-items:center;
                border:1.5px solid #e0e0e0; border-radius:10px; overflow:hidden;
            }
            .qty-btn-d {
                width:34px; height:38px; border:none; background:#f5f5f5;
                cursor:pointer; font-size:1.1rem; font-weight:800; color:#555;
                transition:background 0.15s; display:flex; align-items:center; justify-content:center;
            }
            .qty-btn-d:hover { background:#ff5722; color:#fff; }
            .qty-in-d {
                width:44px; height:38px; border:none;
                border-left:1.5px solid #e0e0e0; border-right:1.5px solid #e0e0e0;
                text-align:center; font-size:0.95rem; font-weight:700; outline:none; color:#222;
            }
            .qty-in-d::-webkit-outer-spin-button,
            .qty-in-d::-webkit-inner-spin-button { -webkit-appearance:none; }
            .qty-in-d[type=number] { -moz-appearance:textfield; }

            .btn-add {
                background:linear-gradient(90deg,#ff5722,#ff9800);
                color:#fff; border:none; border-radius:10px;
                padding:0 20px; height:38px; font-size:0.88rem; font-weight:700;
                cursor:pointer; white-space:nowrap;
                transition:opacity 0.2s, transform 0.15s;
                box-shadow:0 2px 8px rgba(255,87,34,0.25);
            }
            .btn-add:hover { opacity:0.9; transform:translateY(-1px); }

            .menu-header {
                font-size:0.75rem; font-weight:800; text-transform:uppercase;
                letter-spacing:1px; color:#bbb; padding:0 0 14px; margin-bottom:4px;
                border-bottom:2px solid #f0f0f0;
                display:flex; justify-content:space-between; align-items:center;
            }
            .menu-count { background:#fff3e0;color:#ff5722;border-radius:10px;padding:2px 10px;font-weight:700; }

            
            /* Button states */
            .btn-add.adding { background:linear-gradient(90deg,#4caf50,#66bb6a) !important; transform:scale(0.95); pointer-events:none; }

            
            .btn-add.added  { background:linear-gradient(90deg,#4caf50,#66bb6a) !important; }
            /* Floating cart */
            #cartFab { transition:transform 0.2s,box-shadow 0.2s; }
            #cartFab:hover { transform:translateY(-2px); box-shadow:0 10px 28px rgba(255,87,34,0.55) !important; color:#fff; text-decoration:none; }
            #cartFabCount { background:#fff; color:#ff5722; border-radius:50%; width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:900; }
            @media(max-width:640px) {
                .dish-img-wrap { width:120px; }
                .dish-img-wrap img { min-height:120px; }
                .dish-body { padding:12px 14px; }
            }
            </style>

            <!-- Menu heading -->
            <div style="max-width:1000px;margin:0 auto;padding:0 24px;"><div class="menu-header">
                <span><i class="fa fa-cutlery" style="color:#ff5722;margin-right:6px;"></i> Menu</span>
                <span class="menu-count">
                    <?php
                    $cnt = mysqli_num_rows(mysqli_query($db, "SELECT d_id FROM dishes WHERE rs_id='".intval($_GET['res_id'])."'"));
                    echo $cnt.' DISH'.($cnt!=1?'ES':'');
                    ?>
                </span>
            </div>

            <?php
            // Ensure dish discount column exists
            mysqli_query($db, "ALTER TABLE dishes ADD COLUMN IF NOT EXISTS `discount_pct` decimal(5,2) NOT NULL DEFAULT 0");

            // If a specific dish was requested from home page, show it first
            $highlight_id = isset($_GET['dish_id']) ? intval($_GET['dish_id']) : 0;
            $res_id_q = intval($_GET['res_id']);

            if($highlight_id > 0) {
                $stmt = $db->prepare("SELECT * FROM dishes WHERE rs_id=? ORDER BY (d_id = ?) DESC");
                $stmt->bind_param("ii", $res_id_q, $highlight_id);
            } else {
                $stmt = $db->prepare("SELECT * FROM dishes WHERE rs_id=?");
                $stmt->bind_param("i", $res_id_q);
            }
            $stmt->execute();
            $products = $stmt->get_result();

            if($products && $products->num_rows > 0):
                foreach($products as $product):
                    $orig_price    = floatval($product['price']);
                    $dish_own_disc = $product['discount_pct'];

                    // Logic:
                    // - dish discount_pct = NULL  → use restaurant discount
                    // - dish discount_pct = 0     → no discount (explicitly set by restaurant)
                    // - dish discount_pct > 0     → use dish-specific discount
                    // We track "was it explicitly set?" by checking if the DB value is exactly 0
                    // vs never touched (NULL). After user sets to 0 from dashboard, it means "no discount"
                    // We distinguish by adding a flag col OR by checking if res_disc > 0 and dish=0 = explicitly zero

                    // Simple approach: if dish has its own discount set (via dashboard), use it.
                    // Otherwise fall back to restaurant discount.
                    // Dashboard saves actual value, so once user sets a dish discount, it overrides.
                    // To give restaurant discount to ALL dishes: set restaurant discount.
                    // To give specific dish its OWN discount (overriding restaurant): set dish discount.
                    // To EXCLUDE a specific dish from restaurant discount: set dish discount to -1 (we treat -1 as 0)

                    if($dish_own_disc === null) {
                        // Never set — use restaurant discount
                        $disc_pct = isset($res_disc) ? floatval($res_disc) : 0;
                    } elseif(floatval($dish_own_disc) < 0) {
                        // Explicitly excluded from any discount
                        $disc_pct = 0;
                    } elseif(floatval($dish_own_disc) > 0) {
                        // Has its own specific discount — use it, ignore restaurant discount
                        $disc_pct = floatval($dish_own_disc);
                    } else {
                        // Set to exactly 0 — use restaurant discount as fallback
                        $disc_pct = isset($res_disc) ? floatval($res_disc) : 0;
                    }

                    $sale_price = $disc_pct > 0 ? round($orig_price * (1 - $disc_pct/100), 2) : $orig_price;
                    $saving     = $orig_price - $sale_price;
            ?>
            <div class="dish-card" id="dish-<?php echo $product['d_id']; ?>"
                 <?php if($highlight_id > 0 && $product['d_id'] == $highlight_id): ?>
                 style="border:2px solid #ff5722;box-shadow:0 0 0 3px rgba(255,87,34,0.15),0 4px 20px rgba(255,87,34,0.15);"
                 <?php endif; ?>>
                <?php if($highlight_id > 0 && $product['d_id'] == $highlight_id): ?>
                <div style="position:absolute;top:10px;right:10px;background:linear-gradient(135deg,#ff5722,#ff9800);color:#fff;font-size:0.7rem;font-weight:900;padding:4px 10px;border-radius:20px;z-index:5;box-shadow:0 2px 8px rgba(255,87,34,0.4);letter-spacing:0.3px;white-space:nowrap;">
                    ✦ Your Pick
                </div>
                <?php endif; ?>
                <div class="dish-img-wrap">
                    <img src="admin/Res_img/dishes/<?php echo htmlspecialchars($product['img']); ?>"
                         alt="<?php echo htmlspecialchars($product['title']); ?>"
                         onerror="this.src='images/icn.png'">
                    <?php if($disc_pct > 0): ?>
                    <div class="disc-badge"><?php echo round($disc_pct); ?>% OFF</div>
                    <?php endif; ?>
                </div>
                <div class="dish-body">
                    <div class="dish-name"><?php echo htmlspecialchars($product['title']); ?></div>
                    <?php
                    $dish_key_r = strtolower($product['title']);
                    if(!empty($dish_ratings_map[$dish_key_r])):
                        $d_avg   = floatval($dish_ratings_map[$dish_key_r]['avg_r']);
                        $d_total = intval($dish_ratings_map[$dish_key_r]['total_r']);
                    ?>
                    <div style="display:flex;align-items:center;gap:4px;margin-bottom:6px;">
                        <?php
                        $d_full = floor($d_avg); $d_half = ($d_avg - $d_full) >= 0.5;
                        for($si=1;$si<=5;$si++):
                            if($si<=$d_full) echo '<i class="fa fa-star" style="color:#ff9800;font-size:0.78rem;"></i>';
                            elseif($si==$d_full+1 && $d_half) echo '<i class="fa fa-star-half-o" style="color:#ff9800;font-size:0.78rem;"></i>';
                            else echo '<i class="fa fa-star-o" style="color:#ddd;font-size:0.78rem;"></i>';
                        endfor;
                        ?>
                        <span style="font-size:0.78rem;font-weight:700;color:#ff9800;margin-left:2px;"><?php echo $d_avg; ?></span>
                        <span style="font-size:0.72rem;color:#bbb;">(<?php echo $d_total; ?>)</span>
                    </div>
                    <?php endif; ?>
                    <div class="dish-desc"><?php echo htmlspecialchars(substr($product['slogan'],0,120)); ?><?php echo strlen($product['slogan'])>120?'…':''; ?></div>
                    <div class="dish-footer">
                        <div class="price-block">
                            <?php if($disc_pct > 0): ?>
                            <span class="price-orig">₹<?php echo number_format($orig_price,2); ?></span>
                            <span class="price-main">₹<?php echo number_format($sale_price,2); ?></span>
                            <span class="price-save">You save ₹<?php echo number_format($saving,2); ?></span>
                            <?php else: ?>
                            <span class="price-main">₹<?php echo number_format($orig_price,2); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="add-row">
                            <div class="qty-wrap-d">
                                <button type="button" class="qty-btn-d" onclick="chQty('<?php echo $product['d_id']; ?>',-1)">−</button>
                                <input type="number" class="qty-in-d" id="qty_<?php echo $product['d_id']; ?>" value="1" min="1" max="99">
                                <button type="button" class="qty-btn-d" onclick="chQty('<?php echo $product['d_id']; ?>',1)">+</button>
                            </div>
                            <button type="button" class="btn-add"
                                    data-dish-id="<?php echo $product['d_id']; ?>"
                                    onclick="addToCart(<?php echo $product['d_id']; ?>, '<?php echo addslashes($product['title']); ?>')">
                                <i class="fa fa-plus"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
            </div>
                </div><!-- /dish-card -->
            <?php endforeach;
            else: ?>
            <div style="text-align:center;padding:40px;color:#aaa;">
                <i class="fa fa-cutlery fa-3x" style="opacity:0.2;display:block;margin-bottom:12px;"></i>
                No dishes available yet.
            </div>
            <?php endif; ?>
        </div><!-- /max-width wrapper -->
        </div><!-- /grey section -->

        <!-- Footer -->
        <footer style="background:#222;color:#ccc;padding:30px 0;margin-top:40px;">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-sm-4" style="margin-bottom:16px;">
                        <h5 style="color:#ffcb05;font-weight:700;margin-bottom:10px;">Address</h5>
                        <p style="margin:0;font-size:0.9rem;">103, Time Square, Sindhubhavan</p>
                        <p style="margin:4px 0 0;font-size:0.9rem;">📞 8780091312</p>
                    </div>
                    <div class="col-xs-12 col-sm-5">
                        <h5 style="color:#ffcb05;font-weight:700;margin-bottom:10px;">Additional Information</h5>
                        <p style="margin:0;font-size:0.9rem;">Join thousands of other restaurants who benefit from having partnered with us.</p>
                    </div>
                </div>
                <div style="border-top:1px solid rgba(255,255,255,0.1);margin-top:20px;padding-top:14px;text-align:center;font-size:0.8rem;color:#666;">
                    O.F.O.S &copy; <?php echo date('Y'); ?> — Delicious food at your doorstep 🍕
                </div>
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="js/jquery.min.js"></script>
    <script src="js/tether.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/animsition.min.js"></script>
    <script src="js/bootstrap-slider.min.js"></script>
    <script src="js/jquery.isotope.min.js"></script>
    <script src="js/headroom.js"></script>
    <script src="js/foodpicky.min.js"></script>
<script>
var cartCount = 0;
var toastTimer;

function chQty(id, delta) {
    var inp = document.getElementById('qty_' + id);
    var v = parseInt(inp.value || 1) + delta;
    inp.value = Math.min(99, Math.max(1, v));
}

function addToCart(id, name) {
    var qty = parseInt(document.getElementById('qty_' + id).value || 1);
    var btn = document.querySelector('[data-dish-id="' + id + '"]');

    // Button bounce + green state
    if(btn) {
        btn.classList.add('adding');
        btn.innerHTML = '<i class="fa fa-check"></i> Added!';
        setTimeout(function() {
            btn.classList.remove('adding');
            btn.classList.add('added');
            btn.innerHTML = '<i class="fa fa-plus"></i> Add';
            setTimeout(function() { btn.classList.remove('added'); }, 1500);
        }, 700);
    }

    var fd = new FormData();
    fd.append('quantity', qty);

    // Reset qty to 1
    document.getElementById('qty_' + id).value = 1;

    fetch('cart.php?action=add&id=' + id, { method:'POST', body:fd })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        cartCount = d.cart_count || (cartCount + qty);
        showCartToast(name, qty);
        showFab();
    })
    .catch(function() {
        cartCount += qty;
        showCartToast(name, qty);
        showFab();
    });
}

function showCartToast(name, qty) {
    var box    = document.getElementById('cartToastBox');
    var inner  = document.getElementById('cartToastInner');
    var msg    = document.getElementById('cartToastMsg');

    msg.textContent = name + (qty > 1 ? ' × ' + qty : '') + ' added successfully';
    box.style.display = 'block';
    inner.style.animation = 'none';
    inner.offsetHeight; // reflow
    inner.style.animation = 'toastPop 0.45s cubic-bezier(0.34,1.56,0.64,1) forwards';

    clearTimeout(toastTimer);
    toastTimer = setTimeout(function() {
        inner.style.animation = 'toastFade 0.3s ease forwards';
        setTimeout(function() { box.style.display = 'none'; }, 300);
    }, 3000);
}

function showFab() {
    var fab = document.getElementById('cartFab');
    var cnt = document.getElementById('cartFabCount');
    fab.style.display = 'inline-flex';
    cnt.textContent = cartCount;
    fab.style.animation = 'none';
    fab.offsetHeight;
    fab.style.animation = 'fabPop 0.4s cubic-bezier(0.34,1.56,0.64,1)';
}

function copyCoupon(code) {
    navigator.clipboard.writeText(code).then(function() {
        showCartToast('Code "' + code + '" copied! Paste at checkout.', 0);
    }).catch(function() {
        prompt('Copy this code:', code);
    });
}
</script>
</body>
</html>