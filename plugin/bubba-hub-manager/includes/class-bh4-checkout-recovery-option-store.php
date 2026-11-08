<?php
/**
 * Unregistered WordPress storage adapter for checkout recovery.
 * Uses a separate option per booking, add_option is insert-only.
 * No plugin bootstrap registration or schema modifications.
 */
if (!defined('ABSPATH')) {exit;}
final class BH4_Checkout_Recovery_Option_Store {
 private static function key($booking_id) {
  if(!is_int($booking_id)||$booking_id<1) {return null;}
  return 'bh4_checkout_recovery_'.$booking_id;
 }
 public static function insert_once($record) {
  if(!is_array($record)||($record['state']??null)!=='creating'||
    !isset($record['booking_id'],$record['request_fingerprint'],$record['created_at'])||
    !is_int($record['booking_id'])||!is_int($record['created_at'])||
    !is_string($record['request_fingerprint'])||
    !preg_match('/^[a-f0-9]{64}$/D',$record['request_fingerprint'])||
    ($record['session_id']??null)!==null) {return false;}
  $key=self::key($record['booking_id']);
  if($key===null) {return false;}
  return add_option($key,$record,'','no')===true;
 }
 public static function read($booking_id) {
  $key=self::key($booking_id);
  if($key===null) {return null;}
  $record=get_option($key,null);
  return is_array($record)?$record:null;
 }
 public static function update_if_state($booking_id,$expected_state,$next_record) {
  // The atomic writer verifies ownership of the shared booking lock,
  // checks the persisted option bytes, and rejects stale transitions.
  // This adapter is not registered in the checkout runtime yet.
  if (!class_exists('BH4_Checkout_Recovery_Atomic_Writer')) {
   return false;
  }
  return BH4_Checkout_Recovery_Atomic_Writer::update($booking_id,$expected_state,$next_record) === true;
 }
}
