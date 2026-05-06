<?php
include("connection/connect.php");
session_start();
error_reporting(0);

// Redirect to login if user not logged in
if (empty($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Check if cart is empty, redirect to cart page
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Fetch cart items details from DB
$cart_items  = [];
$total_price = 0.0;
$auto_discount_pct   = 0;
$auto_discount_amt   = 0;
$auto_discount_label = '';

// Ensure discount_pct column exists
mysqli_query($db, "ALTER TABLE restaurant ADD COLUMN IF NOT EXISTS `discount_pct` decimal(5,2) NOT NULL DEFAULT 0");

$ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
if($ids !== '') {
    $query  = "SELECT d.*, r.rs_id as rest_id, r.title as res_title, r.discount_pct FROM dishes d LEFT JOIN restaurant r ON d.rs_id=r.rs_id WHERE d.d_id IN ($ids)";
    $result = mysqli_query($db, $query);
    while($row = mysqli_fetch_assoc($result)) {
        $row['quantity'] = intval($_SESSION['cart'][$row['d_id']] ?? 0);
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $total_price    += $row['subtotal'];
        $cart_items[]    = $row;
        // Get restaurant discount (use first item's restaurant)
        if($auto_discount_pct == 0 && floatval($row['discount_pct']) > 0) {
            $auto_discount_pct   = floatval($row['discount_pct']);
            $auto_discount_label = $row['res_title'];
        }
    }
}

// Apply auto discount
if($auto_discount_pct > 0) {
    $auto_discount_amt = round($total_price * ($auto_discount_pct / 100), 2);
    $total_after_auto  = $total_price - $auto_discount_amt;
} else {
    $total_after_auto = $total_price;
}

// Fetch user info
$user_id    = intval($_SESSION["user_id"]);
$user_query = mysqli_query($db, "SELECT * FROM users WHERE u_id=$user_id");
$user       = mysqli_fetch_assoc($user_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Checkout - O.F.O.S Hub</title>
    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/font-awesome.min.css" rel="stylesheet" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #fafafa;
            margin: 0;
        }
        .navbar-brand img {
            height: 45px;
        }
        .checkout-container {
            max-width: 900px;
            margin: 0 auto 50px;
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h2 {
            font-weight: 600;
            margin-bottom: 30px;
            color: #333;
            text-align: center;
        }
        .section-title {
            font-weight: 600;
            margin-bottom: 20px;
            color: #ff5722;
            border-bottom: 2px solid #ff5722;
            padding-bottom: 5px;
            margin-top: 30px;
        }
        table {
            width: 100%;
            margin-bottom: 20px;
        }
        thead th {
            border-bottom: 2px solid #ff5722;
            padding-bottom: 10px;
            color: #ff5722;
            font-weight: 600;
        }
        tbody td {
            vertical-align: middle;
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
        }
        .food-img {
            width: 70px;
            height: 70px;
            border-radius: 10px;
            object-fit: cover;
        }
        .total-row {
            font-weight: 700;
            font-size: 1.2rem;
            color: #333;
        }
        label {
            font-weight: 600;
            margin-top: 10px;
        }
        input[type="text"], input[type="email"], input[type="tel"], textarea, select {
            width: 100%;
            padding: 10px 12px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus, input[type="email"]:focus, input[type="tel"]:focus, textarea:focus, select:focus {
            border-color: #ff5722;
            outline: none;
        }
        .btn-place-order {
            background: #4caf50;
            border: none;
            color: #fff;
            padding: 14px 40px;
            border-radius: 8px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 30px;
            display: block;
            width: 100%;
        }
        .btn-place-order:hover {
            background: #388e3c;
        }
        /* Payment methods */
        .pay-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-top:14px; }
        .pay-card { border:2px solid #eee; border-radius:12px; padding:14px 12px; cursor:pointer; text-align:center; transition:all 0.2s; position:relative; }
        .pay-card:hover { border-color:#ff9800; background:#fffbf5; }
        .pay-card.selected { border-color:#ff5722; background:#fff8f5; box-shadow:0 0 0 3px rgba(255,87,34,0.12); }
        .pay-card input[type=radio] { position:absolute; opacity:0; width:0; height:0; }
        .pay-icon { font-size:1.8rem; display:block; margin-bottom:6px; }
        .pay-name { font-size:0.82rem; font-weight:700; color:#333; }
        .pay-desc { font-size:0.72rem; color:#aaa; margin-top:2px; }
        .pay-badge { font-size:0.65rem; background:#4caf50; color:#fff; border-radius:10px; padding:1px 7px; font-weight:700; display:inline-block; margin-top:4px; }
        /* UPI details box */
        .upi-box { display:none; margin-top:14px; background:#f9f9f9; border-radius:12px; padding:16px; border:1px solid #f0f0f0; }
        .upi-box.show { display:block; }
        .upi-id-display { font-family:monospace; font-size:1rem; font-weight:700; color:#ff5722; background:#fff3e0; border:1.5px dashed #ff9800; border-radius:8px; padding:8px 14px; display:inline-block; margin:10px 0; letter-spacing:0.5px; }
        @media(max-width:576px){ .pay-grid{ grid-template-columns:1fr 1fr; } }
        .footer {
            background: rgba(0, 0, 0, 0.85);
            color: #fff;
            padding: 30px 0;
            margin-top: 40px;
            text-align: center;
        }
        .footer h5 {
            font-weight: 600;
        }
        /* Responsive */
        @media (max-width: 576px) {
            .checkout-container {
                padding: 20px 15px;
            }
            .food-img {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include('navbar.php'); ?>

    <div class="checkout-container">
        <!-- Cart Summary -->
        <div>
            <h4 class="section-title">Order Summary</h4>
            <table class="table table-borderless">
                <thead>
                    <tr>
                        <th>Dish</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item) { ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="admin/Res_img/dishes/<?php echo htmlspecialchars($item['img']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="food-img mr-3" />
                                    <div>
                                        <strong><?php echo htmlspecialchars($item['title']); ?></strong><br />
                                        <small class="text-muted"><?php echo htmlspecialchars($item['slogan']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>₹<?php echo number_format($item['price'], 2); ?></td>
                            <td>₹<?php echo number_format($item['subtotal'], 2); ?></td>
                        </tr>
                    <?php } ?>
                    <tr class="total-row">
                        <td colspan="3" class="text-right">Subtotal:</td>
                        <td id="subtotalAmt">₹<?php echo number_format($total_price, 2); ?></td>
                    </tr>
                    <?php if($auto_discount_pct > 0): ?>
                    <tr style="color:#4caf50;font-weight:700;">
                        <td colspan="3" class="text-right">
                            <i class="fa fa-tag"></i> Auto Discount (<?php echo $auto_discount_pct; ?>% off):
                        </td>
                        <td>-₹<?php echo number_format($auto_discount_amt, 2); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr id="discountRow" style="display:none;">
                        <td colspan="3" class="text-right" style="color:#4caf50;font-weight:600;">Coupon Discount:</td>
                        <td id="discountAmt" style="color:#4caf50;font-weight:600;">-₹0.00</td>
                    </tr>
                    <tr class="total-row" style="background:#fff8f5;">
                        <td colspan="3" class="text-right" style="color:#ff5722;font-weight:800;">Grand Total:</td>
                        <td id="grandTotal" style="color:#ff5722;font-weight:800;">₹<?php echo number_format($total_after_auto, 2); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- COUPON BOX -->
        <div style="background:linear-gradient(135deg,#fff8f5,#fff3e0);border:1.5px dashed #ffb74d;border-radius:12px;padding:18px 20px;margin-bottom:24px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                <span style="font-size:1.5rem;">🎫</span>
                <div>
                    <div style="font-weight:700;color:#e65100;font-size:0.95rem;">Have a Coupon Code?</div>
                    <div style="font-size:0.78rem;color:#aaa;">Enter your code below to get a discount on this order.</div>
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <input type="text" id="cpInput" placeholder="Enter coupon code"
                       style="flex:1;min-width:140px;padding:10px 14px;border:1.5px solid #ffe0b2;border-radius:8px;
                              font-family:monospace;font-size:0.9rem;font-weight:700;text-transform:uppercase;
                              letter-spacing:1px;outline:none;background:#fff;">
                <button type="button" onclick="applyCoupon()" id="cpApplyBtn"
                        style="padding:10px 22px;background:linear-gradient(90deg,#ff5722,#ff9800);color:#fff;
                               border:none;border-radius:8px;font-weight:700;cursor:pointer;white-space:nowrap;">
                    Apply
                </button>
                <button type="button" onclick="removeCoupon()" id="cpRemoveBtn"
                        style="display:none;padding:10px 16px;background:#f5f5f5;color:#f44336;
                               border:none;border-radius:8px;font-weight:700;cursor:pointer;">
                    ✕ Remove
                </button>
            </div>
            <div id="cpMsg" style="display:none;margin-top:10px;border-radius:8px;padding:9px 13px;font-size:0.85rem;"></div>
        </div>

        <!-- Checkout Form -->
        <form action="your_orders.php" method="post" id="checkoutForm" novalidate>
            <h4 class="section-title">Delivery Details</h4>

            <div class="form-group">
                <label for="name">Full Name <span style="color:#ff5722;">*</span></label>
                <input type="text" id="name" name="name" required placeholder="Your full name"
                       value="<?php echo htmlspecialchars(($user['f_name']??'').' '.($user['l_name']??'')); ?>" />
            </div>

            <div class="form-group">
                <label for="email">Email Address <span style="color:#ff5722;">*</span></label>
                <input type="email" id="email" name="email" required placeholder="you@example.com" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" />
            </div>

            <div class="form-group">
                <label for="phone">Phone Number <span style="color:#ff5722;">*</span></label>
                <input type="tel" id="phone" name="phone" required placeholder="+91 9876543210" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" pattern="[0-9+\-\s]{7,15}" />
            </div>

            <div class="form-group">
                <label for="address">Delivery Address <span style="color:#ff5722;">*</span></label>
                <textarea id="address" name="address" rows="3" required placeholder="Street, City, State, ZIP"><?php echo htmlspecialchars($user['address'] ?? $user['Address'] ?? ''); ?></textarea>
            </div>

            <h4 class="section-title">Payment Method</h4>
            <div class="pay-grid">
                <label class="pay-card selected" id="payCard_cod" onclick="selectPay('cod')">
                    <input type="radio" name="payment_method" id="pay_cod" value="cod" checked>
                    <span class="pay-icon">💵</span>
                    <div class="pay-name">Cash on Delivery</div>
                    <div class="pay-desc">Pay when your order arrives</div>
                    <span class="pay-badge">Most Popular</span>
                </label>
                <label class="pay-card" id="payCard_upi" onclick="selectPay('upi')">
                    <input type="radio" name="payment_method" id="pay_upi" value="upi">
                    <span class="pay-icon">📱</span>
                    <div class="pay-name">UPI Payment</div>
                    <div class="pay-desc">GPay, PhonePe, Paytm</div>
                </label>
                <label class="pay-card" id="payCard_card" onclick="selectPay('card')">
                    <input type="radio" name="payment_method" id="pay_card" value="card">
                    <span class="pay-icon">💳</span>
                    <div class="pay-name">Debit / Credit Card</div>
                    <div class="pay-desc">Visa, Mastercard, RuPay</div>
                </label>
            </div>

            <!-- UPI details -->
            <div class="upi-box" id="upiBox">
                <div style="font-weight:700;color:#333;margin-bottom:6px;"><i class="fa fa-mobile" style="color:#ff5722;margin-right:6px;"></i> Pay via UPI</div>
                <div style="font-size:0.82rem;color:#888;">Send payment to this UPI ID and enter the transaction reference:</div>
                <div class="upi-id-display">ofos@upi</div>
                <div style="font-size:0.78rem;color:#aaa;margin-bottom:10px;">Open GPay / PhonePe / Paytm → Pay to UPI ID → Enter amount → Copy transaction ID below</div>
                <label style="font-size:0.82rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">UTR / Transaction Reference <span style="color:#f44336;">*</span></label>
                <input type="text" name="upi_ref" id="upiRef" placeholder="e.g. 407812345678"
                       style="width:100%;padding:9px 12px;border:1.5px solid #ddd;border-radius:8px;font-size:0.9rem;outline:none;">
            </div>

            <!-- Card details -->
            <div class="upi-box" id="cardBox">
                <div style="font-weight:700;color:#333;margin-bottom:10px;"><i class="fa fa-credit-card" style="color:#ff5722;margin-right:6px;"></i> Card Details</div>
                <label style="font-size:0.82rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">Card Number</label>
                <input type="text" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19"
                       style="width:100%;padding:9px 12px;border:1.5px solid #ddd;border-radius:8px;font-size:0.9rem;outline:none;margin-bottom:10px;font-family:monospace;"
                       oninput="this.value=this.value.replace(/[^0-9]/g,'').replace(/(.{4})/g,'$1 ').trim()">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <label style="font-size:0.82rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">Expiry (MM/YY)</label>
                        <input type="text" name="card_expiry" placeholder="MM/YY" maxlength="5"
                               style="width:100%;padding:9px 12px;border:1.5px solid #ddd;border-radius:8px;font-size:0.9rem;outline:none;">
                    </div>
                    <div>
                        <label style="font-size:0.82rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">CVV</label>
                        <input type="password" name="card_cvv" placeholder="•••" maxlength="4"
                               style="width:100%;padding:9px 12px;border:1.5px solid #ddd;border-radius:8px;font-size:0.9rem;outline:none;">
                    </div>
                </div>
                <div style="margin-top:10px;background:#fff3e0;border-radius:8px;padding:8px 12px;font-size:0.75rem;color:#888;">
                    <i class="fa fa-lock" style="color:#ff9800;margin-right:4px;"></i> This is a demo — no real payment is processed.
                </div>
            </div>

            <input type="hidden" name="total_price"     id="finalTotal"    value="<?php echo number_format($total_after_auto, 2, '.', ''); ?>" />
            <input type="hidden" name="coupon_code"     id="cpCodeInput"   value="" />
            <input type="hidden" name="discount_amount" id="discountInput" value="0" />

            <button type="submit" class="btn-place-order" id="placeBtn">
                Place Order — <span id="btnAmtSpan">₹<?php echo number_format($total_after_auto, 2); ?></span>
            </button>
        </form>
    </div>

    <footer class="footer">
        <div class="container">
            <h5>O.F.O.S Hub</h5>
            
        </div>
    </footer>

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        var baseTotal = <?php echo $total_after_auto; ?>;
        var appliedDiscount = 0;

        // Form validation — including payment method requirements
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                alert('Please fill all required fields.');
                return false;
            }

            var method = document.querySelector('input[name="payment_method"]:checked').value;

            if (method === 'upi') {
                var upiRef = document.getElementById('upiRef').value.trim();
                if (!upiRef) {
                    e.preventDefault();
                    document.getElementById('upiRef').focus();
                    document.getElementById('upiRef').style.borderColor = '#f44336';
                    alert('Please enter your UPI Transaction Reference / UTR number to complete payment.');
                    return false;
                }
            }

            if (method === 'card') {
                var cardNum = document.querySelector('input[name="card_number"]').value.replace(/\s/g,'');
                var cardExp = document.querySelector('input[name="card_expiry"]').value.trim();
                var cardCvv = document.querySelector('input[name="card_cvv"]').value.trim();
                if (cardNum.length < 13 || !cardExp || !cardCvv) {
                    e.preventDefault();
                    alert('Please enter your complete card details (Card Number, Expiry, and CVV).');
                    if(cardNum.length < 13) document.querySelector('input[name="card_number"]').style.borderColor = '#f44336';
                    if(!cardExp)           document.querySelector('input[name="card_expiry"]').style.borderColor = '#f44336';
                    if(!cardCvv)           document.querySelector('input[name="card_cvv"]').style.borderColor = '#f44336';
                    return false;
                }
            }
        });

        // Apply coupon
        function applyCoupon() {
            var code = document.getElementById('cpInput').value.trim().toUpperCase();
            if(!code) { showMsg('Please enter a coupon code.', false); return; }
            var btn = document.getElementById('cpApplyBtn');
            btn.textContent = 'Checking...'; btn.disabled = true;
            var fd = new FormData();
            fd.append('code', code);
            fd.append('total', baseTotal);
            fetch('coupon_check.php', { method:'POST', body:fd })
            .then(function(r){ return r.json(); })
            .then(function(d) {
                btn.textContent = 'Apply'; btn.disabled = false;
                if(d.valid) {
                    appliedDiscount = parseFloat(d.discount);
                    var newTotal = Math.max(0, baseTotal - appliedDiscount).toFixed(2);
                    document.getElementById('discountRow').style.display = '';
                    document.getElementById('discountAmt').textContent  = '-₹' + parseFloat(appliedDiscount).toFixed(2);
                    document.getElementById('grandTotal').textContent   = '₹' + newTotal;
                    document.getElementById('btnAmtSpan').textContent   = '₹' + newTotal;
                    document.getElementById('finalTotal').value         = newTotal;
                    document.getElementById('cpCodeInput').value        = code;
                    document.getElementById('discountInput').value      = appliedDiscount.toFixed(2);
                    document.getElementById('cpInput').disabled         = true;
                    document.getElementById('cpApplyBtn').style.display = 'none';
                    document.getElementById('cpRemoveBtn').style.display = '';
                    showMsg(d.msg, true);
                } else {
                    showMsg(d.msg, false);
                }
            })
            .catch(function(){ btn.textContent='Apply'; btn.disabled=false; showMsg('Error. Please try again.', false); });
        }

        // Remove coupon
        function removeCoupon() {
            appliedDiscount = 0;
            document.getElementById('cpInput').value            = '';
            document.getElementById('cpInput').disabled         = false;
            document.getElementById('cpApplyBtn').style.display = '';
            document.getElementById('cpRemoveBtn').style.display= 'none';
            document.getElementById('discountRow').style.display= 'none';
            document.getElementById('grandTotal').textContent   = '₹' + baseTotal.toFixed(2);
            document.getElementById('btnAmtSpan').textContent   = '₹' + baseTotal.toFixed(2);
            document.getElementById('finalTotal').value         = baseTotal.toFixed(2);
            document.getElementById('cpCodeInput').value        = '';
            document.getElementById('discountInput').value      = '0';
            document.getElementById('cpMsg').style.display      = 'none';
        }

        function showMsg(msg, ok) {
            var el = document.getElementById('cpMsg');
            el.innerHTML = msg;
            el.style.display   = 'block';
            el.style.background = ok ? '#e8f5e9' : '#ffebee';
            el.style.color      = ok ? '#2e7d32'  : '#c62828';
            el.style.border     = ok ? '1px solid #a5d6a7' : '1px solid #ef9a9a';
        }

        // Enter key on coupon input
        document.getElementById('cpInput').addEventListener('keypress', function(e){
            if(e.key === 'Enter') { e.preventDefault(); applyCoupon(); }
        });
    </script>
    <script>
    function selectPay(method) {
        ['cod','upi','card'].forEach(function(m){
            document.getElementById('payCard_'+m).classList.remove('selected');
        });
        document.getElementById('payCard_'+method).classList.add('selected');
        document.getElementById('pay_'+method).checked = true;
        document.getElementById('upiBox').classList.remove('show');
        document.getElementById('cardBox').classList.remove('show');
        if(method === 'upi')  document.getElementById('upiBox').classList.add('show');
        if(method === 'card') document.getElementById('cardBox').classList.add('show');
    }

    // Reset red border on user input
    document.getElementById('upiRef').addEventListener('input', function(){ this.style.borderColor=''; });
    document.querySelector('input[name="card_number"]').addEventListener('input', function(){ this.style.borderColor=''; });
    document.querySelector('input[name="card_expiry"]').addEventListener('input', function(){ this.style.borderColor=''; });
    document.querySelector('input[name="card_cvv"]').addEventListener('input', function(){ this.style.borderColor=''; });
    </script>
</body>
</html>