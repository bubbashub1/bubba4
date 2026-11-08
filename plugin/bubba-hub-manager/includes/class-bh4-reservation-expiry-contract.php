<?php
/**
 * Pure, fail-closed decision contract for a future abandoned-booking worker.
 * This does not query Stripe, register a cron job, or update any booking.
 */
if (!defined('ABSPATH')) { exit; }
final class BH4_Reservation_Expiry_Contract {
    public static function decide($booking_status, $payment_status, $expires_at, $now, $stripe_status) {
        if (!is_int($expires_at) || !is_int($now) || $expires_at < 1 || $now < 1) {
            return array('action' => 'manual_review', 'reason' => 'invalid_timestamp');
        }
        if ($booking_status !== 'reserved') {
            return array('action' => 'no_change', 'reason' => 'not_reserved');
        }
        if ($payment_status === 'paid') {
            return array('action' => 'no_change', 'reason' => 'already_paid');
        }
        if ($payment_status !== 'pending') {
            return array('action' => 'manual_review', 'reason' => 'unknown_payment_state');
        }
        if ($now < $expires_at) {
            return array('action' => 'no_change', 'reason' => 'hold_active');
        }
        if ($stripe_status === 'no_checkout_session') {
            return array('action' => 'eligible_after_locked_recheck', 'reason' => 'unpaid_no_checkout');
        }
        if ($stripe_status === 'expired_unpaid') {
            return array('action' => 'eligible_after_locked_recheck', 'reason' => 'stripe_expired_unpaid');
        }
        if ($stripe_status === 'paid' || $stripe_status === 'complete_paid') {
            return array('action' => 'manual_review', 'reason' => 'late_payment_reconcile');
        }
        return array('action' => 'manual_review', 'reason' => 'stripe_unresolved');
    }
}
