<?php
/** Pure Stripe Checkout session recovery verification. No HTTP, secrets or DB writes. */
if (!defined('ABSPATH')) { exit; }
final class BH4_Stripe_Session_Recovery_Contract {
    public static function verify($session, $booking_id, $reference, $expected_pence, $now) {
        if (!is_array($session) || !is_int($booking_id) || $booking_id < 1
            || !is_string($reference) || $reference === '' || !is_int($expected_pence) || $expected_pence < 1
            || !is_int($now) || $now < 1) {
            return array('ok'=>false,'reason'=>'invalid_input');
        }
        if (!isset($session['id'],$session['livemode'],$session['currency'],$session['amount_total'],
            $session['client_reference_id'],$session['expires_at'],$session['payment_status'],$session['status'],$session['metadata'])) {
            return array('ok'=>false,'reason'=>'missing_fields');
        }
        if (!is_string($session['id']) || !preg_match('/^cs_test_[A-Za-z0-9_]+$/',$session['id'])
            || $session['livemode'] !== false || $session['currency'] !== 'gbp'
            || !is_int($session['amount_total']) || $session['amount_total'] !== $expected_pence
            || $session['client_reference_id'] !== $reference
            || !is_array($session['metadata'])
            || ($session['metadata']['bh3_booking_id'] ?? '') !== (string)$booking_id) {
            return array('ok'=>false,'reason'=>'session_mismatch');
        }
        if (!is_int($session['expires_at']) || $session['expires_at'] <= $now
            || $session['status'] !== 'open' || $session['payment_status'] !== 'unpaid') {
            return array('ok'=>false,'reason'=>'session_not_reusable');
        }
        return array('ok'=>true,'reason'=>'verified_open_unpaid_test_session','session_id'=>$session['id']);
    }
}
