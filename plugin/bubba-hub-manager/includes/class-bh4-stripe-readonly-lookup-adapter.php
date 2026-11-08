<?php
/**
 * Read-only Stripe test-mode session lookup adapter.
 * NOT registered in the plugin bootstrap; no automatic reconciliation.
 * Caller must provide a known persisted Checkout session ID and test secret.
 */
if (!defined('ABSPATH')) { exit; }
final class BH4_Stripe_Readonly_Lookup_Adapter {
 public static function retrieve($booking,$expected_total_pence,$test_secret) {
  if (!class_exists('BH4_Stripe_Readonly_Lookup_Contract') ||
      !is_array($booking)||!is_int($expected_total_pence)||$expected_total_pence<1 ||
      !is_string($test_secret)||!preg_match('/^sk_test_[A-Za-z0-9_]+$/D',$test_secret) ||
      !isset($booking['stripe_checkout_session_id'])||
      !is_string($booking['stripe_checkout_session_id'])||
      !preg_match('/^cs_test_[A-Za-z0-9_]+$/D',$booking['stripe_checkout_session_id'])) {
   return array('verified'=>false,'reason'=>'invalid_lookup_input');
  }
  $session_id=$booking['stripe_checkout_session_id'];
  $url='https://api.stripe.com/v1/checkout/sessions/'.rawurlencode($session_id);
  $response=wp_remote_get($url,array(
   'timeout'=>10,'redirection'=>0,
   'headers'=>array('Authorization'=>'Bearer '.$test_secret,'Accept'=>'application/json'),
   'sslverify'=>true
  ));
  if(is_wp_error($response)) {
   return array('verified'=>false,'reason'=>'stripe_transport_error');
  }
  $status=wp_remote_retrieve_response_code($response);
  $body=wp_remote_retrieve_body($response);
  return BH4_Stripe_Readonly_Lookup_Contract::classify($status,$body,$booking,$expected_total_pence);
 }
}
