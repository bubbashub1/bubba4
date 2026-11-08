<?php
/** Pure decision gate after Stripe checkout creation times out. No network or writes. */
if (!defined('ABSPATH')) { exit; }
final class BH4_Checkout_Timeout_Recovery_Gate {
 public static function assess($booking_status,$payment_status,$session_count,$verified_session_count,
  $lookup_complete,$request_fingerprint_matches,$shared_lock_held) {
  $blocked=array('action'=>'manual_review','reason'=>'uncertain_checkout_state');
  if($booking_status!=='reserved'||$payment_status!=='pending') {
   return array('action'=>'block','reason'=>'booking_changed');
  }
  if($shared_lock_held!==true) {return array('action'=>'block','reason'=>'booking_lock_missing');}
  if($lookup_complete!==true||$request_fingerprint_matches!==true) {return $blocked;}
  if(!is_int($session_count)||!is_int($verified_session_count)||$session_count<0||
    $verified_session_count<0||$verified_session_count>$session_count) {return $blocked;}
  if($session_count===1 && $verified_session_count===1) {
   return array('action'=>'persist_verified_existing','reason'=>'single_verified_session');
  }
  if($session_count>1) {return array('action'=>'manual_review','reason'=>'duplicate_session_candidates');}
  // Even a complete search with zero matches does not prove Stripe did not
  // create a session during an ambiguous timeout. Never issue a new create.
  if($session_count===0) {return array('action'=>'reconcile_without_replay','reason'=>'no_session_found_but_timeout_ambiguous');}
  return $blocked;
 }
}
