<?php
/** Pure checkout retry decision. Never initiates Stripe or updates booking state. */
if (!defined('ABSPATH')) { exit; }
final class BH4_Checkout_Retry_Contract {
    public static function decide($booking_status, $payment_status, $saved_session_id, $request_outcome, $stripe_lookup) {
        if ($booking_status !== 'reserved' || $payment_status !== 'pending') {
            return array('action'=>'block', 'reason'=>'booking_not_eligible');
        }
        if (!is_string($saved_session_id) || !is_string($request_outcome) || !is_string($stripe_lookup)) {
            return array('action'=>'manual_review', 'reason'=>'invalid_input');
        }
        if ($saved_session_id !== '') {
            return array('action'=>'reuse_existing_after_verification', 'reason'=>'session_recorded');
        }
        if ($request_outcome === 'not_attempted') {
            return array('action'=>'eligible_for_locked_creation', 'reason'=>'no_attempt');
        }
        if ($request_outcome === 'verified_success' && $stripe_lookup === 'matching_test_session_unpaid') {
            return array('action'=>'persist_verified_session_under_lock', 'reason'=>'verified_stripe_session');
        }
        if ($request_outcome === 'timeout' || $request_outcome === 'network_error' || $request_outcome === 'unknown_response') {
            if ($stripe_lookup === 'matching_test_session_unpaid') {
                return array('action'=>'persist_verified_session_under_lock', 'reason'=>'recovered_stripe_session');
            }
            return array('action'=>'reconcile_without_creation', 'reason'=>'ambiguous_stripe_outcome');
        }
        return array('action'=>'manual_review', 'reason'=>'unrecognised_stripe_state');
    }
}
