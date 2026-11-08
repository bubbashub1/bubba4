<?php
/**
 * Unregistered atomic wp_options compare-and-swap writer.
 * Caller must hold the existing bh3_status_ lock on this DB connection.
 * No new tables, hooks, cron or checkout requests.
 */
if(!defined('ABSPATH')) {exit;}
final class BH4_Checkout_Recovery_Atomic_Writer {
 public static function update($booking_id,$expected_state,$next_record) {
  global $wpdb;
  if(!class_exists('BH4_Checkout_Recovery_CAS_Contract')||
    !is_int($booking_id)||$booking_id<1||!is_string($expected_state)||
    !is_array($next_record)||!isset($wpdb->options,$wpdb->prefix)) {return false;}
  $lock='bh3_status_'.md5($wpdb->prefix.':'.$booking_id);
  // Verify ownership, not merely existence, of the advisory lock.
  $owner=$wpdb->get_var($wpdb->prepare('SELECT IS_USED_LOCK(%s)',$lock));
  $connection=$wpdb->get_var('SELECT CONNECTION_ID()');
  if($owner===null||$connection===null||(string)$owner!==(string)$connection) {return false;}
  $name='bh4_checkout_recovery_'.$booking_id;
  // Bypass get_option cache: the CAS must compare the actual persisted bytes.
  $raw=$wpdb->get_var($wpdb->prepare("SELECT option_value FROM `{$wpdb->options}` WHERE option_name=%s LIMIT 1",$name));
  if(!is_string($raw)) {return false;}
  $before=maybe_unserialize($raw);
  if(!is_array($before)||($before['booking_id']??null)!==$booking_id||
    !BH4_Checkout_Recovery_CAS_Contract::assess($before,$next_record,$expected_state,true)) {return false;}
  $next=maybe_serialize($next_record);
  if(!is_string($next)) {return false;}
  $changed=$wpdb->query($wpdb->prepare(
   "UPDATE `{$wpdb->options}` SET option_value=%s WHERE option_name=%s AND BINARY option_value=BINARY %s",
   $next,$name,$raw
  ));
  if($changed!==1) {return false;}
  wp_cache_delete($name,'options');
  wp_cache_delete('notoptions','options');
  return true;
 }
}
