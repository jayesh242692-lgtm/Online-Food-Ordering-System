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
    <title>Restaurants</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #fafafa; margin: 0; }
        header { background: rgba(0,0,0,0.85); }
        .navbar-brand img { height: 45px; }
        .inner-page-hero {
            background: url('https://images.unsplash.com/photo-1414235077428-338989a2e8c0') no-repeat center center/cover;
            height: 260px; display:flex; align-items:center; justify-content:center; color:#fff; text-align:center; position:relative;
        }
        .inner-page-hero::before { content:""; position:absolute; inset:0; background:rgba(0,0,0,0.5); }
        .inner-page-hero .hero-text { position:relative; z-index:2; }
        .inner-page-hero h1 { font-size:2.5rem; font-weight:600; }
        /* Category filter bar */
        .filter-bar { background:#fff; border-bottom:1px solid #eee; padding:12px 0; }
        .filter-bar .filter-wrap { display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
        .filter-bar a {
            padding:6px 18px; border-radius:20px; font-size:0.85rem;
            border:1.5px solid #ddd; color:#555; text-decoration:none; transition:0.2s;
        }
        .filter-bar a:hover, .filter-bar a.active { background:#ff5722; border-color:#ff5722; color:#fff; }
        .filter-bar .count-label { margin-left:auto; color:#888; font-size:0.85rem; }
        /* Restaurant card */
        .restaurant-entry {
            margin: 18px 0; padding:18px; border-radius:12px;
            background:#fff; box-shadow:0 4px 15px rgba(0,0,0,0.07);
            transition: box-shadow 0.2s;
        }
        .restaurant-entry:hover { box-shadow:0 6px 22px rgba(255,87,34,0.13); }
        .entry-logo img { width:90px; height:90px; border-radius:10px; object-fit:cover; }
        .entry-dscr h5 { margin:0; font-weight:600; color:#333; }
        .entry-dscr span { font-size:0.88rem; color:#666; }
        .badge-cat { background:#fff3e0; color:#ff5722; border-radius:10px; padding:3px 10px; font-size:0.75rem; font-weight:600; display:inline-block; margin-bottom:6px; }
        .btn-purple { background:#ff5722; border:none; padding:7px 16px; border-radius:8px; color:#fff; font-size:0.85rem; transition:0.2s; }
        .btn-purple:hover { background:#e64a19; color:#fff; }
        .no-results { text-align:center; padding:50px; color:#aaa; }
        /* Nav dropdown */
        .nav-item.dropdown { position:relative; }
        .nav-dropdown-menu {
            display:none; position:absolute; z-index:999;
            background:#fff; border-radius:8px; box-shadow:0 4px 15px rgba(0,0,0,0.15);
            min-width:180px; top:100%; left:0; padding:8px 0;
        }
        .nav-item.dropdown:hover .nav-dropdown-menu { display:block; }
        .nav-dropdown-menu a { display:block; padding:8px 16px; color:#333; font-size:0.9rem; text-decoration:none; }
        .nav-dropdown-menu a:hover { background:#ff5722; color:#fff; }
        footer { background:rgba(0,0,0,0.85); color:#fff; padding:30px 0; margin-top:40px; }
        footer h5 { font-weight:600; }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include('navbar.php'); ?>

    <!-- Hero Section -->
    <section class="inner-page-hero">
        <div class="hero-text">
            <h1>Explore Restaurants 🍴</h1>
            <p style="font-size:1rem;">Find the best places to eat near you</p>
        </div>
    </section>

    <!-- Category Filter Bar -->
    <?php
    $cat_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
    $all_cats   = [];
    $cat_q      = mysqli_query($db,"SELECT * FROM res_category ORDER BY c_name");
    while($cr = mysqli_fetch_array($cat_q)) $all_cats[] = $cr;

    // Build restaurant query
    if($cat_filter > 0) {
        $res_q = mysqli_query($db,"SELECT r.*, rc.c_name FROM restaurant r JOIN res_category rc ON r.c_id=rc.c_id WHERE r.c_id=$cat_filter");
    } else {
        $res_q = mysqli_query($db,"SELECT r.*, rc.c_name FROM restaurant r JOIN res_category rc ON r.c_id=rc.c_id");
    }
    $res_list = [];
    while($row = mysqli_fetch_assoc($res_q)) $res_list[] = $row;

    // Fetch average ratings per restaurant from orders
    $ratings_map = [];
    $rq = mysqli_query($db,"SELECT rs_id, ROUND(AVG(rating),1) as avg_r, COUNT(rating) as total_r FROM users_orders WHERE rating IS NOT NULL AND status='closed' GROUP BY rs_id");
    if($rq) while($rr = mysqli_fetch_assoc($rq)) $ratings_map[$rr['rs_id']] = $rr;
    ?>
    <div class="filter-bar">
        <div class="container">
            <div class="filter-wrap">
                <a href="restaurants.php" class="<?php echo $cat_filter==0?'active':''; ?>">
                    <i class="fa fa-th-large"></i> All
                </a>
                <?php foreach($all_cats as $ac): ?>
                <a href="restaurants.php?category=<?php echo $ac['c_id']; ?>"
                   class="<?php echo $cat_filter==$ac['c_id']?'active':''; ?>">
                    <?php echo htmlspecialchars($ac['c_name']); ?>
                </a>
                <?php endforeach; ?>
                <span class="count-label"><?php echo count($res_list); ?> restaurant(s) found</span>
            </div>
        </div>
    </div>

    <!-- Restaurant Listings -->
    <section class="restaurants-page">
        <div class="container">
            <?php if(empty($res_list)): ?>
            <div class="no-results">
                <i class="fa fa-search fa-3x" style="margin-bottom:12px; display:block;"></i>
                <p>No restaurants found for this category.</p>
                <a href="restaurants.php" class="btn-purple" style="text-decoration:none;padding:10px 22px;border-radius:8px;">View All</a>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach($res_list as $rows): ?>
                <div class="col-md-6">
                    <div class="restaurant-entry row">
                        <div class="col-4 entry-logo">
                            <a href="dishes.php?res_id=<?php echo $rows['rs_id']; ?>">
                                <img src="admin/Res_img/<?php echo htmlspecialchars($rows['image']); ?>"
                                     alt="<?php echo htmlspecialchars($rows['title']); ?>"
                                     onerror="this.src='images/icn.png'">
                            </a>
                        </div>
                        <div class="col-8 entry-dscr">
                            <div class="badge-cat"><i class="fa fa-tag"></i> <?php echo htmlspecialchars($rows['c_name']); ?></div>
                            <h5><a href="dishes.php?res_id=<?php echo $rows['rs_id']; ?>" style="color:#333;text-decoration:none;"><?php echo htmlspecialchars($rows['title']); ?></a></h5>
                            <span><?php echo htmlspecialchars(substr($rows['address'],0,80)); ?>...</span><br>
                            <small style="color:#ff5722;"><i class="fa fa-clock-o"></i> <?php echo $rows['o_hr'].' – '.$rows['c_hr']; ?> &nbsp;|&nbsp; <?php echo $rows['o_days']; ?></small><br>
                            <?php
                            $rs_id_r = $rows['rs_id'];
                            if(!empty($ratings_map[$rs_id_r])):
                                $avg_r   = floatval($ratings_map[$rs_id_r]['avg_r']);
                                $total_r = intval($ratings_map[$rs_id_r]['total_r']);
                                $full    = floor($avg_r);
                                $half    = ($avg_r - $full) >= 0.5;
                            ?>
                            <div style="display:flex;align-items:center;gap:5px;margin-top:5px;">
                                <span style="display:flex;gap:2px;">
                                <?php for($si=1;$si<=5;$si++): ?>
                                    <?php if($si<=$full): ?>
                                        <i class="fa fa-star" style="color:#ff9800;font-size:0.8rem;"></i>
                                    <?php elseif($si==$full+1 && $half): ?>
                                        <i class="fa fa-star-half-o" style="color:#ff9800;font-size:0.8rem;"></i>
                                    <?php else: ?>
                                        <i class="fa fa-star-o" style="color:#ddd;font-size:0.8rem;"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                </span>
                                <span style="font-size:0.8rem;font-weight:700;color:#ff9800;"><?php echo $avg_r; ?></span>
                                <span style="font-size:0.75rem;color:#aaa;">(<?php echo $total_r; ?> review<?php echo $total_r!=1?'s':''; ?>)</span>
                            </div>
                            <?php else: ?>
                            <div style="font-size:0.75rem;color:#ccc;margin-top:5px;"><i class="fa fa-star-o"></i> No ratings yet</div>
                            <?php endif; ?>
                            <a href="dishes.php?res_id=<?php echo $rows['rs_id']; ?>" class="btn btn-purple mt-2">View Menu</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Address</h5>
                    <p>103, Time Square, Sindhubhavan</p>
                    <p>📞 8780091312</p>
                </div>
                <div class="col-md-8">
                    <h5>Grow with Us</h5>
                    <p>Over 1,000+ restaurants trust O.F.O.S Hub to grow their business. Join today and start reaching more hungry customers!</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
