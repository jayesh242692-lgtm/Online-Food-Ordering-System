<!DOCTYPE html>
<html lang="en">
<?php
include("connection/connect.php");  
error_reporting(0);  
session_start(); 
?>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">   
  <title>Home</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/font-awesome.min.css" rel="stylesheet">
  <link href="css/animsition.min.css" rel="stylesheet">
  <link href="css/animate.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
  <style>
    .hero {
      position: relative;
      background: url('https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=1600&q=80') no-repeat center center/cover;
      height: 110vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .hero::before {
      content: "";
      position: absolute; top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.55);
    }
    .hero-inner {
      position: relative; z-index: 2;
      color: #fff; text-align: center;
      width: 100%; max-width: 720px; padding: 0 20px;
    }
    .hero-inner h1 {
      font-size: 3rem; font-weight: 700; margin-bottom: 15px;
      color: #ffcb05; text-shadow: 1px 1px 6px rgba(0,0,0,1);
    }
    .search-hero-form {
      margin-top: 20px; display: flex; justify-content: center;
    }
    .search-hero-form input[type="text"] {
      width: 65%; padding: 14px 20px; border: none;
      border-radius: 30px 0 0 30px; font-size: 1rem; outline: none;
    }
    .search-hero-form button {
      padding: 14px 28px; background: #ff5722; color: #fff;
      border: none; border-radius: 0 30px 30px 0;
      font-size: 1rem; cursor: pointer; transition: background 0.2s;
    }
    .search-hero-form button:hover { background: #e64a19; }
    .category-tabs { margin-top: 14px; display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; }
    .category-tabs a {
      background: rgba(255,255,255,0.15); color: #fff;
      border: 1px solid rgba(255,255,255,0.4); border-radius: 20px;
      padding: 6px 18px; font-size: 0.85rem; text-decoration: none; transition: background 0.2s;
    }
    .category-tabs a:hover, .category-tabs a.active { background: #ff5722; border-color: #ff5722; }
    .nav-dropdown-menu {
      display: none; position: absolute; z-index: 999;
      background: #fff; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.15);
      min-width: 180px; top: 100%; left: 0; padding: 8px 0;
    }
    .nav-item.dropdown:hover .nav-dropdown-menu { display: block; }
    .nav-dropdown-menu a {
      display: block; padding: 8px 16px; color: #333;
      font-size: 0.9rem; text-decoration: none;
    }
    .nav-dropdown-menu a:hover { background: #ff5722; color: #fff; }
    .nav-item.dropdown { position: relative; }
    .search-results-section { padding: 30px 0 10px; background: #fff9f5; }
    .search-results-section h3 { color: #ff5722; margin-bottom: 20px; }
    .no-results { color: #888; text-align: center; padding: 30px; }
    .result-card { border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; background: #fff; }
    .result-card .card-img { height: 175px; background-size: cover; background-position: center; }
    .result-card .card-body { padding: 14px; }
    .result-card .res-tag { color: #ff5722; font-size: 0.82rem; font-weight: 600; }
    .result-card .desc { font-size: 0.78rem; color: #777; margin: 4px 0 10px; }
    .result-card .price { font-weight: 700; color: #ff5722; font-size: 1rem; }
    .footer { background: #222; color: #ccc; padding: 30px 0; font-size: 0.9rem; }
    .footer h5 { color: #ffcb05; }
    .footer a { color: #ff5722; text-decoration: none; }
    .footer a:hover { color: #e64a19; }
  </style>
</head>
<body class="home"> 
  <!-- Header -->
  <?php include('navbar.php'); ?>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-inner">
      <h1>Delicious Food, Anytime 🍔</h1>
      <p class="lead">Order delivery &amp; take-out from your favorite restaurants</p>

      <!-- Search Bar -->
      <form class="search-hero-form" method="GET" action="index.php">
        <input type="text" name="search" 
               placeholder="Search dishes, restaurants, cuisine..."
               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <button type="submit"><i class="fa fa-search"></i> Search</button>
      </form>

      <!-- Quick Category Filter Tabs -->
      <div class="category-tabs">
        <a href="index.php" class="<?php echo (!isset($_GET['cat']) && !isset($_GET['search'])) ? 'active' : ''; ?>">All</a>
        <?php
          $cat_tabs = mysqli_query($db,"SELECT * FROM res_category ORDER BY c_name");
          while($ct = mysqli_fetch_array($cat_tabs)) {
            $a = (isset($_GET['cat']) && $_GET['cat'] == $ct['c_id']) ? 'active' : '';
            echo '<a href="index.php?cat='.intval($ct['c_id']).'" class="'.$a.'">'.htmlspecialchars($ct['c_name']).'</a>';
          }
        ?>
      </div>
    </div>
  </section>

  <?php
  $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
  $cat_filter  = isset($_GET['cat'])    ? intval($_GET['cat'])  : 0;

  if($search_term !== '' || $cat_filter > 0):
    if($search_term !== '' && $cat_filter > 0) {
      $safe = mysqli_real_escape_string($db, $search_term);
      $q = "SELECT d.*, r.title as res_name, r.rs_id FROM dishes d 
            JOIN restaurant r ON d.rs_id = r.rs_id 
            WHERE (d.title LIKE '%$safe%' OR d.slogan LIKE '%$safe%' OR r.title LIKE '%$safe%')
            AND r.c_id = $cat_filter";
    } elseif($search_term !== '') {
      $safe = mysqli_real_escape_string($db, $search_term);
      $q = "SELECT d.*, r.title as res_name, r.rs_id FROM dishes d 
            JOIN restaurant r ON d.rs_id = r.rs_id 
            WHERE d.title LIKE '%$safe%' OR d.slogan LIKE '%$safe%' OR r.title LIKE '%$safe%'";
    } else {
      $q = "SELECT d.*, r.title as res_name, r.rs_id FROM dishes d 
            JOIN restaurant r ON d.rs_id = r.rs_id WHERE r.c_id = $cat_filter";
    }
    $search_res = mysqli_query($db, $q);
    $count = mysqli_num_rows($search_res);
  ?>
  <section class="search-results-section">
    <div class="container">
      <h3>
        <?php
          if($search_term) echo 'Results for: <em>"'.htmlspecialchars($search_term).'"</em>';
          elseif($cat_filter) {
            $cn_q = mysqli_query($db,"SELECT c_name FROM res_category WHERE c_id=$cat_filter");
            $cn_r = mysqli_fetch_array($cn_q);
            echo 'Category: <em>'.htmlspecialchars($cn_r['c_name']).'</em>';
          }
          echo ' <small style="color:#aaa;font-size:0.85rem;">— '.$count.' item(s)</small>';
        ?>
      </h3>
      <?php if($count == 0): ?>
        <p class="no-results"><i class="fa fa-search fa-2x" style="margin-bottom:10px;display:block;"></i>No dishes found. Try a different search or category.</p>
      <?php else: ?>
      <div class="row">
        <?php while($r = mysqli_fetch_array($search_res)): ?>
        <div class="col-xs-12 col-sm-6 col-md-4">
          <div class="result-card">
            <div class="card-img" style="background-image:url('admin/Res_img/dishes/<?php echo $r['img']; ?>')"></div>
            <div class="card-body">
              <h5 style="margin:0 0 2px;"><a href="dishes.php?res_id=<?php echo $r['rs_id']; ?>" style="color:#222;text-decoration:none;"><?php echo htmlspecialchars($r['title']); ?></a></h5>
              <div class="res-tag"><i class="fa fa-building-o"></i> <?php echo htmlspecialchars($r['res_name']); ?></div>
              <div class="desc"><?php echo substr(htmlspecialchars($r['slogan']),0,90).'...'; ?></div>
              <div style="display:flex;justify-content:space-between;align-items:center;">
                <span class="price">₹<?php echo $r['price']; ?></span>
                <a href="dishes.php?res_id=<?php echo $r['rs_id']; ?>" class="btn btn-sm" style="background:#ff5722;color:#fff;border-radius:20px;padding:5px 14px;font-size:0.8rem;">Order Now</a>
              </div>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- Popular Dishes -->
  <section style="padding:50px 0 60px; background:#f4f6f9;">
    <div class="container">
      <div style="text-align:center; margin-bottom:36px;">
        <h2 style="font-size:1.8rem;font-weight:800;color:#1a1a2e;margin-bottom:8px;">Popular Dishes 🍽️</h2>
        <p style="color:#aaa;font-size:0.95rem;">Order your favourite food from the best restaurants near you</p>
      </div>

      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;">
        <?php
        $query_res = mysqli_query($db,"SELECT d.*, r.title as res_name, r.rs_id, r.discount_pct as res_disc_pct FROM dishes d LEFT JOIN restaurant r ON d.rs_id=r.rs_id LIMIT 9");
        while($r = mysqli_fetch_array($query_res)):
          $dish_disc = $r['discount_pct'];
          $res_disc_home = floatval($r['res_disc_pct'] ?? 0);
          // Same priority logic as dishes.php
          if($dish_disc === null) {
            $disc = $res_disc_home;
          } elseif(floatval($dish_disc) > 0) {
            $disc = floatval($dish_disc); // dish-specific overrides
          } else {
            $disc = $res_disc_home; // fall back to restaurant
          }
          $sale = $disc > 0 ? round($r['price']*(1-$disc/100),2) : $r['price'];
        ?>
        <div style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 2px 14px rgba(0,0,0,0.07);transition:transform 0.2s,box-shadow 0.2s;position:relative;"
             onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 10px 28px rgba(255,87,34,0.13)'"
             onmouseout="this.style.transform='';this.style.boxShadow='0 2px 14px rgba(0,0,0,0.07)'">

          <!-- Dish image -->
          <div style="position:relative;overflow:hidden;height:200px;">
            <img src="admin/Res_img/dishes/<?php echo htmlspecialchars($r['img']); ?>"
                 alt="<?php echo htmlspecialchars($r['title']); ?>"
                 style="width:100%;height:100%;object-fit:cover;transition:transform 0.4s;"
                 onmouseover="this.style.transform='scale(1.06)'"
                 onmouseout="this.style.transform='scale(1)'"
                 onerror="this.src='images/icn.png'">
            <?php if($disc > 0): ?>
            <div style="position:absolute;top:10px;left:10px;background:linear-gradient(135deg,#f44336,#c62828);color:#fff;font-size:0.72rem;font-weight:900;padding:4px 10px;border-radius:7px;box-shadow:0 3px 8px rgba(244,67,54,0.4);">
              <?php echo round($disc); ?>% OFF
            </div>
            <?php endif; ?>
          </div>

          <!-- Content -->
          <div style="padding:16px 18px 18px;">
            <!-- Restaurant name -->
            <div style="font-size:0.73rem;color:#ff5722;font-weight:700;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.3px;">
              <i class="fa fa-building-o" style="margin-right:3px;"></i><?php echo htmlspecialchars($r['res_name'] ?? ''); ?>
            </div>
            <!-- Dish name -->
            <h5 style="margin:0 0 6px;font-size:1rem;font-weight:800;color:#1a1a2e;line-height:1.3;">
              <a href="dishes.php?res_id=<?php echo $r['rs_id']; ?>" style="color:inherit;text-decoration:none;">
                <?php echo htmlspecialchars($r['title']); ?>
              </a>
            </h5>
            <!-- Description -->
            <p style="font-size:0.78rem;color:#bbb;margin:0 0 14px;line-height:1.5;">
              <?php echo htmlspecialchars(substr($r['slogan'],0,80)); ?>...
            </p>
            <!-- Price + CTA -->
            <div style="display:flex;align-items:center;justify-content:space-between;">
              <div>
                <?php if($disc > 0): ?>
                <span style="font-size:0.78rem;color:#ccc;text-decoration:line-through;display:block;line-height:1;">₹<?php echo number_format($r['price'],2); ?></span>
                <?php endif; ?>
                <span style="font-size:1.15rem;font-weight:900;color:#ff5722;">₹<?php echo number_format($sale,2); ?></span>
              </div>
              <a href="dishes.php?res_id=<?php echo $r['rs_id']; ?>&dish_id=<?php echo $r['d_id']; ?>"
                 style="background:linear-gradient(90deg,#ff5722,#ff9800);color:#fff;text-decoration:none;border-radius:10px;padding:9px 18px;font-size:0.82rem;font-weight:700;box-shadow:0 3px 10px rgba(255,87,34,0.28);transition:opacity 0.2s;"
                 onmouseover="this.style.opacity='0.88'" onmouseout="this.style.opacity='1'">
                Order Now
              </a>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      </div>

      <!-- View all restaurants button -->
      <div style="text-align:center;margin-top:36px;">
        <a href="restaurants.php" style="display:inline-flex;align-items:center;gap:10px;background:#1a1a2e;color:#fff;text-decoration:none;padding:14px 32px;border-radius:12px;font-weight:700;font-size:0.95rem;box-shadow:0 4px 16px rgba(0,0,0,0.15);transition:opacity 0.2s;"
           onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
          <i class="fa fa-th-large"></i> View All Restaurants
        </a>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer style="background:#222;color:#ccc;padding:30px 0;">
    <div class="container">
      <div class="row">
        <div class="col-xs-12 col-sm-4" style="margin-bottom:16px;">
          <h5 style="color:#ffcb05;font-weight:700;margin-bottom:10px;">Address</h5>
          <p style="margin:0;font-size:0.9rem;">103, Time Square, Sindhubhavan</p>
          <p style="margin:4px 0 0;font-size:0.9rem;">📞 <a href="tel:8780091312" style="color:#ff9800;text-decoration:none;">8780091312</a></p>
        </div>
        <div class="col-xs-12 col-sm-5">
          <h5 style="color:#ffcb05;font-weight:700;margin-bottom:10px;">Additional Information</h5>
          <p style="margin:0;font-size:0.9rem;">Many restaurants are already working with us and growing their business. You too can join and enjoy the same benefits.</p>
        </div>
      </div>
      <div style="border-top:1px solid rgba(255,255,255,0.1);margin-top:20px;padding-top:14px;text-align:center;font-size:0.8rem;color:#666;">
        O.F.O.S &copy; <?php echo date('Y'); ?> — Delicious food at your doorstep 🍕
      </div>
    </div>
  </footer>

  <script src="js/jquery.min.js"></script>
  <script src="js/tether.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/animsition.min.js"></script>
  <script src="js/bootstrap-slider.min.js"></script>
  <script src="js/jquery.isotope.min.js"></script>
  <script src="js/headroom.js"></script>
  <script src="js/foodpicky.min.js"></script>
</body>
</html>
