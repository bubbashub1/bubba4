<?php
define('ABSPATH',__DIR__);
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-stripe-reconciliation-triage.php';
$cases=array(
 array(array('verified'=>false),'manual_review'),
 array(array('verified'=>true,'reason'=>'unexpected'),'manual_review'),
 array(array('verified'=>true,'reason'=>'matching_test_session','session_status'=>'complete','payment_status'=>'paid'),'inspect_booking_and_webhook'),
 array(array('verified'=>true,'reason'=>'matching_test_session','session_status'=>'open','payment_status'=>'unpaid'),'wait_or_contact_parent'),
 array(array('verified'=>true,'reason'=>'matching_test_session','session_status'=>'expired','payment_status'=>'unpaid'),'review_expired_hold'),
 array(array('verified'=>true,'reason'=>'matching_test_session','session_status'=>'complete','payment_status'=>'unpaid'),'manual_review'),
 array(array('verified'=>true,'reason'=>'matching_test_session','session_status'=>'open','payment_status'=>'paid'),'manual_review'),
 array(array('verified'=>true,'reason'=>'matching_test_session','session_status'=>'expired','payment_status'=>'paid'),'manual_review')
);
foreach($cases as $i=>$case){
 $result=BH4_Stripe_Reconciliation_Triage::assess($case[0]);
 if($result['action']!==$case[1]){exit("FAIL triage case $i\n");}
}
echo "PASS: 8 non-mutating reconciliation triage scenarios\n";
