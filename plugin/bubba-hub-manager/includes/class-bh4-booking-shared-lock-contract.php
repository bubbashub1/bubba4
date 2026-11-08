<?php
/** Shared booking lock-name contract for future migration; not wired into BH3. */
if (!defined('ABSPATH')) { exit; }
final class BH4_Booking_Shared_Lock_Contract {
    public static function name($prefix, $booking_id) {
        if (!is_string($prefix) || $prefix === '' || !is_int($booking_id) || $booking_id < 1) {
            return '';
        }
        return 'bh4_booking_' . md5($prefix . ':' . $booking_id);
    }
    public static function sequence($booking_id, $lock_acquired, $reloaded, $stripe_reconciled, $state) {
        if (!is_int($booking_id) || $booking_id < 1 || $lock_acquired !== true) {
            return 'retry_without_mutation';
        }
        if ($reloaded !== true || $stripe_reconciled !== true) {
            return 'manual_review_without_mutation';
        }
        if ($state === 'verified_paid') { return 'confirm_payment_only'; }
        if ($state === 'verified_expired_unpaid') { return 'expire_hold_only'; }
        return 'manual_review_without_mutation';
    }
}
