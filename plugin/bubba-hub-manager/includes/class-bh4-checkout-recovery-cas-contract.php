<?php
/**
 * Pure validation for an atomic option-row compare-and-swap transition.
 * The caller must hold the shared booking lock and use an SQL WHERE
 * matching both option_name and the exact previously read option_value.
 */
if(!defined('ABSPATH')) {exit;}
final class BH4_Checkout_Recovery_CAS_Contract {
 public static function assess($before,$after,$expected_state,$lock_held) {
  if($lock_held!==true||!is_array($before)||!is_array($after)||
    !is_string($expected_state)||($before['state']??null)!==$expected_state) {return false;}
  foreach(array('booking_id','request_fingerprint','created_at') as $key) {
   if(!array_key_exists($key,$before)||!array_key_exists($key,$after)||
      $before[$key]!==$after[$key]) {return false;}
  }
  if(!is_int($before['booking_id'])||$before['booking_id']<1||
    !is_int($before['created_at'])||$before['created_at']<1||
    !is_string($before['request_fingerprint'])||
    !preg_match('/^[a-f0-9]{64}$/D',$before['request_fingerprint'])) {return false;}
  $states=array(
   'creating'=>array('needs_reconciliation','saved'),
   'needs_reconciliation'=>array('manual_review','saved'),
   'manual_review'=>array('saved'),
   'saved'=>array()
  );
  if(!in_array($after['state']??null,$states[$expected_state]??array(),true)) {return false;}
  if(($after['state']??null)==='saved') {
   return is_string($after['session_id']??null)&&
     preg_match('/^cs_test_[A-Za-z0-9_]+$/D',$after['session_id'])===1;
  }
  return ($after['session_id']??null)===null&&($before['session_id']??null)===null;
 }
}
