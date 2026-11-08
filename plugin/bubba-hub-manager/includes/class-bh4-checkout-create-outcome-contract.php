<?php
/** Pure retry decision after an uncertain Stripe Checkout create response. */
if (!defined('ABSPATH')) {exit;}
final class BH4_Checkout_Create_Outcome_Contract {
 public static function decide($transport_error,$http_status,$valid_session,$session_persisted) {
  if($transport_error===true) {
   return array('action'=>'reconcile_without_replay','reason'=>'unknown_remote_outcome');
  }
  if($transport_error!==false||!is_int($http_status)||!is_bool($valid_session)||!is_bool($session_persisted)) {
   return array('action'=>'manual_review','reason'=>'invalid_outcome');
  }
  if($http_status===200 && $valid_session===true && $session_persisted===true) {
   return array('action'=>'return_saved_checkout','reason'=>'verified_and_persisted');
  }
  if($http_status===200 && $valid_session===true && $session_persisted===false) {
   return array('action'=>'reconcile_without_replay','reason'=>'remote_session_not_saved');
  }
  return array('action'=>'reconcile_without_replay','reason'=>'remote_response_not_proven_safe');
 }
}
