<!DOCTYPE html>
<html lang="en">
<?php
include("../connection/connect.php");
error_reporting(0);
session_start();
if(empty($_SESSION['user_id'])) {
    header('location:../login.php');
    exit();
}

// Handle status update from inline dropdown
if(isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status   = mysqli_real_escape_string($db, $_POST['status']);
    mysqli_query($db, "UPDATE orders SET status='$status' WHERE order_id='$order_id'");
    // Also log to remark table (kept for backward compat)
    mysqli_query($db, "INSERT INTO remark(frm_id,status,remark) VALUES('$order_id','$status','Updated from admin')");
    header("Location: all_orders.php?updated=1");
    exit();
}

// Handle delete
if(isset($_GET['order_del'])) {
    $order_id = intval($_GET['order_del']);
    // order_items rows are deleted automatically via ON DELETE CASCADE
    mysqli_query($db, "DELETE FROM orders WHERE order_id='$order_id'");
    header("Location: all_orders.php?deleted=1");
    exit();
}

// Fetch all orders joined with user, newest first
$sql = "SELECT o.*, u.username, u.f_name, u.l_name, u.phone AS user_phone
        FROM orders o
        INNER JOIN users u ON o.u_id = u.u_id
        ORDER BY o.order_date DESC";
$query = mysqli_query($db, $sql);
?>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon.png">
    <title>All Orders</title>
    <link href="css/lib/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="css/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .items-mini { margin:0; padding:0; list-style:none; font-size:0.82rem; }
        .items-mini li { padding:2px 0; color:#555; }
        .items-mini li::before { content:"• "; color:#ff5722; font-weight:700; }
        .badge-status { border-radius:12px; padding:4px 10px; font-size:0.75rem; font-weight:700; display:inline-block; }
        .bs-pending   { background:#fff8e1; color:#f57f17; }
        .bs-confirmed { background:#e3f2fd; color:#1565c0; }
        .bs-inprocess { background:#fff3e0; color:#e65100; }
        .bs-onway     { background:#fce4ec; color:#880e4f; }
        .bs-closed    { background:#e8f5e9; color:#2e7d32; }
        .bs-rejected  { background:#ffebee; color:#c62828; }
        .order-id-badge { background:#ff5722; color:#fff; border-radius:8px; padding:2px 8px; font-weight:800; font-size:0.85rem; }
    </style>
</head>
<body class="fix-header fix-sidebar">
    <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
        </svg>
    </div>
    <div id="main-wrapper">
        <div class="header">
            <nav class="navbar top-navbar navbar-expand-md navbar-light">
                <div class="navbar-header">
                    <a class="navbar-brand">
                        <span><img src="images/icn.png" alt="homepage" class="dark-logo"/></span>
                    </a>
                </div>
                <div class="navbar-collapse">
                    <ul class="navbar-nav mr-auto mt-md-0"></ul>
                    <ul class="navbar-nav my-lg-0">
                        <li class="nav-item dropdown">
                            <div class="dropdown-menu dropdown-menu-right mailbox animated zoomIn">
                                <ul>
                                    <li><div class="drop-title">Notifications</div></li>
                                    <li><a class="nav-link text-center" href="javascript:void(0);"><strong>Check all notifications</strong> <i class="fa fa-angle-right"></i></a></li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-muted" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="images/bookingSystem/user-icn.png" alt="user" class="profile-pic"/>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right animated zoomIn">
                                <ul class="dropdown-user">
                                    <li><a href="logout.php"><i class="fa fa-power-off"></i> Logout</a></li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>

        <div class="left-sidebar">
            <div class="scroll-sidebar">
                <nav class="sidebar-nav">
                    <ul id="sidebarnav">
                        <li class="nav-devider"></li>
                        <li class="nav-label">Home</li>
                        <li><a href="dashboard.php"><i class="fa fa-tachometer"></i><span>Dashboard</span></a></li>
                        <li class="nav-label">Log</li>
                        <li><a href="all_users.php"><span><i class="fa fa-user f-s-20"></i></span><span>Users</span></a></li>
                        <li>
                            <a class="has-arrow" href="#" aria-expanded="false">
                                <i class="fa fa-archive f-s-20 color-warning"></i><span class="hide-menu">Restaurant</span>
                            </a>
                            <ul aria-expanded="false" class="collapse">
                                <li><a href="all_restaurant.php">All Restaurants</a></li>
                                <li><a href="add_category.php">Add Category</a></li>
                                <li><a href="add_restaurant.php">Add Restaurant</a></li>
                            </ul>
                        </li>
                        <li>
                            <a class="has-arrow" href="#" aria-expanded="false">
                                <i class="fa fa-cutlery" aria-hidden="true"></i><span class="hide-menu">Menu</span>
                            </a>
                            <ul aria-expanded="false" class="collapse">
                                <li><a href="all_menu.php">All Menues</a></li>
                                <li><a href="add_menu.php">Add Menu</a></li>
                            </ul>
                        </li>
                        <li><a href="all_orders.php"><i class="fa fa-shopping-cart" aria-hidden="true"></i><span>Orders</span></a></li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="col-lg-12">
                            <div class="card card-outline-primary">
                                <div class="card-header">
                                    <h4 class="m-b-0 text-white">All Orders
                                        <small style="font-size:.75rem;opacity:.8;margin-left:10px;">
                                            (Grouped — multiple dishes share one Order ID)
                                        </small>
                                    </h4>
                                </div>

                                <?php if(isset($_GET['updated'])): ?>
                                <div class="alert alert-success m-3" style="margin-bottom:0!important;">
                                    ✅ Order status updated successfully.
                                </div>
                                <?php endif; ?>
                                <?php if(isset($_GET['deleted'])): ?>
                                <div class="alert alert-warning m-3" style="margin-bottom:0!important;">
                                    🗑️ Order deleted.
                                </div>
                                <?php endif; ?>

                                <div class="table-responsive m-t-40">
                                    <table id="myTable" class="table table-bordered table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Order ID</th>
                                                <th>User</th>
                                                <th>Items Ordered</th>
                                                <th>Total (₹)</th>
                                                <th>Delivery Address</th>
                                                <th>Payment</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        if (!mysqli_num_rows($query)) {
                                            echo '<tr><td colspan="9"><center>No Orders Found</center></td></tr>';
                                        } else {
                                            while ($row = mysqli_fetch_assoc($query)):
                                                $order_id = $row['order_id'];
                                                $status   = $row['status'];

                                                // Fetch items for this order
                                                $iq    = mysqli_query($db, "SELECT * FROM order_items WHERE order_id='$order_id' ORDER BY item_id ASC");
                                                $items = [];
                                                while ($it = mysqli_fetch_assoc($iq)) $items[] = $it;

                                                // Status badge class
                                                $bs = match($status) {
                                                    'pending'    => 'bs-pending',
                                                    'confirmed'  => 'bs-confirmed',
                                                    'in process' => 'bs-inprocess',
                                                    'on the way' => 'bs-onway',
                                                    'closed'     => 'bs-closed',
                                                    'rejected'   => 'bs-rejected',
                                                    default      => 'bs-pending'
                                                };
                                                $status_label = match($status) {
                                                    'closed'     => '✅ Delivered',
                                                    'rejected'   => '❌ Cancelled',
                                                    'in process' => '🔥 Preparing',
                                                    'on the way' => '🛵 On the Way',
                                                    'confirmed'  => '👍 Confirmed',
                                                    default      => '⏳ Pending'
                                                };
                                        ?>
                                        <tr>
                                            <td><span class="order-id-badge">#<?php echo $order_id; ?></span></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($row['f_name'].' '.$row['l_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($row['username']); ?></small>
                                            </td>
                                            <td>
                                                <!-- All items grouped under this order_id -->
                                                <ul class="items-mini">
                                                    <?php foreach ($items as $it): ?>
                                                    <li>
                                                        <?php echo htmlspecialchars($it['title']); ?>
                                                        &nbsp;<span style="color:#888;">× <?php echo $it['quantity']; ?></span>
                                                        &nbsp;<span style="color:#ff5722;font-weight:700;">₹<?php echo number_format($it['subtotal'],2); ?></span>
                                                    </li>
                                                    <?php endforeach; ?>
                                                    <?php if (empty($items)): ?>
                                                    <li style="color:#bbb;">No items found</li>
                                                    <?php endif; ?>
                                                </ul>
                                            </td>
                                            <td><strong style="color:#ff5722;">₹<?php echo number_format($row['total_price'],2); ?></strong>
                                                <?php if(floatval($row['discount_amount'])>0): ?>
                                                <br><small style="color:#4caf50;">Saved ₹<?php echo number_format($row['discount_amount'],2); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td style="max-width:160px;font-size:.82rem;">
                                                <?php echo htmlspecialchars($row['delivery_address']); ?><br>
                                                <small class="text-muted"><i class="fa fa-phone"></i> <?php echo htmlspecialchars($row['delivery_phone']); ?></small>
                                            </td>
                                            <td style="text-transform:uppercase;font-weight:700;font-size:.8rem;">
                                                <?php echo htmlspecialchars($row['payment_method']); ?>
                                            </td>
                                            <td>
                                                <span class="badge-status <?php echo $bs; ?>"><?php echo $status_label; ?></span>
                                            </td>
                                            <td style="font-size:.8rem;white-space:nowrap;">
                                                <?php echo date('d M Y', strtotime($row['order_date'])); ?><br>
                                                <small class="text-muted"><?php echo date('h:i A', strtotime($row['order_date'])); ?></small>
                                            </td>
                                            <td>
                                                <!-- Inline status update -->
                                                <form method="POST" style="margin-bottom:6px;">
                                                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                                    <select name="status" style="font-size:.78rem;padding:3px 6px;border:1px solid #ddd;border-radius:6px;margin-bottom:4px;width:100%;">
                                                        <option value="pending"    <?php echo $status==='pending'?'selected':''; ?>>⏳ Pending</option>
                                                        <option value="confirmed"  <?php echo $status==='confirmed'?'selected':''; ?>>👍 Confirmed</option>
                                                        <option value="in process" <?php echo $status==='in process'?'selected':''; ?>>🔥 Preparing</option>
                                                        <option value="on the way" <?php echo $status==='on the way'?'selected':''; ?>>🛵 On the Way</option>
                                                        <option value="closed"     <?php echo $status==='closed'?'selected':''; ?>>✅ Delivered</option>
                                                        <option value="rejected"   <?php echo $status==='rejected'?'selected':''; ?>>❌ Cancelled</option>
                                                    </select>
                                                    <button type="submit" name="update_status"
                                                            style="width:100%;font-size:.75rem;padding:3px 8px;background:#ff5722;color:#fff;border:none;border-radius:6px;cursor:pointer;">
                                                        Update
                                                    </button>
                                                </form>
                                                <a href="delete_orders.php?order_del=<?php echo $order_id; ?>"
                                                   onclick="return confirm('Delete Order #<?php echo $order_id; ?> and all its items?');"
                                                   class="btn btn-danger btn-flat btn-xs" style="width:100%;font-size:.75rem;text-align:center;">
                                                    <i class="fa fa-trash-o"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; } ?>
                                        </tbody>
                                    </table>
                                </div><!-- /table-responsive -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="footer">© 2022 - Online Food Ordering System</footer>
        </div>
    </div>

    <script src="js/lib/jquery/jquery.min.js"></script>
    <script src="js/lib/bootstrap/js/popper.min.js"></script>
    <script src="js/lib/bootstrap/js/bootstrap.min.js"></script>
    <script src="js/jquery.slimscroll.js"></script>
    <script src="js/sidebarmenu.js"></script>
    <script src="js/lib/sticky-kit-master/dist/sticky-kit.min.js"></script>
    <script src="js/custom.min.js"></script>
    <script src="js/lib/datatables/datatables.min.js"></script>
    <script src="js/lib/datatables/cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
    <script src="js/lib/datatables/cdn.datatables.net/buttons/1.2.2/js/buttons.flash.min.js"></script>
    <script src="js/lib/datatables/cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
    <script src="js/lib/datatables/cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
    <script src="js/lib/datatables/cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
    <script src="js/lib/datatables/cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>
    <script src="js/lib/datatables/cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>
    <script>
    // Auto-dismiss alerts after 4s
    setTimeout(function(){
        document.querySelectorAll('.alert').forEach(function(a){
            a.style.transition='all .4s'; a.style.opacity='0'; setTimeout(function(){a.remove();},400);
        });
    }, 4000);
    </script>
</body>
</html>
