<?php
/** Pure, read-only candidate planner for future booking expiry reconciliation. */
if (!defined('ABSPATH')) { exit; }
final class BH4_Expiry_Candidate_Planner {
    public static function plan($rows, $now, $limit = 25) {
        if (!is_array($rows) || !is_int($now) || $now < 1 || !is_int($limit) || $limit < 1 || $limit > 100) {
            return array('ok' => false, 'candidates' => array(), 'reason' => 'invalid_input');
        }
        $candidates = array();
        foreach ($rows as $row) {
            if (!is_array($row) || !isset($row['id'], $row['booking_status'], $row['payment_status'], $row['expires_at'])) { continue; }
            if (!is_int($row['id']) || $row['id'] < 1 || !is_int($row['expires_at']) || $row['expires_at'] < 1) { continue; }
            if ($row['booking_status'] !== 'reserved' || $row['payment_status'] !== 'pending' || $row['expires_at'] > $now) { continue; }
            $candidates[] = array('booking_id' => $row['id'], 'action' => 'reconcile_only');
            if (count($candidates) >= $limit) { break; }
        }
        return array('ok' => true, 'candidates' => $candidates, 'reason' => 'requires_stripe_and_locked_recheck');
    }
}
