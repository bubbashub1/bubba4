<?php
/** Source-only lock identity regression: no WordPress boot or SQL. */
$s = file_get_contents(dirname(__DIR__) . '/plugin/bubba-hub-manager/includes/class-bh-marketplace-settings.php');
function lock_guard($condition, $message) {
    if (!$condition) { fwrite(STDERR, "FAIL: $message\n"); exit(1); }
}
lock_guard(is_string($s), 'marketplace source exists');
$parts = array(
    'status' => array('public static function transition_booking_status(', 'public static function create_test_checkout('),
    'checkout' => array('public static function create_test_checkout(', 'public static function verify_test_webhook('),
    'webhook' => array('public static function confirm_verified_test_payment(', 'public static function stripe_status('),
);
foreach ($parts as $name => $bounds) {
    $start = strpos($s, $bounds[0]);
    $end = strpos($s, $bounds[1], $start === false ? 0 : $start);
    lock_guard($start !== false && $end !== false && $end > $start, "$name method boundaries");
    $parts[$name] = substr($s, $start, $end-$start);
}
lock_guard(strpos($parts['status'], "'bh3_status_'") !== false, 'status uses shared legacy lock');
lock_guard(strpos($parts['webhook'], "'bh3_status_'") !== false, 'webhook shares legacy status lock');
lock_guard(strpos($parts['checkout'], "'bh3_checkout_'") !== false, 'checkout still has distinct lock');
foreach (array('status','checkout','webhook') as $name) {
    lock_guard(strpos($parts[$name], 'SELECT GET_LOCK(%s, 5)') !== false, "$name acquires lock");
    lock_guard(strpos($parts[$name], 'SELECT RELEASE_LOCK(%s)') !== false, "$name releases lock");
}
lock_guard(strpos($parts['webhook'], "booking_status='reserved' AND payment_status='pending'") !== false,
    'webhook has guarded paid transition');
echo "PASS: legacy status/webhook shared lock and separate checkout lock documented\n";
