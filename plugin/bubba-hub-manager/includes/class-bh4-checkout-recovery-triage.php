<?php
/** Pure, privacy-safe triage labels for checkout recovery records. */
if(!defined('ABSPATH')) {exit;}
final class BH4_Checkout_Recovery_Triage {
 public static function classify($record,$now) {
  if(!is_array($record)||!is_int($now)||$now<1||
    !isset($record['state'],$record['created_at'])||
    !is_int($record['created_at'])||$record['created_at']<1||
    $record['created_at']>$now) {
   return array('priority'=>'review','label'=>'Invalid recovery record');
  }
  switch($record['state']) {
   case 'saved':
    return array('priority'=>'resolved','label'=>'Checkout session recorded');
   case 'manual_review':
    return array('priority'=>'urgent','label'=>'Manual investigation required');
   case 'needs_reconciliation':
    return array('priority'=>'urgent','label'=>'Verify Stripe session before retry');
   case 'creating':
    return $now-$record['created_at']>=180?
     array('priority'=>'urgent','label'=>'Interrupted checkout may need investigation'):
     array('priority'=>'pending','label'=>'Checkout creation in progress');
   default:
    return array('priority'=>'review','label'=>'Unknown recovery state');
  }
 }
}
