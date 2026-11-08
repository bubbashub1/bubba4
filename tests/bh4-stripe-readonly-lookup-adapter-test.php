<?php
define('ABSPATH',__DIR__);
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-stripe-readonly-verification-gate.php';
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-stripe-readonly-lookup-contract.php';
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-stripe-readonly-lookup-adapter.php';
$GLOBALS['bh4_requests']=array();
function wp_remote_get($url,$args){$GLOBALS['bh4_requests'][]=array($url,$args);return $GLOBALS['bh4_response'];}
function is_wp_error($value){return $value==='transport_error';}
function wp_remote_retrieve_response_code($r){return $r['status'];}
function wp_remote_retrieve_body($r){return $r['body'];}
$b=array('id'=>42,'stripe_checkout_session_id'=>'cs_test_ABC123');
$s=array('id'=>'cs_test_ABC123','livemode'=>false,'currency'=>'gbp','amount_total'=>550,
 'metadata'=>array('bh3_booking_id'=>'42'),'status'=>'open','payment_status'=>'unpaid');
$GLOBALS['bh4_response']=array('status'=>200,'body'=>json_encode($s));
$ok=BH4_Stripe_Readonly_Lookup_Adapter::retrieve($b,550,'sk_test_example');
if($ok['verified']!==true||count($GLOBALS['bh4_requests'])!==1){exit("FAIL valid read-only GET\n");}
list($url,$args)=$GLOBALS['bh4_requests'][0];
if($url!=='https://api.stripe.com/v1/checkout/sessions/cs_test_ABC123'
 ||$args['redirection']!==0||$args['sslverify']!==true||$args['timeout']>10){
 exit("FAIL safe GET request\n");
}
if(BH4_Stripe_Readonly_Lookup_Adapter::retrieve($b,550,'sk_live_wrong')['verified']!==false
 ||count($GLOBALS['bh4_requests'])!==1){exit("FAIL live key accepted\n");}
$GLOBALS['bh4_response']='transport_error';
if(BH4_Stripe_Readonly_Lookup_Adapter::retrieve($b,550,'sk_test_example')['verified']!==false){
 exit("FAIL transport error\n");
}
echo "PASS: Stripe test-mode read-only GET adapter fixtures\n";
