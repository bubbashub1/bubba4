<?php
/** Source-only regression for one-payment checkout and conservative hold expiry. */
$source = file_get_contents(dirname(__DIR__) . '/plugin/bubba-hub-manager/includes/class-bh-marketplace-settings.php');
function bh4_fee_assert($ok, $label) {
    if (!$ok) { fwrite(STDERR, "FAIL: $label\n"); exit(1); }
}
bh4_fee_assert(is_string($source), 'marketplace source readable');
$required = array(
    "'unit_amount' => \$parent_total",
    "'quantity' => 1",
    "'application_fee_amount' => \$customer_fee",
    "'transfer_data' => array( 'destination' => \$account )",
    "'Idempotency-Key' => 'bh3-test-checkout-'",
    "'amount_total'] ?? -1 ) !== \$parent_total",
    "\$gross_pence = \$unit_pence * \$places",
    "\$total_pence = (int) ceil(",
    "time() + 35 * MINUTE_IN_SECONDS",
    "time() + 30 * MINUTE_IN_SECONDS",
    "booking_status IN ('reserved','confirmed','enrolled')",
    "if ( \$target_status === 'expired' )",
    "booking['payment_status'] !== 'pending'",
);
foreach ($required as $marker) {
    bh4_fee_assert(strpos($source, $marker) !== false, 'missing fee/expiry safeguard: ' . $marker);
}
bh4_fee_assert(strpos($source, "wp_schedule_event(") === false, 'no automatic expiry scheduler in marketplace source; must be verified separately');
echo "PASS: combined fee and conservative expiry source guard (expiry worker not verified)\n";
