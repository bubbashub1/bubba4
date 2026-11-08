<?php
define('ABSPATH', __DIR__);
require dirname(__DIR__) . '/plugin/bubba-hub-manager/includes/class-bh4-stripe-session-recovery-contract.php';
$valid=array('id'=>'cs_test_abc123','livemode'=>false,'currency'=>'gbp','amount_total'=>1042,
 'client_reference_id'=>'ref-1','expires_at'=>2000,'payment_status'=>'unpaid','status'=>'open',
 'metadata'=>array('bh3_booking_id'=>'42'));
$cases=array(
 array('valid',array(),true),
 array('live_mode',array('livemode'=>true),false),
 array('wrong_currency',array('currency'=>'usd'),false),
 array('wrong_amount',array('amount_total'=>1043),false),
 array('wrong_reference',array('client_reference_id'=>'other'),false),
 array('wrong_booking',array('metadata'=>array('bh3_booking_id'=>'43')),false),
 array('paid',array('payment_status'=>'paid'),false),
 array('complete',array('status'=>'complete'),false),
 array('expired',array('expires_at'=>999),false),
 array('invalid_id',array('id'=>'cs_live_abc'),false),
 array('missing_metadata',array('metadata'=>array()),false),
 array('non_integer_amount',array('amount_total'=>'1042'),false),
);
foreach($cases as $i=>$case) {
 $session=array_replace($valid,$case[1]);
 $result=BH4_Stripe_Session_Recovery_Contract::verify($session,42,'ref-1',1042,1000);
 if($result['ok']!==$case[2]) { fwrite(STDERR,"FAIL session recovery fixture ".($i+1)."\n");exit(1); }
}
echo "PASS: 12 Stripe session recovery verification fixtures (no network)\n";
