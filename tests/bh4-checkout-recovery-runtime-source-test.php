<?php
$source=file_get_contents(dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh-marketplace-settings.php');
$start=strpos($source,'function create_test_checkout(');
$end=strpos($source,'Internal-only signed Stripe event verifier',$start);
if($start===false||$end===false){exit("FAIL checkout handler not found\n");}
$body=substr($source,$start,$end-$start);
$required=array(
 "BH4_ENABLE_CHECKOUT_RECOVERY",
 "BH4_Checkout_Recovery_Option_Store::insert_once",
 "BH4_Checkout_Recovery_Option_Store::update_if_state",
 "BH4_Checkout_Recovery_Record_Contract::transition",
 "checkout_reconciliation_required",
 "wp_remote_post( 'https://api.stripe.com/v1/checkout/sessions'"
);
foreach($required as $needle){if(strpos($body,$needle)===false){exit("FAIL missing $needle\n");}}
if(strpos($body,'BH4_Checkout_Recovery_Option_Store::insert_once')>
 strpos($body,"wp_remote_post( 'https://api.stripe.com/v1/checkout/sessions'")) {
 exit("FAIL record created after Stripe request\n");
}
echo "PASS: staging opt-in recovery record precedes Stripe request\n";
