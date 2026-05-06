<?php
include("connection/connect.php");
session_start();
error_reporting(0);

if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Remove item
if(isset($_GET['remove'])) {
    unset($_SESSION['cart'][intval($_GET['remove'])]);
    header("Location: cart.php"); exit();
}

// Update quantities
if(isset($_POST['update_cart'])) {
    if(isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach($_POST['quantities'] as $d_id => $qty) {
            $d_id = intval($d_id);
            $qty  = intval($qty);
            if($qty <= 0) unset($_SESSION['cart'][$d_id]);
            else $_SESSION['cart'][$d_id] = $qty;
        }
    }
    header("Location: cart.php"); exit();
}

// Add item
if(isset($_GET['action']) && $_GET['action']==='add' && isset($_GET['id'])) {
    $d_id = intval($_GET['id']);
    $qty  = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    if($qty < 1) $qty = 1;
    $_SESSION['cart'][$d_id] = isset($_SESSION['cart'][$d_id]) ? $_SESSION['cart'][$d_id] + $qty : $qty;
    // If called via AJAX (fetch), return JSON; otherwise redirect
    $is_ajax = !empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'],'application/json')!==false;
    $is_fetch = isset($_SERVER['HTTP_SEC_FETCH_MODE']) && $_SERVER['HTTP_SEC_FETCH_MODE']==='cors';
    if($is_ajax || $is_fetch) {
        header('Content-Type: application/json');
        echo json_encode(['success'=>true,'cart_count'=>array_sum($_SESSION['cart'])]);
        exit();
    }
    header("Location: cart.php"); exit();
}

