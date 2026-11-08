<?php
/**
 * Fail-closed eligibility for administrator-triggered read-only Stripe lookup.
 * An ambiguous create timeout without a persisted session ID cannot be
 * reconciled through GET /checkout/sessions/{id}; never replay create.
 */
if (!defined('ABSPATH')) { exit; }
final class BH4_Stripe_Lookup_Eligibility {
 public static function assess($booking,$recovery) {
  if(!is_array($booking)||!is_array($recovery)
   ||!isset($booking['id'],$recovery['booking_id'])
   ||!is_int($booking['id'])||$booking['id']<1
   ||!is_int($recovery['booking_id'])||$booking['id']!==$recovery['booking_id']) {
   return array('allowed'=>false,'reason'=>'booking_identity_mismatch');
  }
  if(!in_array($recovery['state']??null,array('creating','needs_reconciliation','manual_review','saved'),true)) {
   return array('allowed'=>false,'reason'=>'invalid_recovery_state');
  }
  $id=$booking['stripe_checkout_session_id']??null;
  if(!is_string($id)||!preg_match('/^cs_test_[A-Za-z0-9_]+$/D',$id)) {
   return array('allowed'=>false,'reason'=>'missing_persisted_session_manual_stripe_investigation');
  }
  $recorded=$recovery['session_id']??null;
  if($recorded!==null&&$recorded!==$id) {
   return array('allowed'=>false,'reason'=>'conflicting_session_ids');
  }
  return array('allowed'=>true,'reason'=>'known_test_session_read_only_lookup');
 }
}
