<?php
/**
 * Pure fail-closed classifier for read-only Stripe Checkout GET responses.
 * Caller must authenticate to Stripe in test mode and supply the response.
 * No network, database writes, payment creation, or automatic retry.
 */
if (!defined('ABSPATH')) { exit; }
final class BH4_Stripe_Readonly_Lookup_Contract {
 public static function classify($http_status,$body,$booking,$expected_total_pence) {
  if (!is_int($http_status)||!is_string($body)||strlen($body)>131072
   ||!is_array($booking)||!is_int($expected_total_pence)||$expected_total_pence<1) {
   return array('verified'=>false,'reason'=>'invalid_lookup_input');
  }
  if ($http_status!==200) {
   return array('verified'=>false,'reason'=>'stripe_lookup_unavailable');
  }
  $session=json_decode($body,true);
  if (json_last_error()!==JSON_ERROR_NONE||!is_array($session)) {
   return array('verified'=>false,'reason'=>'invalid_stripe_json');
  }
  return BH4_Stripe_Readonly_Verification_Gate::verify($booking,$session,$expected_total_pence);
 }
}
