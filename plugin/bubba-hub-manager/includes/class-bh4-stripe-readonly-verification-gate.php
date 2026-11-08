<?php
/** Pure verification gate for Stripe Checkout session reconciliation.
 * Does not call Stripe, create sessions, or modify payment records.
 */
if (!defined('ABSPATH')) { exit; }
final class BH4_Stripe_Readonly_Verification_Gate {
 public static function verify($booking, $session, $expected_total_pence) {
  if (!is_array($booking)||!is_array($session)||!is_int($expected_total_pence)
   ||$expected_total_pence<1||!isset($booking['id'],$booking['stripe_checkout_session_id'])
   ||!is_int($booking['id'])||$booking['id']<1
   ||!is_string($booking['stripe_checkout_session_id'])
   ||!preg_match('/^cs_test_[A-Za-z0-9_]+$/D',$booking['stripe_checkout_session_id'])) {
   return array('verified'=>false,'reason'=>'invalid_booking_or_amount');
  }
  if (($session['id']??null)!==$booking['stripe_checkout_session_id']
   ||($session['livemode']??null)!==false
   ||($session['currency']??null)!=='gbp'
   ||($session['amount_total']??null)!==$expected_total_pence
   ||!isset($session['metadata'])||!is_array($session['metadata'])
   ||($session['metadata']['bh3_booking_id']??null)!==(string)$booking['id']) {
   return array('verified'=>false,'reason'=>'session_mismatch');
  }
  if (!in_array($session['status']??null,array('open','complete','expired'),true)
   ||!in_array($session['payment_status']??null,array('paid','unpaid','no_payment_required'),true)) {
   return array('verified'=>false,'reason'=>'invalid_stripe_status');
  }
  // Verification is not payment confirmation. Webhook and booking state
  // still govern fulfillment; this result only supports human review.
  return array('verified'=>true,'reason'=>'matching_test_session',
   'session_status'=>$session['status'],'payment_status'=>$session['payment_status']);
 }
}
