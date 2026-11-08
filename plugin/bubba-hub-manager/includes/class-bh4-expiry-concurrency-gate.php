<?php
/** Final fail-closed gate for a future expiry writer; pure, no side effects. */
if(!defined('ABSPATH')) {exit;}
final class BH4_Expiry_Concurrency_Gate {
 public static function assess($decision,$evidence) {
  $blocked=array('allowed'=>false,'reason'=>'insufficient_verified_evidence');
  if(!is_array($decision)||!is_array($evidence)||
     ($decision['action']??null)!=='eligible_after_locked_recheck') {return $blocked;}
  foreach(array('shared_booking_lock_held','booking_reloaded_under_lock',
    'booking_still_reserved_pending','hold_expired','no_successful_payment_event',
    'stripe_terminal_unpaid_verified','checkout_creation_not_in_flight') as $key) {
    if(($evidence[$key]??null)!==true) {return array('allowed'=>false,'reason'=>'missing_'.$key);}
  }
  return array('allowed'=>true,'reason'=>'eligible_for_atomic_guarded_update');
 }
}
