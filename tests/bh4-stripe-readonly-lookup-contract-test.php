<?php
define('ABSPATH',__DIR__);
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-stripe-readonly-verification-gate.php';
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-stripe-readonly-lookup-contract.php';
$b=array('id'=>42,'stripe_checkout_session_id'=>'cs_test_ABC123');
$s=array('id'=>'cs_test_ABC123','livemode'=>false,'currency'=>'gbp','amount_total'=>550,
 'metadata'=>array('bh3_booking_id'=>'42'),'status'=>'open','payment_status'=>'unpaid');
$body=json_encode($s);
$cases=array(
 array(200,$body,true),
 array(500,$body,false),
 array(404,$body,false),
 array(429,$body,false),
 array(200,'not-json',false),
 array(200,'[]',false),
 array(200,'',false),
 array(200,str_repeat('x',131073),false)
);
foreach($cases as $i=>$case) {
 $actual=BH4_Stripe_Readonly_Lookup_Contract::classify($case[0],$case[1],$b,550);
 if($actual['verified']!==$case[2]){exit("FAIL lookup case $i\n");}
}
$wrong=$s;$wrong['metadata']['bh3_booking_id']='999';
if(BH4_Stripe_Readonly_Lookup_Contract::classify(200,json_encode($wrong),$b,550)['verified']!==false) {
 exit("FAIL mismatched booking\n");
}
echo "PASS: 9 read-only Stripe lookup classification fixtures\n";
