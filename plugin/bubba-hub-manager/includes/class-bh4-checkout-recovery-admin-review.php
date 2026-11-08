<?php
/**
 * Read-only admin page for checkout recovery options.
 * Unregistered until the BH4 bootstrap explicitly includes and registers it.
 * Never prints customer, child, email, payment secret or request fingerprint.
 */
if(!defined('ABSPATH')) {exit;}
final class BH4_Checkout_Recovery_Admin_Review {
 public static function register() {
  // A Tools submenu would open without the required nonce. Register a
  // hidden page instead; the administrator notice supplies a signed link.
  add_submenu_page(null,'Checkout Recovery Review','Checkout Recovery Review',
   'manage_options','bh4-checkout-recovery',array(__CLASS__,'render'));
 }
 public static function render() {
  if(!is_user_logged_in()||!current_user_can('manage_options')) {
   wp_die('Not authorised.');
  }
  $nonce=isset($_GET['_wpnonce'])&&is_string($_GET['_wpnonce'])?
   sanitize_text_field(wp_unslash($_GET['_wpnonce'])):'';
  $auth=BH4_Reconciliation_Access_Contract::authorize(
   true,true,is_admin(),$_SERVER['REQUEST_METHOD']??'',wp_verify_nonce($nonce,'bh4_recovery_review')===1
  );
  if(empty($auth['allowed'])) {
   echo '<div class="wrap"><h1>Checkout Recovery Review</h1><p>Read-only review requires a valid link.</p></div>';
   return;
  }
  require_once __DIR__ . '/class-bh4-checkout-recovery-triage.php';
  global $wpdb;
  $pattern=$wpdb->esc_like('bh4_checkout_recovery_').'%';
  $values=$wpdb->get_results($wpdb->prepare(
   "SELECT option_name,option_value FROM `{$wpdb->options}` WHERE option_name LIKE %s ORDER BY option_id DESC LIMIT 50",
   $pattern
  ),ARRAY_A);
  echo '<div class="wrap"><h1>Checkout Recovery Review</h1><p>Read-only. Do not retry or refund from this page.</p>';
  echo '<table class="widefat striped"><thead><tr><th>Booking ID</th><th>Priority</th><th>Investigation guidance</th><th>Recovery state</th><th>Created (UTC)</th><th>Session recorded</th></tr></thead><tbody>';
  foreach((array)$values as $row) {
   $id=(string)($row['option_name']??'');
   if(!preg_match('/^bh4_checkout_recovery_([1-9][0-9]*)$/D',$id,$match)) {continue;}
   $record=maybe_unserialize($row['option_value']??'');
   if(!is_array($record)||(int)($record['booking_id']??0)!==(int)$match[1]) {continue;}
   $state=$record['state']??'unknown';
   if(!in_array($state,array('creating','needs_reconciliation','manual_review','saved'),true)) {$state='unknown';}
   $created=isset($record['created_at'])&&is_int($record['created_at'])&&$record['created_at']>0?
    gmdate('Y-m-d H:i',$record['created_at']):'Unknown';
   $session=!empty($record['session_id'])?'Yes':'No';
   $triage=BH4_Checkout_Recovery_Triage::classify($record,time());
   echo '<tr><td>'.esc_html($match[1]).'</td><td>'.esc_html($triage['priority']).
    '</td><td>'.esc_html($triage['label']).'</td><td>'.esc_html($state).
    '</td><td>'.esc_html($created).'</td><td>'.esc_html($session).'</td></tr>';
  }
  echo '</tbody></table></div>';
 }
}