// Fetch items
$cart_items = []; $total_price = 0.0;
if(!empty($_SESSION['cart'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
    if($ids !== '') {
        $result = mysqli_query($db, "SELECT d.*, r.title as res_title FROM dishes d LEFT JOIN restaurant r ON d.rs_id=r.rs_id WHERE d.d_id IN ($ids)");
        while($row = mysqli_fetch_assoc($result)) {
            $d_id = (int)$row['d_id'];
            $qty  = intval($_SESSION['cart'][$d_id] ?? 0);
            if($qty <= 0) continue;
            $row['quantity'] = $qty;
            $row['subtotal'] = $row['price'] * $qty;
            $total_price    += $row['subtotal'];
            $cart_items[]    = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Cart — O.F.O.S</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <style>
        body { font-family:'Segoe UI',sans-serif; background:#f4f6f9; margin:0; }

        .cart-wrap {
            max-width: 920px;
            margin: 30px auto 60px;
            padding: 0 16px;
        }
        .cart-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.07);
            overflow: hidden;
        }
        .cart-title {
            padding: 22px 28px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 1.4rem; font-weight: 800; color: #1a1a2e;
            display: flex; align-items: center; gap: 10px;
        }
        .cart-title i { color: #ff5722; }

        /* Table */
        table { width: 100%; border-collapse: collapse; }
        thead th {
            padding: 12px 16px;
            background: #fafafa;
            color: #ff5722; font-weight: 700; font-size: 0.85rem;
            text-transform: uppercase; letter-spacing: 0.5px;
            border-bottom: 2px solid #ff5722;
            text-align: left;
        }
        tbody td { padding: 16px; border-bottom: 1px solid #f5f5f5; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: #fff9f7; }

        /* Dish cell */
        .dish-cell { display:flex; align-items:center; gap:14px; }
        .dish-img  { width:70px; height:70px; border-radius:10px; object-fit:cover; flex-shrink:0; }
        .dish-name { font-weight:700; color:#222; font-size:0.95rem; }
        .dish-rest { font-size:0.75rem; color:#ff5722; margin-top:2px; }
        .dish-desc { font-size:0.75rem; color:#aaa; margin-top:2px; max-width:260px; }

        /* Qty controls */
        .qty-wrap {
            display: flex; align-items: center; gap: 0;
            border: 1.5px solid #e0e0e0; border-radius: 10px;
            overflow: hidden; width: fit-content;
        }
        .qty-btn {
            width: 34px; height: 36px; border: none;
            background: #f5f5f5; cursor: pointer;
            font-size: 1.1rem; font-weight: 700; color: #555;
            transition: background 0.15s;
            display: flex; align-items: center; justify-content: center;
        }
        .qty-btn:hover { background: #ff5722; color: #fff; }
        .qty-input {
            width: 48px; height: 36px;
            border: none; border-left: 1.5px solid #e0e0e0; border-right: 1.5px solid #e0e0e0;
            text-align: center; font-size: 0.95rem; font-weight: 700; color: #222;
            outline: none;
        }
        /* Hide number spinners */
        .qty-input::-webkit-outer-spin-button,
        .qty-input::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
        .qty-input[type=number] { -moz-appearance: textfield; }

        .price-cell  { font-weight:600; color:#555; }
        .sub-cell    { font-weight:800; color:#ff5722; font-size:1rem; }
        .remove-btn  { background:none; border:none; color:#ddd; font-size:1.3rem; cursor:pointer; transition:color 0.2s; }
        .remove-btn:hover { color:#f44336; }

        /* Total row */
        .total-row td { padding: 18px 16px; background: #fafafa; }
        .total-label { font-size:1rem; font-weight:700; color:#555; text-align:right; }
        .total-amount { font-size:1.4rem; font-weight:800; color:#ff5722; }

        /* Action buttons */
        .cart-actions {
            padding: 20px 24px;
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 12px;
            border-top: 1px solid #f0f0f0;
        }
        .btn-continue {
            display: inline-flex; align-items: center; gap: 8px;
            color: #ff5722; text-decoration: none; font-weight: 700;
            font-size: 0.9rem; padding: 10px 18px;
            border: 2px solid #ff5722; border-radius: 10px;
            transition: all 0.2s;
        }
        .btn-continue:hover { background: #ff5722; color: #fff; text-decoration: none; }
        .btn-checkout {
            display: inline-flex; align-items: center; gap: 8px;
            background: linear-gradient(90deg,#4caf50,#66bb6a);
            color: #fff; text-decoration: none; font-weight: 800;
            font-size: 1rem; padding: 12px 28px; border-radius: 12px;
            box-shadow: 0 4px 14px rgba(76,175,80,0.3);
            transition: opacity 0.2s, transform 0.15s;
        }
        .btn-checkout:hover { opacity:0.9; transform:translateY(-1px); color:#fff; text-decoration:none; }

        /* Empty state */
        .empty-state { padding: 60px 20px; text-align: center; }
        .empty-state i { font-size: 3.5rem; color: #ddd; display: block; margin-bottom: 16px; }
        .empty-state p { color: #aaa; font-size: 1.05rem; margin-bottom: 20px; }
        .btn-shop {
            display: inline-block; background: linear-gradient(90deg,#ff5722,#ff9800);
            color: #fff; text-decoration: none; padding: 12px 28px;
            border-radius: 12px; font-weight: 700;
            box-shadow: 0 4px 14px rgba(255,87,34,0.3);
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="cart-wrap">
    <div class="cart-card">
        <div class="cart-title">
            <i class="fa fa-shopping-cart"></i> Your Cart
            <?php if(!empty($cart_items)): ?>
            <span style="background:#fff3e0;color:#ff5722;border-radius:20px;padding:2px 12px;font-size:0.82rem;font-weight:700;margin-left:4px;">
                <?php echo count($cart_items); ?> item(s)
            </span>
            <?php endif; ?>
        </div>

        <?php if(empty($cart_items)): ?>
        <div class="empty-state">
            <i class="fa fa-shopping-cart"></i>
            <p>Your cart is empty.<br>Add some delicious dishes!</p>
            <a href="restaurants.php" class="btn-shop"><i class="fa fa-search"></i> Browse Restaurants</a>
        </div>

        <?php else: ?>
        <form method="POST" action="cart.php" id="cartForm">
            <table>
                <thead>
                    <tr>
                        <th>Dish</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($cart_items as $item): ?>
                <tr>
                    <td>
                        <div class="dish-cell">
                            <img src="admin/Res_img/dishes/<?php echo htmlspecialchars($item['img']); ?>"
                                 alt="" class="dish-img" onerror="this.src='images/icn.png'">
                            <div>
                                <div class="dish-name"><?php echo htmlspecialchars($item['title']); ?></div>
                                <?php if(!empty($item['res_title'])): ?>
                                <div class="dish-rest"><i class="fa fa-building-o"></i> <?php echo htmlspecialchars($item['res_title']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="price-cell">₹<?php echo number_format($item['price'], 2); ?></td>
                    <td>
                        <div class="qty-wrap">
                            <button type="button" class="qty-btn" onclick="changeQty(<?php echo $item['d_id']; ?>, -1)">−</button>
                            <input type="number" class="qty-input"
                                   id="qty_<?php echo $item['d_id']; ?>"
                                   name="quantities[<?php echo $item['d_id']; ?>]"
                                   value="<?php echo $item['quantity']; ?>"
                                   min="1" max="99"
                                   onchange="updateRow(<?php echo $item['d_id']; ?>, <?php echo $item['price']; ?>)"
                                   oninput="updateRow(<?php echo $item['d_id']; ?>, <?php echo $item['price']; ?>)">
                            <button type="button" class="qty-btn" onclick="changeQty(<?php echo $item['d_id']; ?>, 1)">+</button>
                        </div>
                    </td>
                    <td class="sub-cell" id="sub_<?php echo $item['d_id']; ?>">₹<?php echo number_format($item['subtotal'], 2); ?></td>
                    <td>
                        <a href="cart.php?remove=<?php echo $item['d_id']; ?>" class="remove-btn" title="Remove" onclick="return confirm('Remove this item?')">
                            <i class="fa fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="3" class="total-label">Total:</td>
                    <td colspan="2" class="total-amount" id="cartTotal">₹<?php echo number_format($total_price, 2); ?></td>
                </tr>
                </tbody>
            </table>

            <div class="cart-actions">
                <a href="index.php" class="btn-continue">
                    <i class="fa fa-arrow-left"></i> Continue Shopping
                </a>
                <a href="checkout.php" class="btn-checkout">
                    <i class="fa fa-lock"></i> Proceed to Checkout — <span id="checkoutAmt">₹<?php echo number_format($total_price, 2); ?></span>
                </a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
// Item prices map
var prices = {
<?php foreach($cart_items as $item): ?>
    <?php echo $item['d_id']; ?>: <?php echo floatval($item['price']); ?>,
<?php endforeach; ?>
};

function changeQty(id, delta) {
    var inp = document.getElementById('qty_' + id);
    var newVal = parseInt(inp.value || 1) + delta;
    if(newVal < 1) newVal = 1;
    if(newVal > 99) newVal = 99;
    inp.value = newVal;
    updateRow(id, prices[id]);
}

function updateRow(id, price) {
    var inp = document.getElementById('qty_' + id);
    var qty = parseInt(inp.value || 1);
    if(qty < 1) { qty = 1; inp.value = 1; }
    if(qty > 99) { qty = 99; inp.value = 99; }
    
    var sub = qty * price;
    document.getElementById('sub_' + id).textContent = '₹' + sub.toFixed(2);
    recalcTotal();
}

function recalcTotal() {
    var total = 0;
    Object.keys(prices).forEach(function(id) {
        var inp = document.getElementById('qty_' + id);
        if(inp) {
            var qty = parseInt(inp.value || 1);
            total += qty * prices[id];
        }
    });
    var fmt = '₹' + total.toFixed(2);
    document.getElementById('cartTotal').textContent    = fmt;
    document.getElementById('checkoutAmt').textContent  = fmt;
}
</script>
</body>
</html>
