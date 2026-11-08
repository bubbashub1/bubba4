<?php
/** Static lock identity guard: detects dangerous one-sided renames. */
$source = file_get_contents(dirname(__DIR__) . '/plugin/bubba-hub-manager/includes/class-bh-marketplace-settings.php');
function bh4_lock_check($ok, $message) {
    if (!$ok) { fwrite(STDERR, "FAIL: $message\n"); exit(1); }
}
bh4_lock_check(is_string($source), 'marketplace source exists');
function bh4_method_source($source, $start, $next) {
    $a = strpos($source, 'public static function ' . $start . '(');
    $b = $a === false ? false : strpos($source, 'public static function ' . $next . '(', $a + 1);
    return ($a !== false && $b !== false) ? substr($source, $a, $b - $a) : '';
}
$status = bh4_method_source($source, 'transition_booking_status', 'create_test_checkout');
$checkout = bh4_method_source($source, 'create_test_checkout', 'inspect_booking_form_submission');
$confirm = bh4_method_source($source, 'confirm_verified_test_payment', 'stripe_status');
bh4_lock_check($status !== '' && $checkout !== '' && $confirm !== '', 'all three handler boundaries found');
bh4_lock_check(strpos($status, "'bh3_status_' . md5(") !== false, 'status transition uses status lock');
bh4_lock_check(strpos($confirm, "'bh3_status_' . md5(") !== false, 'payment confirmation shares status lock');
bh4_lock_check(strpos($checkout, "'bh3_status_' . md5(") !== false, 'checkout shares booking status lock');
foreach (array($status,$checkout,$confirm) as $section) {
    bh4_lock_check(strpos($section, 'SELECT GET_LOCK(%s, 5)') !== false, 'lock acquired');
    bh4_lock_check(strpos($section, 'SELECT RELEASE_LOCK(%s)') !== false, 'lock released');
}
echo "PASS: payment/status lock identity and checkout shared lock (source-only)\n";
