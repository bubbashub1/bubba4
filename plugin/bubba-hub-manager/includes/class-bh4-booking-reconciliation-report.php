<?php
/** Privacy-minimised read-only booking reconciliation report formatter. */
if (!defined('ABSPATH')) { exit; }
final class BH4_Booking_Reconciliation_Report {
    public static function build($items, $limit = 50) {
        if (!is_array($items) || !is_int($limit) || $limit < 1 || $limit > 100) {
            return array('ok'=>false,'rows'=>array(),'reason'=>'invalid_input');
        }
        $rows = array();
        $allowed = array('reconcile_without_creation','manual_review','verify_unique_candidate',
            'verify_saved_session','retry_without_mutation','block');
        foreach ($items as $item) {
            if (!is_array($item) || !isset($item['booking_id'],$item['action'],$item['reason'])
                || !is_int($item['booking_id']) || $item['booking_id'] < 1
                || !is_string($item['action']) || !in_array($item['action'],$allowed,true)
                || !is_string($item['reason']) || !preg_match('/^[a-z0-9_]{1,64}$/',$item['reason'])) {
                return array('ok'=>false,'rows'=>array(),'reason'=>'untrusted_row');
            }
            $rows[] = array('booking_id'=>$item['booking_id'],'action'=>$item['action'],'reason'=>$item['reason']);
            if (count($rows) >= $limit) { break; }
        }
        return array('ok'=>true,'rows'=>$rows,'reason'=>'read_only_review');
    }
}
