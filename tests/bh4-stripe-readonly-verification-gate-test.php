<?php
define('ABSPATH',__DIR__);
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-stripe-readonly-verification-gate.php';
$b=array('id'=>42,'stripe_checkout_session_id'=>'cs_test_ABC123');
$s=array('id'=>'cs_test_ABC123','livemode'=>false,'currency'=>'gbp','amount_total'=>550,
 'metadata'=>array('bh3_booking_id'=>'42'),'status'=>'open','payment_status'=>'unpaid');
$good=BH4_Stripe_Readonly_Verification_Gate::verify($b,$s,550);
if($good['verified']!==true){exit("FAIL valid test session\n");}
$cases=array(
 array('id','cs_test_WRONG'),
 array('livemode',true),
 array('currency','usd'),
 array('amount_total',500),
 array('metadata',array('bh3_booking_id'=>'43')),
 array('status','mystery'),
 array('payment_status','mystery')
);
foreach($cases as $i=>$case){
 $bad=$s;$bad[$case[0]]=$case[1];
 if(BH4_Stripe_Readonly_Verification_Gate::verify($b,$bad,550)['verified']!==false){
  exit("FAIL mismatch case $i\n");
 }
}
if(BH4_Stripe_Readonly_Verification_Gate::verify($b,$s,0)['verified']!==false){
 exit("FAIL invalid amount\n");
}
if(BH4_Stripe_Readonly_Verification_Gate::verify(array('id'=>42,'stripe_checkout_session_id'=>'cs_live_X'),$s,550)['verified']!==false){
 exit("FAIL live booking session\n");
}
echo "PASS: 10 Stripe session verification fixtures\n";
