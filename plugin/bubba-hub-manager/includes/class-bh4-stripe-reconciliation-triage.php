<?php
/**
 * Pure administrator triage decision from a verified test Checkout session.
 * No writes, automatic fulfillment, payment creation, refunds or retries.
 */
if (!defined('ABSPATH')) { exit; }
final class BH4_Stripe_Reconciliation_Triage {
 public static function assess($verification) {
  if(!is_array($verification)||($verification['verified']??null)!==true
    ||($verification['reason']??null)!=='matching_test_session') {
   return array('action'=>'manual_review','reason'=>'session_unverified');
  }
  $status=$verification['session_status']??null;
  $payment=$verification['payment_status']??null;
  if($status==='complete'&&$payment==='paid') {
   return array('action'=>'inspect_booking_and_webhook','reason'=>'stripe_reports_paid');
  }
  if($status==='open'&&$payment==='unpaid') {
   return array('action'=>'wait_or_contact_parent','reason'=>'checkout_still_open');
  }
  if($status==='expired'&&$payment==='unpaid') {
   return array('action'=>'review_expired_hold','reason'=>'checkout_expired_unpaid');
  }
  return array('action'=>'manual_review','reason'=>'ambiguous_session_state');
 }
}
