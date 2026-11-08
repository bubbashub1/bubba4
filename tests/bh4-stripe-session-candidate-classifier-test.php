<?php
define('ABSPATH', __DIR__);
require dirname(__DIR__) . '/plugin/bubba-hub-manager/includes/class-bh4-stripe-session-candidate-classifier.php';
$s=array('id'=>'cs_test_one','livemode'=>false,'currency'=>'gbp','amount_total'=>550,
 'client_reference_id'=>'booking-ref','metadata'=>array('bh3_booking_id'=>'42'),
 'status'=>'open','payment_status'=>'unpaid');
$other=array_replace($s,array('id'=>'cs_test_other','client_reference_id'=>'unrelated','metadata'=>array('bh3_booking_id'=>'43')));
$cases=array(
 array(array($s),'verify_unique_candidate'),
 array(array(),'reconcile_without_creation'),
 array(array($s,array_replace($s,array('id'=>'cs_test_two'))),'manual_review'),
 array(array($s,$other),'verify_unique_candidate'),
 array(array(array_replace($s,array('amount_total'=>551))),'manual_review'),
 array(array(array_replace($s,array('livemode'=>true))),'manual_review'),
 array(array(array_replace($s,array('payment_status'=>'paid'))),'manual_review'),
 array(array(array_replace($s,array('status'=>'expired'))),'manual_review'),
 array(array(array_replace($s,array('client_reference_id'=>'other'))),'manual_review'),
 array(array(array_replace($s,array('metadata'=>array('bh3_booking_id'=>'43')))),'manual_review'),
 array(array(array_replace($s,array('id'=>'cs_live_wrong'))),'manual_review'),
 array(array('malformed'),'manual_review'),
);
foreach($cases as $i=>$case) {
 $result=BH4_Stripe_Session_Candidate_Classifier::classify($case[0],42,'booking-ref',550);
 if($result['action']!==$case[1]) { fwrite(STDERR,"FAIL Stripe candidate fixture ".($i+1).": ".$result['action']."\n");exit(1); }
}
echo "PASS: 12 Stripe candidate duplicate/conflict fixtures\n";
