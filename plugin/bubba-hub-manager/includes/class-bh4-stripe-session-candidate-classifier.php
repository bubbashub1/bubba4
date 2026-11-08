<?php
/** Pure read-only Stripe Checkout candidate classifier; no API or DB calls. */
if (!defined('ABSPATH')) { exit; }
final class BH4_Stripe_Session_Candidate_Classifier {
    public static function classify($sessions, $booking_id, $reference, $amount_pence) {
        if (!is_array($sessions) || !is_int($booking_id) || $booking_id < 1
            || !is_string($reference) || $reference === '' || !is_int($amount_pence) || $amount_pence < 1) {
            return array('action'=>'manual_review', 'reason'=>'invalid_input');
        }
        $matching = array();
        $conflicts = 0;
        foreach ($sessions as $session) {
            if (!is_array($session)) { return array('action'=>'manual_review', 'reason'=>'malformed_session'); }
            $metadata = $session['metadata'] ?? null;
            if (!is_array($metadata)) { return array('action'=>'manual_review', 'reason'=>'missing_metadata'); }
            $is_booking = ($metadata['bh3_booking_id'] ?? '') === (string)$booking_id
                || ($session['client_reference_id'] ?? '') === $reference;
            if (!$is_booking) { continue; }
            if (($metadata['bh3_booking_id'] ?? '') !== (string)$booking_id
                || ($session['client_reference_id'] ?? '') !== $reference
                || ($session['livemode'] ?? null) !== false
                || ($session['currency'] ?? null) !== 'gbp'
                || ($session['amount_total'] ?? null) !== $amount_pence
                || !is_string($session['id'] ?? null)
                || !preg_match('/^cs_test_[A-Za-z0-9_]+$/', $session['id'])) {
                $conflicts++;
                continue;
            }
            $matching[$session['id']] = $session;
        }
        if ($conflicts > 0) { return array('action'=>'manual_review', 'reason'=>'conflicting_session'); }
        if (count($matching) > 1) { return array('action'=>'manual_review', 'reason'=>'duplicate_sessions'); }
        if (count($matching) === 0) { return array('action'=>'reconcile_without_creation', 'reason'=>'no_proven_match'); }
        $session = reset($matching);
        if (($session['status'] ?? '') !== 'open' || ($session['payment_status'] ?? '') !== 'unpaid') {
            return array('action'=>'manual_review', 'reason'=>'non_open_or_payment_pending');
        }
        return array('action'=>'verify_unique_candidate', 'reason'=>'one_matching_unpaid_session', 'session_id'=>$session['id']);
    }
}
