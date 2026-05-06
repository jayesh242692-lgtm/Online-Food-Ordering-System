<?php
include("connection/connect.php");
session_start();
header('Content-Type: application/json');

// Auto-create table if missing — prevents white page / fatal errors
mysqli_query($db, "CREATE TABLE IF NOT EXISTS `coupons` (
    `c_id`         int(11) NOT NULL AUTO_INCREMENT,
    `code`         varchar(50) NOT NULL,
    `type`         enum('percent','flat') NOT NULL DEFAULT 'percent',
    `value`        decimal(10,2) NOT NULL,
    `min_order`    decimal(10,2) NOT NULL DEFAULT 0,
    `max_discount` decimal(10,2) NOT NULL DEFAULT 0,
    `expiry_date`  datetime NOT NULL,
    `usage_limit`  int(11) NOT NULL DEFAULT 0,
    `used_count`   int(11) NOT NULL DEFAULT 0,
    `is_active`    tinyint(1) NOT NULL DEFAULT 1,
    `description`  varchar(255) DEFAULT '',
    `rs_id`        int(11) NOT NULL DEFAULT 0,
    `created_at`   timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`c_id`),
    UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1");

$code  = strtoupper(trim($_POST['code'] ?? ''));
$total = floatval($_POST['total'] ?? 0);

if(empty($code)) {
    echo json_encode(['valid'=>false,'msg'=>'Please enter a coupon code.']);
    exit();
}

$safe = mysqli_real_escape_string($db, $code);
$now  = date('Y-m-d H:i:s');
$q    = mysqli_query($db, "SELECT * FROM coupons WHERE code='$safe' AND is_active=1 AND expiry_date>='$now' LIMIT 1");

if(!$q || mysqli_num_rows($q) == 0) {
    echo json_encode(['valid'=>false,'msg'=>'Invalid or expired coupon code.']);
    exit();
}

$c = mysqli_fetch_assoc($q);

if($c['usage_limit'] > 0 && $c['used_count'] >= $c['usage_limit']) {
    echo json_encode(['valid'=>false,'msg'=>'This coupon has reached its usage limit.']);
    exit();
}
if($total < $c['min_order']) {
    echo json_encode(['valid'=>false,'msg'=>'Minimum order of ₹'.number_format($c['min_order'],2).' required for this coupon.']);
    exit();
}

if($c['type'] == 'percent') {
    $discount = round($total * ($c['value'] / 100), 2);
    if($c['max_discount'] > 0) $discount = min($discount, $c['max_discount']);
    $label = $c['value'].'% off';
} else {
    $discount = min($c['value'], $total);
    $label = '₹'.number_format($c['value'],2).' off';
}

echo json_encode([
    'valid'    => true,
    'msg'      => '🎉 Coupon applied! You save '.$label,
    'discount' => $discount,
    'code'     => $code,
    'desc'     => $c['description'] ?? ''
]);
