<?php
/** Pure Stripe idempotency replay eligibility. No API requests. */
if (!defined('ABSPATH')) { exit; }
final class BH4_Stripe_Idempotency_Replay_Contract {
    public static function decide($booking_status, $payment_status, $saved_session, $request_fingerprint_matches,
        $elapsed_seconds, $lookup_state) {
        if ($booking_status !== 'reserved' || $payment_status !== 'pending') {
            return array('action'=>'block', 'reason'=>'booking_not_eligible');
        }
        if (!is_bool($request_fingerprint_matches) || !is_int($elapsed_seconds) || $elapsed_seconds < 0
            || !is_string($saved_session) || !is_string($lookup_state)) {
            return array('action'=>'manual_review', 'reason'=>'invalid_input');
        }
        if ($saved_session !== '') {
            return array('action'=>'verify_saved_session', 'reason'=>'session_already_recorded');
        }
        if ($lookup_state === 'verified_existing_session') {
            return array('action'=>'verify_and_persist_existing', 'reason'=>'session_found');
        }
        if (!$request_fingerprint_matches) {
            return array('action'=>'manual_review', 'reason'=>'request_parameters_changed');
        }
        // Do not assume Stripe's idempotency retention window is guaranteed.
        // Any ambiguous creation outcome needs a verified Stripe reconciliation.
        if ($lookup_state === 'ambiguous_timeout' || $lookup_state === 'not_found'
            || $lookup_state === 'lookup_failed' || $lookup_state === 'unknown') {
            return array('action'=>'reconcile_without_replay', 'reason'=>'creation_outcome_unproven');
        }
        return array('action'=>'manual_review', 'reason'=>'unrecognised_lookup_state');
    }
}
