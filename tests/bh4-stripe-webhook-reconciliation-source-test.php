<?php
/**
 * Static regression checks for webhook reconciliation ordering.
 * No WordPress bootstrap, Stripe calls, database writes or email delivery.
 */
$source = file_get_contents(dirname(__DIR__) . '/plugin/bubba-hub-manager/includes/class-bh-marketplace-settings.php');
function bh4_retry_assert($ok, $message) {
    if (!$ok) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
}
bh4_retry_assert(is_string($source), 'checkout source exists');
$start = strpos($source, 'public static function confirm_verified_test_payment( $event )');
$end = strpos($source, 'public static function stripe_status()', $start);
bh4_retry_assert($start !== false && $end !== false && $end > $start, 'isolated confirmation method');
$method = substr($source, $start, $end - $start);
$lock = strpos($method, 'SELECT GET_LOCK(%s, 5)');
$recheck = strpos($method, 'self::inspect_verified_checkout_payment( $event )', strpos($method, 'try {'));
$paid = strpos($method, "if ( \$record['payment_status'] === 'paid' )");
$notifyPaid = strpos($method, 'self::send_booking_confirmation_notifications', $paid);
$hold = strpos($method, "if ( \$record['booking_status'] !== 'reserved' )");
$ledger = strpos($method, "SELECT processing_status FROM");
$transition = strpos($method, "SET booking_status='confirmed', payment_status='paid'");
$notifyNew = strpos($method, 'self::send_booking_confirmation_notifications', $transition);
$unlock = strpos($method, 'SELECT RELEASE_LOCK(%s)');
bh4_retry_assert($lock !== false && $recheck > $lock, 'payment rechecked under booking lock');
bh4_retry_assert($paid > $recheck && $notifyPaid > $paid && $hold > $notifyPaid, 'already-paid retry resends only missing notifications before hold check');
bh4_retry_assert($ledger > $hold && $transition > $ledger && $notifyNew > $transition, 'new payment requires active hold and ledger before notification');
bh4_retry_assert($unlock > $notifyNew, 'lock released after confirmation path');
bh4_retry_assert(strpos($method, "reservation_expires_at > %s") !== false, 'expired holds fail closed');
bh4_retry_assert(strpos($source, "'duplicate' => \$result['duplicate']") !== false, 'duplicate delivery status is surfaced');
echo "PASS: static webhook reconciliation ordering (runtime retries unverified)\n";
