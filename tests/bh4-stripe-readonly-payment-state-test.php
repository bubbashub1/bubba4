<?php
define('ABSPATH',__DIR__);
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-stripe-readonly-verification-gate.php';
$b=array('id'=>42,'stripe_checkout_session_id'=>'cs_test_ABC123');
$s=array('id'=>'cs_test_ABC123','livemode'=>false,'currency'=>'gbp','amount_total'=>550,
 'metadata'=>array('bh3_booking_id'=>'42'),'status'=>'complete','payment_status'=>'paid');
if(BH4_Stripe_Readonly_Verification_Gate::verify($b,$s,550)['verified']!==true){exit("FAIL valid paid complete\n");}
foreach(array(
 array('open','paid'),array('expired','paid'),
 array('complete','no_payment_required'),array('open','no_payment_required'),
 array('expired','no_payment_required')
) as $i=>$pair) {
 $bad=$s;$bad['status']=$pair[0];$bad['payment_status']=$pair[1];
 if(BH4_Stripe_Readonly_Verification_Gate::verify($b,$bad,550)['verified']!==false){
  exit("FAIL contradictory state $i\n");
 }
}
echo "PASS: paid complete and 5 fail-closed payment-state combinations\n";
