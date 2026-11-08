<?php
/** Source-only regression guard: configured checkout form, not a hard-coded Form 7. */
$source = file_get_contents(dirname(__DIR__) . '/plugin/bubba-hub-manager/includes/class-bh-marketplace-settings.php');
function bh4_form_assert($ok, $message) {
    if (!$ok) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
}
bh4_form_assert(is_string($source), 'marketplace source exists');
foreach (array(
    "get_option( 'bh3_booking_form_id', 8 )",
    "absint( \$form->id ?? 0 ) !== \$form_id",
    "absint( \$form->id ?? 0 ) !== absint( get_option( 'bh3_booking_form_id', 8 ) )",
    "verify_booking_form_context_token(",
    "get_current_user_id()",
    "booking_context_token",
    "family_profile_ids",
    "booking_terms_consent",
    "share_child_details_consent",
    "do_shortcode( '[fluentform id="' . \$form_id . '"]' )",
    "update_option( 'bh3_booking_form_id', \$booking_form_id, false )",
    "fluentform_forms",
) as $marker) {
    bh4_form_assert(strpos($source, $marker) !== false, 'required configured-form marker: ' . $marker);
}
$start = strpos($source, 'public static function filter_form7_checkout_confirmation(');
$end = strpos($source, 'public static function filter_form7_checkbox_field(', $start);
bh4_form_assert($start !== false && $end > $start, 'confirmation handler isolated');
$handler = substr($source, $start, $end - $start);
bh4_form_assert(strpos($handler, "get_option( 'bh3_booking_form_id', 8 )") !== false, 'confirmation uses configured ID');
bh4_form_assert(strpos($handler, 'verify_booking_form_context_token(') < strpos($handler, 'start_bound_parent_test_checkout('), 'signed session context checked before checkout');
bh4_form_assert(strpos($handler, 'https://checkout.stripe.com/') !== false, 'redirect limited to Stripe Checkout');
echo "PASS: configured Form 8 checkout source guards (staging form content unverified)\n";
