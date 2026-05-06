<?php
// Shared navbar — include this in every front-end page
// Requires: $db (connection), session_start() already called
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
  /* ===== SHARED NAVBAR STYLES ===== */
  #main-nav {
    background: rgba(30,30,40,0.97);
    box-shadow: 0 2px 12px rgba(0,0,0,0.25);
    position: sticky;
    top: 0;
    z-index: 1050;
    width: 100%;
  }
  #main-nav .nav-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    height: 58px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  #main-nav .nav-brand {
    display: flex; align-items: center; gap: 10px;
    text-decoration: none; color: #ffcb05;
    font-size: 1.2rem; font-weight: 800;
    flex-shrink: 0;
  }
  #main-nav .nav-brand img {
    height: 34px; border-radius: 6px;
  }
  #main-nav .nav-brand span { color: #ffcb05; }
  #main-nav .nav-items {
    display: flex; align-items: center; gap: 4px;
  }
  #main-nav .nav-items a,
  #main-nav .nav-items .nav-dd > a {
    color: rgba(255,255,255,0.82);
    text-decoration: none;
    padding: 8px 13px;
    border-radius: 8px;
    font-size: 0.88rem;
    font-weight: 500;
    display: flex; align-items: center; gap: 6px;
    transition: background 0.18s, color 0.18s;
    cursor: pointer;
    white-space: nowrap;
  }
  #main-nav .nav-items a:hover,
  #main-nav .nav-items .nav-dd:hover > a {
    background: rgba(255,255,255,0.1);
    color: #fff;
  }
  #main-nav .nav-items a.nav-current {
    background: rgba(255,87,34,0.22);
    color: #ff9800;
  }
  /* Dropdown */
  #main-nav .nav-dd { position: relative; }
  #main-nav .nav-dd-menu {
    display: none;
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 6px 24px rgba(0,0,0,0.18);
    min-width: 175px;
    padding: 6px 0;
    z-index: 2000;
  }
  #main-nav .nav-dd:hover .nav-dd-menu { display: block; }
  #main-nav .nav-dd-menu a {
    display: block !important;
    padding: 9px 16px !important;
    color: #333 !important;
    font-size: 0.87rem !important;
    background: none !important;
    text-decoration: none !important;
    border-radius: 0 !important;
  }
  #main-nav .nav-dd-menu a:hover {
    background: #fff5f2 !important;
    color: #ff5722 !important;
  }
  #main-nav .nav-dd-menu hr { margin: 4px 0; border-color: #f0f0f0; }
  /* Mobile toggle */
  #main-nav .nav-toggle {
    display: none;
    background: none; border: 1px solid rgba(255,255,255,0.3);
    color: #fff; padding: 6px 10px; border-radius: 6px;
    cursor: pointer; font-size: 1.1rem;
  }
  @media(max-width: 768px) {
    #main-nav .nav-toggle { display: block; }
    #main-nav .nav-items {
      display: none;
      position: absolute; top: 58px; left: 0; right: 0;
      background: rgba(30,30,40,0.98);
      flex-direction: column; align-items: stretch;
      padding: 10px 0; gap: 2px;
      border-top: 1px solid rgba(255,255,255,0.08);
    }
    #main-nav .nav-items.open { display: flex; }
    #main-nav .nav-dd-menu {
      position: static;
      box-shadow: none; border-radius: 0;
      background: rgba(255,255,255,0.05);
      padding: 0;
    }
    #main-nav .nav-dd-menu a { color: rgba(255,255,255,0.7) !important; }
    #main-nav .nav-dd-menu a:hover { background: rgba(255,87,34,0.2) !important; color: #ff9800 !important; }
  }
</style>

<nav id="main-nav">
  <div class="nav-container">
    <!-- Brand -->
    <a class="nav-brand" href="index.php">
      <img src="images/icn.png" alt="O.F.O.S" onerror="this.style.display='none'">
      <span></span>
    </a>

    <!-- Mobile toggle button -->
    <button class="nav-toggle" onclick="document.querySelector('#main-nav .nav-items').classList.toggle('open')">
      &#9776;
    </button>

    <!-- Nav links -->
    <div class="nav-items">
      <a href="index.php" <?php echo $current_page=='index.php'?'class="nav-current"':''; ?>>
        <i class="fa fa-home"></i> Home
      </a>
      <a href="restaurants.php" <?php echo $current_page=='restaurants.php'?'class="nav-current"':''; ?>>
        <i class="fa fa-building-o"></i> Restaurants
      </a>

      <!-- Menu dropdown -->
      <div class="nav-dd">
        <a><i class="fa fa-cutlery"></i> Menu <i class="fa fa-caret-down" style="font-size:0.75rem;margin-left:2px;"></i></a>
        <div class="nav-dd-menu">
          <a href="restaurants.php"><i class="fa fa-th-large" style="color:#ff5722;margin-right:6px;"></i>All Restaurants</a>
          <hr>
          <?php
          $__cats = mysqli_query($db, "SELECT * FROM res_category ORDER BY c_name");
          if($__cats) while($__c = mysqli_fetch_array($__cats))
            echo '<a href="restaurants.php?category='.intval($__c['c_id']).'">'.htmlspecialchars($__c['c_name']).'</a>';
          ?>
        </div>
      </div>

      <!-- Partner dropdown 
      <div class="nav-dd">
        <a><i class="fa fa-motorcycle"></i> Partners <i class="fa fa-caret-down" style="font-size:0.75rem;margin-left:2px;"></i></a>
        <div class="nav-dd-menu">
          <a href="delivery_register.php" <?php echo $current_page=='delivery_register.php'?'style="color:#ff5722!important;background:#fff5f2!important;"':''; ?>>
            <i class="fa fa-user-plus" style="color:#1565c0;margin-right:6px;"></i>Become a Delivery Partner
          </a>
          <a href="delivery_login.php" <?php echo $current_page=='delivery_login.php'?'style="color:#ff5722!important;background:#fff5f2!important;"':''; ?>>
            <i class="fa fa-motorcycle" style="color:#0097a7;margin-right:6px;"></i>Delivery Partner Login
          </a>
        </div>
      </div>
-->

      <?php if($current_page !== 'login.php'): ?>
        <?php if(empty($_SESSION["user_id"])): ?>
          <a href="login.php" <?php echo $current_page=='login.php'?'class="nav-current"':''; ?>>
            <i class="fa fa-sign-in"></i> Login
          </a>
        <?php else: ?>
          <a href="your_orders.php" <?php echo $current_page=='your_orders.php'?'class="nav-current"':''; ?>>
            <i class="fa fa-list-alt"></i> My Orders
          </a>
          <a href="cart.php" <?php echo $current_page=='cart.php'?'class="nav-current"':''; ?> style="position:relative;">
            <i class="fa fa-shopping-cart"></i> Cart
            <?php
              $__cart_total = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
              if($__cart_total > 0):
            ?>
            <span id="navCartBadge" style="position:absolute;top:-4px;right:-6px;background:#ff5722;color:#fff;font-size:0.62rem;font-weight:900;min-width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;line-height:1;border:2px solid rgba(30,30,40,0.97);">
              <?php echo $__cart_total > 99 ? '99+' : $__cart_total; ?>
            </span>
            <?php endif; ?>
          </a>
          <a href="logout.php">
            <i class="fa fa-sign-out"></i> Logout
          </a>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</nav>
