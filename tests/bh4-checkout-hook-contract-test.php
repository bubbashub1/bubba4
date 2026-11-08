<?php
/**
 * Source-only regression guard for the existing Stripe test checkout owner.
 * Run: php tests/bh4-checkout-hook-contract-test.php
 * Does not bootstrap WordPress, send email, write data, or call Stripe.
 */
$path = dirname(__DIR__) . '/plugin/bubba-hub-manager/includes/class-bh-marketplace-settings.php';
$source = file_get_contents($path);
function bh4_checkout_assert($condition, $label) {
    if (!$condition) { fwrite(STDERR, "FAIL: $label\n"); exit(1); }
}
bh4_checkout_assert($source !== false, 'checkout owner source is available');
foreach (array(
    "add_shortcode( 'bh_parent_booking'",
    "add_filter( 'fluentform/submission_confirmation'",
    "register_rest_route( 'bubba-hub/v3', '/parent-bound-test-checkout'",
    "register_rest_route( 'bubba-hub/v3', '/parent-booking-bind'",
    "register_rest_route( 'bubba-hub/v3', '/stripe-test-webhook'",
    "BH3_ENABLE_STRIPE_TEST_WEBHOOK",
    "BH3_ENABLE_TEST_BOOKING_CONFIRMATION",
    "verify_test_webhook( \$request->get_body()",
    "hash_equals( \$expected, \$candidate )",
    "stripe_event_id=%s LIMIT 1",
    "payment_status='paid'",
    "send_booking_confirmation_notifications",
    "GET_LOCK(%s, 5)",
    "booking_status='reserved' AND payment_status='pending'",
) as $required) {
    bh4_checkout_assert(strpos($source, $required) !== false, 'required safety/ownership marker: ' . $required);
}
bh4_checkout_assert(preg_match("/register_test_webhook_route\(\).*?register_rest_route\( 'bubba-hub\/v3', '\/stripe-test-webhook'/s", $source) === 1, 'webhook route is registered through gated method');
bh4_checkout_assert(strpos($source, "'permission_callback' => '__return_true'") !== false, 'Stripe webhook allows unsigned transport and validates signature in callback');
bh4_checkout_assert(strpos($source, "get_option( 'bh3_booking_form_id', 8 )") !== false, 'configured checkout form defaults to 8');
echo "PASS: checkout ownership and webhook source guardrails present (not runtime payment verification)\n";
